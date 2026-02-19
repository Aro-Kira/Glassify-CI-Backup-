// --- DOM ELEMENTS AND STATE ---
const btnCustomize = document.getElementById('btn-customize');
const btnStandard = document.getElementById('btn-standard');
const customWrapper = document.getElementById('custom-wrapper');
const standardWrapper = document.getElementById('standard-wrapper');
const priceBox = document.getElementById('price-box');
const standardSubtitle = document.getElementById('standard-subtitle');
const nextBtn = document.getElementById('next-btn');
const backBtn = document.getElementById('back-btn');
const backGroup = document.getElementById('back-group');
const step1 = document.getElementById('step-1');
const step2 = document.getElementById('step-2');
const step3 = document.getElementById('step-3');
// Breadcrumbs are now managed dynamically by updateDynamicBreadcrumbs()
const breadcrumbsContainer = document.getElementById('breadcrumbs-container');
const nextNote = document.getElementById('next-note');
const backNote = document.getElementById('back-note');
const inputHeight = document.getElementById('input-height');
const btnUnitHeight = document.getElementById('btn-unit-height');
const inputWidth = document.getElementById('input-width');
const btnUnitWidth = document.getElementById('btn-unit-width');
const inputH1 = document.getElementById('input-h1');
const btnUnitH1 = document.getElementById('btn-unit-h1');
const inputGroupH1 = document.getElementById('input-group-h1');
const inputH2 = document.getElementById('input-h2');
const btnUnitH2 = document.getElementById('btn-unit-h2');
const inputGroupH2 = document.getElementById('input-group-h2');
const shapeCards = document.querySelectorAll('.option-card[data-shape]');

// Modal elements
const uploadModal = document.getElementById('upload-modal');
const openModalBtn = document.getElementById('open-modal-btn');
const modalCloseBtn = document.getElementById('modal-close-btn');
const modalCancelBtn = document.getElementById('modal-cancel-btn');
const modalDoneBtn = document.getElementById('modal-done-btn');
const browseFilesBtn = document.getElementById('browse-files-btn');
const fileInput = document.getElementById('file-input');
const uploadedFilesContainer = document.getElementById('uploaded-files-container');
const dropzone = document.getElementById('dropzone');
let uploadedFiles = [];
const MAX_FILE_SIZE_MB = 25;


// --- APPLICATION STATE ---
let currentStep = 1;
let isStandardMode = false;
let dimensionsLocked = false; // Lock state for equalizing height and width

// Track the last step before entering review
let lastStepBeforeReview = 1;

// Pricing database - declared early to avoid temporal dead zone issues
let pricingDatabase = null;

// Store calculated price breakdown - declared early to avoid temporal dead zone issues
let priceBreakdown = {
    baseArea: 0,
    fieldPrices: {},
    total: 0
};

// Store the design image data globally for cart submission - declared early to avoid temporal dead zone
let currentDesignImageData = null;

// CUSTOM STATE VARIABLES
let currentShape = 'rectangle';
let currentGlassType = 'tempered';
let currentThickness = '6mm';
let currentEdgeWork = 'flat-polish';
let currentFrameType = 'vinyl';
// Corner radius in inches (applies to rectangle/square only)
// Can be a single number (when linked) or an object with individual corners: {topLeft, topRight, bottomLeft, bottomRight}
let currentCornerRadius = 0; // Default: single value (linked mode)
let cornerRadiusLinked = true; // "Link All" state - when true, all corners use same value
let currentDimensions = {
    height: { value: 45, unit: 'in' },
    width: { value: 35, unit: 'in' }
};

const unitMap = {
    'in': { name: 'Inches', toMm: 25.4 },
    'cm': { name: 'Centimeters', toMm: 10 },
    'mm': { name: 'Millimeters', toMm: 1 }
};

/**
 * Convert a value from any unit to millimeters
 * @param {number} value - The value to convert
 * @param {string} unit - The unit ('in', 'cm', 'mm')
 * @returns {number} Value in millimeters
 */
function convertToMm(value, unit) {
    const unitInfo = unitMap[unit.toLowerCase()] || unitMap['in'];
    return value * unitInfo.toMm;
}


// --- KONVA.JS VISUALIZATION LOGIC ---
// Default configuration values per KONVA_DEFAULT_OPTIONS_REFERENCE.md

var KONVA_CONTAINER_ID = 'konva-container';
const konvaWrapper = document.getElementById(KONVA_CONTAINER_ID);
const STAGE_SIZE = konvaWrapper ? konvaWrapper.offsetWidth : 0;

// Canvas Layout Constants - per KONVA_DEFAULT_OPTIONS_REFERENCE.md
const PADDING = 40; // Padding around drawing area (pixels)
const DRAWING_SIZE = STAGE_SIZE - PADDING * 2; // Available drawing area
const DIM_OFFSET = 15; // Offset for dimension lines from glass panel

// --- VISUAL CONFIGURATION ---
// Default glass type visual styles. These are SYSTEM DEFAULTS and will NOT be overridden by database configs.
// Synced with KONVA_DEFAULT_OPTIONS_REFERENCE.md
// CONSOLIDATED: Eliminated redundancy (case variations), added categories and visual indicators
let glassStyles = {
    // ========== BASIC CLEAR GLASS ==========
    'clear': { fill: '#E0F2F1', opacity: 0.6, isDefault: true, category: 'basic', indicator: '◇', description: 'Standard clear glass' },
    'Clear': { fill: '#E0F2F1', opacity: 0.6, isDefault: true, category: 'basic', indicator: '◇', description: 'Standard clear glass (capitalized)' },
    'ordinary': { fill: '#E0F2F1', opacity: 0.5, isDefault: true, category: 'basic', indicator: '◇', description: 'Ordinary clear glass' },
    'ultra clear': { fill: 'rgba(255, 255, 255, 0.1)', opacity: 0.5, isDefault: true, category: 'basic', indicator: '◇', description: 'Ultra clear glass with minimal color tint' },
    
    // ========== TEMPERED GLASS ==========
    'tempered': { fill: '#E0F2F1', opacity: 0.6, isDefault: true, category: 'tempered', indicator: '✓', description: 'Safety tempered glass' },
    'tempered: clear': { fill: 'rgba(255, 255, 255, 0.2)', opacity: 0.6, isDefault: true, category: 'tempered', indicator: '✓', description: 'Clear tempered glass' },
    'tempered: bronze': { fill: 'rgba(205, 127, 50, 0.3)', opacity: 0.6, isDefault: true, category: 'tempered', indicator: '✓', description: 'Bronze tinted tempered glass' },
    '10mm frosted tempered': { fill: '#FFFFFF', opacity: 0.6, isDefault: true, category: 'tempered', indicator: '✓', description: '10mm frosted tempered glass' },
    
    // ========== TINTED & COLOR GLASS ==========
    'tinted': { fill: '#546E7A', opacity: 0.7, isDefault: true, category: 'tinted', indicator: '⬤', description: 'General tinted glass' },
    'bronze': { fill: 'rgba(205, 127, 50, 0.4)', opacity: 0.7, isDefault: true, category: 'tinted', indicator: '⬤', description: 'Bronze tinted glass' },
    'light green': { fill: 'rgba(144, 238, 144, 0.4)', opacity: 0.7, isDefault: true, category: 'tinted', indicator: '⬤', description: 'Light green tinted glass' },
    'dark gray': { fill: 'rgba(105, 105, 105, 0.5)', opacity: 0.6, isDefault: true, category: 'tinted', indicator: '⬤', description: 'Dark gray tinted glass' },
    'euro gray': { fill: 'rgba(169, 169, 169, 0.5)', opacity: 0.7, isDefault: true, category: 'tinted', indicator: '⬤', description: 'European gray tinted glass' },
    'ford blue': { fill: 'rgba(70, 130, 180, 0.5)', opacity: 0.7, isDefault: true, category: 'tinted', indicator: '⬤', description: 'Ford blue tinted glass' },
    'smoked': { fill: '#808080', opacity: 0.6, isDefault: true, category: 'tinted', indicator: '⬤', description: 'Smoked gray glass' },
    
    // ========== FROSTED GLASS ==========
    'frosted': { fill: '#98dfffff', opacity: 0.6, isDefault: true, category: 'frosted', indicator: '❄', description: 'Standard frosted glass' },
    'fully frosted': { fill: '#FFFFFF', opacity: 0.95, isDefault: true, category: 'frosted', indicator: '❄', description: 'Fully frosted opaque glass' },
    'frosted (full or partial)': { fill: '#FFFFFF', opacity: 0.95, isDefault: true, category: 'frosted', indicator: '❄', description: 'Frosted - full or partial coverage' },
    'clear with frosted sticker': { fill: '#E0F2F1', opacity: 0.9, isDefault: true, category: 'frosted', indicator: '❄', description: 'Clear glass with frosted sticker' },
    
    // ========== LAMINATED GLASS ==========
    'laminated': { fill: '#CFD8DC', opacity: 0.95, isDefault: true, category: 'laminated', indicator: '≡', description: 'Standard laminated glass' },
    'laminated safety glass': { fill: '#CFD8DC', opacity: 0.95, isDefault: true, category: 'laminated', indicator: '≡', description: 'Laminated safety glass' },
    
    // ========== SAFETY GLASS ==========
    'safety glass': { fill: '#CFD8DC', opacity: 0.95, isDefault: true, category: 'safety', indicator: '⚔', description: 'Tempered safety glass' },
    'bulletproof': { fill: '#CFD8DC', opacity: 0.98, isDefault: true, category: 'safety', indicator: '⚔', description: 'Bulletproof safety glass' },
    
    // ========== INSULATED & ENERGY EFFICIENT ==========
    'double': { fill: '#B2DFDB', opacity: 0.9, isDefault: true, category: 'insulated', indicator: '⊞', description: 'Double-pane insulated glass' },
    'low-e': { fill: '#Dcedc8', opacity: 0.85, isDefault: true, category: 'insulated', indicator: '⊞', description: 'Low-emissivity energy efficient glass' },
    
    // ========== PATTERNED & SPECIALTY ==========
    'patterned': { fill: '#E8E8E8', opacity: 0.9, isDefault: true, category: 'patterned', indicator: '▦', description: 'Patterned decorative glass' },
    
    // ========== REFLECTIVE GLASS ==========
    'reflective': { fill: 'rgba(200, 200, 200, 0.6)', opacity: 0.85, isDefault: true, category: 'reflective', indicator: '◆', description: 'Reflective coated glass' },
    'reflective coatings': { fill: 'rgba(200, 200, 200, 0.6)', opacity: 0.85, isDefault: true, category: 'reflective', indicator: '◆', description: 'Reflective coatings' },
    'reflective: clear': { fill: 'rgba(255, 255, 255, 0.6)', opacity: 0.9, isDefault: true, category: 'reflective', indicator: '◆', description: 'Clear reflective glass' },
    'reflective: gray': { fill: 'rgba(169, 169, 169, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Gray reflective glass' },
    'reflective: light blue': { fill: 'rgba(173, 216, 230, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Light blue reflective glass' },
    'reflective: dark blue': { fill: 'rgba(0, 0, 139, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Dark blue reflective glass' },
    'reflective: light green': { fill: 'rgba(50, 205, 50, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Light green reflective glass' },
    'reflective: dark green': { fill: 'rgba(0, 100, 0, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Dark green reflective glass' },
    'reflective: light bronze': { fill: 'rgba(205, 127, 50, 0.6)', opacity: 0.8, isDefault: true, category: 'reflective', indicator: '◆', description: 'Light bronze reflective glass' },
    
    // ========== MIRROR TYPES ==========
    'mirror-clear': { fill: 'rgba(224, 242, 241, 0.9)', opacity: 0.95, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Clear mirror' },
    'mirror-bronze': { fill: 'rgba(205, 127, 50, 0.6)', opacity: 0.7, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Bronze mirror' },
    'mirror-grey': { fill: 'rgba(96, 125, 139, 0.5)', opacity: 0.6, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Grey mirror' },
    'mirror-grey-smoked': { fill: 'rgba(96, 125, 139, 0.5)', opacity: 0.6, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Grey smoked mirror' },
    'mirror-smoked': { fill: 'rgba(96, 125, 139, 0.5)', opacity: 0.6, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Smoked mirror' },
    'mirror-black': { fill: 'rgba(38, 50, 56, 0.7)', opacity: 0.8, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Black mirror' },
    'copperfree mirror': { fill: 'rgba(192, 192, 192, 0.8)', opacity: 0.9, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Copper free and lead free mirror' },
    'copper free and lead free mirror': { fill: 'rgba(192, 192, 192, 0.8)', opacity: 0.9, isDefault: true, category: 'mirror', indicator: '🔍', description: 'Copper free and lead free mirror' }
};

// DEFAULT frame styles - these are SYSTEM DEFAULTS and will NOT be overridden by database configs
// Synced with KONVA_DEFAULT_OPTIONS_REFERENCE.md
// CONSOLIDATED: Eliminated redundancy (case variations), added categories and visual indicators
let frameStyles = {
    // ========== METAL FINISHES ==========
    'white': { color: '#FFFFFF', width: 4, isDefault: true, category: 'metal', indicator: '■', description: 'White frame' },
    'black': { color: '#000000', width: 4, isDefault: true, category: 'metal', indicator: '■', description: 'Black frame' },
    'silver': { color: '#C0C0C0', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Silver frame' },
    'bronze': { color: '#CD7F32', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Bronze frame' },
    'gold': { color: '#FFD700', width: 4, isDefault: true, category: 'metal', indicator: '■', description: 'Gold frame' },
    'rose-gold': { color: '#B76E79', width: 4, isDefault: true, category: 'metal', indicator: '■', description: 'Rose gold frame' },
    'aluminum': { color: '#90A4AE', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Aluminum frame' },
    'chrome': { color: '#E8E8E8', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Chrome frame' },
    'brushed-nickel': { color: '#A8A9AD', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Brushed nickel frame' },
    'stainless-steel': { color: '#C9CCD1', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Stainless steel frame' },
    'stainless mirror finish': { color: '#D4D4D4', width: 3, isDefault: true, category: 'metal', indicator: '■', description: 'Stainless mirror finish' },
    'custom-color': { color: '#888888', width: 4, isDefault: true, category: 'metal', indicator: '■', description: 'Custom color frame' },
    
    // ========== WOOD FINISHES ==========
    'wood': { color: '#795548', width: 6, isDefault: true, category: 'wood', indicator: '▌', description: 'Wood frame' },
    'wood finish': { color: '#8B4513', width: 4, isDefault: true, category: 'wood', indicator: '▌', description: 'Wood finish frame' },
    'brown (wood-look)': { color: '#8B4513', width: 4, isDefault: true, category: 'wood', indicator: '▌', description: 'Brown wood-look frame' },
    
    // ========== POWDER COATED & MATTE ==========
    'powder coated white': { color: '#F8F8F8', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Powder coated white frame' },
    'analok': { color: '#F5F5DC', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Analok finish frame' },
    'analok (dark/bronze finish)': { color: '#8B4513', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Analok dark/bronze finish' },
    'matte gray': { color: '#6B6B6B', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Matte gray frame' },
    'matte black': { color: '#1A1A1A', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Matte black frame' },
    'dark grey/black': { color: '#2C2C2C', width: 4, isDefault: true, category: 'coated', indicator: '◆', description: 'Dark grey/black frame' },
    
    // ========== MATERIAL TYPES ==========
    'vinyl': { color: '#333333', width: 4, isDefault: true, category: 'material', indicator: '▢', description: 'Vinyl frame' },
    'hanalok': { color: '#F5F5DC', width: 4, isDefault: true, category: 'material', indicator: '▢', description: 'Hanalok frame' },
    
    // ========== FRAMELESS ==========
    'frameless': { color: 'transparent', width: 0, isDefault: true, category: 'frameless', indicator: '─', description: 'Frameless design' },
    
    // ========== GRAY VARIANTS ==========
    'gray': { color: '#808080', width: 4, isDefault: true, category: 'gray', indicator: '◇', description: 'Gray frame' },
    'grey': { color: '#808080', width: 4, isDefault: true, category: 'gray', indicator: '◇', description: 'Grey frame' },
    
    // ========== SPECIALTY FRAME TYPES ==========
    'standard-frame': { color: '#333333', width: 6, isDefault: true, category: 'specialty', indicator: '◊', description: 'Standard frame' },
    'thin-frame': { color: '#333333', width: 3, isDefault: true, category: 'specialty', indicator: '◊', description: 'Thin frame' },
    'grid-frame': { color: '#333333', width: 4, isDefault: true, category: 'specialty', indicator: '◊', description: 'Grid frame pattern' },
    'custom colors': { color: '#888888', width: 4, isDefault: true, category: 'specialty', indicator: '◊', description: 'Custom color options' }
};

/**
 * Extended visual configs storage - stores full visual configurations from admin
 * Includes advanced effects like gradients, shadows, patterns, etc.
 */
let extendedVisualConfigs = {};

/**
 * Loads custom visual configurations from product data and extends glassStyles/frameStyles
 * This allows admin-defined tags to have custom Konva visualizations
 * Supports advanced effects: gradients, shadows, patterns, edge styles
 * 
 * IMPORTANT: This function syncs the 2D preview colors from admin to customer side.
 * When admin configures colors in the "Enable 2D Preview Style" toggle, those settings
 * are saved to the database and loaded here for the customer's Konva canvas.
 * 
 * @param {Object} tagVisualConfigs - Visual configs from product { fieldId: { tagName: { fill, opacity, stroke, strokeWidth, ... } } }
 */
function loadDynamicVisualConfigs(tagVisualConfigs) {
    if (!tagVisualConfigs || typeof tagVisualConfigs !== 'object') {
        console.log('[Konva] No custom visual configs to load');
        return;
    }

    const totalFields = Object.keys(tagVisualConfigs).length;
    console.log(`[Konva] ========== LOADING VISUAL CONFIGS FROM ADMIN ==========`);
    console.log(`[Konva] Total fields with visual configs: ${totalFields}`);
    console.log('[Konva] Full config data:', JSON.stringify(tagVisualConfigs, null, 2));

    // Store full configs for advanced rendering
    extendedVisualConfigs = { ...extendedVisualConfigs, ...tagVisualConfigs };

    let glassConfigsAdded = 0;
    let frameConfigsAdded = 0;

    // Process each field's visual configs
    Object.keys(tagVisualConfigs).forEach(fieldId => {
        const fieldConfigs = tagVisualConfigs[fieldId];
        if (!fieldConfigs || typeof fieldConfigs !== 'object') return;

        const tagCount = Object.keys(fieldConfigs).length;
        console.log(`[Konva] Processing field "${fieldId}" with ${tagCount} tag config(s)`);

        Object.keys(fieldConfigs).forEach(tagName => {
            const config = fieldConfigs[tagName];
            if (!config) return;

            // Skip if visual config is disabled
            if (config.enabled === false) {
                console.log(`[Konva] ⏭️ Skipping disabled config for ${fieldId}/${tagName}`);
                return;
            }

            // Normalize tag name for lookup (lowercase, replace spaces with dashes)
            const normalizedTagName = tagName.toLowerCase().replace(/\s+/g, '-');

            console.log(`[Konva] Processing: "${tagName}" -> "${normalizedTagName}"`);

            // Store extended config with all advanced properties
            extendedVisualConfigs[normalizedTagName] = { ...config, fieldId, originalTagName: tagName };

            // Determine which style object to update based on field type AND effect type
            const fieldIdLower = fieldId.toLowerCase();
            const effectType = (config.effectType || 'fill').toLowerCase();

            // Check if this is a frame-related field FIRST (has priority over glass)
            // This prevents frameColor from being treated as glass
            const isFrameField = fieldIdLower.includes('frame') ||
                (fieldIdLower.includes('color') && !fieldIdLower.includes('glass')) ||
                fieldIdLower.includes('edge') ||
                fieldIdLower.includes('border') ||
                fieldIdLower.includes('stroke') ||
                effectType === 'frame' ||
                effectType === 'edge';

            // Check if this is a glass-related field (expanded detection)
            // NOTE: Exclude frame-related fields to prevent conflicts
            const isGlassField = !isFrameField && (
                fieldIdLower.includes('glass') ||
                fieldIdLower.includes('tint') ||
                fieldIdLower.includes('finish') ||
                (fieldIdLower.includes('type') && !fieldIdLower.includes('frame')) ||
                fieldIdLower.includes('material') ||
                effectType === 'fill' ||
                effectType === 'gradient' ||
                effectType === 'pattern' ||
                effectType === 'overlay'
            );

            if (isGlassField) {
                // Check if this style already exists as a default - if so, preserve it and skip database override
                const existingStyle = glassStyles[normalizedTagName];
                if (existingStyle && existingStyle.isDefault === true) {
                    console.log(`[Konva] ⏭️ Skipping database override for default glass style "${normalizedTagName}" - preserving system default`);
                    return; // Skip this tag, preserve default
                }

                // Only add/update if it's a new style (not a default)
                glassStyles[normalizedTagName] = {
                    fill: config.fill || '#E0F2F1',
                    opacity: config.opacity !== undefined ? config.opacity : 0.9,
                    // Extended properties for advanced effects
                    effectType: config.effectType || 'fill',
                    gradientEnd: config.gradientEnd,
                    gradientDirection: config.gradientDirection,
                    patternType: config.patternType,
                    patternDensity: config.patternDensity,
                    shadowBlur: config.shadowBlur,
                    shadowOffset: config.shadowOffset,
                    shadowColor: config.shadowColor,
                    shadowOpacity: config.shadowOpacity,
                    isDefault: false // Mark as database config
                };
                glassConfigsAdded++;
                console.log(`[Konva] ✅ GLASS style added for "${normalizedTagName}": fill=${config.fill}, opacity=${config.opacity}`);
            }

            if (isFrameField) {
                // Check if this style already exists as a default - if so, preserve it and skip database override
                const existingStyle = frameStyles[normalizedTagName];
                if (existingStyle && existingStyle.isDefault === true) {
                    console.log(`[Konva] ⏭️ Skipping database override for default frame style "${normalizedTagName}" - preserving system default`);
                    return; // Skip this tag, preserve default
                }

                // For frame colors, support multiple config formats:
                // 1. Direct format: { color: "#FFF", width: 4 }
                // 2. Konva format: { stroke: "#FFF", strokeWidth: 4 }
                // 3. Legacy format: { fill: "#FFF" } (for backward compatibility)
                const frameColor = config.color || config.stroke || config.fill || '#333333';
                const frameWidth = config.width !== undefined ? config.width : (config.strokeWidth !== undefined ? config.strokeWidth : 4);

                // Only add/update if it's a new style (not a default)
                frameStyles[normalizedTagName] = {
                    color: frameColor,
                    width: frameWidth,
                    // Extended properties
                    edgeStyle: config.edgeStyle || 'solid',
                    cornerRadius: config.cornerRadius || 0,
                    shadowBlur: config.shadowBlur,
                    shadowOffset: config.shadowOffset,
                    shadowColor: config.shadowColor,
                    shadowOpacity: config.shadowOpacity,
                    // Store original config for reference
                    fill: config.fill,
                    stroke: config.stroke,
                    opacity: config.opacity,
                    isDefault: false // Mark as database config
                };
                frameConfigsAdded++;
                console.log(`[Konva] ✅ FRAME style added for "${normalizedTagName}": color=${frameColor}, width=${frameWidth}`);
            }

            // Also add by original tag name variants for flexible lookups
            // Only add variants if the style was actually added (not skipped as default)
            const tagVariants = [
                tagName.toLowerCase(),
                tagName.toLowerCase().replace(/\s+/g, '-'),
                tagName.toLowerCase().replace(/\s+/g, '_'),
                tagName.replace(/\s+/g, '')
            ];

            tagVariants.forEach(variant => {
                if (variant !== normalizedTagName) {
                    // Only add variants if the main style was added (not a default that was skipped)
                    if (isFrameField && frameStyles[normalizedTagName] && !frameStyles[normalizedTagName].isDefault) {
                        // Check if variant is also a default - if so, don't override
                        if (!frameStyles[variant] || !frameStyles[variant].isDefault) {
                            frameStyles[variant] = { ...frameStyles[normalizedTagName] };
                        }
                    }
                    if (isGlassField && glassStyles[normalizedTagName] && !glassStyles[normalizedTagName].isDefault) {
                        // Check if variant is also a default - if so, don't override
                        if (!glassStyles[variant] || !glassStyles[variant].isDefault) {
                            glassStyles[variant] = { ...glassStyles[normalizedTagName] };
                        }
                    }
                }
            });
        });
    });

    // Expose extended configs globally
    if (typeof window !== 'undefined') {
        window.extendedVisualConfigs = extendedVisualConfigs;
        window.frameStyles = frameStyles;
        window.glassStyles = glassStyles;
    }

    console.log(`[Konva] ========== VISUAL CONFIG LOADING COMPLETE ==========`);
    console.log(`[Konva] Glass styles added/updated: ${glassConfigsAdded}`);
    console.log(`[Konva] Frame styles added/updated: ${frameConfigsAdded}`);
    console.log('[Konva] All frameStyles:', Object.keys(frameStyles));
    console.log('[Konva] All glassStyles:', Object.keys(glassStyles));
}

/**
 * Get extended visual config for a tag
 * @param {string} tagName - Tag name to look up
 * @returns {Object|null} Full visual config or null
 */
function getExtendedVisualConfig(tagName) {
    if (!tagName) return null;
    const normalizedName = tagName.toLowerCase().replace(/\s+/g, '-');
    return extendedVisualConfigs[normalizedName] || null;
}

/**
 * Get all glass types grouped by category
 * @returns {Object} Glass types organized by category with indicators
 */
function getGlassTypesByCategory() {
    const categories = {};
    
    Object.entries(glassStyles).forEach(([name, style]) => {
        const category = style.category || 'other';
        if (!categories[category]) {
            categories[category] = [];
        }
        categories[category].push({
            name,
            ...style
        });
    });
    
    return categories;
}

/**
 * Get all frame types grouped by category
 * @returns {Object} Frame types organized by category with indicators
 */
function getFrameTypesByCategory() {
    const categories = {};
    
    Object.entries(frameStyles).forEach(([name, style]) => {
        const category = style.category || 'other';
        if (!categories[category]) {
            categories[category] = [];
        }
        categories[category].push({
            name,
            ...style
        });
    });
    
    return categories;
}

/**
 * Generate a display label for glass type with indicator
 * @param {string} glassType - Glass type name
 * @returns {string} Formatted label with indicator
 */
function getGlassTypeLabel(glassType) {
    const style = glassStyles[glassType.toLowerCase()];
    if (!style) return glassType;
    
    const indicator = style.indicator || '●';
    const description = style.description || glassType;
    return `${indicator} ${description}`;
}

/**
 * Generate a display label for frame type with indicator
 * @param {string} frameType - Frame type name
 * @returns {string} Formatted label with indicator
 */
function getFrameTypeLabel(frameType) {
    const style = frameStyles[frameType.toLowerCase()];
    if (!style) return frameType;
    
    const indicator = style.indicator || '●';
    const description = style.description || frameType;
    return `${indicator} ${description}`;
}

/**
 * Apply advanced visual effects to a Konva shape
 * @param {Konva.Shape} shape - Konva shape to modify
 * @param {Object} config - Visual config with advanced options
 */
function applyAdvancedVisualEffects(shape, config) {
    if (!shape || !config) return;

    const effectType = config.effectType || 'fill';

    // Apply gradient if configured
    if (effectType === 'gradient' && config.gradientEnd) {
        const bounds = shape.getClientRect();
        const width = bounds.width || 100;
        const height = bounds.height || 100;

        if (config.gradientDirection === 'radial') {
            shape.fillRadialGradientStartPoint({ x: width / 2, y: height / 2 });
            shape.fillRadialGradientStartRadius(0);
            shape.fillRadialGradientEndPoint({ x: width / 2, y: height / 2 });
            shape.fillRadialGradientEndRadius(Math.max(width, height) / 2);
            shape.fillRadialGradientColorStops([0, config.fill, 1, config.gradientEnd]);
            shape.fill(null); // Clear solid fill
        } else {
            let startPoint = { x: 0, y: 0 };
            let endPoint = { x: 0, y: height };

            if (config.gradientDirection === 'horizontal') {
                endPoint = { x: width, y: 0 };
            } else if (config.gradientDirection === 'diagonal') {
                endPoint = { x: width, y: height };
            }

            shape.fillLinearGradientStartPoint(startPoint);
            shape.fillLinearGradientEndPoint(endPoint);
            shape.fillLinearGradientColorStops([0, config.fill, 1, config.gradientEnd]);
            shape.fill(null); // Clear solid fill
        }
    }

    // Apply shadow if configured
    // Defaults per KONVA_DEFAULT_OPTIONS_REFERENCE.md: shadowColor: #000000, shadowBlur: 10, shadowOffset: { x: 5, y: 5 }, shadowOpacity: 0.3
    if ((effectType === 'shadow' || config.shadowBlur) && config.shadowBlur > 0) {
        shape.shadowColor(config.shadowColor || '#000000');
        shape.shadowBlur(config.shadowBlur || 10);
        // shadowOffset can be a number or an object { x, y }
        const shadowOffset = config.shadowOffset || { x: 5, y: 5 };
        const offsetX = typeof shadowOffset === 'object' ? shadowOffset.x : shadowOffset;
        const offsetY = typeof shadowOffset === 'object' ? shadowOffset.y : shadowOffset;
        shape.shadowOffset({ x: offsetX, y: offsetY });
        shape.shadowOpacity(config.shadowOpacity !== undefined ? config.shadowOpacity : 0.3);
    }

    // Apply edge style if configured
    if (config.edgeStyle) {
        if (config.edgeStyle === 'dashed') {
            shape.dash([10, 5]);
        } else if (config.edgeStyle === 'dotted') {
            shape.dash([2, 4]);
        }
    }

    // Apply corner radius if configured and shape supports it
    if (config.cornerRadius && typeof shape.cornerRadius === 'function') {
        shape.cornerRadius(config.cornerRadius);
    }
}

    /**
     * Generate a small canvas that simulates frosted / hammered glass texture.
     * Returns the canvas element which can be used as an image source for Konva.Image.
     * @param {number} w - canvas width
     * @param {number} h - canvas height
     * @param {Object} opts - { density, color, intensity }
     */
    function generateFrostCanvas(w, h, opts = {}) {
        const density = Math.max(1, opts.density || 6);
        const intensity = typeof opts.intensity === 'number' ? opts.intensity : 0.6;
        const baseColor = opts.color || '#ffffff';

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');

        // Slight translucent base tint (so pattern blends with underlying glass color)
        ctx.fillStyle = baseColor;
        ctx.globalAlpha = 0.02 * intensity;
        ctx.fillRect(0, 0, w, h);
        ctx.globalAlpha = 1;

        // Draw many small soft bumps using radial gradients
        const bumps = density * 180; // number of bumps scales with density
        for (let i = 0; i < bumps; i++) {
            const rx = Math.random() * w;
            const ry = Math.random() * h;
            // size biased by intensity and canvas size
            const maxR = Math.max(2, Math.min(w, h) * (0.005 + Math.random() * 0.03) * (1 + intensity));
            const r = Math.random() * maxR + 0.5;

            const g = ctx.createRadialGradient(rx, ry, 0, rx, ry, r * 2);
            const a = 0.03 + Math.random() * 0.12 * intensity; // alpha per bump
            g.addColorStop(0, `rgba(255,255,255,${a})`);
            g.addColorStop(0.6, `rgba(250,250,250,${a * 0.6})`);
            g.addColorStop(1, 'rgba(255,255,255,0)');
            ctx.fillStyle = g;
            ctx.fillRect(rx - r * 2, ry - r * 2, r * 4, r * 4);
        }

        // Add a few darker mottles to break uniformity
        const darks = Math.max(10, density * 8);
        for (let i = 0; i < darks; i++) {
            const rx = Math.random() * w;
            const ry = Math.random() * h;
            const r = Math.random() * Math.max(1, Math.min(w, h) * 0.02);
            const g = ctx.createRadialGradient(rx, ry, 0, rx, ry, r * 2);
            const a = 0.01 + Math.random() * 0.04 * intensity;
            g.addColorStop(0, `rgba(200,200,200,${a})`);
            g.addColorStop(1, 'rgba(200,200,200,0)');
            ctx.fillStyle = g;
            ctx.fillRect(rx - r * 2, ry - r * 2, r * 4, r * 4);
        }

        // Soft blur pass for smoother bumps (use ctx.filter for modern browsers)
        try {
            ctx.filter = `blur(${1.0 * intensity}px)`;
            const temp = document.createElement('canvas');
            temp.width = w;
            temp.height = h;
            const tctx = temp.getContext('2d');
            tctx.drawImage(canvas, 0, 0);
            ctx.clearRect(0, 0, w, h);
            ctx.drawImage(temp, 0, 0);
            ctx.filter = 'none';
        } catch (e) {
            // If ctx.filter not supported, ignore
        }

        // Add subtle noise overlay to create granular frost look
        const imageData = ctx.getImageData(0, 0, w, h);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
            const n = (Math.random() - 0.5) * 6 * intensity; // noise amount
            data[i] = Math.min(255, Math.max(0, data[i] + n));
            data[i+1] = Math.min(255, Math.max(0, data[i+1] + n));
            data[i+2] = Math.min(255, Math.max(0, data[i+2] + n));
        }
        ctx.putImageData(imageData, 0, 0);

        return canvas;
    }


/**
 * Draw pattern overlay on a Konva layer
 * @param {Konva.Layer} layer - Layer to draw on
 * @param {number} x - Start X position
 * @param {number} y - Start Y position  
 * @param {number} width - Pattern width
 * @param {number} height - Pattern height
 * @param {string} patternType - Type of pattern
 * @param {number} density - Pattern density (1-20)
 * @param {string} color - Pattern color
 */
function drawKonvaPatternOverlay(layer, x, y, width, height, patternType, density, color) {
    if (!patternType || patternType === 'none') return;

    const spacing = Math.max(5, 30 / (density || 5));

    if (patternType === 'lines') {
        for (let i = spacing; i < width; i += spacing) {
            layer.add(new Konva.Line({
                points: [x + i, y, x + i, y + height],
                stroke: color,
                strokeWidth: 0.5,
                opacity: 0.3,
                listening: false
            }));
        }
    } else if (patternType === 'grid') {
        for (let i = spacing; i < width; i += spacing) {
            layer.add(new Konva.Line({
                points: [x + i, y, x + i, y + height],
                stroke: color,
                strokeWidth: 0.5,
                opacity: 0.3,
                listening: false
            }));
        }
        for (let i = spacing; i < height; i += spacing) {
            layer.add(new Konva.Line({
                points: [x, y + i, x + width, y + i],
                stroke: color,
                strokeWidth: 0.5,
                opacity: 0.3,
                listening: false
            }));
        }
    } else if (patternType === 'dots') {
        for (let i = spacing; i < width; i += spacing) {
            for (let j = spacing; j < height; j += spacing) {
                layer.add(new Konva.Circle({
                    x: x + i,
                    y: y + j,
                    radius: 1,
                    fill: color,
                    opacity: 0.4,
                    listening: false
                }));
            }
        }
    } else if (patternType === 'frosted') {
        // Frosted glass effect - create a repeating canvas texture that resembles
        // the attachment (hammered / frosted glass). We draw many small bumps,
        // blur and adjust opacity, then add as a Konva.Image so it's fast to render.
        const frostImage = generateFrostCanvas(Math.max(96, Math.round(width/3)), Math.max(96, Math.round(height/3)), {
            density: density || 6,
            color: color || '#ffffff',
            intensity: 0.6
        });

        if (frostImage) {
            const img = new Konva.Image({
                x: x,
                y: y,
                width: width,
                height: height,
                image: frostImage,
                opacity: 0.45,
                listening: false
            });
            layer.add(img);
        }
    } else if (patternType === 'rain') {
        // Rain/water drops effect
        for (let i = 0; i < (density || 5) * 10; i++) {
            const dropX = x + Math.random() * width;
            const dropY = y + Math.random() * height;
            const dropLen = Math.random() * 10 + 5;
            layer.add(new Konva.Ellipse({
                x: dropX,
                y: dropY,
                radiusX: 1,
                radiusY: dropLen / 4,
                fill: '#FFFFFF',
                opacity: Math.random() * 0.4 + 0.1,
                listening: false
            }));
        }
    }
}

// Auto-load visual configs when product data is available
if (typeof window !== 'undefined') {
    // Track if configs have been loaded to avoid duplicate loading
    let visualConfigsLoaded = false;

    // Check if product data is already available
    const checkAndLoadConfigs = () => {
        if (visualConfigsLoaded) {
            console.log('[Konva] Visual configs already loaded, skipping');
            return;
        }

        // Check for pending configs (set by 2DModeling.php if function wasn't ready)
        if (window.pendingVisualConfigs) {
            console.log('[Konva] Loading pending visual configs...');
            loadDynamicVisualConfigs(window.pendingVisualConfigs);
            delete window.pendingVisualConfigs;
            visualConfigsLoaded = true;
            return;
        }

        /*  if (window.selectedProduct && window.selectedProduct.tagVisualConfigs) {
             const configCount = Object.keys(window.selectedProduct.tagVisualConfigs).length;
             if (configCount > 0) {
                 console.log(`[Konva] Auto-loading ${configCount} visual config field(s) from product data`);
                 loadDynamicVisualConfigs(window.selectedProduct.tagVisualConfigs);
                 visualConfigsLoaded = true;
             }
         } */
    };

    // Try at multiple intervals to catch different loading scenarios
    setTimeout(checkAndLoadConfigs, 100);
    setTimeout(checkAndLoadConfigs, 300);
    setTimeout(checkAndLoadConfigs, 600);

    // Also listen for DOM ready in case data loads later
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(checkAndLoadConfigs, 200);
            setTimeout(checkAndLoadConfigs, 700);
        });
    }

    // Expose functions globally for manual calls
    window.loadDynamicVisualConfigs = loadDynamicVisualConfigs;
    window.getExtendedVisualConfig = getExtendedVisualConfig;
    window.applyAdvancedVisualEffects = applyAdvancedVisualEffects;
    window.drawKonvaPatternOverlay = drawKonvaPatternOverlay;

        // Fallback: attach click handlers to any visible control labeled "Frosted"
        // This helps older UIs that don't use data-glass-type attributes.
        function attachFrostedButtonFallback() {
            const candidates = Array.from(document.querySelectorAll('.option-card, button, .tag-option, .tag-item'));
            candidates.forEach(el => {
                try {
                    if (el.__frostedFallbackAttached) return;
                    const txt = (el.textContent || '').trim().toLowerCase();
                    if (txt === 'frosted' || txt === 'frost') {
                        el.addEventListener('click', () => {
                            currentGlassType = 'frosted';
                            if (typeof window !== 'undefined') {
                                if (!window.selectedCustomizationValues) window.selectedCustomizationValues = {};
                                window.selectedCustomizationValues.glassColor = 'Frosted';
                            }
                            if (typeof renderCustomState === 'function') renderCustomState();
                        });
                        el.__frostedFallbackAttached = true;
                    }
                } catch (e) { /* ignore */ }
            });
        }

        // Run fallback attachment after DOM ready and also after short delay to catch dynamic UIs
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => { attachFrostedButtonFallback(); setTimeout(attachFrostedButtonFallback, 800); });
        } else {
            attachFrostedButtonFallback(); setTimeout(attachFrostedButtonFallback, 800);
        }

    // Helper to check if visual configs are loaded
    window.areVisualConfigsLoaded = () => visualConfigsLoaded;

    // Helper to force reload visual configs (useful for debugging)
    window.reloadVisualConfigs = () => {
        visualConfigsLoaded = false;
        checkAndLoadConfigs();
    };
}

// Initialize Konva (only if container exists)
let stage = null;
let layer = null;
if (konvaWrapper && STAGE_SIZE > 0 && typeof Konva !== 'undefined') {
    stage = new Konva.Stage({
        container: KONVA_CONTAINER_ID,
        width: STAGE_SIZE,
        height: STAGE_SIZE,
    });

    layer = new Konva.Layer();
    stage.add(layer);
    // expose for other modules
    window.stage = stage;
    window.layer = layer;
} else {
    // Ensure `window.layer` exists (null) so other scripts can check it safely
    window.layer = null;
}

/**
 * Renders multi-panel product configuration (e.g., sliding doors, windows with multiple panels)
 * Based on product catalog JSON customization options
 */
function renderMultiPanelProduct(widthIn, heightIn, unit, glassType, thickness, edgeWork, frameType, originalWidth, originalHeight, heightUnit, customizationValues = {}) {
    if (!layer) return; // Konva not initialized (container missing) - nothing to render
    layer.destroyChildren();

    // Get panel configuration from customization values
    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || customizationValues.NumberOfPanels || '2-panel');
    const operation = (customizationValues.operation || customizationValues.Operation || 'sliding').toLowerCase();
    const configuration = (customizationValues.configuration || customizationValues.Configuration || '').toLowerCase();

    // Get transom type (Top / Bottom Fixed Panel)
    const transomType = customizationValues.transomType ||
        customizationValues.TransomType ||
        customizationValues.transomTypeTopBottomFixedPanel ||
        customizationValues.TransomTypeTopBottomFixedPanel ||
        'None';
    const hasTransom = transomType && transomType.toLowerCase() !== 'none';
    const isFixedTransomHead = hasTransom && transomType.toLowerCase().includes('head');
    const isFixedTransomSill = hasTransom && transomType.toLowerCase().includes('sill');

    // Get h1 (sliding section height) and h2 (fixed transom height) values from inputs if available
    let h1Value = null;
    let h1Unit = heightUnit;
    let h2Value = null;
    let h2Unit = heightUnit;

    let w1Value = null;
    let w1Unit = unit;
    let w2Value = null;
    let w2Unit = unit;

    let w3Value = null;
    let w3Unit = unit;


    // Check if h1 input exists and is visible
    const h1InputGroup = inputGroupH1 || document.getElementById('input-group-h1');
    const h1Input = inputH1 || document.getElementById('input-h1');
    const h1UnitBtn = btnUnitH1 || document.getElementById('btn-unit-h1');

    // Check if h2 input exists and is visible
    const h2InputGroup = inputGroupH2 || document.getElementById('input-group-h2');
    const h2Input = inputH2 || document.getElementById('input-h2');
    const h2UnitBtn = btnUnitH2 || document.getElementById('btn-unit-h2');

    // check if w1 input exists and is visible
    const w1InputGroup = inputGroupW1 || document.getElementById('input-group-w1');
    const w1Input = inputW1 || document.getElementById('input-w1');
    const w1UnitBtn = btnUnitW1 || document.getElementById('btn-unit-w1');

    // check if w2 input exists and is visible
    const w2InputGroup = inputGroupW2 || document.getElementById('input-group-w2');
    const w2Input = inputW2 || document.getElementById('input-w2');
    const w2UnitBtn = btnUnitW2 || document.getElementById('btn-unit-w2');

    const w3InputGroup = inputGroupW3 || document.getElementById('input-group-w3');
    const w3Input = inputW3 || document.getElementById('input-w3');
    const w3UnitBtn = btnUnitW3 || document.getElementById('btn-unit-w3');

    if (w1Input && w1Input.value && w1InputGroup && !w1InputGroup.classList.contains('hidden-step') && w1InputGroup.style.display !== 'none') {
        const w1InputValue = parseFloat(w1Input.value);
        if (!isNaN(w1InputValue) && w1InputValue > 0) {
            w1Value = w1InputValue;
            if (w1UnitBtn) {
                w1Unit = w1UnitBtn.getAttribute('data-current-unit') || unit;
            }
        }
    }

    if (w2Input && w2Input.value && w2InputGroup && !w2InputGroup.classList.contains('hidden-step') && w2InputGroup.style.display !== 'none') {
        const w2InputValue = parseFloat(w2Input.value);
        if (!isNaN(w2InputValue) && w2InputValue > 0) {
            w2Value = w2InputValue;
            if (w2UnitBtn) {
                w2Unit = w2UnitBtn.getAttribute('data-current-unit') || unit;
            }
        }
    }
    if (w3Input && w3Input.value && w3InputGroup && !w3InputGroup.classList.contains('hidden-step') && w3InputGroup.style.display !== 'none') {
        const w3InputValue = parseFloat(w3Input.value);
        if (!isNaN(w3InputValue) && w3InputValue > 0) {
            w3Value = w3InputValue;
            if (w3UnitBtn) {
                w3Unit = w3UnitBtn.getAttribute('data-current-unit') || unit;
            }
        }
    }

    if (w2Input && w2Input.value && w2InputGroup && !w2InputGroup.classList.contains('hidden-step') && w2InputGroup.style.display !== 'none') {
        const w2InputValue = parseFloat(w2Input.value);
        if (!isNaN(w2InputValue) && w2InputValue > 0) {
            w2Value = w2InputValue;
            if (w2UnitBtn) {
                w2Unit = w2UnitBtn.getAttribute('data-current-unit') || unit;
            }
        }
    }

    if (w3Input && w3Input.value && w3InputGroup && !w3InputGroup.classList.contains('hidden-step') && w3InputGroup.style.display !== 'none') {
        const w3InputValue = parseFloat(w3Input.value);
        if (!isNaN(w3InputValue) && w3InputValue > 0) {
            w3Value = w3InputValue;
            if (w3UnitBtn) {
                w3Unit = w3UnitBtn.getAttribute('data-current-unit') || unit;
            }
        }
    }

    if (h1Input && h1Input.value && h1InputGroup && !h1InputGroup.classList.contains('hidden-step') && h1InputGroup.style.display !== 'none') {
        const h1InputValue = parseFloat(h1Input.value);
        if (!isNaN(h1InputValue) && h1InputValue > 0) {
            h1Value = h1InputValue;
            if (h1UnitBtn) {
                h1Unit = h1UnitBtn.getAttribute('data-current-unit') || heightUnit;
            }
        }
    }

    if (h2Input && h2Input.value && h2InputGroup && !h2InputGroup.classList.contains('hidden-step') && h2InputGroup.style.display !== 'none') {
        const h2InputValue = parseFloat(h2Input.value);
        if (!isNaN(h2InputValue) && h2InputValue > 0) {
            h2Value = h2InputValue;
            if (h2UnitBtn) {
                h2Unit = h2UnitBtn.getAttribute('data-current-unit') || heightUnit;
            }
        }
    }

    console.log('[Konva] Transom heights:', { h1: h1Value, h1Unit, h2: h2Value, h2Unit, totalHeight: originalHeight, heightUnit });

    // Get Windows-specific panel configuration
    const panelConfig = customizationValues.panelConfiguration || customizationValues.PanelConfiguration || '';
    let panelTypes = [];

    // Parse Windows panel configuration (e.g., "S | S (Sliding | Sliding)" or "F | S | S | F (Fixed | Sliding | Sliding | Fixed)")
    if (panelConfig) {
        console.log('[Konva] Parsing panel configuration:', panelConfig);

        // Remove the descriptive text in parentheses if present (e.g., "(Sliding | Sliding)")
        let configString = panelConfig.replace(/\([^)]*\)/g, '').trim();

        // Split by pipe character and extract panel types
        const parts = configString.split('|').map(p => p.trim()).filter(p => p.length > 0);

        panelTypes = parts.map(part => {
            // Check for 'S' or 'Sliding' (case insensitive)
            if (part.toUpperCase().includes('S') && !part.toUpperCase().includes('F')) {
                return 'sliding';
            }
            // Check for 'F' or 'Fixed' (case insensitive)
            else if (part.toUpperCase().includes('F')) {
                return 'fixed';
            }
            // Default to sliding if unclear
            return 'sliding';
        });

        // Ensure we have the correct number of panels
        if (panelTypes.length !== numberOfPanels) {
            console.warn(`[Konva] Panel configuration has ${panelTypes.length} panels but numberOfPanels is ${numberOfPanels}. Adjusting...`);
            // If we have fewer types than panels, repeat the pattern
            if (panelTypes.length < numberOfPanels) {
                const pattern = [...panelTypes];
                while (panelTypes.length < numberOfPanels) {
                    panelTypes.push(...pattern);
                }
                panelTypes = panelTypes.slice(0, numberOfPanels);
            } else {
                // If we have more types than panels, truncate
                panelTypes = panelTypes.slice(0, numberOfPanels);
            }
        }

        console.log('[Konva] Parsed panel types:', panelTypes);
    } else {
        // Determine if panels are fixed or operable based on operation/configuration
        const hasFixedPanels = configuration.includes('fixed') || operation.includes('fixed');
        const isSliding = operation.includes('sliding');
        const isSwing = operation.includes('swing');

        // If no specific panel config, create default based on number of panels
        // Default: all sliding unless explicitly fixed
        panelTypes = new Array(numberOfPanels).fill(hasFixedPanels ? 'fixed' : 'sliding');
        console.log('[Konva] No panel configuration found, using defaults:', panelTypes);
    }

    // Calculate panel dimensions
    const actualRatio = widthIn / heightIn;
    let totalWidth, totalHeight;

    if (actualRatio > 1) {
        totalWidth = DRAWING_SIZE;
        totalHeight = DRAWING_SIZE / actualRatio;
    } else {
        totalHeight = DRAWING_SIZE;
        totalWidth = DRAWING_SIZE * actualRatio;
    }

    const offsetX = (STAGE_SIZE - totalWidth) / 2;
    const offsetY = (STAGE_SIZE - totalHeight) / 2;

    // Normalize styles
    const normalizedGlassType = normalizeGlassType(glassType);
    const normalizedFrameType = normalizeFrameType(frameType);
    const gStyle = glassStyles[normalizedGlassType] || glassStyles['clear'];
    let fStyle = frameStyles[normalizedFrameType];

    // If frame style not found, try to create a sensible default based on color name
    if (!fStyle) {
        const colorName = normalizedFrameType.toLowerCase();
        let fallbackColor = '#FFFFFF'; // Default white

        // Common color mappings
        const commonColors = {
            'gold': '#FFD700',
            'silver': '#C0C0C0',
            'bronze': '#CD7F32',
            'black': '#000000',
            'white': '#FFFFFF',
            'rose': '#B76E79',
            'chrome': '#E8E8E8',
            'nickel': '#A8A9AD',
            'stainless': '#C9CCD1',
            'wood': '#795548',
            'brown': '#8B4513',
            'gray': '#808080',
            'grey': '#808080'
        };

        // Find matching color
        for (const [key, color] of Object.entries(commonColors)) {
            if (colorName.includes(key)) {
                fallbackColor = color;
                break;
            }
        }

        fStyle = { color: fallbackColor, width: 4 };
        frameStyles[normalizedFrameType] = fStyle;
    }

    // Calculate panel width (divide total width by number of panels)
    const panelWidth = totalWidth / numberOfPanels;

    // Handle transom: split window into upper and lower sections
    let upperSectionHeight = 0;
    let lowerSectionHeight = totalHeight;
    let transomDividerY = 0;
    let actualInnerHeight = null; // Store the actual inner height for dimension display

    if (hasTransom) {
        const totalHeightInMm = convertToMm(originalHeight, heightUnit);
        let transomHeightMm = null;
        let slidingHeightMm = null;

        // Convert h1 and h2 to millimeters for calculation
        if (h1Value !== null && h1Value > 0) {
            slidingHeightMm = convertToMm(h1Value, h1Unit);
        }
        if (h2Value !== null && h2Value > 0) {
            transomHeightMm = convertToMm(h2Value, h2Unit);
        }

        // Auto-adjust: if one is missing, calculate from the other
        if (transomHeightMm !== null && slidingHeightMm === null) {
            // h2 provided, calculate h1
            slidingHeightMm = Math.max(0.1, totalHeightInMm - transomHeightMm);
        } else if (slidingHeightMm !== null && transomHeightMm === null) {
            // h1 provided, calculate h2
            transomHeightMm = Math.max(0.1, totalHeightInMm - slidingHeightMm);
        } else if (transomHeightMm === null && slidingHeightMm === null) {
            // Neither provided, use default ratios
            transomHeightMm = totalHeightInMm * 0.3; // 30% for transom
            slidingHeightMm = totalHeightInMm * 0.7; // 70% for sliding
        }

        // Ensure they don't exceed total height
        const sum = transomHeightMm + slidingHeightMm;
        if (sum > totalHeightInMm) {
            // Scale both proportionally to fit
            const scale = totalHeightInMm / sum;
            transomHeightMm *= scale;
            slidingHeightMm *= scale;
        }

        // Convert back to ratios for rendering
        const transomRatio = transomHeightMm / totalHeightInMm;
        const slidingRatio = slidingHeightMm / totalHeightInMm;

        // Clamp ratios to valid range
        const clampedTransomRatio = Math.max(0.1, Math.min(0.9, transomRatio));
        const clampedSlidingRatio = Math.max(0.1, Math.min(0.9, slidingRatio));

        if (isFixedTransomHead) {
            // Fixed transom at top, sliding section at bottom
            upperSectionHeight = totalHeight * clampedTransomRatio;
            lowerSectionHeight = totalHeight * clampedSlidingRatio;
            transomDividerY = offsetY + upperSectionHeight;
            actualInnerHeight = h1Value || (slidingHeightMm / (unitMap[h1Unit]?.toMm || 1)); // Store for dimension display
        } else if (isFixedTransomSill) {
            // Fixed transom at bottom, sliding section at top
            upperSectionHeight = totalHeight * clampedSlidingRatio;
            lowerSectionHeight = totalHeight * clampedTransomRatio;
            transomDividerY = offsetY + upperSectionHeight;
            actualInnerHeight = h1Value || (slidingHeightMm / (unitMap[h1Unit]?.toMm || 1)); // Store for dimension display
        }

        console.log('[Konva] Transom rendering:', {
            transomRatio: clampedTransomRatio,
            slidingRatio: clampedSlidingRatio,
            upperSectionHeight,
            lowerSectionHeight,
            isFixedTransomHead
        });
    }

    // Draw panels based on transom configuration or panelTypes
    if (hasTransom) {
        // Draw transom section (fixed panels at top or bottom)
        // Note: Transom section is always fixed glass, regardless of panel configuration
        const transomSectionY = isFixedTransomHead ? offsetY : offsetY + upperSectionHeight;
        const transomSectionHeight = isFixedTransomHead ? upperSectionHeight : lowerSectionHeight;

        // Draw fixed panels in transom section (transom is always fixed)
        for (let i = 0; i < numberOfPanels; i++) {
            const panelX = offsetX + (i * panelWidth);

            // Draw fixed transom panel (transom section is always fixed)
            const fixedRect = new Konva.Rect({
                x: panelX,
                y: transomSectionY,
                width: panelWidth,
                height: transomSectionHeight,
                fill: '#4A90E2', // Darker blue for fixed panels
                opacity: 0.8,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(fixedRect);

            // Add "F" label for Fixed
            const fixedLabel = new Konva.Text({
                x: panelX + panelWidth / 2,
                y: transomSectionY + transomSectionHeight / 2,
                text: 'F',
                fontSize: Math.max(12, Math.min(16, transomSectionHeight / 3)),
                fontFamily: 'Montserrat, Arial',
                fontStyle: 'bold',
                fill: '#FFFFFF',
                align: 'center',
                offsetX: 8,
                offsetY: 8,
                listening: false,
            });
            layer.add(fixedLabel);

            // Add panel divider (vertical line between panels)
            if (i < numberOfPanels - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, transomSectionY, panelX + panelWidth, transomSectionY + transomSectionHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
        }

        // Draw main section (sliding panels)
        const mainSectionY = isFixedTransomHead ? offsetY + upperSectionHeight : offsetY;
        const mainSectionHeight = isFixedTransomHead ? lowerSectionHeight : upperSectionHeight;

        for (let i = 0; i < numberOfPanels; i++) {
            const panelX = offsetX + (i * panelWidth);
            const panelType = panelTypes[i] || 'sliding'; // Default to sliding in main section

            if (panelType === 'sliding') {
                // Draw sliding panel (operable glass)
                const glassRect = new Konva.Rect({
                    x: panelX,
                    y: mainSectionY,
                    width: panelWidth,
                    height: mainSectionHeight,
                    fill: gStyle.fill,
                    opacity: gStyle.opacity,
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width,
                    listening: false,
                });
                layer.add(glassRect);

                // Add "S" label for Sliding - only show when no transom is selected
                if (!hasTransom) {
                    const handleX = panelX + panelWidth / 2;
                    const handleY = mainSectionY + mainSectionHeight / 2;
                    const slidingLabelFontSize = Math.max(12, Math.min(16, mainSectionHeight / 3));
                    const slidingLabel = new Konva.Text({
                        x: panelX + panelWidth / 2,
                        y: handleY,
                        text: 'S',
                        fontSize: slidingLabelFontSize,
                        fontFamily: 'Montserrat, Arial',
                        fontStyle: 'bold',
                        fill: '#333333',
                        align: 'center',
                        verticalAlign: 'middle',
                        offsetX: slidingLabelFontSize * 0.35,
                        offsetY: slidingLabelFontSize * 0.5,
                        listening: false,
                    });
                    layer.add(slidingLabel);
                }
            } else {
                // Draw fixed panel in main section (if configuration specifies)
                const fixedRect = new Konva.Rect({
                    x: panelX,
                    y: mainSectionY,
                    width: panelWidth,
                    height: mainSectionHeight,
                    fill: '#4A90E2', // Darker blue for fixed panels
                    opacity: 0.8,
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width,
                    listening: false,
                });
                layer.add(fixedRect);

                // Add "F" label for Fixed
                const fixedLabel = new Konva.Text({
                    x: panelX + panelWidth / 2,
                    y: mainSectionY + mainSectionHeight / 2,
                    text: 'F',
                    fontSize: Math.max(12, Math.min(16, mainSectionHeight / 3)),
                    fontFamily: 'Montserrat, Arial',
                    fontStyle: 'bold',
                    fill: '#FFFFFF',
                    align: 'center',
                    offsetX: 8,
                    offsetY: 8,
                    listening: false,
                });
                layer.add(fixedLabel);
            }

            // Add panel divider (vertical line between panels)
            if (i < numberOfPanels - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, mainSectionY, panelX + panelWidth, mainSectionY + mainSectionHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
        }

        // Draw horizontal divider between transom and main sections
        if (transomDividerY > 0) {
            const transomDivider = new Konva.Line({
                points: [offsetX, transomDividerY, offsetX + totalWidth, transomDividerY],
                stroke: fStyle.color,
                strokeWidth: fStyle.width * 1.5,
                listening: false,
            });
            layer.add(transomDivider);
        }
    } else {
        // No transom - draw panels normally based on panelTypes configuration
        const panelHeight = totalHeight;
        for (let i = 0; i < numberOfPanels; i++) {
            const panelX = offsetX + (i * panelWidth);
            const panelY = offsetY;
            const panelType = panelTypes[i] || 'sliding';

            if (panelType === 'fixed') {
                // Draw fixed panel (entire panel is fixed)
                const fixedRect = new Konva.Rect({
                    x: panelX,
                    y: panelY,
                    width: panelWidth,
                    height: panelHeight,
                    fill: '#4A90E2', // Darker blue for fixed panels
                    opacity: 0.8,
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width,
                    listening: false,
                });
                layer.add(fixedRect);

                // Add "F" label for Fixed
                const fixedLabel = new Konva.Text({
                    x: panelX + panelWidth / 2,
                    y: panelY + panelHeight / 2,
                    text: 'F',
                    fontSize: 16,
                    fontFamily: 'Montserrat, Arial',
                    fontStyle: 'bold',
                    fill: '#FFFFFF',
                    align: 'center',
                    offsetX: 8,
                    offsetY: 8,
                    listening: false,
                });
                layer.add(fixedLabel);
            } else {
                // Draw sliding panel (operable glass)
                const glassRect = new Konva.Rect({
                    x: panelX,
                    y: panelY,
                    width: panelWidth,
                    height: panelHeight,
                    fill: gStyle.fill,
                    opacity: gStyle.opacity,
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width,
                    listening: false,
                });
                layer.add(glassRect);

            }

            // Add panel divider (vertical line between panels)
            if (i < numberOfPanels - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, panelY, panelX + panelWidth, panelY + panelHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
        }
    }

    // Draw outer frame
    const outerFrame = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: 'transparent',
        stroke: fStyle.color,
        strokeWidth: fStyle.width * 1.5,
        listening: false,
    });
    layer.add(outerFrame);

    // Draw dimensions (same as single panel)
    // Dimension line defaults per KONVA_DEFAULT_OPTIONS_REFERENCE.md
    const dimColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-dark').trim() || '#333';
    const DIM_EXTENSION = 20; // Extension line length (pixels)
    const DIM_LINE_OFFSET = 15; // Distance from glass to dimension line

    // Width dimension (top)
    layer.add(new Konva.Line({
        points: [offsetX, offsetY, offsetX, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    layer.add(new Konva.Line({
        points: [offsetX + totalWidth, offsetY, offsetX + totalWidth, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    layer.add(new Konva.Line({
        points: [offsetX, offsetY - DIM_LINE_OFFSET, offsetX + totalWidth, offsetY - DIM_LINE_OFFSET],
        stroke: dimColor,
        strokeWidth: 1.5,
        dash: [5, 3],
        listening: false
    }));
    const widthText = `${originalWidth}${unit}`;
    layer.add(new Konva.Text({
        x: offsetX + totalWidth / 2,
        y: offsetY - DIM_LINE_OFFSET - 18,
        text: widthText,
        fontSize: 11,
        fontFamily: 'Montserrat, Arial',
        fontStyle: 'normal',
        fill: dimColor,
        align: 'center',
        offsetX: (widthText.length * 6) / 2,
        listening: false,
    }));

    // Height dimension (right side)
    layer.add(new Konva.Line({
        points: [offsetX + totalWidth, offsetY, offsetX + totalWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    layer.add(new Konva.Line({
        points: [offsetX + totalWidth, offsetY + totalHeight, offsetX + totalWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY + totalHeight],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    layer.add(new Konva.Line({
        points: [offsetX + totalWidth + DIM_LINE_OFFSET, offsetY, offsetX + totalWidth + DIM_LINE_OFFSET, offsetY + totalHeight],
        stroke: dimColor,
        strokeWidth: 1.5,
        dash: [5, 3],
        listening: false
    }));
    const heightText = `${originalHeight}${heightUnit}`;
    layer.add(new Konva.Text({
        x: offsetX + totalWidth + DIM_LINE_OFFSET + 18,
        y: offsetY + totalHeight / 2,
        text: heightText,
        fontSize: 11,
        fontFamily: 'Montserrat, Arial',
        fontStyle: 'normal',
        fill: dimColor,
        align: 'center',
        rotation: 90,
        offsetX: (heightText.length * 6) / 2,
        listening: false,
    }));

    // Inner height dimension (h1) - shows height of sliding section when transom is present
    if (hasTransom) {
        // Use h1 input value if provided, otherwise calculate from ratio
        let innerHeightValue;
        let innerHeightUnit = heightUnit;

        if (actualInnerHeight !== null) {
            // Use the h1 input value
            innerHeightValue = actualInnerHeight;
            innerHeightUnit = h1Unit;
        } else {
            // Calculate the actual inner height based on the transom ratio
            const transomHeightRatio = 0.3;
            const innerHeightRatio = 1 - transomHeightRatio;
            innerHeightValue = originalHeight * innerHeightRatio;
        }

        // Determine the start and end Y positions for the inner height dimension
        const innerHeightStartY = isFixedTransomHead ? offsetY + upperSectionHeight : offsetY;
        const innerHeightEndY = isFixedTransomHead ? offsetY + totalHeight : offsetY + upperSectionHeight;

        // Use blue color for inner height dimension (h1) as shown in the image
        const innerHeightColor = '#0066CC'; // Blue color for h1 dimension

        // Draw inner height dimension on the left side
        const INNER_DIM_OFFSET = 25; // Offset from left edge
        layer.add(new Konva.Line({
            points: [offsetX, innerHeightStartY, offsetX - INNER_DIM_OFFSET - DIM_EXTENSION, innerHeightStartY],
            stroke: innerHeightColor,
            strokeWidth: 1.5,
            listening: false
        }));
        layer.add(new Konva.Line({
            points: [offsetX, innerHeightEndY, offsetX - INNER_DIM_OFFSET - DIM_EXTENSION, innerHeightEndY],
            stroke: innerHeightColor,
            strokeWidth: 1.5,
            listening: false
        }));
        layer.add(new Konva.Line({
            points: [offsetX - INNER_DIM_OFFSET, innerHeightStartY, offsetX - INNER_DIM_OFFSET, innerHeightEndY],
            stroke: innerHeightColor,
            strokeWidth: 1.5,
            dash: [5, 3],
            listening: false
        }));

        // Format inner height value (round to 1 decimal place)
        const formattedInnerHeight = innerHeightValue.toFixed(1);
        const innerHeightText = `${formattedInnerHeight}${innerHeightUnit}`;
        layer.add(new Konva.Text({
            x: offsetX - INNER_DIM_OFFSET - 18,
            y: innerHeightStartY + (innerHeightEndY - innerHeightStartY) / 2,
            text: innerHeightText,
            fontSize: 11,
            fontFamily: 'Montserrat, Arial',
            fontStyle: 'normal',
            fill: innerHeightColor,
            align: 'center',
            rotation: 90,
            offsetX: (innerHeightText.length * 6) / 2,
            listening: false,
        }));

        // Add h2 (fixed transom height) dimension display
        let transomHeightValue;
        let transomHeightUnit = heightUnit;

        // Get h2 value from input if available
        const h2InputGroup = inputGroupH2 || document.getElementById('input-group-h2');
        const h2Input = inputH2 || document.getElementById('input-h2');
        const h2UnitBtn = btnUnitH2 || document.getElementById('btn-unit-h2');

        if (h2Value !== null && h2Value > 0) {
            transomHeightValue = h2Value;
            transomHeightUnit = h2Unit;
        } else if (h2Input && h2Input.value && h2InputGroup && !h2InputGroup.classList.contains('hidden-step') && h2InputGroup.style.display !== 'none') {
            const h2InputValue = parseFloat(h2Input.value);
            if (!isNaN(h2InputValue) && h2InputValue > 0) {
                transomHeightValue = h2InputValue;
                if (h2UnitBtn) {
                    transomHeightUnit = h2UnitBtn.getAttribute('data-current-unit') || heightUnit;
                }
            }
        }

        // If h2 value is available, display it
        if (transomHeightValue !== undefined && transomHeightValue > 0) {
            // Determine positions for transom height dimension
            const transomHeightStartY = isFixedTransomHead ? offsetY : offsetY + upperSectionHeight;
            const transomHeightEndY = isFixedTransomHead ? offsetY + upperSectionHeight : offsetY + totalHeight;

            // Use a different color for h2 (e.g., green) to distinguish from h1
            const transomHeightColor = '#00AA00'; // Green color for h2 dimension

            // Draw transom height dimension on the left side (offset further left than h1)
            const H2_DIM_OFFSET = 50; // Further left than h1
            layer.add(new Konva.Line({
                points: [offsetX, transomHeightStartY, offsetX - H2_DIM_OFFSET - DIM_EXTENSION, transomHeightStartY],
                stroke: transomHeightColor,
                strokeWidth: 1.5,
                listening: false
            }));
            layer.add(new Konva.Line({
                points: [offsetX, transomHeightEndY, offsetX - H2_DIM_OFFSET - DIM_EXTENSION, transomHeightEndY],
                stroke: transomHeightColor,
                strokeWidth: 1.5,
                listening: false
            }));
            layer.add(new Konva.Line({
                points: [offsetX - H2_DIM_OFFSET, transomHeightStartY, offsetX - H2_DIM_OFFSET, transomHeightEndY],
                stroke: transomHeightColor,
                strokeWidth: 1.5,
                dash: [5, 3],
                listening: false
            }));

            // Format transom height value (remove "h2:" prefix, just show value)
            const formattedTransomHeight = transomHeightValue.toFixed(1);
            const transomHeightText = `${formattedTransomHeight}${transomHeightUnit}`;
            layer.add(new Konva.Text({
                x: offsetX - H2_DIM_OFFSET - 18,
                y: transomHeightStartY + (transomHeightEndY - transomHeightStartY) / 2,
                text: transomHeightText,
                fontSize: 11,
                fontFamily: 'Montserrat, Arial',
                fontStyle: 'normal',
                fill: transomHeightColor,
                align: 'center',
                rotation: 90,
                offsetX: (transomHeightText.length * 6) / 2,
                listening: false,
            }));
        }
    }

    // Annotations
    // Get frameColor from customizationValues (for sliding windows)
    let frameColorValue = customizationValues.frameColor || customizationValues.FrameColor || frameType || '';
    if (Array.isArray(frameColorValue)) {
        frameColorValue = frameColorValue[0] || '';
    }
    const formatFrame = frameColorValue ? String(frameColorValue) : '';
    const formatThickness = thickness.replace(/mm+$/g, '') + 'mm';
    const annotationText = formatFrame ?
        `Thickness: ${formatThickness}  |  Frame: ${formatFrame}` :
        `Thickness: ${formatThickness}`;

    layer.add(new Konva.Text({
        x: offsetX + totalWidth / 2,
        y: offsetY + totalHeight + 15,
        text: annotationText,
        fontSize: 11,
        fontStyle: 'bold',
        fontFamily: 'Montserrat',
        fill: '#555',
        offsetX: (annotationText.length * 6) / 2,
        listening: false,
    }));

    layer.draw();
}

/**
 * Extracts panel count from string (e.g., "2-panel" -> 2, "3-panel" -> 3)
 */
function extractPanelCount(panelString) {
    if (!panelString) return 2; // Default to 2 panels
    const match = panelString.toString().match(/(\d+)/);
    return match ? parseInt(match[1], 10) : 2;
}

/**
 * Checks if product should use multi-panel rendering based on customization options
 */
function shouldUseMultiPanelRendering(customizationValues = {}) {
    // Check if NumberOfPanels or similar field exists
    const hasPanelField = customizationValues.numberOfPanels ||
        customizationValues.NumberOfPanels ||
        customizationValues.panelCount ||
        customizationValues.PanelCount;

    if (!hasPanelField) return false;

    // Extract panel count
    const panelCount = extractPanelCount(hasPanelField);

    // Use multi-panel if more than 1 panel
    return panelCount > 1;
}

/**
 * Renders the 2D window figure.
 * Synced with customization_fields_presets_summary.md
 * Enhanced to support multi-panel configurations
 */
function renderWindow(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType, originalWidth, originalHeight, heightUnit, cornerRadiusIn = 0) {
    if (!layer) return; // Konva not initialized
    layer.destroyChildren();

    // Check if we should use multi-panel rendering
    const customizationValues = window.selectedCustomizationValues || {};
    if (shouldUseMultiPanelRendering(customizationValues)) {
        // Use multi-panel rendering
        renderMultiPanelProduct(
            widthIn,
            heightIn,
            unit,
            glassType,
            thickness,
            edgeWork,
            frameType,
            originalWidth,
            originalHeight,
            heightUnit,
            customizationValues
        );
        return;
    }

    // Ratio and Scale
    const actualRatio = widthIn / heightIn;
    let windowWidth, windowHeight;

    if (actualRatio > 1) {
        windowWidth = DRAWING_SIZE;
        windowHeight = DRAWING_SIZE / actualRatio;
    } else {
        windowHeight = DRAWING_SIZE;
        windowWidth = DRAWING_SIZE * actualRatio;
    }

    const offsetX = (STAGE_SIZE - windowWidth) / 2;
    const offsetY = (STAGE_SIZE - windowHeight) / 2;

    // Normalize values (handle preset values)
    // Check for mirror tint in customization values
    let effectiveGlassType = glassType;
    if (customizationValues.tint || customizationValues.Tint) {
        const tint = (customizationValues.tint || customizationValues.Tint).toLowerCase();
        if (tint === 'clear') effectiveGlassType = 'mirror-clear';
        else if (tint === 'bronze') effectiveGlassType = 'mirror-bronze';
        else if (tint.includes('grey') || tint.includes('gray') || tint.includes('smoked')) effectiveGlassType = 'mirror-grey-smoked';
        else if (tint === 'black') effectiveGlassType = 'mirror-black';
    }

    const normalizedGlassType = normalizeGlassType(effectiveGlassType);
    const normalizedFrameType = normalizeFrameType(frameType);
    const normalizedShape = normalizeShape(shape);

    // Check for separate frame color in customization values (for mirrors)
    let frameColor = null;
    let frameColorValue = null;
    if (customizationValues.frameColor || customizationValues.FrameColor) {
        frameColorValue = (customizationValues.frameColor || customizationValues.FrameColor);
        const colorValue = frameColorValue.toLowerCase();
        frameColor = normalizeFrameColor(colorValue);
        console.log(`[Konva] Frame color from customizationValues: "${frameColorValue}" -> normalized: "${frameColor}"`);
    }

    // Styles - with fallback color handling
    const gStyle = glassStyles[normalizedGlassType] || glassStyles['clear'];
    let fStyle = frameStyles[normalizedFrameType];

    // If frame style not found, try to create a sensible default based on color name
    if (!fStyle) {
        const colorName = normalizedFrameType.toLowerCase();
        let fallbackColor = '#FFFFFF'; // Default white

        // Common color mappings
        const commonColors = {
            'gold': '#FFD700',
            'silver': '#C0C0C0',
            'bronze': '#CD7F32',
            'black': '#000000',
            'white': '#FFFFFF',
            'rose': '#B76E79',
            'chrome': '#E8E8E8',
            'nickel': '#A8A9AD',
            'stainless': '#C9CCD1',
            'wood': '#795548',
            'brown': '#8B4513',
            'gray': '#808080',
            'grey': '#808080'
        };

        // Find matching color
        for (const [key, color] of Object.entries(commonColors)) {
            if (colorName.includes(key)) {
                fallbackColor = color;
                break;
            }
        }

        console.log(`[Konva] Frame style "${normalizedFrameType}" not found, using fallback color: ${fallbackColor}`);
        fStyle = { color: fallbackColor, width: 4 };

        // Add to frameStyles for future use
        frameStyles[normalizedFrameType] = fStyle;
    }

    // Override frame color if frameColor is specified separately (for mirrors)
    // Also try to get color directly from frameStyles if normalizeFrameColor returned null
    if (frameColorValue && !frameColor) {
        // If normalizeFrameColor returned null, try to find it directly in frameStyles
        const normalizedColorKey = frameColorValue.toLowerCase().replace(/\s+/g, '-');
        const spacedColorKey = normalizedColorKey.replace(/-/g, ' ');
        if (frameStyles[normalizedColorKey] && frameStyles[normalizedColorKey].color) {
            frameColor = frameStyles[normalizedColorKey].color;
            console.log(`[Konva] Found frame color in frameStyles using key "${normalizedColorKey}": ${frameColor}`);
        } else if (frameStyles[spacedColorKey] && frameStyles[spacedColorKey].color) {
            frameColor = frameStyles[spacedColorKey].color;
            console.log(`[Konva] Found frame color in frameStyles using key "${spacedColorKey}": ${frameColor}`);
        } else {
            // Try to extract color from the frameColorValue name
            const colorName = frameColorValue.toLowerCase();
            const commonColors = {
                'gold': '#FFD700',
                'silver': '#C0C0C0',
                'bronze': '#CD7F32',
                'black': '#000000',
                'white': '#FFFFFF',
                'rose': '#B76E79',
                'chrome': '#E8E8E8',
                'nickel': '#A8A9AD',
                'stainless': '#C9CCD1',
                'wood': '#795548',
                'brown': '#8B4513',
                'gray': '#808080',
                'grey': '#808080'
            };
            for (const [key, color] of Object.entries(commonColors)) {
                if (colorName.includes(key)) {
                    frameColor = color;
                    console.log(`[Konva] Extracted frame color from name "${frameColorValue}": ${frameColor}`);
                    break;
                }
            }
        }
    }

    if (frameColor && fStyle) {
        fStyle = { ...fStyle, color: frameColor };
        console.log(`[Konva] Applied frame color: ${frameColor}, width: ${fStyle.width}`);
    } else if (frameColorValue) {
        console.warn(`[Konva] Frame color value "${frameColorValue}" found but could not be normalized. Using frameType: ${normalizedFrameType}`);
    }

    // Draw glass shape based on preset shapes
    let glassShape;
    const centerX = offsetX + windowWidth / 2;
    const centerY = offsetY + windowHeight / 2;
    const minRadius = Math.min(windowWidth, windowHeight) / 2;

    // Corner radius (inches -> pixels), used for rectangle/square only
    // Support individual corner radius values or single value (linked mode)
    let cornerRadiusPx = 0;
    let cornerRadiusArray = null;

    // Check if cornerRadiusIn is an object with individual corners
    // Also check customizationValues for corner radius data
    const cornerRadiusData = customizationValues?.cornerRadius || customizationValues?.CornerRadius || cornerRadiusIn;

    if (typeof cornerRadiusData === 'object' && cornerRadiusData !== null && !Array.isArray(cornerRadiusData)) {
        // Individual corner radius values from object
        const pxPerInX = widthIn > 0 ? (windowWidth / widthIn) : 0;
        const pxPerInY = heightIn > 0 ? (windowHeight / heightIn) : 0;
        const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);

        const topLeft = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.topLeft || 0)) * (pxPerIn || 0));
        const topRight = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.topRight || 0)) * (pxPerIn || 0));
        const bottomRight = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.bottomRight || 0)) * (pxPerIn || 0));
        const bottomLeft = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.bottomLeft || 0)) * (pxPerIn || 0));

        cornerRadiusArray = [topLeft, topRight, bottomRight, bottomLeft];
    } else {
        // Single value (linked mode) - convert to array format
        const safeCornerRadiusIn = Math.max(0, parseFloat(cornerRadiusData) || 0);
        const pxPerInX = widthIn > 0 ? (windowWidth / widthIn) : 0;
        const pxPerInY = heightIn > 0 ? (windowHeight / heightIn) : 0;
        const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);
        cornerRadiusPx = Math.min(minRadius, safeCornerRadiusIn * (pxPerIn || 0));
        cornerRadiusArray = [cornerRadiusPx, cornerRadiusPx, cornerRadiusPx, cornerRadiusPx];
    }

    if (normalizedShape === 'round' || normalizedShape === 'circle') {
        // Circle
        glassShape = new Konva.Circle({
            x: centerX,
            y: centerY,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'oval' || normalizedShape === 'ellipse') {
        // Ellipse
        glassShape = new Konva.Ellipse({
            x: centerX,
            y: centerY,
            radiusX: windowWidth / 2,
            radiusY: windowHeight / 2,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'triangle') {
        // Triangle - 3-sided polygon
        const points = [
            centerX, offsetY,                    // Top point
            offsetX, offsetY + windowHeight,     // Bottom left
            offsetX + windowWidth, offsetY + windowHeight // Bottom right
        ];
        glassShape = new Konva.Line({
            points: points,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            closed: true,
            listening: false,
        });
    } else if (normalizedShape === 'pentagon') {
        // Pentagon - 5-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 5,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'hexagon') {
        // Hexagon - 6-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 6,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'octagon') {
        // Octagon - 8-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 8,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'star') {
        // Star - 5-pointed star
        glassShape = new Konva.Star({
            x: centerX,
            y: centerY,
            numPoints: 5,
            innerRadius: minRadius * 0.5,
            outerRadius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
    } else if (normalizedShape === 'diamond') {
        // Diamond - 4-sided polygon rotated 45 degrees
        const points = [
            centerX, offsetY,                    // Top
            offsetX + windowWidth, centerY,      // Right
            centerX, offsetY + windowHeight,     // Bottom
            offsetX, centerY                    // Left
        ];
        glassShape = new Konva.Line({
            points: points,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            closed: true,
            listening: false,
        });
    } else if (normalizedShape === 'arched') {
        // Arched shape - rectangle with rounded top corners (top-left and top-right)
        // Uses corner radius as the radius for top corners (max radius = half width)
        // Shape has: flat bottom, straight vertical sides, rounded top corners
        // The top corners use the corner radius value (or max possible if larger)
        const maxTopRadius = windowWidth / 2; // Maximum radius is half the width
        const topRadius = cornerRadiusPx > 0 ? Math.min(cornerRadiusPx, maxTopRadius) : maxTopRadius;
        const numPoints = 20; // Number of points for smooth corner arc

        // Use Konva.Rect with individual corner radius
        // Top-left and top-right corners get the radius, bottom corners are 0
        glassShape = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: windowWidth,
            height: windowHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            cornerRadius: [topRadius, topRadius, 0, 0], // [topLeft, topRight, bottomRight, bottomLeft]
            listening: false,
        });
    } else {
        // Rectangle (default) - supports individual corner radius
        const hasCornerRadius = cornerRadiusArray && cornerRadiusArray.some(radius => radius > 0);
        glassShape = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: windowWidth,
            height: windowHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            cornerRadius: hasCornerRadius ? cornerRadiusArray : 0,
            listening: false,
        });
    }
    layer.add(glassShape);

    // Apply frosted overlay when glass color/type indicates frosted
    try {
        let glassColorValue = customizationValues.glassColor || customizationValues.GlassColor || currentGlassType || '';
        if (Array.isArray(glassColorValue)) glassColorValue = glassColorValue[0] || '';
        const glassColorLower = (glassColorValue || '').toString().toLowerCase();

        const shouldFrost = glassColorLower.includes('frost') || normalizedGlassType === 'frosted' || (gStyle && gStyle.patternType === 'frosted');
        if (shouldFrost) {
            const patternDensity = (gStyle && gStyle.patternDensity) ? gStyle.patternDensity : 6;
            // Choose a neutral pattern color that blends with most fills
            const patternColor = (gStyle && typeof gStyle.fill === 'string' && gStyle.fill.indexOf('#') === 0) ? '#ffffff' : '#ffffff';
            drawKonvaPatternOverlay(layer, offsetX, offsetY, windowWidth, windowHeight, 'frosted', patternDensity, patternColor);
        }
    } catch (e) {
        console.warn('[Konva] Frost overlay error:', e && e.message);
    }

    // Draw grid frame pattern if frame type is grid-frame
    if (normalizedFrameType === 'grid-frame' && fStyle.width > 0) {
        drawGridFrame(offsetX, offsetY, windowWidth, windowHeight, fStyle);
    }

    // Apply lighting effects for mirrors (LED Backlight, LED Front Light)
    applyLightingEffects(glassShape, customizationValues, offsetX, offsetY, windowWidth, windowHeight);

    // Apply orientation visualization (for mirrors)
    applyOrientationVisualization(customizationValues, offsetX, offsetY, windowWidth, windowHeight);

    // Apply mounting method visualization (for mirrors)
    applyMountingMethodVisualization(customizationValues, offsetX, offsetY, windowWidth, windowHeight);

    // Draw Dimensions (Reference style: extension lines with dashed dimension line and labels)
    const dimColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-dark').trim() || '#333';
    const DIM_EXTENSION = 20; // Extension line length
    const DIM_LINE_OFFSET = 15; // Distance from glass panel to dimension line

    // Width Dimension (at top) - Reference: horizontal dashed line with "35in" label
    // Left extension line (vertical line extending upward from left corner)
    layer.add(new Konva.Line({
        points: [offsetX, offsetY, offsetX, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    // Right extension line (vertical line extending upward from right corner)
    layer.add(new Konva.Line({
        points: [offsetX + windowWidth, offsetY, offsetX + windowWidth, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    // Horizontal dashed dimension line (reference style)
    layer.add(new Konva.Line({
        points: [offsetX, offsetY - DIM_LINE_OFFSET, offsetX + windowWidth, offsetY - DIM_LINE_OFFSET],
        stroke: dimColor,
        strokeWidth: 1.5,
        dash: [5, 3],
        listening: false
    }));
    // Width label text (centered above dimension line)
    // Use original values if provided, otherwise use converted inches
    const widthValue = originalWidth !== undefined ? originalWidth : widthIn;
    const widthText = `${widthValue}${unit}`;
    const WIDTH_LABEL_OFFSET = 18; // Space between dimension line and label text
    layer.add(new Konva.Text({
        x: offsetX + windowWidth / 2,
        y: offsetY - DIM_LINE_OFFSET - WIDTH_LABEL_OFFSET,
        text: widthText,
        fontSize: 11,
        fontFamily: 'Montserrat, Arial',
        fontStyle: 'normal',
        fill: dimColor,
        align: 'center',
        offsetX: (widthText.length * 6) / 2,
        listening: false,
    }));

    // Height Dimension (on right side) - Reference: vertical dashed line with "45in" label
    // Top extension line (horizontal line extending rightward from top corner)
    layer.add(new Konva.Line({
        points: [offsetX + windowWidth, offsetY, offsetX + windowWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    // Bottom extension line (horizontal line extending rightward from bottom corner)
    layer.add(new Konva.Line({
        points: [offsetX + windowWidth, offsetY + windowHeight, offsetX + windowWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY + windowHeight],
        stroke: dimColor,
        strokeWidth: 1.5,
        listening: false
    }));
    // Vertical dashed dimension line (reference style)
    layer.add(new Konva.Line({
        points: [offsetX + windowWidth + DIM_LINE_OFFSET, offsetY, offsetX + windowWidth + DIM_LINE_OFFSET, offsetY + windowHeight],
        stroke: dimColor,
        strokeWidth: 1.5,
        dash: [5, 3],
        listening: false
    }));
    // Height label text (rotated, centered on dimension line)
    // Use original values if provided, otherwise use converted inches
    // Use heightUnit if provided, otherwise fall back to unit parameter
    const heightValue = originalHeight !== undefined ? originalHeight : heightIn;
    const heightLabelUnit = heightUnit !== undefined ? heightUnit : unit;
    const heightText = `${heightValue}${heightLabelUnit}`;
    const HEIGHT_LABEL_OFFSET = 18; // Space between dimension line and label text
    layer.add(new Konva.Text({
        x: offsetX + windowWidth + DIM_LINE_OFFSET + HEIGHT_LABEL_OFFSET,
        y: offsetY + windowHeight / 2,
        text: heightText,
        fontSize: 11,
        fontFamily: 'Montserrat, Arial',
        fontStyle: 'normal',
        fill: dimColor,
        align: 'center',
        rotation: 90,
        offsetX: (heightText.length * 6) / 2,
        listening: false,
    }));

    // Draw corner radius annotations (if applicable)
    drawCornerRadiusAnnotations(customizationValues, offsetX, offsetY, windowWidth, windowHeight, normalizedShape);

    // Annotations - Reference format: "Thickness: 6mm" and "Edge: Polished"
    const formatEdge = edgeWork.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    const formatThickness = thickness.replace(/mm+$/g, '') + 'mm'; // Ensure mm format

    // Display thickness and edge info below the glass panel
    const annotationText = `Thickness: ${formatThickness}  |  Edge: ${formatEdge}`;

    layer.add(new Konva.Text({
        x: offsetX + windowWidth / 2,
        y: offsetY + windowHeight + 15,
        text: annotationText,
        fontSize: 11,
        fontStyle: 'bold',
        fontFamily: 'Montserrat',
        fill: '#555',
        offsetX: (annotationText.length * 6) / 2,
        listening: false,
    }));

    layer.draw();
}

/**
 * Draw corner radius annotations on the Konva canvas
 * Shows radius values and labels at each corner
 */
function drawCornerRadiusAnnotations(customizationValues, offsetX, offsetY, windowWidth, windowHeight, shape) {
    if (!customizationValues) return;

    // Only show for rectangle/square shapes
    const rectangleShapes = ['rectangle', 'square'];
    const normalizedShapeLower = shape.toLowerCase();
    const isRectangleShape = rectangleShapes.includes(shape) ||
        normalizedShapeLower.includes('rectangle') ||
        normalizedShapeLower.includes('square');
    if (!isRectangleShape) return;

    // Get corner radius data
    const cornerRadiusData = customizationValues.cornerRadius || customizationValues.CornerRadius || customizationValues.cornerRadiusIn;
    const cornerRadiusUnit = customizationValues.cornerRadius_unit || customizationValues.CornerRadius_unit || 'in';

    if (!cornerRadiusData) return;

    // Helper function to convert from inches to display unit
    function convertFromInches(value, unit) {
        switch (unit) {
            case 'cm': return value * 2.54;
            case 'mm': return value * 25.4;
            default: return value; // inches
        }
    }

    // Get the actual values in the selected unit
    let cornerValues = {};
    let isLinked = true;

    if (typeof cornerRadiusData === 'object' && cornerRadiusData !== null && !Array.isArray(cornerRadiusData)) {
        // Individual corner values (stored in inches, need to convert to display unit)
        isLinked = false;
        cornerValues = {
            topLeft: convertFromInches(cornerRadiusData.topLeft || 0, cornerRadiusUnit),
            topRight: convertFromInches(cornerRadiusData.topRight || 0, cornerRadiusUnit),
            bottomRight: convertFromInches(cornerRadiusData.bottomRight || 0, cornerRadiusUnit),
            bottomLeft: convertFromInches(cornerRadiusData.bottomLeft || 0, cornerRadiusUnit)
        };
    } else {
        // Single value (linked mode, stored in inches)
        const valueIn = parseFloat(cornerRadiusData) || 0;
        const value = convertFromInches(valueIn, cornerRadiusUnit);
        cornerValues = {
            topLeft: value,
            topRight: value,
            bottomRight: value,
            bottomLeft: value
        };
    }

    // Only draw if at least one corner has a radius > 0
    const hasRadius = Object.values(cornerValues).some(v => v > 0);
    if (!hasRadius) return;

    const radiusColor = '#666666';
    const radiusLabelSize = 10;
    const radiusOffset = 12; // Distance from corner

    // Draw corner radius indicators at each corner
    const corners = [
        { key: 'topLeft', x: offsetX, y: offsetY, labelX: offsetX + radiusOffset, labelY: offsetY + radiusOffset },
        { key: 'topRight', x: offsetX + windowWidth, y: offsetY, labelX: offsetX + windowWidth - radiusOffset, labelY: offsetY + radiusOffset },
        { key: 'bottomRight', x: offsetX + windowWidth, y: offsetY + windowHeight, labelX: offsetX + windowWidth - radiusOffset, labelY: offsetY + windowHeight - radiusOffset },
        { key: 'bottomLeft', x: offsetX, y: offsetY + windowHeight, labelX: offsetX + radiusOffset, labelY: offsetY + windowHeight - radiusOffset }
    ];

    corners.forEach(corner => {
        const radiusValue = cornerValues[corner.key];
        if (radiusValue > 0) {
            // Calculate label position outside the shape
            let labelX, labelY, arcX, arcY;
            const outsideOffset = 26;
            const labelNudge = 6;

            if (corner.key === 'topLeft') {
                labelX = offsetX - outsideOffset - labelNudge;
                labelY = offsetY - outsideOffset - labelNudge;
                arcX = offsetX;
                arcY = offsetY;
            } else if (corner.key === 'topRight') {
                labelX = offsetX + windowWidth + outsideOffset + labelNudge;
                labelY = offsetY - outsideOffset - labelNudge;
                arcX = offsetX + windowWidth;
                arcY = offsetY;
            } else if (corner.key === 'bottomRight') {
                labelX = offsetX + windowWidth + outsideOffset + labelNudge;
                labelY = offsetY + windowHeight + outsideOffset + labelNudge;
                arcX = offsetX + windowWidth;
                arcY = offsetY + windowHeight;
            } else { // bottomLeft
                labelX = offsetX - outsideOffset - labelNudge;
                labelY = offsetY + windowHeight + outsideOffset + labelNudge;
                arcX = offsetX;
                arcY = offsetY + windowHeight;
            }

            // Draw arc indicator at corner (visual representation of radius)
            const arcSize = Math.min(20, Math.max(8, radiusValue * 1.5)); // Visual arc size
            let arcRotation = 0;
            if (corner.key === 'topLeft') arcRotation = 180;
            else if (corner.key === 'topRight') arcRotation = 270;
            else if (corner.key === 'bottomRight') arcRotation = 0;
            else arcRotation = 90; // bottomLeft

            const arc = new Konva.Arc({
                x: arcX,
                y: arcY,
                innerRadius: 0,
                outerRadius: arcSize,
                angle: 90,
                rotation: arcRotation,
                stroke: radiusColor,
                strokeWidth: 1.5,
                fill: 'transparent',
                listening: false
            });
            layer.add(arc);

            // Draw dashed line from corner to label
            layer.add(new Konva.Line({
                points: [arcX, arcY, labelX, labelY],
                stroke: radiusColor,
                strokeWidth: 1,
                dash: [4, 3],
                listening: false
            }));

            // Draw radius label with "R" prefix
            const labelText = `R ${radiusValue.toFixed(1)}${cornerRadiusUnit}`;
            const radiusLabel = new Konva.Text({
                x: labelX,
                y: labelY,
                text: labelText,
                fontSize: radiusLabelSize,
                fontFamily: 'Montserrat, Arial',
                fontStyle: 'normal',
                fill: radiusColor,
                align: 'left',
                offsetX: corner.key.includes('Right') ? (labelText.length * 5) : 0,
                offsetY: corner.key.includes('Bottom') ? -6 : 6,
                listening: false
            });
            layer.add(radiusLabel);
        }
    });

    // If all corners have the same value (linked), also show a summary label in the center
    if (isLinked && cornerValues.topLeft > 0) {
        const allSame = cornerValues.topLeft === cornerValues.topRight &&
            cornerValues.topRight === cornerValues.bottomRight &&
            cornerValues.bottomRight === cornerValues.bottomLeft;

        if (allSame) {
            const centerX = offsetX + windowWidth / 2;
            const centerY = offsetY + windowHeight / 2;
            const labelText = `Corner Radius: ${cornerValues.topLeft.toFixed(1)}${cornerRadiusUnit}`;

            layer.add(new Konva.Text({
                x: centerX,
                y: centerY,
                text: labelText,
                fontSize: 10,
                fontFamily: 'Montserrat, Arial',
                fontStyle: 'normal',
                fill: '#888888',
                align: 'center',
                offsetX: (labelText.length * 5) / 2,
                offsetY: 6,
                listening: false
            }));
        }
    }
}

/**
 * Draw grid frame pattern inside the mirror
 * @param {number} offsetX - X offset of the glass panel
 * @param {number} offsetY - Y offset of the glass panel
 * @param {number} windowWidth - Width of the glass panel
 * @param {number} windowHeight - Height of the glass panel
 * @param {Object} fStyle - Frame style object
 */
function drawGridFrame(offsetX, offsetY, windowWidth, windowHeight, fStyle) {
    const gridSpacing = Math.min(windowWidth, windowHeight) / 4; // 4x4 grid
    const gridColor = fStyle.color || '#333333';
    const gridWidth = 1.5;

    // Draw vertical grid lines
    for (let i = 1; i < 4; i++) {
        const x = offsetX + (windowWidth / 4) * i;
        layer.add(new Konva.Line({
            points: [x, offsetY, x, offsetY + windowHeight],
            stroke: gridColor,
            strokeWidth: gridWidth,
            listening: false
        }));
    }

    // Draw horizontal grid lines
    for (let i = 1; i < 4; i++) {
        const y = offsetY + (windowHeight / 4) * i;
        layer.add(new Konva.Line({
            points: [offsetX, y, offsetX + windowWidth, y],
            stroke: gridColor,
            strokeWidth: gridWidth,
            listening: false
        }));
    }
}

/**
 * Apply orientation visualization (for mirrors)
 * Shows visual indicator for vertical, horizontal, or full-body orientation
 * @param {Object} customizationValues - Customization values object
 * @param {number} offsetX - X offset of the glass panel
 * @param {number} offsetY - Y offset of the glass panel
 * @param {number} windowWidth - Width of the glass panel
 * @param {number} windowHeight - Height of the glass panel
 */
function applyOrientationVisualization(customizationValues, offsetX, offsetY, windowWidth, windowHeight) {
    if (!customizationValues) return;

    const orientation = (customizationValues.orientation || customizationValues.Orientation || '').toLowerCase();
    if (!orientation || orientation === 'full-body') return; // Full-body doesn't need indicator

    const centerX = offsetX + windowWidth / 2;
    const centerY = offsetY + windowHeight / 2;
    const indicatorColor = '#666666';
    const indicatorSize = 8;

    if (orientation === 'vertical') {
        // Draw vertical arrow indicator (pointing up/down)
        const arrowY = offsetY + 15;
        layer.add(new Konva.Arrow({
            points: [centerX, arrowY, centerX, arrowY + 20],
            pointerLength: 6,
            pointerWidth: 6,
            fill: indicatorColor,
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
        // Add label
        layer.add(new Konva.Text({
            x: centerX,
            y: arrowY + 25,
            text: 'Vertical',
            fontSize: 9,
            fontFamily: 'Montserrat, Arial',
            fill: indicatorColor,
            align: 'center',
            offsetX: 20,
            listening: false
        }));
    } else if (orientation === 'horizontal') {
        // Draw horizontal arrow indicator (pointing left/right)
        const arrowX = offsetX + 15;
        layer.add(new Konva.Arrow({
            points: [arrowX, centerY, arrowX + 20, centerY],
            pointerLength: 6,
            pointerWidth: 6,
            fill: indicatorColor,
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
        // Add label
        layer.add(new Konva.Text({
            x: arrowX + 10,
            y: centerY - 10,
            text: 'Horizontal',
            fontSize: 9,
            fontFamily: 'Montserrat, Arial',
            fill: indicatorColor,
            align: 'center',
            offsetX: 25,
            rotation: -90,
            listening: false
        }));
    }
}

/**
 * Apply mounting method visualization (for mirrors)
 * Shows visual indicator for mounting method
 * @param {Object} customizationValues - Customization values object
 * @param {number} offsetX - X offset of the glass panel
 * @param {number} offsetY - Y offset of the glass panel
 * @param {number} windowWidth - Width of the glass panel
 * @param {number} windowHeight - Height of the glass panel
 */
function applyMountingMethodVisualization(customizationValues, offsetX, offsetY, windowWidth, windowHeight) {
    if (!customizationValues) return;

    const mountingMethod = (customizationValues.mountingMethod || customizationValues.MountingMethod || '').toLowerCase();
    if (!mountingMethod) return;

    const indicatorColor = '#888888';
    const iconSize = 12;

    // Position indicator at bottom-right corner
    const iconX = offsetX + windowWidth - 25;
    const iconY = offsetY + windowHeight - 25;

    if (mountingMethod.includes('wall') || mountingMethod.includes('mounted')) {
        // Wall-mounted: Draw wall bracket icon
        layer.add(new Konva.Line({
            points: [iconX, iconY, iconX + iconSize, iconY, iconX + iconSize, iconY + iconSize],
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
        layer.add(new Konva.Circle({
            x: iconX + iconSize / 2,
            y: iconY + iconSize / 2,
            radius: 2,
            fill: indicatorColor,
            listening: false
        }));
    } else if (mountingMethod.includes('freestanding')) {
        // Freestanding: Draw stand icon
        layer.add(new Konva.Line({
            points: [iconX + iconSize / 2, iconY, iconX + iconSize / 2, iconY + iconSize],
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
        layer.add(new Konva.Line({
            points: [iconX, iconY + iconSize, iconX + iconSize, iconY + iconSize],
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
    } else if (mountingMethod.includes('leaning')) {
        // Leaning: Draw leaning indicator
        layer.add(new Konva.Line({
            points: [iconX, iconY + iconSize, iconX + iconSize, iconY],
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
    } else if (mountingMethod.includes('hanging')) {
        // Hanging: Draw hook icon
        layer.add(new Konva.Line({
            points: [iconX + iconSize / 2, iconY, iconX + iconSize / 2, iconY + iconSize / 2],
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
        layer.add(new Konva.Arc({
            x: iconX + iconSize / 2,
            y: iconY + iconSize / 2,
            innerRadius: 0,
            outerRadius: iconSize / 3,
            angle: 180,
            fill: 'transparent',
            stroke: indicatorColor,
            strokeWidth: 2,
            listening: false
        }));
    } else if (mountingMethod.includes('adhesive')) {
        // Adhesive: Draw adhesive dots
        layer.add(new Konva.Circle({
            x: iconX + iconSize / 3,
            y: iconY + iconSize / 3,
            radius: 2,
            fill: indicatorColor,
            listening: false
        }));
        layer.add(new Konva.Circle({
            x: iconX + (iconSize * 2) / 3,
            y: iconY + (iconSize * 2) / 3,
            radius: 2,
            fill: indicatorColor,
            listening: false
        }));
    }
}

/**
 * Apply lighting effects based on customization values (for mirrors)
 * @param {Konva.Shape} glassShape - The glass shape to apply effects to
 * @param {Object} customizationValues - Customization values object
 * @param {number} offsetX - X offset of the glass panel
 * @param {number} offsetY - Y offset of the glass panel
 * @param {number} windowWidth - Width of the glass panel
 * @param {number} windowHeight - Height of the glass panel
 */
function applyLightingEffects(glassShape, customizationValues, offsetX, offsetY, windowWidth, windowHeight) {
    if (!customizationValues || !glassShape) return;

    const lighting = (customizationValues.lighting || customizationValues.Lighting || '').toLowerCase();
    const ledColor = (customizationValues.ledColor || customizationValues.LEDColor || '').toLowerCase();

    // If no lighting, return early
    if (!lighting || lighting === 'none') {
        return;
    }

    // Determine shadow color based on LED color
    let shadowColor = '#FFFFFF'; // Default white
    let shadowBlur = 20;
    let shadowOpacity = 0.5;

    if (ledColor) {
        switch (ledColor) {
            case 'warm white':
                shadowColor = '#FFF8E1';
                shadowBlur = 25;
                shadowOpacity = 0.6;
                break;
            case 'cool white':
                shadowColor = '#E3F2FD';
                shadowBlur = 25;
                shadowOpacity = 0.6;
                break;
            case 'daylight':
                shadowColor = '#E3F2FD';
                shadowBlur = 30;
                shadowOpacity = 0.7;
                break;
            case 'rgb':
                shadowColor = '#E040FB';
                shadowBlur = 25;
                shadowOpacity = 0.6;
                break;
            default:
                shadowColor = '#FFFFFF';
        }
    }

    // Apply different shadow effects based on lighting type
    if (lighting.includes('backlight') || lighting === 'led backlight') {
        // Backlight: glow from behind (stronger, larger blur)
        glassShape.shadowColor(shadowColor);
        glassShape.shadowBlur(shadowBlur + 10);
        glassShape.shadowOpacity(shadowOpacity + 0.1);
        glassShape.shadowOffsetX(0);
        glassShape.shadowOffsetY(0);
    } else if (lighting.includes('front') || lighting === 'led front light') {
        // Front light: glow from front (moderate blur)
        glassShape.shadowColor(shadowColor);
        glassShape.shadowBlur(shadowBlur);
        glassShape.shadowOpacity(shadowOpacity);
        glassShape.shadowOffsetX(0);
        glassShape.shadowOffsetY(0);
    }

    // Add smart features badges if present
    const smartFeatures = customizationValues.smartFeatures || customizationValues.SmartFeatures;
    if (smartFeatures) {
        const features = Array.isArray(smartFeatures) ? smartFeatures : [smartFeatures];
        const centerX = offsetX + windowWidth / 2;
        const centerY = offsetY + windowHeight / 2;
        const badgeY = offsetY + 10; // Top of the panel

        features.forEach((feature, index) => {
            if (!feature || feature.toLowerCase() === 'none') return;

            const featureLower = feature.toLowerCase();
            let badgeText = '';
            let badgeColor = '#4CAF50';

            if (featureLower.includes('dimmer') || featureLower.includes('touch')) {
                badgeText = 'Dimmer';
                badgeColor = '#FF9800';
            } else if (featureLower.includes('defog')) {
                badgeText = 'Defog';
                badgeColor = '#2196F3';
            } else if (featureLower.includes('motion')) {
                badgeText = 'Motion';
                badgeColor = '#4CAF50';
            } else if (featureLower.includes('bluetooth') || featureLower.includes('speaker')) {
                badgeText = 'BT';
                badgeColor = '#9C27B0';
            }

            if (badgeText) {
                const badgeX = offsetX + 10 + (index * 60);
                const badge = new Konva.Circle({
                    x: badgeX,
                    y: badgeY,
                    radius: 12,
                    fill: badgeColor,
                    opacity: 0.9,
                    listening: false
                });
                layer.add(badge);

                const badgeLabel = new Konva.Text({
                    x: badgeX,
                    y: badgeY,
                    text: badgeText,
                    fontSize: 8,
                    fontFamily: 'Montserrat, Arial',
                    fontStyle: 'bold',
                    fill: '#FFFFFF',
                    align: 'center',
                    offsetX: badgeText.length * 3,
                    offsetY: 4,
                    listening: false
                });
                layer.add(badgeLabel);
            }
        });
    }
}

/**
 * Normalize glass type values from presets
 * Maps preset values to internal keys
 */
function normalizeGlassType(glassType) {
    if (!glassType) return 'clear';
    const normalized = glassType.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'clear': 'clear',
        'tinted': 'tinted',
        'laminated': 'laminated',
        'tempered': 'tempered',
        'double': 'double',
        'low-e': 'low-e',
        'frosted': 'frosted',
        'patterned': 'patterned',
        // Mirror tints
        'mirror-clear': 'mirror-clear',
        'mirror-bronze': 'mirror-bronze',
        'mirror-grey': 'mirror-grey',
        'mirror-grey-smoked': 'mirror-grey-smoked',
        'mirror-smoked': 'mirror-smoked',
        'mirror-black': 'mirror-black',
        // Handle tint field for mirrors
        'bronze': 'mirror-bronze',
        'grey': 'mirror-grey',
        'grey-smoked': 'mirror-grey-smoked',
        'grey (smoked)': 'mirror-grey-smoked',
        'smoked': 'mirror-smoked',
        'black': 'mirror-black'
    };

    // Check if it's a mirror tint (from tint field)
    if (normalized.includes('bronze') && !normalized.includes('mirror')) {
        return 'mirror-bronze';
    }
    if ((normalized.includes('grey') || normalized.includes('gray')) && (normalized.includes('smoke') || normalized.includes('smoked'))) {
        return 'mirror-grey-smoked';
    }
    if (normalized.includes('grey') || normalized.includes('gray')) {
        return 'mirror-grey';
    }
    if (normalized.includes('black') && !normalized.includes('mirror')) {
        return 'mirror-black';
    }

    return mapping[normalized] || 'clear';
}

/**
 * Normalize frame color values from presets
 * Returns hex color code for frame colors
 * @param {string} frameColor - Frame color name
 * @returns {string} Hex color code
 */
function normalizeFrameColor(frameColor) {
    if (!frameColor) return null;
    const normalized = frameColor.toLowerCase().replace(/\s+/g, '-');

    const colorMap = {
        // Windows-specific frame colors (synced with KONVA_DEFAULT_OPTIONS_REFERENCE.md)
        'powder-coated-white': '#F8F8F8',
        'analok': '#F5F5DC',
        'matte-gray': '#6B6B6B',
        'matte-black': '#1A1A1A',
        'wood-finish': '#8B4513',
        'gold': '#FFD700',
        'silver': '#C0C0C0',
        'rose-gold': '#B76E79',
        'rosegold': '#B76E79',
        'rose': '#B76E79',
        'bronze': '#CD7F32',
        'black': '#000000',
        'white': '#FFFFFF',
        'wood': '#795548',
        'custom-color': '#888888',
        'custom': '#888888'
    };

    // Try exact match first
    if (colorMap[normalized]) {
        return colorMap[normalized];
    }

    // Try partial match
    for (const [key, color] of Object.entries(colorMap)) {
        if (normalized.includes(key) || key.includes(normalized)) {
            return color;
        }
    }

    // If frame color exists in frameStyles, use its color
    if (typeof frameStyles !== 'undefined') {
        // Try the normalized key first (e.g. "matte-black")
        if (frameStyles[normalized] && frameStyles[normalized].color) {
            return frameStyles[normalized].color;
        }
        // Also try a de-hyphenated key (e.g. "matte black") since many defaults use spaces
        const spacedKey = normalized.replace(/-/g, ' ');
        if (frameStyles[spacedKey] && frameStyles[spacedKey].color) {
            return frameStyles[spacedKey].color;
        }
    }

    return null;
}

/**
 * Normalize frame type/color values from presets
 * Maps preset values to internal keys
 */
function normalizeFrameType(frameType) {
    if (!frameType) return 'white';
    const normalized = frameType.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'white': 'white',
        'black': 'black',
        'silver': 'silver',
        'bronze': 'bronze',
        'gold': 'gold',
        'rose-gold': 'rose-gold',
        'rosegold': 'rose-gold',
        'rose': 'rose-gold',
        'wood': 'wood',
        'aluminum': 'aluminum',
        'chrome': 'chrome',
        'brushed-nickel': 'brushed-nickel',
        'stainless-steel': 'stainless-steel',
        'stainless': 'stainless-steel',
        'custom-color': 'custom-color',
        'custom': 'custom-color',
        'vinyl': 'vinyl',
        'frameless': 'frameless',
        'none': 'frameless',
        // Windows-specific frame colors (UI uses title-cased labels; state uses hyphenated)
        'powder-coated-white': 'powder coated white',
        'analok': 'analok',
        'matte-gray': 'matte gray',
        'matte-black': 'matte black',
        'wood-finish': 'wood finish',
        // Mirror-specific frame types
        'standard-frame': 'standard-frame',
        'standard': 'standard-frame',
        'thin-frame': 'thin-frame',
        'thin': 'thin-frame',
        'grid-frame': 'grid-frame',
        'grid': 'grid-frame'
    };

    // First try exact match
    if (mapping[normalized]) {
        return mapping[normalized];
    }

    // Then try to find a partial match
    for (const key in mapping) {
        if (normalized.includes(key) || key.includes(normalized)) {
            return mapping[key];
        }
    }

    // If frame type exists in frameStyles directly (could be dynamically added), use it
    if (typeof frameStyles !== 'undefined' && frameStyles[normalized]) {
        return normalized;
    }

    return 'white'; // Default fallback
}

/**
 * Normalize shape values from presets
 * Maps preset values to internal keys
 */
function normalizeShape(shape) {
    if (!shape) return 'rectangle';
    const normalized = shape.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'rectangle': 'rectangle',
        'rectangular': 'rectangle',
        'rectangle/square': 'rectangle',
        'rectangle-square': 'rectangle',
        'round': 'round',
        'circle': 'round',
        'oval': 'oval',
        'ellipse': 'oval',
        // New shapes
        'triangle': 'triangle',
        'triangular': 'triangle',
        'pentagon': 'pentagon',
        'hexagon': 'hexagon',
        'octagon': 'octagon',
        'star': 'star',
        'diamond': 'diamond',
        'square': 'rectangle', // Square is just a rectangle with equal sides
        // Mirror-specific shapes
        'arched': 'arched',
        'arch': 'arched',
        'custom': 'rectangle' // Custom defaults to rectangle
    };
    return mapping[normalized] || 'rectangle';
}

// --- INITIAL RENDER & UPDATES ---
function renderCustomState() {
    if (!layer) return; // Konva not initialized
    // Quick sync: Check if the DOM has an active shape that differs from currentShape
    // This catches cases where the DOM was updated but currentShape wasn't synced
    const activeShapeCard = document.querySelector('[data-field-id="shape"] .option-card.active, .option-card[data-shape].active');
    if (activeShapeCard) {
        const domShape = (activeShapeCard.dataset.value || activeShapeCard.dataset.shape || activeShapeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
        if (domShape && domShape !== currentShape) {
            console.log('[RenderCustomState] Shape mismatch detected. DOM:', domShape, 'Current:', currentShape, '- Syncing...');
            currentShape = domShape;
            window.currentShape = domShape;
        }
    }

    // Convert dimensions to inches for rendering (visual size calculation)
    let widthIn = currentDimensions.width.value;
    let heightIn = currentDimensions.height.value;
    const widthUnit = currentDimensions.width.unit;
    const heightUnit = currentDimensions.height.unit;

    // Convert width to inches
    if (widthUnit === 'cm') {
        widthIn /= 2.54;
    } else if (widthUnit === 'mm') {
        widthIn /= 25.4;
    }
    // If unit is 'in', no conversion needed

    // Convert height to inches
    if (heightUnit === 'cm') {
        heightIn /= 2.54;
    } else if (heightUnit === 'mm') {
        heightIn /= 25.4;
    }
    // If unit is 'in', no conversion needed

    // Get customization values
    const customizationValues = window.selectedCustomizationValues || {};

    // Get product data
    const productData = window.selectedProduct || {};

    // Check if we should use comprehensive renderer
    // Use comprehensive renderer if:
    // 1. Product has a category (Windows, Doors, Partitions, Specialty, Commercial)
    // 2. OR customization values contain product-specific fields
    const shouldUseComprehensive =
        (productData.category && (
            productData.category.includes('Windows') ||
            productData.category.includes('Doors') ||
            productData.category.includes('Partitions') ||
            productData.category.includes('Specialty') ||
            productData.category.includes('Commercial') ||
            productData.category.includes('Glass Partitions & Enclosures') ||
            productData.category.includes('Mirrors & Specialty Glass') ||
            productData.category.includes('Commercial & Exterior')
        )) ||
        customizationValues.numberOfPanels ||
        customizationValues.panelCount ||
        customizationValues.trackSystem ||
        customizationValues.doorType ||
        customizationValues.layout ||
        customizationValues.handrailType;

    if (shouldUseComprehensive && typeof Comprehensive2DRenderer !== 'undefined') {
        // Use comprehensive renderer
        const dimensions = {
            width: widthIn,
            height: heightIn,
            unit: 'in'
        };

        // Prepare product info
        const productInfo = {
            category: productData.category || '',
            productType: productData.subcategory || productData.type || productData.name || '',
            originalWidth: currentDimensions.width.value,
            originalHeight: currentDimensions.height.value,
            widthUnit: widthUnit,
            heightUnit: heightUnit,
            customizationValues: {
                ...customizationValues,
                // Include legacy fields for compatibility
                shape: currentShape,
                glassType: currentGlassType,
                // Prefer explicit selection from customization UI; fallback to legacy `currentThickness`
                thickness: customizationValues.thickness || customizationValues.Thickness || window.currentThickness || currentThickness,
                edgeWork: currentEdgeWork,
                // Prefer explicit selection from customization UI; fallback to legacy `currentFrameType`
                frameColor: (customizationValues.frameColor || customizationValues.FrameColor) ? (customizationValues.frameColor || customizationValues.FrameColor) : currentFrameType,
                cornerRadius: customizationValues.cornerRadius || customizationValues.CornerRadius || currentCornerRadius
            }
        };

        // Render with comprehensive renderer
        Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, layer);
    } else {
        // Fall back to existing renderWindow function
        // Get corner radius from customizationValues if available, otherwise use currentCornerRadius
        const cornerRadiusValue = (window.selectedCustomizationValues?.cornerRadius ||
            window.selectedCustomizationValues?.CornerRadius ||
            currentCornerRadius);

        // Use frameColor from customizationValues if available, otherwise use currentFrameType
        const frameTypeToUse = (customizationValues.frameColor || customizationValues.FrameColor)
            ? (customizationValues.frameColor || customizationValues.FrameColor)
            : currentFrameType;

        renderWindow(
            widthIn, // Converted to inches for visual size
            heightIn, // Converted to inches for visual size
            widthUnit, // Width unit for width label
            currentShape,
            currentGlassType,
            currentThickness,
            currentEdgeWork,
            frameTypeToUse, // Use frameColor from customizationValues if available
            currentDimensions.width.value, // Original width value for label
            currentDimensions.height.value, // Original height value for label
            heightUnit, // Height unit for height label
            cornerRadiusValue // Corner radius in inches (can be number or object)
        );
    }

    // 2. Update the estimated price immediately
    updateRealTimePriceDisplay();
}

// Helper to render standard size with default "Standard" aesthetics
function renderStandardState(width, height) {
    renderWindow(
        width,
        height,
        'in', // Standard uses inches
        'rectangle', // Force Rectangle
        'tempered', // Force Standard Glass
        '6mm',      // Force Standard Thickness
        'flat-polish', // Force Standard Edge
        'vinyl'     // Force Standard Frame
    );
}

// Export for use in dynamic_customization.js and comprehensive renderer
window.renderStandardState = renderStandardState;
window.renderWindow = renderWindow;
window.renderCustomState = renderCustomState;
window.renderMultiPanelProduct = renderMultiPanelProduct;
window.shouldUseMultiPanelRendering = shouldUseMultiPanelRendering;
window.extractPanelCount = extractPanelCount;
window.normalizeGlassType = normalizeGlassType;
window.normalizeFrameType = normalizeFrameType;
window.normalizeFrameColor = normalizeFrameColor;

// Export global variables for comprehensive renderer
window.glassStyles = glassStyles;
window.frameStyles = frameStyles;
window.STAGE_SIZE = STAGE_SIZE;
window.DRAWING_SIZE = DRAWING_SIZE;
window.PADDING = PADDING;
window.layer = layer;
window.normalizeShape = normalizeShape;
window.isRoundShape = isRoundShape;
window.lockDimensionsForRoundShape = lockDimensionsForRoundShape;
window.unlockDimensionsIfNotRound = unlockDimensionsIfNotRound;
window.updateDimensions = updateDimensions;
// Expose state variables for dynamic updates
window.currentShape = currentShape;
window.currentGlassType = currentGlassType;
window.currentThickness = currentThickness;
window.currentEdgeWork = currentEdgeWork;
window.currentFrameType = currentFrameType;
window.currentCornerRadius = currentCornerRadius;
window.cornerRadiusLinked = cornerRadiusLinked;
window.dimensionsLocked = dimensionsLocked;

// Initialize pricing database on load
window.onload = function () {
    // Initialize pricing database first (will use defaults if productBasePrice not available)
    initializePricingDatabase();

    // Initialize default dimensions and render initial state
    if (inputHeight && inputWidth) {
        // Ensure default values are set if inputs are empty
        if (!inputHeight.value || inputHeight.value === '') inputHeight.value = '45';
        if (!inputWidth.value || inputWidth.value === '') inputWidth.value = '35';

        // Update currentDimensions with values from inputs (or defaults)
        const heightValue = parseFloat(inputHeight.value) || 45;
        const widthValue = parseFloat(inputWidth.value) || 35;
        const heightUnit = btnUnitHeight ? (btnUnitHeight.dataset.currentUnit || 'in') : 'in';
        const widthUnit = btnUnitWidth ? (btnUnitWidth.dataset.currentUnit || 'in') : 'in';

        currentDimensions.height = { value: heightValue, unit: heightUnit };
        currentDimensions.width = { value: widthValue, unit: widthUnit };
    }

    // Initial sync attempt - fields might not be rendered yet
    syncShapeFromActiveSelection();

    // Check if initial shape is round and auto-lock dimensions
    setTimeout(() => {
        if (isRoundShape(currentShape)) {
            lockDimensionsForRoundShape();
        }

        // Render initial Konva visualization with correct shape
        if (typeof renderCustomState === 'function') {
            renderCustomState();
        }
    }, 100);

    // Delayed sync - ensures dynamic fields have been rendered
    // Dynamic fields are rendered with a 200ms delay after DOMContentLoaded
    // This sync runs after that to catch dynamically rendered shape options
    setTimeout(() => {
        console.log('[Init] Running delayed shape sync (500ms)...');
        syncShapeFromActiveSelection();

        // Re-check dimension lock after sync
        if (isRoundShape(currentShape)) {
            lockDimensionsForRoundShape();
        }

        // Re-render with synced state
        if (typeof renderCustomState === 'function') {
            renderCustomState();
        }
    }, 500);

    // Final sync - catches any late-rendered fields
    setTimeout(() => {
        console.log('[Init] Running final shape sync (1200ms)...');
        syncShapeFromActiveSelection();

        if (isRoundShape(currentShape)) {
            lockDimensionsForRoundShape();
        }

        if (typeof renderCustomState === 'function') {
            renderCustomState();
        }
    }, 1200);
};

/**
 * Syncs currentShape variable with the active shape card in the DOM
 * This ensures Konva renders the correct shape on initial load
 */
function syncShapeFromActiveSelection() {
    // Check dynamic customization shape field first
    const dynamicShapeContainer = document.querySelector('[data-field-id="shape"]');
    if (dynamicShapeContainer) {
        const activeCard = dynamicShapeContainer.querySelector('.option-card.active');
        if (activeCard) {
            const shapeValue = (activeCard.dataset.value || activeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
            console.log('[Init] Syncing shape from dynamic field:', shapeValue);
            currentShape = shapeValue;
            window.currentShape = shapeValue;
            return;
        }
    }

    // Check legacy shape cards (data-shape attribute)
    const legacyActiveShape = document.querySelector('.option-card[data-shape].active');
    if (legacyActiveShape) {
        const shapeValue = legacyActiveShape.dataset.shape.toLowerCase().replace(/\s+/g, '-');
        console.log('[Init] Syncing shape from legacy field:', shapeValue);
        currentShape = shapeValue;
        window.currentShape = shapeValue;
        return;
    }

    // No active shape found, check if there are shape cards at all and make first one active
    const firstDynamicShapeCard = document.querySelector('[data-field-id="shape"] .option-card');
    if (firstDynamicShapeCard) {
        firstDynamicShapeCard.classList.add('active');
        const shapeValue = (firstDynamicShapeCard.dataset.value || firstDynamicShapeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
        console.log('[Init] No active shape found, setting first option:', shapeValue);
        currentShape = shapeValue;
        window.currentShape = shapeValue;
        return;
    }

    const firstLegacyShapeCard = document.querySelector('.option-card[data-shape]');
    if (firstLegacyShapeCard) {
        firstLegacyShapeCard.classList.add('active');
        const shapeValue = firstLegacyShapeCard.dataset.shape.toLowerCase().replace(/\s+/g, '-');
        console.log('[Init] No active legacy shape found, setting first option:', shapeValue);
        currentShape = shapeValue;
        window.currentShape = shapeValue;
    }
}


// --- TOGGLE MODE LOGIC (UPDATED) ---

btnCustomize.addEventListener('click', () => {
    if (!isStandardMode) return;
    isStandardMode = false;

    // UI Updates
    btnCustomize.classList.add('active'); btnCustomize.classList.remove('inactive');
    btnStandard.classList.remove('active'); btnStandard.classList.add('inactive');
    customWrapper.classList.remove('hidden-step'); standardWrapper.classList.add('hidden-step');
    priceBox.classList.remove('hidden-step'); standardSubtitle.classList.add('hidden-step');
    // Breadcrumbs are now managed dynamically

    // DRAWING UPDATE: Restore the User's Custom State
    renderCustomState();
});

btnStandard.addEventListener('click', () => {
    if (isStandardMode) return;
    isStandardMode = true;

    // Set standard mode defaults for summary
    currentShape = 'rectangle';
    currentGlassType = 'tempered';
    currentThickness = '5mm';
    currentEdgeWork = 'flat-polish';
    currentFrameType = 'vinyl';

    // UI Updates
    btnStandard.classList.add('active'); btnStandard.classList.remove('inactive');
    btnCustomize.classList.remove('active'); btnCustomize.classList.add('inactive');
    standardWrapper.classList.remove('hidden-step'); customWrapper.classList.add('hidden-step');
    priceBox.classList.add('hidden-step'); standardSubtitle.classList.remove('hidden-step');
    // Breadcrumbs are now managed dynamically

    // DRAWING UPDATE: Force Standard Look
    // Get the currently selected standard card values
    const activeStdCard = document.querySelector('#standard-wrapper .option-card.active');
    if (activeStdCard) {
        const h = parseFloat(activeStdCard.dataset.height);
        const w = parseFloat(activeStdCard.dataset.width);
        currentDimensions.height = { value: h, unit: 'in' };
        currentDimensions.width = { value: w, unit: 'in' };
        renderStandardState(w, h);
    }
});


// --- STANDARD BUTTON LISTENERS (NEW) ---
const standardCards = document.querySelectorAll('#standard-wrapper .option-card');
standardCards.forEach(card => {
    card.addEventListener('click', function () {
        // Visual toggle
        standardCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        // Update dimensions for summary display
        const h = parseFloat(this.dataset.height);
        const w = parseFloat(this.dataset.width);
        currentDimensions.height = { value: h, unit: 'in' };
        currentDimensions.width = { value: w, unit: 'in' };

        // Render Standard
        renderStandardState(w, h);
    });
});


// --- CUSTOM EVENT LISTENERS (EXISTING) ---

function updateDimensions(type, value, unit) {
    if (isNaN(value) || value <= 0) return;
    currentDimensions[type] = { value: parseFloat(value), unit };

    // If dimensions are locked, update the other dimension to match
    if (dimensionsLocked) {
        const otherType = type === 'height' ? 'width' : 'height';
        const otherInput = type === 'height' ? inputWidth : inputHeight;
        const otherBtn = type === 'height' ? btnUnitWidth : btnUnitHeight;

        // Convert value to the other dimension's unit if needed
        let convertedValue = parseFloat(value);
        const otherUnit = otherBtn ? otherBtn.dataset.currentUnit : unit;

        if (unit !== otherUnit) {
            // Convert to millimeters first, then to target unit
            const unitMap = {
                'in': { toMm: 25.4 },
                'cm': { toMm: 10 },
                'mm': { toMm: 1 }
            };
            const valueInMm = convertedValue * (unitMap[unit]?.toMm || 1);
            convertedValue = valueInMm / (unitMap[otherUnit]?.toMm || 1);
        }

        if (otherInput) {
            otherInput.value = Math.round(convertedValue * 100) / 100;
            currentDimensions[otherType] = { value: convertedValue, unit: otherUnit };
        }
    }

    renderCustomState(); // Call the wrapper function
}

// Input Listeners (only if elements exist - they may be dynamically generated)
// Add event listener for h1 input (sliding section)
if (inputH1) {
    inputH1.addEventListener('input', (e) => {
        const h1Value = parseFloat(inputH1.value) || 0;
        if (h1Value > 0) {
            // Mark h1 as last modified for auto-adjustment
            inputH1.dataset.lastModified = Date.now();
            // Auto-adjust h2
            if (typeof adjustTransomHeights === 'function') {
                adjustTransomHeights();
            }
            // Trigger window visualization update
            if (typeof renderCustomState === 'function') {
                renderCustomState();
            } else if (typeof updateWindowVisualization === 'function') {
                updateWindowVisualization();
            }
        }
    });

    // Also listen for blur to update when user finishes editing
    inputH1.addEventListener('blur', (e) => {
        const h1Value = parseFloat(inputH1.value) || 0;
        if (h1Value > 0) {
            if (typeof adjustTransomHeights === 'function') {
                adjustTransomHeights();
            }
            if (typeof renderCustomState === 'function') {
                renderCustomState();
            } else if (typeof updateWindowVisualization === 'function') {
                updateWindowVisualization();
            }
        }
    });
}

// Add event listener for h2 input (fixed transom section)
if (inputH2) {
    inputH2.addEventListener('input', (e) => {
        const h2Value = parseFloat(inputH2.value) || 0;
        if (h2Value > 0) {
            // Mark h2 as last modified for auto-adjustment
            inputH2.dataset.lastModified = Date.now();
            // Auto-adjust h1
            if (typeof adjustTransomHeights === 'function') {
                adjustTransomHeights();
            }
            // Trigger window visualization update
            if (typeof renderCustomState === 'function') {
                renderCustomState();
            } else if (typeof updateWindowVisualization === 'function') {
                updateWindowVisualization();
            }
        }
    });

    // Also listen for blur to update when user finishes editing
    inputH2.addEventListener('blur', (e) => {
        const h2Value = parseFloat(inputH2.value) || 0;
        if (h2Value > 0) {
            if (typeof adjustTransomHeights === 'function') {
                adjustTransomHeights();
            }
            if (typeof renderCustomState === 'function') {
                renderCustomState();
            } else if (typeof updateWindowVisualization === 'function') {
                updateWindowVisualization();
            }
        }
    });
}

if (inputHeight && btnUnitHeight) {
    inputHeight.addEventListener('input', (e) => {
        // Mark height as last modified for auto-adjustment
        inputHeight.dataset.lastModified = Date.now();
        updateDimensions('height', e.target.value, btnUnitHeight.dataset.currentUnit);
        // Auto-adjust transom heights when total height changes
        if (typeof adjustTransomHeights === 'function') {
            setTimeout(() => adjustTransomHeights(), 100);
        }
    });
}
if (inputWidth && btnUnitWidth) {
    inputWidth.addEventListener('input', (e) => {
        updateDimensions('width', e.target.value, btnUnitWidth.dataset.currentUnit);
    });
}

// Lock/Unlock Button Handler
const dimensionLockBtn = document.getElementById('dimension-lock-btn');
if (dimensionLockBtn) {
    dimensionLockBtn.addEventListener('click', () => {
        // Prevent unlocking if round shape is selected
        if (isRoundShape(currentShape) && dimensionsLocked) {
            // Show a message or just prevent the unlock
            dimensionLockBtn.title = 'Dimensions are locked for round shapes';
            return; // Don't allow unlocking
        }

        dimensionsLocked = !dimensionsLocked;
        const lockIcon = document.getElementById('lock-icon');
        const unlockIcon = document.getElementById('unlock-icon');

        if (dimensionsLocked) {
            lockIcon.style.display = 'none';
            unlockIcon.style.display = 'block';
            dimensionLockBtn.classList.add('locked');
            dimensionLockBtn.title = 'Unlock dimensions to allow independent height and width';

            // When locking, sync the current values (make width equal to height)
            if (inputHeight && inputWidth) {
                const heightValue = parseFloat(inputHeight.value) || 0;
                const heightUnit = btnUnitHeight ? btnUnitHeight.dataset.currentUnit : 'in';
                const widthUnit = btnUnitWidth ? btnUnitWidth.dataset.currentUnit : 'in';

                // Convert height to width's unit
                let convertedValue = heightValue;
                if (heightUnit !== widthUnit) {
                    const unitMap = {
                        'in': { toMm: 25.4 },
                        'cm': { toMm: 10 },
                        'mm': { toMm: 1 }
                    };
                    const valueInMm = heightValue * (unitMap[heightUnit]?.toMm || 1);
                    convertedValue = valueInMm / (unitMap[widthUnit]?.toMm || 1);
                }

                inputWidth.value = Math.round(convertedValue * 100) / 100;
                updateDimensions('width', convertedValue, widthUnit);
            }
        } else {
            lockIcon.style.display = 'block';
            unlockIcon.style.display = 'none';
            dimensionLockBtn.classList.remove('locked');
            dimensionLockBtn.title = 'Lock dimensions to keep height and width equal';
        }
    });
}

// Unit Dropdowns (only if elements exist - they may be dynamically generated)
// Note: These handlers may be redundant if setupUnitDropdown is used, but kept for backward compatibility
const dropdownHeight = document.getElementById('dropdown-height');
const dropdownWidth = document.getElementById('dropdown-width');

if (dropdownHeight) {
    dropdownHeight.addEventListener('click', (e) => {
        if (e.target.classList.contains('unit-option')) {
            const targetUnit = e.target.dataset.value;
            updateDimensions('height', inputHeight ? inputHeight.value : 0, targetUnit);
            if (btnUnitHeight) {
                btnUnitHeight.dataset.currentUnit = targetUnit;
                btnUnitHeight.textContent = e.target.textContent + ' ▼';
            }
            // Sync width unit to match height unit
            if (btnUnitWidth && btnUnitWidth.dataset.currentUnit !== targetUnit) {
                const widthVal = parseFloat(inputWidth ? inputWidth.value : 0);
                if (!isNaN(widthVal)) {
                    const currentWidthUnit = btnUnitWidth.dataset.currentUnit || 'in';
                    const convertedWidth = Math.round((widthVal * unitMap[currentWidthUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100;
                    if (inputWidth) inputWidth.value = convertedWidth;
                    updateDimensions('width', convertedWidth, targetUnit);
                }
                btnUnitWidth.dataset.currentUnit = targetUnit;
                btnUnitWidth.textContent = e.target.textContent + ' ▼';
            }
            dropdownHeight.classList.add('hidden-step');
        }
    });
}

if (dropdownWidth) {
    dropdownWidth.addEventListener('click', (e) => {
        if (e.target.classList.contains('unit-option')) {
            const targetUnit = e.target.dataset.value;
            updateDimensions('width', inputWidth ? inputWidth.value : 0, targetUnit);
            if (btnUnitWidth) {
                btnUnitWidth.dataset.currentUnit = targetUnit;
                btnUnitWidth.textContent = e.target.textContent + ' ▼';
            }
            // Sync height unit to match width unit
            if (btnUnitHeight && btnUnitHeight.dataset.currentUnit !== targetUnit) {
                const heightVal = parseFloat(inputHeight ? inputHeight.value : 0);
                if (!isNaN(heightVal)) {
                    const currentHeightUnit = btnUnitHeight.dataset.currentUnit || 'in';
                    const convertedHeight = Math.round((heightVal * unitMap[currentHeightUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100;
                    if (inputHeight) inputHeight.value = convertedHeight;
                    updateDimensions('height', convertedHeight, targetUnit);
                }
                btnUnitHeight.dataset.currentUnit = targetUnit;
                btnUnitHeight.textContent = e.target.textContent + ' ▼';
            }
            dropdownWidth.classList.add('hidden-step');
        }
    });
}

// Helper to update selectedCustomizationValues for legacy fields
function updateSelectedValueForLegacyField(fieldId, value) {
    if (typeof window !== 'undefined') {
        if (!window.selectedCustomizationValues) {
            window.selectedCustomizationValues = {};
        }
        window.selectedCustomizationValues[fieldId] = value;
    }
}

// Helper function to check if shape requires equal dimensions
function isRoundShape(shape) {
    if (!shape) return false;
    const normalized = normalizeShape(shape);
    // Shapes that require equal dimensions (width = height)
    const equalDimensionShapes = ['round', 'circle', 'star', 'pentagon', 'hexagon', 'octagon'];
    return equalDimensionShapes.includes(normalized);
}

// Helper function to lock dimensions and update UI
function lockDimensionsForRoundShape() {
    dimensionsLocked = true;
    const lockIcon = document.getElementById('lock-icon');
    const unlockIcon = document.getElementById('unlock-icon');
    const dimensionLockBtn = document.getElementById('dimension-lock-btn');

    if (lockIcon && unlockIcon && dimensionLockBtn) {
        lockIcon.style.display = 'none';
        unlockIcon.style.display = 'block';
        dimensionLockBtn.classList.add('locked');
        dimensionLockBtn.title = 'Dimensions locked for round shapes';
    }

    // Sync width to height when locking for round shape
    if (inputHeight && inputWidth) {
        const heightValue = parseFloat(inputHeight.value) || 0;
        const heightUnit = btnUnitHeight ? btnUnitHeight.dataset.currentUnit : 'in';
        const widthUnit = btnUnitWidth ? btnUnitWidth.dataset.currentUnit : 'in';

        // Convert height to width's unit
        let convertedValue = heightValue;
        if (heightUnit !== widthUnit) {
            const unitMap = {
                'in': { toMm: 25.4 },
                'cm': { toMm: 10 },
                'mm': { toMm: 1 }
            };
            const valueInMm = heightValue * (unitMap[heightUnit]?.toMm || 1);
            convertedValue = valueInMm / (unitMap[widthUnit]?.toMm || 1);
        }

        inputWidth.value = Math.round(convertedValue * 100) / 100;
        updateDimensions('width', convertedValue, widthUnit);
    }
}

// Helper function to unlock dimensions (when switching away from round)
function unlockDimensionsIfNotRound() {
    const normalizedShape = normalizeShape(currentShape);
    if (normalizedShape !== 'round' && normalizedShape !== 'circle') {
        // Only unlock if it was auto-locked for round shape
        // We'll track if it was manually locked vs auto-locked
        // For now, we'll unlock if switching to non-round shape
        dimensionsLocked = false;
        const lockIcon = document.getElementById('lock-icon');
        const unlockIcon = document.getElementById('unlock-icon');
        const dimensionLockBtn = document.getElementById('dimension-lock-btn');

        if (lockIcon && unlockIcon && dimensionLockBtn) {
            lockIcon.style.display = 'block';
            unlockIcon.style.display = 'none';
            dimensionLockBtn.classList.remove('locked');
            dimensionLockBtn.title = 'Lock dimensions to keep height and width equal';
        }
    }
}

// Shape Selection
shapeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('div[class$="-section"]');
        if (section) section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentShape = this.dataset.shape;
        updateSelectedValueForLegacyField('shape', currentShape);

        // Auto-lock dimensions for round shapes
        if (isRoundShape(currentShape)) {
            lockDimensionsForRoundShape();
        } else {
            unlockDimensionsIfNotRound();
        }

        renderCustomState();
    });
});

// Type & Thickness
const glassTypeCards = document.querySelectorAll('.option-card[data-glass-type]');
glassTypeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.type-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentGlassType = this.dataset.glassType;
        updateSelectedValueForLegacyField('glassType', currentGlassType);
        renderCustomState();
    });
});

const thicknessCards = document.querySelectorAll('.option-card[data-thickness]');
thicknessCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.thickness-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentThickness = this.dataset.thickness;
        updateSelectedValueForLegacyField('thickness', currentThickness);
        renderCustomState();
    });
});

// Edge & Frame
const edgeCards = document.querySelectorAll('.option-card[data-edge-work]');
edgeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.edge-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentEdgeWork = this.dataset.edgeWork;
        updateSelectedValueForLegacyField('edgeWork', currentEdgeWork);
        renderCustomState();
    });
});

const frameCards = document.querySelectorAll('.option-card[data-frame-type]');
frameCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.frame-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentFrameType = this.dataset.frameType;
        updateSelectedValueForLegacyField('frameType', currentFrameType);
        renderCustomState();
    });
});

// --- GENERAL UTILITIES (Unit Setup, Modal, Navigation) ---

function setupUnitDropdown(btnId, dropdownId, inputId, dimensionType) {
    const btn = document.getElementById(btnId);
    const dropdown = document.getElementById(dropdownId);
    const input = document.getElementById(inputId);
    if (!btn || !dropdown || !input) return;

    btn.addEventListener('click', (e) => { e.stopPropagation(); document.querySelectorAll('.unit-dropdown').forEach(d => d !== dropdown && d.classList.add('hidden-step')); dropdown.classList.toggle('hidden-step'); });
    dropdown.querySelectorAll('.unit-option').forEach(opt => {
        opt.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetUnit = opt.dataset.value;
            const currentUnit = btn.dataset.currentUnit;
            // Use full unit name from unitMap
            const unitName = unitMap[targetUnit]?.name || targetUnit.charAt(0).toUpperCase() + targetUnit.slice(1);
            btn.innerHTML = `${unitName} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
            const val = parseFloat(input.value);
            if (!isNaN(val)) { input.value = Math.round((val * unitMap[currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
            btn.dataset.currentUnit = targetUnit;
            const otherType = dimensionType === 'height' ? 'width' : 'height';
            const otherBtn = document.getElementById(`btn-unit-${otherType}`);
            const otherInput = document.getElementById(`input-${otherType}`);
            if (otherBtn && otherBtn.dataset.currentUnit !== targetUnit) {
                const otherVal = parseFloat(otherInput.value);
                if (!isNaN(otherVal)) { otherInput.value = Math.round((otherVal * unitMap[otherBtn.dataset.currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
                otherBtn.dataset.currentUnit = targetUnit;
                const otherUnitName = unitMap[targetUnit]?.name || targetUnit.charAt(0).toUpperCase() + targetUnit.slice(1);
                otherBtn.innerHTML = `${otherUnitName} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
            }
            // Update both dimensions since units are synced
            updateDimensions(dimensionType, input.value, targetUnit);
            updateDimensions(otherType, otherInput.value, targetUnit);
            dropdown.classList.add('hidden-step');
        });
    });
}
setupUnitDropdown('btn-unit-height', 'dropdown-height', 'input-height', 'height');
setupUnitDropdown('btn-unit-width', 'dropdown-width', 'input-width', 'width');
if (document.getElementById('btn-unit-h1')) {
    setupUnitDropdown('btn-unit-h1', 'dropdown-h1', 'input-h1', 'height');

    // Add listener for h1 unit changes to trigger re-render
    const dropdownH1 = document.getElementById('dropdown-h1');
    if (dropdownH1) {
        dropdownH1.querySelectorAll('.unit-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                // After unit change, auto-adjust and trigger re-render
                setTimeout(() => {
                    if (typeof adjustTransomHeights === 'function') {
                        adjustTransomHeights();
                    }
                    if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    } else if (typeof updateWindowVisualization === 'function') {
                        updateWindowVisualization();
                    }
                }, 100);
            });
        });
    }
}
if (document.getElementById('btn-unit-h2')) {
    setupUnitDropdown('btn-unit-h2', 'dropdown-h2', 'input-h2', 'height');

    // Add listener for h2 unit changes to trigger re-render
    const dropdownH2 = document.getElementById('dropdown-h2');
    if (dropdownH2) {
        dropdownH2.querySelectorAll('.unit-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                // After unit change, auto-adjust and trigger re-render
                setTimeout(() => {
                    if (typeof adjustTransomHeights === 'function') {
                        adjustTransomHeights();
                    }
                    if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    } else if (typeof updateWindowVisualization === 'function') {
                        updateWindowVisualization();
                    }
                }, 100);
            });
        });
    }
}

if (document.getElementById('btn-unit-w1')) {
    setupUnitDropdown('btn-unit-w1', 'dropdown-w1', 'input-w1', 'width');
    const dropdownW1 = document.getElementById('dropdown-w1');
    if (dropdownW1) {
        dropdownW1.querySelectorAll('.unit-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                // After unit change, auto-adjust and trigger re-render
                setTimeout(() => {
                    if (typeof adjustMullionWidths === 'function') {
                        adjustMullionWidths();
                    }
                    if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    } else if (typeof updateWindowVisualization === 'function') {
                        updateWindowVisualization();
                    }
                }, 100);
            });
        });
    }
}

if (document.getElementById('btn-unit-w2')) {
    setupUnitDropdown('btn-unit-w2', 'dropdown-w2', 'input-w2', 'width');
    const dropdownW2 = document.getElementById('dropdown-w2');
    if (dropdownW2) {
        dropdownW2.querySelectorAll('.unit-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                // After unit change, auto-adjust and trigger re-render
                setTimeout(() => {
                    if (typeof adjustMullionWidths === 'function') {
                        adjustMullionWidths();
                    }
                    if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    } else if (typeof updateWindowVisualization === 'function') {
                        updateWindowVisualization();
                    }
                }, 100);
            });
        });
    }

    // Setup w3 unit dropdown
    setupUnitDropdown('btn-unit-w3', 'dropdown-w3', 'input-w3', 'width');
    const dropdownW3 = document.getElementById('dropdown-w3');
    if (dropdownW3) {
        dropdownW3.querySelectorAll('.unit-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                // After unit change, auto-adjust and trigger re-render
                setTimeout(() => {
                    if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    }
                }, 100);
            });
        });
    }
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.unit-control')) document.querySelectorAll('.unit-dropdown').forEach(d => d.classList.add('hidden-step'));
    if (e.target === uploadModal) closeUploadModal();
});

// Navigation Logic (single next/back handler covers standard & custom flows)
nextBtn.addEventListener('click', () => {
    // Standard mode: move to standard step or finalize
    if (isStandardMode) {
        if (currentStep < 2) {
            goToStep(2);
        } else {
            // Finalize standard order
            console.log('Finalizing Standard Order...');
            showOrderSummary();
            logOrderSummary();
        }
        return;
    }

    // Custom mode normal flow
    if (currentStep === 1) goToStep(2);
    else if (currentStep === 2) goToStep(3);
    else {
        // Step 3 -> Finalize custom order
        console.log('Finalizing Custom Order...');
        showOrderSummary();
        logOrderSummary();
    }
});


backBtn.addEventListener('click', () => {
    if (currentStep === 2) goToStep(1);
    else if (currentStep === 3) goToStep(2);
});

function goToStep(targetStep) {
    // Hide all steps first (use querySelectorAll to handle dynamically created steps)
    document.querySelectorAll('[id^="step-"]').forEach(s => s.classList.add('hidden-step'));

    // Show the target step
    const targetStepEl = document.getElementById(`step-${targetStep}`);
    if (targetStepEl) {
        targetStepEl.classList.remove('hidden-step');
    }

    // Update UI
    updateActionArea(targetStep);
    // Breadcrumbs are now managed dynamically

    // Update currentStep AFTER UI (use window.currentStep as source of truth)
    if (typeof window !== 'undefined') {
        window.currentStep = targetStep;
    }
    // Keep local currentStep in sync
    currentStep = targetStep;
}


function updateActionArea(step) {
    const totalSteps = window.totalCustomizationSteps || 3;
    if (step === 1) {
        backGroup.classList.add('hidden-step');
        nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
        nextNote.innerText = `Step ${step + 1}`;
        backNote.innerText = '';
    }
    if (step === 2) {
        backGroup.classList.remove('hidden-step');
        nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
        backNote.innerText = `Step ${step - 1}`;
        nextNote.innerText = step < totalSteps - 1 ? `Step ${step + 1}` : '';
    }
    if (step >= 3 && step < totalSteps) {
        backGroup.classList.remove('hidden-step');
        nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
        backNote.innerText = `Step ${step - 1}`;
        nextNote.innerText = step < totalSteps - 1 ? `Step ${step + 1}` : '';
    }
    if (step === totalSteps) {
        backGroup.classList.remove('hidden-step');
        nextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
        backNote.innerText = `Step ${step - 1}`;
        nextNote.innerText = '';
    }
}

// Breadcrumb management is now handled dynamically by updateDynamicBreadcrumbs() in dynamic_customization.js


// Breadcrumb helper functions removed - now using dynamic breadcrumb system

// Modal & File Logic
function closeUploadModal() { 
    if (uploadModal) uploadModal.classList.add('hidden-step'); 
}

if (openModalBtn) {
    openModalBtn.addEventListener('click', () => { 
        if (uploadModal) uploadModal.classList.remove('hidden-step'); 
    });
}
if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', closeUploadModal);
}
if (modalCancelBtn) {
    modalCancelBtn.addEventListener('click', closeUploadModal);
}
if (modalDoneBtn) {
    modalDoneBtn.addEventListener('click', closeUploadModal);
}

// Guest Authentication Modal Logic
const guestAuthModal = document.getElementById('guest-auth-modal');
const guestModalCloseBtn = document.getElementById('guest-modal-close-btn');

function closeGuestAuthModal() { 
    if (guestAuthModal) {
        guestAuthModal.classList.add('hidden-step'); 
        guestAuthModal.style.display = 'none';
    }
}

if (guestModalCloseBtn) {
    guestModalCloseBtn.addEventListener('click', closeGuestAuthModal);
}

// Close modal when clicking backdrop
if (guestAuthModal) {
    guestAuthModal.addEventListener('click', function(e) {
        if (e.target === guestAuthModal) {
            closeGuestAuthModal();
        }
    });
}

browseFilesBtn.addEventListener('click', () => { fileInput.click(); });
fileInput.addEventListener('change', (e) => { handleFiles(e.target.files); fileInput.value = ''; });

dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', (e) => { e.preventDefault(); dropzone.classList.remove('drag-over'); });
dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });

function handleFiles(files) {
    if (files.length === 0) return;
    const placeholder = uploadedFilesContainer.querySelector('.placeholder-text');
    if (placeholder) placeholder.remove();

    Array.from(files).forEach(file => {
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png', 'pdf'].includes(fileExtension)) {
            console.error(`File type not supported`); return;
        }
        const newFile = {
            id: Date.now() + Math.random(),
            name: file.name,
            size: file.size,
            progress: 0,
            status: 'uploading',
            isError: file.size > MAX_FILE_SIZE_MB * 1024 * 1024,
            extension: fileExtension,
            file: file  // Store the actual file object for upload
        };
        uploadedFiles.push(newFile);
        renderFileItem(newFile);
        if (newFile.isError) {
            newFile.status = 'error';
            updateFileItem(newFile);
        } else {
            uploadFileToServer(newFile);
        }
    });
    // Update external file display after adding files
    updateExternalFileDisplay();
}

// Upload file to server
function uploadFileToServer(file) {
    if (!file.file) {
        console.error('File object not found');
        file.status = 'error';
        updateFileItem(file);
        return;
    }

    const formData = new FormData();
    // Ensure we send a filename (some File/Blob objects may lack a name)
    let filename = file.name || '';
    if (!filename) {
        // Derive extension from known properties
        const extFromMeta = file.extension || (file.file && file.file.type ? file.file.type.split('/').pop() : 'png');
        filename = `upload.${extFromMeta}`;
    }
    formData.append('file', file.file, filename);
    formData.append('customer_id', document.body.getAttribute('data-customer-id') || '');

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            file.progress = percentComplete;
            updateFileItem(file);
        }
    });

    xhr.addEventListener('load', () => {
        const text = xhr.responseText || '';
        const looksLikeHtml = typeof text === 'string' && (text.trim().startsWith('<') || text.trim().startsWith('<!'));
        if (xhr.status === 200 && !looksLikeHtml) {
            try {
                const response = JSON.parse(text);
                if (response.status === 'success') {
                    file.progress = 100;
                    file.status = 'completed';
                    file.filePath = response.file_path || response.filePath || null;
                    updateFileItem(file);
                    updateExternalFileDisplay();
                } else {
                    file.status = 'error';
                    updateFileItem(file);
                    console.error('Upload failed:', response.message || 'Unknown error');
                }
            } catch (e) {
                file.status = 'error';
                updateFileItem(file);
                console.error('Error parsing upload response:', e.message || e);
            }
        } else {
            file.status = 'error';
            updateFileItem(file);
            if (looksLikeHtml || xhr.status >= 400) {
                console.error('Upload failed: server returned ' + (looksLikeHtml ? 'HTML (likely 500 or error page)' : 'status ' + xhr.status));
            } else {
                console.error('Upload failed with status:', xhr.status);
            }
        }
    });

    xhr.addEventListener('error', () => {
        file.status = 'error';
        updateFileItem(file);
        console.error('Upload error occurred');
    });

    // Determine the upload endpoint URL
    let uploadUrl = base_url || '';
    if (!uploadUrl.endsWith('/')) {
        uploadUrl += '/';
    }
    uploadUrl += 'CustomizationCon/upload_file';

    xhr.open('POST', uploadUrl);
    xhr.send(formData);
}

function getFileIconSvg(ext) {
    if (ext === 'pdf') return `<svg viewBox="0 0 24 24" stroke="#CC3333" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;
    if (ext === 'png') return `<svg viewBox="0 0 24 24" stroke="#00A78F" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><rect x="8" y="12" width="8" height="8" rx="1" fill="#fff" stroke="#00A78F"/><circle cx="12" cy="16" r="2" fill="#00A78F"/></svg>`;
    return `<svg viewBox="0 0 24 24" stroke="#E69500" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><circle cx="9.5" cy="9.5" r="1.5"/><polyline points="15 8 22 17 17 22 10 16"/></svg>`;
}

function renderFileItem(file) {
    const item = document.createElement('div');
    item.className = 'file-item';
    item.id = `file-item-${file.id}`;
    item.innerHTML = `<div class="file-icon-wrapper">${getFileIconSvg(file.extension)}</div><div class="file-details"><div class="file-name-progress"><span class="file-name-text">${file.name}</span><span class="file-status status-${file.status}">${file.status}</span></div><div class="progress-bar-container"><div class="progress-bar" style="width: ${file.progress}%;"></div></div></div><button class="cancel-btn" data-file-id="${file.id}">Cancel</button>`;
    uploadedFilesContainer.appendChild(item);
    item.querySelector('.cancel-btn').addEventListener('click', deleteFile);
}

function updateFileItem(file) {
    const item = document.getElementById(`file-item-${file.id}`);
    if (!item) return;
    item.querySelector('.progress-bar').style.width = `${file.progress}%`;
    item.querySelector('.file-status').textContent = file.status === 'error' ? 'Error' : (file.status === 'completed' ? 'Completed' : `${file.progress}%`);
    item.querySelector('.file-status').className = `file-status status-${file.status}`;
}

function deleteFile(e) {
    const fileId = parseFloat(e.target.dataset.fileId);
    uploadedFiles = uploadedFiles.filter(file => file.id !== fileId);
    document.getElementById(`file-item-${fileId}`).remove();
    if (uploadedFiles.length === 0) {
        uploadedFilesContainer.innerHTML = '<p class="placeholder-text">No files uploaded yet.</p>';
        // Hide external display if no files
        const externalDisplay = document.getElementById('uploaded-files-display');
        if (externalDisplay) externalDisplay.style.display = 'none';
    }
    // Update external display
    updateExternalFileDisplay();
}

// Update external file display (outside modal)
function updateExternalFileDisplay() {
    const externalList = document.getElementById('external-uploaded-files-list');
    const externalContainer = document.getElementById('external-uploaded-files-container');

    // If no external display container exists, skip (it's optional)
    if (!externalList || !externalContainer) {
        return;
    }

    // Filter only completed files
    const completedFiles = uploadedFiles.filter(f => f.status === 'completed');

    if (completedFiles.length === 0) {
        externalList.style.display = 'none';
        externalContainer.innerHTML = '<p class="placeholder-text" style="font-style: italic; color: #666; text-align: center; padding: 10px;">No files uploaded yet.</p>';
        return;
    }

    externalList.style.display = 'block';

    // Show max 4 files with scroll
    const filesToShow = completedFiles.slice(0, 4);

    // Clear container
    externalContainer.innerHTML = '';
    externalContainer.style.cssText = 'display: flex; gap: 10px; overflow-x: auto; padding: 10px 0; max-height: 120px;';

    filesToShow.forEach(file => {
        const fileItem = document.createElement('div');
        fileItem.style.cssText = 'min-width: 80px; text-align: center; padding: 8px; background: #f5f5f5; border-radius: 4px; flex-shrink: 0; cursor: pointer;';
        const fileIcon = getFileIconSvg(file.extension);
        // If filePath exists, wrap icon and name in a link
        if (file.filePath) {
            const fileUrl = file.filePath.startsWith('http') ? file.filePath : (window.base_url || '') + file.filePath.replace(/^\/+/, '');
            fileItem.innerHTML = `
                <a href="${fileUrl}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color: inherit; display: block;">
                    <div style="margin-bottom: 5px;">${fileIcon}</div>
                    <div style="font-size: 11px; word-break: break-word; max-width: 80px;">${file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name}</div>
                </a>
            `;
        } else {
            fileItem.innerHTML = `
                <div style="margin-bottom: 5px;">${fileIcon}</div>
                <div style="font-size: 11px; word-break: break-word; max-width: 80px;">${file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name}</div>
            `;
        }
        externalContainer.appendChild(fileItem);
    });

    // Show/hide scroll arrows if more than 4 files
    const scrollNav = externalList.querySelector('.external-files-scroll-nav');
    if (scrollNav && completedFiles.length > 4) {
        const leftArrow = scrollNav.querySelector('.scroll-arrow.left');
        const rightArrow = scrollNav.querySelector('.scroll-arrow.right');
        if (leftArrow) leftArrow.style.display = 'block';
        if (rightArrow) rightArrow.style.display = 'block';
    }
}

// --- PRICING LOGIC (Philippines Context) ---
// Price per square inch based on product base price
// pricingDatabase is declared at the top of the file to avoid temporal dead zone issues

// Initialize pricing database (must be called after productBasePrice is available)
function initializePricingDatabase() {
    // Get product base price from various sources
    let basePrice = 0;
    if (typeof window !== 'undefined' && window.productBasePrice) {
        basePrice = window.productBasePrice;
    } else if (typeof productBasePrice !== 'undefined') {
        basePrice = productBasePrice;
    }

    // Get tag prices from selectedProduct (from database)
    const product = getSelectedProduct();
    const tagPrices = (product && product.tagPrices) ? product.tagPrices : {};

    // Calculate base rate per square inch (base price / 100)
    const BASE_PRICE_PER_SQ_IN = basePrice > 0 ? basePrice / 100 : 15; // Default to 15 if no base price

    const db = {
        basePrice: basePrice,
        baseRatePerSqIn: BASE_PRICE_PER_SQ_IN,
        tagPrices: tagPrices, // Store database tag prices
        minimumPrice: basePrice > 0 ? basePrice : 1500 // Use base price as minimum, or 1500 default
    };

    console.log("Pricing system initialized with base price:", basePrice, "from database");
    console.log("Tag prices from database:", tagPrices);
    pricingDatabase = db; // Assign to the global variable
    return db;
}

// Helper function to get selected product
function getSelectedProduct() {
    return window.selectedProduct || null;
}

// Helper function to get price for a field option from database
function getPriceFromDatabase(fieldId, optionName) {
    if (!pricingDatabase || !pricingDatabase.tagPrices) {
        console.log('No pricing database or tagPrices available');
        return 0;
    }

    const fieldPrices = pricingDatabase.tagPrices[fieldId];
    if (!fieldPrices) {
        console.log(`No prices found for fieldId: ${fieldId}`);
        return 0;
    }

    // Try exact match first
    if (fieldPrices[optionName] !== undefined) {
        const price = parseFloat(fieldPrices[optionName]) || 0;
        console.log(`Found exact match for ${fieldId}.${optionName}: ${price}`);
        return price;
    }

    // Try case-insensitive match
    const lowerOption = optionName.toLowerCase().trim();
    for (const key in fieldPrices) {
        if (key.toLowerCase().trim() === lowerOption) {
            const price = parseFloat(fieldPrices[key]) || 0;
            console.log(`Found case-insensitive match for ${fieldId}.${optionName} (matched ${key}): ${price}`);
            return price;
        }
    }

    // Try partial match (e.g., "Round" matches "round", "Round ", etc.)
    for (const key in fieldPrices) {
        if (key.toLowerCase().trim().includes(lowerOption) || lowerOption.includes(key.toLowerCase().trim())) {
            const price = parseFloat(fieldPrices[key]) || 0;
            console.log(`Found partial match for ${fieldId}.${optionName} (matched ${key}): ${price}`);
            return price;
        }
    }

    console.log(`No price match found for ${fieldId}.${optionName}. Available options:`, Object.keys(fieldPrices));
    return 0;
}

// Helper function to get selected value for a field
function getSelectedValueForField(fieldId) {
    // First check dynamic customization values (from database fields)
    if (typeof window !== 'undefined' && window.selectedCustomizationValues) {
        const value = window.selectedCustomizationValues[fieldId];
        if (value !== undefined && value !== null && value !== '') {
            // Handle array values (for multi-select fields)
            if (Array.isArray(value) && value.length > 0) {
                return value[0]; // Return first selected value
            }
            return value;
        }
    }

    // Fallback to legacy field mappings (for backward compatibility)
    const legacyMappings = {
        'shape': currentShape,
        'glassType': currentGlassType,
        'thickness': currentThickness,
        'frameType': currentFrameType,
        'edgeWork': currentEdgeWork,
        'frameColor': currentFrameType,
        'edgeFinish': currentEdgeWork
    };

    // Also try to get from DOM based on field type
    const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
    if (fieldContainer) {
        // Check for checkbox fields
        const checkbox = fieldContainer.querySelector('input[type="checkbox"]');
        if (checkbox) {
            return checkbox.checked ? true : null;
        }

        // Check for number fields
        const numberInput = fieldContainer.querySelector('input[type="number"]');
        if (numberInput && numberInput.value) {
            return numberInput.value;
        }

        // Check for tag fields (option cards)
        const activeCard = fieldContainer.querySelector('.option-card.active');
        if (activeCard && activeCard.dataset.value) {
            return activeCard.dataset.value;
        }
    }

    return legacyMappings[fieldId] || null;
}

// priceBreakdown is declared at top of file to avoid temporal dead zone issues

function calculateTotal() {
    // Initialize pricing database if not already done
    if (typeof pricingDatabase === 'undefined' || pricingDatabase === null) {
        initializePricingDatabase();
    }

    // Safety check - if still null, use defaults
    if (!pricingDatabase) {
        console.warn('Pricing database not initialized, using defaults');
        return 1500;
    }

    // 1. Convert dimensions to Inches for calculation
    let h_in = currentDimensions.height.value;
    let w_in = currentDimensions.width.value;
    const unit = currentDimensions.height.unit;

    if (unit === 'cm') { h_in /= 2.54; w_in /= 2.54; }
    if (unit === 'mm') { h_in /= 25.4; w_in /= 25.4; }

    const areaSqIn = h_in * w_in;

    // 2. Calculate base area cost from database base price
    const baseAreaCost = areaSqIn * pricingDatabase.baseRatePerSqIn;
    priceBreakdown.baseArea = baseAreaCost;
    priceBreakdown.fieldPrices = {}; // Reset field prices

    // 3. Get all selected customization field values and calculate their prices
    let totalFieldPrices = 0;

    // Get product to access customization fields
    const product = getSelectedProduct();

    // First, check all active option-cards in the DOM (most reliable source)
    // This ensures we catch all selections regardless of whether they have prices
    const allFieldContainers = document.querySelectorAll('[data-field-id]');
    const processedFieldIds = new Set();

    allFieldContainers.forEach(container => {
        const fieldId = container.dataset.fieldId;
        if (!fieldId) return;

        const activeCard = container.querySelector('.option-card.active');
        if (activeCard) {
            const optionValue = activeCard.dataset.value || activeCard.textContent.trim();
            if (optionValue) {
                const optionPrice = getPriceFromDatabase(fieldId, optionValue);
                totalFieldPrices += optionPrice;
                priceBreakdown.fieldPrices[fieldId] = {
                    option: optionValue,
                    price: optionPrice
                };
                processedFieldIds.add(fieldId);
                console.log(`Added price for ${fieldId}.${optionValue}: ${optionPrice}`);
            }
        }
    });

    // Second, check product.tagPrices for fields that might not be in DOM yet
    // or for fields that have prices but weren't captured above
    if (product && product.tagPrices) {
        for (const fieldId in product.tagPrices) {
            // Skip if already processed from DOM
            if (processedFieldIds.has(fieldId)) {
                // Update if option changed
                const selectedValue = getSelectedValueForField(fieldId);
                const currentData = priceBreakdown.fieldPrices[fieldId];
                if (currentData && currentData.option !== selectedValue && selectedValue) {
                    // Remove old price
                    totalFieldPrices -= currentData.price;
                    // Add new price
                    const optionPrice = getPriceFromDatabase(fieldId, selectedValue);
                    totalFieldPrices += optionPrice;
                    priceBreakdown.fieldPrices[fieldId] = {
                        option: selectedValue,
                        price: optionPrice
                    };
                    console.log(`Updated price for ${fieldId}: ${currentData.option} -> ${selectedValue}, price: ${optionPrice}`);
                }
                continue;
            }

            // Process fields not yet captured
            const selectedValue = getSelectedValueForField(fieldId);
            if (selectedValue) {
                const optionPrice = getPriceFromDatabase(fieldId, selectedValue);
                totalFieldPrices += optionPrice;
                priceBreakdown.fieldPrices[fieldId] = {
                    option: selectedValue,
                    price: optionPrice
                };
                processedFieldIds.add(fieldId);
                console.log(`Added price for ${fieldId}.${selectedValue}: ${optionPrice}`);
            }
        }
    }

    // Third, check all customization fields from configuration to ensure we capture
    // fields that might not have prices but are still selected
    if (product && product.customizationFields && Array.isArray(product.customizationFields)) {
        product.customizationFields.forEach(field => {
            const fieldId = field.id;
            if (!fieldId || processedFieldIds.has(fieldId)) return;

            // Check DOM for this field
            const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
            if (fieldContainer) {
                const activeCard = fieldContainer.querySelector('.option-card.active');
                if (activeCard) {
                    const optionValue = activeCard.dataset.value || activeCard.textContent.trim();
                    if (optionValue) {
                        const optionPrice = getPriceFromDatabase(fieldId, optionValue);
                        totalFieldPrices += optionPrice;
                        priceBreakdown.fieldPrices[fieldId] = {
                            option: optionValue,
                            price: optionPrice
                        };
                        processedFieldIds.add(fieldId);
                        console.log(`Added price for ${fieldId}.${optionValue} (from config): ${optionPrice}`);
                    }
                }
            } else {
                // Field not in DOM yet, check selectedCustomizationValues
                const selectedValue = getSelectedValueForField(fieldId);
                if (selectedValue) {
                    const optionPrice = getPriceFromDatabase(fieldId, selectedValue);
                    totalFieldPrices += optionPrice;
                    priceBreakdown.fieldPrices[fieldId] = {
                        option: selectedValue,
                        price: optionPrice
                    };
                    processedFieldIds.add(fieldId);
                    console.log(`Added price for ${fieldId}.${selectedValue} (from values): ${optionPrice}`);
                }
            }
        });
    }

    // 4. Calculate total: base area cost + all field option prices
    let total = baseAreaCost + totalFieldPrices;

    // Apply minimum price constraint
    if (total < pricingDatabase.minimumPrice) {
        total = pricingDatabase.minimumPrice;
    }

    priceBreakdown.total = total;
    return total;
}

// Format price in PHP
function formatPrice(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    }).format(amount);
}

// --- REAL-TIME PRICE UPDATE WITH BREAKDOWN ---
function updateRealTimePriceDisplay() {
    // Price calculation has been disabled as price breakdown is no longer used
    // The price is now static and determined by ocular visit
    return;

    // Old code preserved for reference:
    /*
    // Initialize pricing database if needed (guard against undefined access)
    try {
        if (typeof pricingDatabase === 'undefined' || !pricingDatabase) {
            if (typeof initializePricingDatabase === 'function') {
                initializePricingDatabase();
            }
        }
    } catch (e) {
        console.warn('Error initializing pricing database:', e);
    }

    // 1. Calculate total (also updates priceBreakdown)
    const total = calculateTotal();

    // 2. Update main price display - DISABLED: Price is now static
    // const priceValue = document.getElementById('total-price');
    // if (priceValue) {
    //     priceValue.textContent = formatPrice(total);
    // }

    // 3. Update breakdown details - DISABLED: Breakdown removed
    // updatePriceBreakdown();
    */
}

function updatePriceBreakdown() {
    // Initialize pricing database if needed
    if (!pricingDatabase) {
        initializePricingDatabase();
    }

    // Safety check
    if (!pricingDatabase) {
        console.warn('Pricing database not available for breakdown');
        return;
    }

    // Get product data to determine which fields to show
    const selectedProduct = getSelectedProduct();
    if (!selectedProduct) {
        console.warn('Product data not available for dynamic breakdown');
        // Fallback: still update base area cost
        const costArea = document.getElementById('cost-area');
        if (costArea) costArea.textContent = formatPrice(priceBreakdown.baseArea);
        const estimatedCostArea = document.getElementById('estimated-cost-area');
        if (estimatedCostArea) estimatedCostArea.textContent = formatPrice(priceBreakdown.baseArea);
        return;
    }

    // Get customization fields configuration
    const customizationFields = selectedProduct.customizationFields || [];

    // Update dimensions in estimated price section
    const estimatedDimension = document.getElementById('estimated-dimension');
    if (estimatedDimension && currentDimensions) {
        const widthValue = currentDimensions.width.value || 0;
        const heightValue = currentDimensions.height.value || 0;
        const widthUnit = currentDimensions.width.unit || 'in';
        const heightUnit = currentDimensions.height.unit || 'in';
        estimatedDimension.textContent = `${widthValue}${widthUnit} × ${heightValue}${heightUnit}`;
    }

    // Update base area cost in both sections
    const costArea = document.getElementById('cost-area');
    if (costArea) costArea.textContent = formatPrice(priceBreakdown.baseArea);
    const estimatedCostArea = document.getElementById('estimated-cost-area');
    if (estimatedCostArea) estimatedCostArea.textContent = formatPrice(priceBreakdown.baseArea);

    // Get containers for dynamic rows (both sections)
    const dynamicContainer = document.getElementById('dynamic-breakdown-rows');
    const estimatedDynamicContainer = document.getElementById('estimated-dynamic-breakdown-rows');

    // Debug: Check if containers exist
    console.log('🔍 updatePriceBreakdown - dynamicContainer:', !!dynamicContainer);
    console.log('🔍 updatePriceBreakdown - estimatedDynamicContainer:', !!estimatedDynamicContainer);
    console.log('🔍 updatePriceBreakdown - priceBreakdown.fieldPrices:', priceBreakdown.fieldPrices);
    console.log('🔍 updatePriceBreakdown - customizationFields count:', customizationFields.length);

    // Don't return early - continue even if one container is missing
    if (!dynamicContainer && !estimatedDynamicContainer) {
        console.warn('Both breakdown containers not found');
        return;
    }

    // Clear existing dynamic rows in both sections
    if (dynamicContainer) {
        dynamicContainer.innerHTML = '';
    }
    if (estimatedDynamicContainer) {
        estimatedDynamicContainer.innerHTML = '';
    }

    // Create a map of field IDs to field configurations for quick lookup
    const fieldConfigMap = {};
    if (Array.isArray(customizationFields) && customizationFields.length > 0) {
        customizationFields.forEach(field => {
            if (field.id) {
                fieldConfigMap[field.id] = field;
            }
        });
    }

    // Get ALL fields from customization configuration, sorted by stepNumber
    // This ensures we show all fields defined for this product, not just those with prices
    let sortedFields = [];
    if (Array.isArray(customizationFields) && customizationFields.length > 0) {
        sortedFields = customizationFields
            .filter(field => field.id) // Only include fields with IDs
            .sort((a, b) => {
                const stepA = a.stepNumber || a.step || 999;
                const stepB = b.stepNumber || b.step || 999;
                if (stepA !== stepB) return stepA - stepB;
                // If same step, maintain original order
                return customizationFields.indexOf(a) - customizationFields.indexOf(b);
            });
    }

    // Also include any fields in priceBreakdown that might not be in customizationFields
    // (for backward compatibility with legacy fields)
    if (priceBreakdown.fieldPrices) {
        Object.keys(priceBreakdown.fieldPrices).forEach(fieldId => {
            if (!fieldConfigMap[fieldId]) {
                // Add as a field object for rendering
                sortedFields.push({
                    id: fieldId,
                    label: getFieldDisplayName(fieldId),
                    stepNumber: 999 // Put legacy fields at the end
                });
            }
        });
    }

    console.log('🔍 updatePriceBreakdown - sortedFields count:', sortedFields.length);

    // Render each field from the customization configuration
    let renderedCount = 0;
    sortedFields.forEach(field => {
        const fieldId = field.id;
        if (!fieldId) return;

        // Get field configuration
        const fieldConfig = fieldConfigMap[fieldId] || field;
        const fieldType = fieldConfig.type || 'tags'; // Default to tags if not specified

        // Get selected value for this field - check multiple sources
        let selectedValue = getSelectedValueForField(fieldId);

        // Also check DOM directly based on field type
        let optionValueFromDOM = null;
        const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);

        if (fieldContainer) {
            if (fieldType === 'checkbox') {
                // For checkbox fields, check if checkbox is checked
                const checkbox = fieldContainer.querySelector('input[type="checkbox"]');
                if (checkbox && checkbox.checked) {
                    optionValueFromDOM = 'Yes'; // Or use field label
                    if (!selectedValue) {
                        selectedValue = true; // Store as boolean for checkbox
                    }
                } else {
                    // Checkbox not checked - skip this field
                    return;
                }
            } else if (fieldType === 'number') {
                // For number fields, get the input value
                const numberInput = fieldContainer.querySelector('input[type="number"]');
                if (numberInput && numberInput.value && parseFloat(numberInput.value) > 0) {
                    optionValueFromDOM = numberInput.value;
                    if (!selectedValue) {
                        selectedValue = optionValueFromDOM;
                    }
                } else {
                    // Number field has no value - skip it
                    return;
                }
            } else {
                // For tags/other fields, check active option cards
                const activeCard = fieldContainer.querySelector('.option-card.active');
                if (activeCard) {
                    optionValueFromDOM = activeCard.dataset.value || activeCard.textContent.trim();
                    if (optionValueFromDOM && !selectedValue) {
                        selectedValue = optionValueFromDOM;
                    }
                }
            }
        }

        // Get price data if available
        const fieldData = priceBreakdown.fieldPrices && priceBreakdown.fieldPrices[fieldId];

        // Determine option name and price
        let optionName = '';
        let priceText = '';

        if (fieldType === 'checkbox') {
            // For checkbox, show "Yes" or field label if checked
            if (selectedValue || optionValueFromDOM) {
                optionName = fieldConfig.label || fieldId;
                priceText = 'Included';

                // Check if checkbox has a price
                if (fieldData && fieldData.price !== undefined) {
                    const numPrice = parseFloat(fieldData.price) || 0;
                    if (numPrice > 0) {
                        priceText = '+' + formatPrice(numPrice);
                    } else if (numPrice < 0) {
                        priceText = formatPrice(numPrice);
                    }
                } else {
                    // Try to get price from database
                    const product = getSelectedProduct();
                    if (product && product.tagPrices && product.tagPrices[fieldId]) {
                        // Checkbox prices might be stored as "Yes" or fieldId itself
                        const price = product.tagPrices[fieldId]['Yes'] ||
                            product.tagPrices[fieldId][fieldId] ||
                            product.tagPrices[fieldId][fieldConfig.label];
                        if (price !== undefined && price !== null) {
                            const numPrice = parseFloat(price) || 0;
                            if (numPrice > 0) {
                                priceText = '+' + formatPrice(numPrice);
                            } else if (numPrice < 0) {
                                priceText = formatPrice(numPrice);
                            }
                        }
                    }
                }
            } else {
                // Checkbox not checked - skip
                return;
            }
        } else if (fieldType === 'number') {
            // For number fields, show the value with unit if applicable
            if (selectedValue || optionValueFromDOM) {
                const numValue = selectedValue || optionValueFromDOM;
                optionName = numValue;

                // Add unit if field has one (e.g., "in" for corner radius)
                if (fieldId === 'cornerRadius' || fieldId === 'cornerRadiusIn') {
                    optionName = numValue + 'in';
                } else if (fieldId === 'thickness' || fieldId.includes('Thickness')) {
                    optionName = numValue + 'mm';
                }

                priceText = 'Included';

                // Check if number field has a price
                if (fieldData && fieldData.price !== undefined) {
                    const numPrice = parseFloat(fieldData.price) || 0;
                    if (numPrice > 0) {
                        priceText = '+' + formatPrice(numPrice);
                    } else if (numPrice < 0) {
                        priceText = formatPrice(numPrice);
                    }
                }
            } else {
                // Number field has no value - skip
                return;
            }
        } else if (fieldData && fieldData.option) {
            // Use data from priceBreakdown (has price info) - for tags fields
            optionName = fieldData.option;
            if (fieldData.price > 0) {
                priceText = '+' + formatPrice(fieldData.price);
            } else if (fieldData.price < 0) {
                priceText = formatPrice(fieldData.price);
            } else {
                if (fieldId === 'glassType' || fieldId === 'thickness') {
                    priceText = 'Standard';
                } else {
                    priceText = 'Included';
                }
            }
        } else if (selectedValue || optionValueFromDOM) {
            // Field is selected but no price data yet - still show it
            optionName = selectedValue || optionValueFromDOM;
            priceText = 'Included'; // Default to included if no price

            // Try to get price from database if available
            const product = getSelectedProduct();
            if (product && product.tagPrices && product.tagPrices[fieldId] && optionName) {
                const price = product.tagPrices[fieldId][optionName];
                if (price !== undefined && price !== null) {
                    const numPrice = parseFloat(price) || 0;
                    if (numPrice > 0) {
                        priceText = '+' + formatPrice(numPrice);
                    } else if (numPrice < 0) {
                        priceText = formatPrice(numPrice);
                    }
                }
            }
        } else {
            // Field is not selected - skip it (only show selected fields)
            console.log(`⏭️ Skipping field ${fieldId} - no selected value`);
            return;
        }

        // If we have an option name, render the row in both sections
        if (optionName) {
            const fieldConfigForDisplay = fieldConfigMap[fieldId] || field;
            const displayName = fieldConfigForDisplay ? (fieldConfigForDisplay.label || fieldId) : getFieldDisplayName(fieldId);

            // Format option name - handle various formats (skip for checkbox and number fields that already have proper formatting)
            if (fieldType !== 'checkbox' && fieldType !== 'number') {
                // Preserve the original option name from database/selection, but format it nicely
                // Remove camelCase conversion and keep original format from options
                optionName = String(optionName);

                // Only format if it looks like camelCase or lowercase
                if (/^[a-z]/.test(optionName) || /[a-z][A-Z]/.test(optionName)) {
                    optionName = optionName.replace(/([A-Z])/g, ' $1').trim();
                    optionName = optionName.charAt(0).toUpperCase() + optionName.slice(1);
                }

                // Capitalize first letter of each word
                optionName = optionName.split(' ').map(word => {
                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                }).join(' ');
            }

            // Helper function to create a breakdown row
            const createBreakdownRow = () => {
                const row = document.createElement('div');
                row.className = 'breakdown-row';
                row.dataset.fieldId = fieldId;

                // For checkbox, show just the label without option name in parentheses
                if (fieldType === 'checkbox') {
                    row.innerHTML = `
                        <span>${escapeHtml(displayName)}:</span>
                        <span class="price-value">${priceText}</span>
                    `;
                } else {
                    row.innerHTML = `
                        <span>${escapeHtml(displayName)} (<span class="option-name">${escapeHtml(optionName)}</span>):</span>
                        <span class="price-value">${priceText}</span>
                    `;
                }
                return row;
            };

            // Add to Price Breakdown section (collapsible)
            if (dynamicContainer) {
                const row = createBreakdownRow();
                dynamicContainer.appendChild(row);
            }

            // Add to Estimated Price section (always visible)
            if (estimatedDynamicContainer) {
                const estimatedRow = createBreakdownRow();
                estimatedDynamicContainer.appendChild(estimatedRow);
                renderedCount++;
            }

            console.log(`✅ Rendering field: ${fieldId} = ${optionName} (${priceText})`);
        } else {
            console.log(`❌ Skipping field: ${fieldId} - no option name (selectedValue: ${selectedValue}, optionValueFromDOM: ${optionValueFromDOM}, fieldData:`, fieldData, ')');
        }
    });

    console.log(`📊 Total fields rendered in estimated section: ${renderedCount}`);

    // Show/hide total row
    const totalRow = document.getElementById('breakdown-total-row');
    if (totalRow) {
        const totalEl = document.getElementById('cost-total');
        if (totalEl) {
            totalEl.textContent = formatPrice(priceBreakdown.total);
        }
        totalRow.style.display = 'flex';
    }
}

// Helper function to get display name for a field (fallback)
function getFieldDisplayName(fieldId) {
    // Try to get from active option card in DOM
    const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
    if (fieldContainer) {
        const activeCard = fieldContainer.querySelector('.option-card.active');
        if (activeCard) {
            const label = activeCard.closest('.field-section, .type-section, .thickness-section, .edge-section, .frame-section')?.querySelector('.section-label');
            if (label) {
                return label.textContent.trim();
            }
        }
    }

    // Fallback to common field name mappings based on CUSTOMIZATION_REFERENCE.md
    const commonNames = {
        'shape': 'Shape',
        'glassType': 'Glass Type',
        'thickness': 'Thickness',
        'frameType': 'Frame Type',
        'frameColor': 'Frame Color',
        'edgeWork': 'Edge Work',
        'edgeFinish': 'Edge Finish',
        'mountingMethod': 'Mounting Method',
        'tintFinish': 'Tint/Finish',
        'tint': 'Tint/Finish',
        'cornerRadius': 'Corner Radius',
        'orientation': 'Orientation',
        'lighting': 'Lighting',
        'ledColor': 'LED Color/Temperature',
        'control': 'Control',
        'glassColor': 'Glass Color',
        'operation': 'Operation',
        'handleType': 'Handle Type',
        'hardwareFinish': 'Hardware Finish',
        'softClose': 'Soft-close',
        'screen': 'Screen',
        'lockType': 'Lock Type',
        'rollerType': 'Roller Type',
        'numberOfPanels': 'Number of Panels',
        'panelCount': 'Panel Count',
        'trackSystem': 'Track System',
        'panelConfiguration': 'Panel Configuration',
        'transomType': 'Transom Type',
        'openingDirection': 'Opening Direction',
        'hingeSide': 'Hinge Side',
        'configuration': 'Configuration',
        'layout': 'Layout',
        'finish': 'Finish',
        'glassTreatment': 'Glass Treatment',
        'handleStyle': 'Handle Style',
        'doorSwing': 'Door Swing',
        'mounting': 'Mounting',
        'hardware': 'Hardware'
    };

    return commonNames[fieldId] || fieldId.replace(/([A-Z])/g, ' $1').trim();
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Toggle price breakdown visibility
const breakdownToggle = document.getElementById('breakdown-toggle');
const breakdownDetails = document.getElementById('breakdown-details');

if (breakdownToggle && breakdownDetails) {
    breakdownToggle.addEventListener('click', () => {
        breakdownDetails.classList.toggle('hidden-step');
        breakdownToggle.classList.toggle('active');
    });
}

// Log will be done after initialization

// --- KONVA IMAGE EXPORT FUNCTIONS ---

// Generate high-quality image from Konva stage
function getKonvaImageData(pixelRatio = 3) {
    return stage.toDataURL({
        pixelRatio: pixelRatio,
        mimeType: 'image/png'
    });
}

// currentDesignImageData is declared at top of file to avoid temporal dead zone issues

// --- SUMMARY VIEW LOGIC ---

// Update summary price breakdown dynamically
function updateSummaryPriceBreakdown() {
    const selectedProduct = getSelectedProduct();
    if (!selectedProduct) {
        console.warn('Product data not available for dynamic summary breakdown');
        return;
    }

    const customizationFields = selectedProduct.customizationFields || [];
    const dynamicSummaryContainer = document.getElementById('dynamic-summary-rows');

    if (!dynamicSummaryContainer) {
        console.warn('Dynamic summary container not found');
        return;
    }

    // Clear existing dynamic rows
    dynamicSummaryContainer.innerHTML = '';

    // Create field config map
    const fieldConfigMap = {};
    if (Array.isArray(customizationFields) && customizationFields.length > 0) {
        customizationFields.forEach(field => {
            if (field.id) {
                fieldConfigMap[field.id] = field;
            }
        });
    }

    // Get ALL fields from customization configuration, sorted by stepNumber
    // This ensures we show all fields defined for this product, not just those with prices
    let sortedFields = [];
    if (Array.isArray(customizationFields) && customizationFields.length > 0) {
        sortedFields = customizationFields
            .filter(field => field.id) // Only include fields with IDs
            .sort((a, b) => {
                const stepA = a.stepNumber || a.step || 999;
                const stepB = b.stepNumber || b.step || 999;
                if (stepA !== stepB) return stepA - stepB;
                return customizationFields.indexOf(a) - customizationFields.indexOf(b);
            });
    }

    // Also include any fields in priceBreakdown that might not be in customizationFields
    // (for backward compatibility with legacy fields)
    if (priceBreakdown.fieldPrices) {
        Object.keys(priceBreakdown.fieldPrices).forEach(fieldId => {
            if (!fieldConfigMap[fieldId]) {
                // Add as a field object for rendering
                sortedFields.push({
                    id: fieldId,
                    label: getFieldDisplayName(fieldId),
                    stepNumber: 999 // Put legacy fields at the end
                });
            }
        });
    }

    // Render each field from the customization configuration
    sortedFields.forEach(field => {
        const fieldId = field.id;
        if (!fieldId) return;

        // Get selected value for this field - check multiple sources
        let selectedValue = getSelectedValueForField(fieldId);

        // Also check DOM directly for active option cards (most reliable)
        let optionValueFromDOM = null;
        const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
        if (fieldContainer) {
            const activeCard = fieldContainer.querySelector('.option-card.active');
            if (activeCard) {
                optionValueFromDOM = activeCard.dataset.value || activeCard.textContent.trim();
                if (optionValueFromDOM && !selectedValue) {
                    selectedValue = optionValueFromDOM;
                }
            }
        }

        // Get price data if available
        const fieldData = priceBreakdown.fieldPrices && priceBreakdown.fieldPrices[fieldId];

        // Determine option name and price
        let optionName = '';
        let priceText = '';

        if (fieldData && fieldData.option) {
            // Use data from priceBreakdown (has price info)
            optionName = fieldData.option;
            if (fieldData.price > 0) {
                priceText = '+' + formatPrice(fieldData.price);
            } else if (fieldData.price < 0) {
                priceText = formatPrice(fieldData.price);
            } else {
                if (fieldId === 'glassType' || fieldId === 'thickness') {
                    priceText = 'Standard';
                } else {
                    priceText = 'Included';
                }
            }
        } else if (selectedValue || optionValueFromDOM) {
            // Field is selected but no price data yet - still show it
            optionName = selectedValue || optionValueFromDOM;
            priceText = 'Included'; // Default to included if no price

            // Try to get price from database if available
            const product = getSelectedProduct();
            if (product && product.tagPrices && product.tagPrices[fieldId] && optionName) {
                const price = product.tagPrices[fieldId][optionName];
                if (price !== undefined && price !== null) {
                    const numPrice = parseFloat(price) || 0;
                    if (numPrice > 0) {
                        priceText = '+' + formatPrice(numPrice);
                    } else if (numPrice < 0) {
                        priceText = formatPrice(numPrice);
                    }
                }
            }
        } else {
            // Field is not selected - skip it (only show selected fields)
            return;
        }

        // If we have an option name, render the row (without price info)
        if (optionName) {
            const fieldConfig = fieldConfigMap[fieldId];
            const displayName = fieldConfig ? (fieldConfig.label || fieldId) : getFieldDisplayName(fieldId);

            // Format option name - handle various formats
            optionName = String(optionName).replace(/([A-Z])/g, ' $1').trim();
            optionName = optionName.charAt(0).toUpperCase() + optionName.slice(1);

            const row = document.createElement('div');
            row.className = 'summary-row';
            row.dataset.fieldId = fieldId;
            row.innerHTML = `
                <span class="spec-label">${escapeHtml(displayName)}:</span>
                <span class="spec-value">
                    <span>${escapeHtml(optionName)}</span>
                </span>
            `;

            dynamicSummaryContainer.appendChild(row);
        }
    });

    // Total row removed - price is now static
}

// Update the Quotation Preview section with current customization data
function updateQuotationPreview(totalPrice) {
    // Generate quotation number (random for now, would be server-generated in production)
    const quotNumEl = document.getElementById('quotation-number');
    if (quotNumEl && !quotNumEl.dataset.generated) {
        const randomNum = Math.floor(100000 + Math.random() * 900000);
        quotNumEl.textContent = randomNum;
        quotNumEl.dataset.generated = 'true';
    }

    // Update dimensions in quotation
    const quotDimensions = document.getElementById('quot-dimensions');
    if (quotDimensions && currentDimensions) {
        quotDimensions.textContent = `${currentDimensions.width.value}${currentDimensions.width.unit} × ${currentDimensions.height.value}${currentDimensions.height.unit}`;
    }

    // Update base price
    const quotBasePrice = document.getElementById('quot-base-price');
    if (quotBasePrice) {
        quotBasePrice.textContent = formatPrice(priceBreakdown.baseArea || 0);
    }

    // Update quotation items table with dynamic fields
    const quotItemsContainer = document.getElementById('quotation-items');
    if (quotItemsContainer) {
        // Keep only the first row (dimensions)
        const firstRow = quotItemsContainer.querySelector('tr');
        quotItemsContainer.innerHTML = '';
        if (firstRow) {
            quotItemsContainer.appendChild(firstRow);
        }

        // Add dynamic field rows from price breakdown
        const selectedProduct = getSelectedProduct();
        if (selectedProduct && selectedProduct.customizationFields) {
            const customizationFields = selectedProduct.customizationFields;
            const selectedValues = window.selectedCustomizationValues || {};

            customizationFields.forEach(field => {
                const fieldId = field.id;
                const selectedValue = selectedValues[fieldId];
                
                if (selectedValue && fieldId !== 'dimensions') {
                    const displayName = field.label || field.id.replace(/([A-Z])/g, ' $1').trim();
                    let optionName = selectedValue;
                    let priceAmount = 0;

                    // Get price from priceBreakdown if available
                    if (priceBreakdown.customizations && priceBreakdown.customizations[fieldId]) {
                        priceAmount = priceBreakdown.customizations[fieldId];
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${escapeHtml(displayName)}: ${escapeHtml(optionName)}</td>
                        <td class="text-right">${formatPrice(priceAmount)}</td>
                    `;
                    quotItemsContainer.appendChild(row);
                }
            });
        }
    }

    // Update subtotal and total
    const quotSubtotal = document.getElementById('quot-subtotal');
    const quotTotal = document.getElementById('quot-total');
    if (quotSubtotal) {
        quotSubtotal.textContent = formatPrice(totalPrice);
    }
    if (quotTotal) {
        quotTotal.textContent = formatPrice(totalPrice);
    }
}

function showOrderSummary() {
    // Store the current step before entering review
    lastStepBeforeReview = window.currentStep || currentStep;

    // Hide testimonials section when finalize order is clicked
    const testimonialsSection = document.getElementById('testimonials-section');
    if (testimonialsSection) {
        testimonialsSection.style.display = 'none';
    }
    // 1. Hide Builder UI
    customWrapper.classList.add('hidden-step');
    standardWrapper.classList.add('hidden-step');
    priceBox.classList.add('hidden-step');
    document.querySelector('.build-toggle').classList.add('hidden-step');
    document.getElementById('standard-subtitle').classList.add('hidden-step');

    // --- Hide Related Products and Testimonials ---
    document.getElementById('related-products-section').classList.add('hidden-step');

    // --- Hide Product Description Section ---
    const productDescSection = document.getElementById('product-description-section');
    if (productDescSection) {
        productDescSection.style.display = 'none';
    }

    // --- Hide Order Type Note ---
    const orderTypeNote = document.querySelector('.order-type-note');
    if (orderTypeNote) {
        orderTypeNote.style.display = 'none';
    }

    // --- Hide Guest Notice Banner ---
    const guestNoticeBanner = document.getElementById('guest-notice-banner');
    if (guestNoticeBanner) {
        guestNoticeBanner.style.display = 'none';
    }

    // 2. Show Summary UI
    const summaryWrapper = document.getElementById('summary-wrapper');
    summaryWrapper.classList.remove('hidden-step');

    // Scroll to review section so user can see it
    setTimeout(() => {
        summaryWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);

    // 3. Generate and display design preview image
    currentDesignImageData = getKonvaImageData(3);
    const designPreviewImg = document.getElementById('design-preview-img');
    if (designPreviewImg) {
        designPreviewImg.src = currentDesignImageData;
    }

    // 4. Update Summary Data with dynamic price breakdown
    // Force populate priceBreakdown.fieldPrices so updateSummaryPriceBreakdown shows fields
    try {
        calculateTotal(); // This will populate priceBreakdown.fieldPrices with selected values
    } catch (e) {
        console.warn('Calculate total failed, will show fields from DOM instead:', e);
    }
    updateSummaryPriceBreakdown();

    // Dimensions (always shown)
    const sumDimEl = document.getElementById('sum-dim');
    if (sumDimEl && currentDimensions) {
        sumDimEl.textContent = `${currentDimensions.width.value}${currentDimensions.width.unit} × ${currentDimensions.height.value}${currentDimensions.height.unit}`;
    }
    const sumDimPrice = document.getElementById('sum-dim-price');
    if (sumDimPrice) {
        sumDimPrice.textContent = 'Base: ' + formatPrice(priceBreakdown.baseArea);
    }

    // Engraving - Check both custom step-3 and standard wrapper
    let engravingInput = document.querySelector('#step-3 .engraving-section input');
    if (!engravingInput || !engravingInput.value) {
        engravingInput = document.querySelector('#standard-wrapper .engraving-section input');
    }
    const engravingText = engravingInput ? engravingInput.value : '';
    const engravingRow = document.getElementById('summary-engraving-row');
    const sumEngraveEl = document.getElementById('sum-engrave');
    if (engravingText && engravingText.trim()) {
        if (sumEngraveEl) sumEngraveEl.textContent = engravingText;
        if (engravingRow) engravingRow.style.display = 'flex';
    } else {
        if (sumEngraveEl) sumEngraveEl.textContent = 'None';
        if (engravingRow) engravingRow.style.display = 'none';
    }

    // 5. Update Total Price - DISABLED: sum-total element removed, price is determined at ocular visit
    // const totalPrice = calculateTotal();
    // const sumTotalEl = document.getElementById('sum-total');
    // if (sumTotalEl) {
    //     sumTotalEl.textContent = formatPrice(totalPrice);
    // }

    // 6. Update Quotation Preview Section - DISABLED: No price calculation
    // updateQuotationPreview(totalPrice);

    // 7. Update breadcrumbs to show Review Order (for site orders that were hidden until now)
    const crumbReview = document.getElementById('crumb-review');
    if (crumbReview) {
        crumbReview.style.display = '';
        crumbReview.classList.add('active');
        // Clear active state from other crumbs - only Review Order should be active
        const crumbStep1 = document.getElementById('crumb-step1');
        const crumbStep2 = document.getElementById('crumb-step2');
        if (crumbStep1) crumbStep1.classList.remove('active');
        if (crumbStep2) crumbStep2.classList.remove('active');

        // Clear active state from any intermediate steps
        const intermediateSteps = document.querySelectorAll('[id^="crumb-step"]:not(#crumb-step1):not(#crumb-step2):not(#crumb-review)');
        intermediateSteps.forEach(step => step.classList.remove('active'));

        // Ensure there's a chevron connecting to Review Order
        if (typeof ensureChevronBeforeReviewOrder === 'function') {
            ensureChevronBeforeReviewOrder();
        }
    }
}

function editConfiguration() {
    // Hide Summary
    document.getElementById('summary-wrapper').classList.add('hidden-step');

    // --- Show Related Products again (Testimonials should already be visible) ---
    document.getElementById('related-products-section').classList.remove('hidden-step');
    // Testimonials are always visible, no need to show/hide

    // --- Show Product Description Section again ---
    const productDescSection = document.getElementById('product-description-section');
    if (productDescSection) {
        productDescSection.style.display = 'block';
    }

    // --- Show Order Type Note again ---
    const orderTypeNote = document.querySelector('.order-type-note');
    if (orderTypeNote) {
        orderTypeNote.style.display = 'block';
    }

    // --- Show Guest Notice Banner again ---
    const guestNoticeBanner = document.getElementById('guest-notice-banner');
    if (guestNoticeBanner) {
        guestNoticeBanner.style.display = 'block';
    }

    // Hide Review Order in breadcrumbs when editing configuration
    const crumbReview = document.getElementById('crumb-review');
    if (crumbReview) {
        crumbReview.style.display = 'none';
        crumbReview.classList.remove('active');
        // Also hide the chevron before Review Order if it exists
        const reviewChevron = document.getElementById('chevron-crumb-review');
        if (reviewChevron) reviewChevron.style.display = 'none';
    }

    // Keep all previous steps active to show progress, but remove active from current step if going back
    // The breadcrumbs will be updated by the step navigation logic

    // Show Toggle and Subtitle
    document.querySelector('.build-toggle').classList.remove('hidden-step');

    // Determine which wrapper to show based on mode
    if (isStandardMode) {
        standardWrapper.classList.remove('hidden-step');
        document.getElementById('standard-subtitle').classList.remove('hidden-step');
        // Update breadcrumbs for standard mode (no steps)
        // Breadcrumbs are managed dynamically
    } else {
        customWrapper.classList.remove('hidden-step');
        priceBox.classList.remove('hidden-step');
        // Return to the IMMEDIATELY PREVIOUS step before review
        // This preserves all selections and allows modification from that step onward
        const previousStep = lastStepBeforeReview || 3; // Fallback to step 3 if not set
        goToStep(previousStep);
        // Update breadcrumbs to reflect the current step
        if (typeof updateDynamicBreadcrumbs === 'function') {
            updateDynamicBreadcrumbs(previousStep, window.totalCustomizationSteps || 3);
        }
    }
}

// Helper Utils
function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
function formatText(str) {
    if (!str) return '';
    return str.split('-').map(word => capitalize(word)).join(' ');
}

// --- EVENT LISTENER UPDATES ---
// Wrap in DOMContentLoaded to ensure elements exist
document.addEventListener('DOMContentLoaded', function() {
    
    // 2. Finalize Button (Standard Flow)
    const stdFinalizeBtn = document.querySelector('#standard-wrapper .next-btn');
    if (stdFinalizeBtn) {
        stdFinalizeBtn.onclick = null; // Remove inline alert
        stdFinalizeBtn.addEventListener('click', () => {
            console.log("Finalizing Standard Order...");
            showOrderSummary();
            logOrderSummary();
        });
    }

    // Add listener for finalize button with specific ID
    const finalizeOrderBtn = document.getElementById('finalize-order-btn');
    if (finalizeOrderBtn) {
        finalizeOrderBtn.addEventListener('click', () => {
            console.log("Finalizing Order...");
            showOrderSummary();
            logOrderSummary();
        });
    }

    // 3. Edit Order Button - ensure it works
    const editOrderBtn = document.getElementById('edit-order-btn');
    if (editOrderBtn) {
        console.log('Edit order button found, attaching event listener');
        editOrderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Edit configuration clicked');
            editConfiguration();
        });
    } else {
        console.warn('Edit order button not found!');
    }

    // 4. Design Preview Image Click to Enlarge (in review section)
    const designPreviewImg = document.getElementById('design-preview-img');
    if (designPreviewImg) {
        designPreviewImg.style.cursor = 'pointer';
        designPreviewImg.style.transition = 'transform 0.2s ease';
        designPreviewImg.title = 'Click to enlarge';
        
        // Add hover effect
        designPreviewImg.addEventListener('mouseenter', () => {
            designPreviewImg.style.transform = 'scale(1.02)';
        });
        designPreviewImg.addEventListener('mouseleave', () => {
            designPreviewImg.style.transform = 'scale(1)';
        });
        
        designPreviewImg.addEventListener('click', () => {
            const previewModal = document.getElementById('preview-modal');
            const zoomedImg = document.getElementById('zoomed-preview-img');
            if (previewModal && zoomedImg) {
                // Use current design image data or generate new one
                const dataUrl = currentDesignImageData || getKonvaImageData(3);
                zoomedImg.src = dataUrl;
                previewModal.classList.remove('hidden-step');
            }
        });
    }

});


// --- 2D PREVIEW MODAL LOGIC ---
// (This handles the pop-up when clicking "2D Preview")
const previewLabel = document.querySelector('.preview-label');
const previewModal = document.getElementById('preview-modal');
const zoomedImg = document.getElementById('zoomed-preview-img');
const previewCloseBtn = document.getElementById('preview-close-btn');
const downloadDesignBtn = document.getElementById('download-design-btn');

// Check if elements exist to avoid errors
if (previewLabel && previewModal && zoomedImg) {
    // Open Modal when clicking 2D Preview label
    previewLabel.addEventListener('click', () => {
        // Check if custom wrapper is locked (incomplete setup users)
        const customWrapper = document.getElementById('custom-wrapper');
        if (customWrapper && customWrapper.classList.contains('custom-wrapper-locked')) {
            // Don't open preview modal when locked
            return;
        }
        
        // Generate a high-quality image from the Konva Stage
        const dataUrl = getKonvaImageData(3);
        zoomedImg.src = dataUrl;
        previewModal.classList.remove('hidden-step');
    });

    // Close Modal (Click Outside) - improved event handling
    previewModal.addEventListener('click', (e) => {
        // Close if clicking the backdrop (modal itself) or any non-interactive area
        if (e.target === previewModal || e.target.classList.contains('modal-backdrop')) {
            previewModal.classList.add('hidden-step');
        }
    });

    // Prevent modal content clicks from bubbling up to backdrop
    const previewContent = previewModal.querySelector('.preview-modal-content');
    if (previewContent) {
        previewContent.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Close button
    if (previewCloseBtn) {
        previewCloseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            previewModal.classList.add('hidden-step');
        });
    }

    // Close modal with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !previewModal.classList.contains('hidden-step')) {
            previewModal.classList.add('hidden-step');
        }
    });

    // Download button
    if (downloadDesignBtn) {
        downloadDesignBtn.addEventListener('click', () => {
            downloadDesign();
        });
    }
}

// Download design image function
function downloadDesign() {
    const dataUrl = getKonvaImageData(4); // Higher quality for download
    const link = document.createElement('a');
    link.download = `glassify-design-${Date.now()}.png`;
    link.href = dataUrl;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Make design image data accessible globally for cart submission
window.getDesignImageData = function () {
    return currentDesignImageData || getKonvaImageData(3);
};

// Get current customization state for cart
window.getCustomizationState = function () {
    return {
        shape: currentShape,
        glassType: currentGlassType,
        thickness: currentThickness,
        edgeWork: currentEdgeWork,
        frameType: currentFrameType,
        dimensions: currentDimensions,
        priceBreakdown: priceBreakdown
    };
};


// --- BUY NOW REDIRECT LOGIC ---
// Handler moved to addtocustomization.js for proper AJAX handling


function logOrderSummary() {
    console.log("=== ORDER SUMMARY (from database + customizations) ===");

    // From PHP (database)
    console.log("Product ID:", selectedProduct.id);
    console.log("Product Name:", selectedProduct.name);
    console.log("Category:", selectedProduct.category);
    console.log("Material:", selectedProduct.material);
    console.log("Base Price:", selectedProduct.price);

    // From your customization UI
    console.log("Shape:", currentShape);
    console.log("Glass Type:", currentGlassType);
    console.log("Thickness:", currentThickness);
    console.log("Edge Work:", currentEdgeWork);
    console.log("Frame Type:", currentFrameType);

    console.log("Dimensions:", {
        height: currentDimensions.height.value + currentDimensions.height.unit,
        width: currentDimensions.width.value + currentDimensions.width.unit
    });

    console.log("=== END SUMMARY ===");
}

// Image Counter Update (for product gallery)
(function () {
    let currentImageIndex = 1;
    const productImages = document.querySelectorAll('.main-product-image');
    const totalImages = productImages.length || 1;
    const imageCounter = document.getElementById('image-counter');
    const prevBtn = document.getElementById('prev-image');
    const nextBtn = document.getElementById('next-image');

    function updateImageCounter() {
        if (imageCounter) {
            imageCounter.textContent = `${currentImageIndex}/${totalImages}`;
        }

        // Show/hide images based on current index
        productImages.forEach((img, index) => {
            if (index + 1 === currentImageIndex) {
                img.style.display = 'block';
                img.classList.add('active');
            } else {
                img.style.display = 'none';
                img.classList.remove('active');
            }
        });

        // Enable/disable navigation buttons
        if (prevBtn) {
            prevBtn.disabled = currentImageIndex === 1;
            prevBtn.style.opacity = currentImageIndex === 1 ? '0.5' : '1';
        }
        if (nextBtn) {
            nextBtn.disabled = currentImageIndex === totalImages;
            nextBtn.style.opacity = currentImageIndex === totalImages ? '0.5' : '1';
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentImageIndex > 1) {
                currentImageIndex--;
                updateImageCounter();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentImageIndex < totalImages) {
                currentImageIndex++;
                updateImageCounter();
            }
        });
    }

    // Initialize counter and display
    updateImageCounter();
})();



