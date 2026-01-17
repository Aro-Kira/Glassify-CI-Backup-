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
const crumbMain = document.getElementById('crumb-main');
const breadcrumbsContainer = document.getElementById('breadcrumbs-container');
const nextNote = document.getElementById('next-note');
const backNote = document.getElementById('back-note');
const inputHeight = document.getElementById('input-height');
const btnUnitHeight = document.getElementById('btn-unit-height');
const inputWidth = document.getElementById('input-width');
const btnUnitWidth = document.getElementById('btn-unit-width');
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

// CUSTOM STATE VARIABLES
let currentShape = 'rectangle';
let currentGlassType = 'tempered';
let currentThickness = '5mm';
let currentEdgeWork = 'flat-polish';
let currentFrameType = 'vinyl';
// Corner radius in inches (applies to rectangle/square only)
let currentCornerRadius = 0;
let currentDimensions = {
    height: { value: 45, unit: 'in' },
    width: { value: 35, unit: 'in' }
};

const unitMap = {
    'in': { name: 'Inches', toMm: 25.4 },
    'cm': { name: 'Centimeters', toMm: 10 },
    'mm': { name: 'Millimeters', toMm: 1 }
};


// --- KONVA.JS VISUALIZATION LOGIC ---

const KONVA_CONTAINER_ID = 'konva-container';
const konvaWrapper = document.getElementById(KONVA_CONTAINER_ID);
const STAGE_SIZE = konvaWrapper.offsetWidth;

const PADDING = 40;
const DRAWING_SIZE = STAGE_SIZE - PADDING * 2;
const DIM_OFFSET = 15;

// --- VISUAL CONFIGURATION ---
// Synced with customization_fields_presets_summary.md
// These objects are extended dynamically with custom visual configs from admin
let glassStyles = {
    // Preset glass types
    'clear': { fill: '#E0F2F1', opacity: 0.9 },
    'tinted': { fill: '#546E7A', opacity: 0.7 },
    'laminated': { fill: '#CFD8DC', opacity: 0.95 },
    // Additional glass types
    'tempered': { fill: '#E0F2F1', opacity: 0.9 },
    'double': { fill: '#B2DFDB', opacity: 0.9 },
    'low-e': { fill: '#Dcedc8', opacity: 0.85 },
    'frosted': { fill: '#FFFFFF', opacity: 0.95 },
    'patterned': { fill: '#E8E8E8', opacity: 0.9 }
};

// DEFAULT frame styles - these are FALLBACKS only
// Admin-configured visual styles will OVERRIDE these when loadDynamicVisualConfigs() runs
let frameStyles = {
    // Preset frame colors/materials (will be overwritten by admin configs)
    'white': { color: '#FFFFFF', width: 4, isDefault: true },
    'black': { color: '#000000', width: 4, isDefault: true },
    'silver': { color: '#C0C0C0', width: 3, isDefault: true },
    'bronze': { color: '#CD7F32', width: 3, isDefault: true },
    'gold': { color: '#FFD700', width: 4, isDefault: true },
    'rose-gold': { color: '#B76E79', width: 4, isDefault: true },
    'wood': { color: '#795548', width: 6, isDefault: true },
    'aluminum': { color: '#90A4AE', width: 3, isDefault: true },
    'chrome': { color: '#E8E8E8', width: 3, isDefault: true },
    'brushed-nickel': { color: '#A8A9AD', width: 3, isDefault: true },
    'stainless-steel': { color: '#C9CCD1', width: 3, isDefault: true },
    'custom-color': { color: '#888888', width: 4, isDefault: true },
    // Legacy frame types (mapped from old system)
    'vinyl': { color: '#333333', width: 4, isDefault: true },
    'frameless': { color: 'transparent', width: 0, isDefault: true }
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
            
            // Check if this is a glass-related field (expanded detection)
            const isGlassField = fieldIdLower.includes('glass') || 
                                 fieldIdLower.includes('tint') || 
                                 fieldIdLower.includes('finish') ||
                                 fieldIdLower.includes('type') ||
                                 fieldIdLower.includes('material') ||
                                 effectType === 'fill' ||
                                 effectType === 'gradient' ||
                                 effectType === 'pattern' ||
                                 effectType === 'overlay';
            
            // Check if this is a frame-related field (expanded detection)
            const isFrameField = fieldIdLower.includes('frame') || 
                                 fieldIdLower.includes('color') || 
                                 fieldIdLower.includes('edge') || 
                                 fieldIdLower.includes('border') ||
                                 fieldIdLower.includes('stroke') ||
                                 effectType === 'frame' ||
                                 effectType === 'edge';
            
            if (isGlassField) {
                // Add/update glass style with full config
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
                    shadowOpacity: config.shadowOpacity
                };
                glassConfigsAdded++;
                console.log(`[Konva] ✅ GLASS style added for "${normalizedTagName}": fill=${config.fill}, opacity=${config.opacity}`);
            }
            
            if (isFrameField) {
                // For frame colors, use the stroke color as the primary frame color
                // Fall back to fill color if stroke is not set
                const frameColor = config.stroke || config.fill || '#333333';
                
                // Add/update frame style with full config
                frameStyles[normalizedTagName] = {
                    color: frameColor,
                    width: config.strokeWidth || 4,
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
                    opacity: config.opacity
                };
                frameConfigsAdded++;
                console.log(`[Konva] ✅ FRAME style added for "${normalizedTagName}": color=${frameColor}, width=${config.strokeWidth}`);
            }
            
            // Also add by original tag name variants for flexible lookups
            const tagVariants = [
                tagName.toLowerCase(),
                tagName.toLowerCase().replace(/\s+/g, '-'),
                tagName.toLowerCase().replace(/\s+/g, '_'),
                tagName.replace(/\s+/g, '')
            ];
            
            tagVariants.forEach(variant => {
                if (variant !== normalizedTagName) {
                    if (isFrameField && frameStyles[normalizedTagName]) {
                        frameStyles[variant] = frameStyles[normalizedTagName];
                    }
                    if (isGlassField && glassStyles[normalizedTagName]) {
                        glassStyles[variant] = glassStyles[normalizedTagName];
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
    if ((effectType === 'shadow' || config.shadowBlur) && config.shadowBlur > 0) {
        shape.shadowColor(config.shadowColor || '#000000');
        shape.shadowBlur(config.shadowBlur || 10);
        shape.shadowOffset({ x: config.shadowOffset || 5, y: config.shadowOffset || 5 });
        shape.shadowOpacity(config.shadowOpacity || 0.3);
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
        // Frosted glass effect - random small dots
        for (let i = 0; i < (density || 5) * 20; i++) {
            const dotX = x + Math.random() * width;
            const dotY = y + Math.random() * height;
            layer.add(new Konva.Circle({
                x: dotX,
                y: dotY,
                radius: Math.random() * 2 + 0.5,
                fill: '#FFFFFF',
                opacity: Math.random() * 0.3 + 0.1,
                listening: false
            }));
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
        
        if (window.selectedProduct && window.selectedProduct.tagVisualConfigs) {
            const configCount = Object.keys(window.selectedProduct.tagVisualConfigs).length;
            if (configCount > 0) {
                console.log(`[Konva] Auto-loading ${configCount} visual config field(s) from product data`);
                loadDynamicVisualConfigs(window.selectedProduct.tagVisualConfigs);
                visualConfigsLoaded = true;
            }
        }
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
    
    // Helper to check if visual configs are loaded
    window.areVisualConfigsLoaded = () => visualConfigsLoaded;
    
    // Helper to force reload visual configs (useful for debugging)
    window.reloadVisualConfigs = () => {
        visualConfigsLoaded = false;
        checkAndLoadConfigs();
    };
}

// Initialize Konva
const stage = new Konva.Stage({
    container: KONVA_CONTAINER_ID,
    width: STAGE_SIZE,
    height: STAGE_SIZE,
});

const layer = new Konva.Layer();
stage.add(layer);

/**
 * Renders multi-panel product configuration (e.g., sliding doors, windows with multiple panels)
 * Based on product catalog JSON customization options
 */
function renderMultiPanelProduct(widthIn, heightIn, unit, glassType, thickness, edgeWork, frameType, originalWidth, originalHeight, heightUnit, customizationValues = {}) {
    layer.destroyChildren();

    // Get panel configuration from customization values
    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || customizationValues.NumberOfPanels || '2-panel');
    const operation = (customizationValues.operation || customizationValues.Operation || 'sliding').toLowerCase();
    const configuration = (customizationValues.configuration || customizationValues.Configuration || '').toLowerCase();
    
    // Determine if panels are fixed or operable
    const hasFixedPanels = configuration.includes('fixed') || operation.includes('fixed');
    const isSliding = operation.includes('sliding');
    const isSwing = operation.includes('swing');
    
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
    const panelHeight = totalHeight;
    
    // Fixed section height (top portion of each panel - shown as darker section with "F")
    // Show fixed section on all panels if configuration indicates fixed panels, or show on all by default for multi-panel
    const showFixedSection = hasFixedPanels || numberOfPanels > 1; // Show on all panels for multi-panel products
    const fixedSectionHeight = showFixedSection ? panelHeight * 0.15 : 0; // 15% of panel height
    const operableSectionHeight = panelHeight - fixedSectionHeight;
    
    // Draw panels
    for (let i = 0; i < numberOfPanels; i++) {
        const panelX = offsetX + (i * panelWidth);
        const panelY = offsetY;
        
        // Draw fixed section (top portion with "F" label) - show on all panels for multi-panel products
        if (showFixedSection) {
            const fixedRect = new Konva.Rect({
                x: panelX,
                y: panelY,
                width: panelWidth,
                height: fixedSectionHeight,
                fill: '#4A90E2', // Darker blue for fixed section
                opacity: 0.8,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(fixedRect);
            
            // Add "F" label for Fixed
            const fixedLabel = new Konva.Text({
                x: panelX + panelWidth / 2,
                y: panelY + fixedSectionHeight / 2,
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
        }
        
        // Draw operable section (main glass panel)
        const glassRect = new Konva.Rect({
            x: panelX,
            y: panelY + fixedSectionHeight,
            width: panelWidth,
            height: operableSectionHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
        layer.add(glassRect);
        
        // Add handle/opening indicator (circle "O") in center of operable section
        const handleX = panelX + panelWidth / 2;
        const handleY = panelY + fixedSectionHeight + operableSectionHeight / 2;
        
        const handleCircle = new Konva.Circle({
            x: handleX,
            y: handleY,
            radius: 8,
            fill: 'transparent',
            stroke: '#FFFFFF',
            strokeWidth: 2,
            listening: false,
        });
        layer.add(handleCircle);
        
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
    const dimColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-dark').trim() || '#333';
    const DIM_EXTENSION = 20;
    const DIM_LINE_OFFSET = 15;
    
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
    
    // Annotations
    const formatEdge = edgeWork.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    const formatThickness = thickness.replace('mm', '') + 'mm';
    const annotationText = `Thickness: ${formatThickness}  |  Edge: ${formatEdge}`;
    
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
    const normalizedGlassType = normalizeGlassType(glassType);
    const normalizedFrameType = normalizeFrameType(frameType);
    const normalizedShape = normalizeShape(shape);

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

    // Draw glass shape based on preset shapes
    let glassShape;
    const centerX = offsetX + windowWidth / 2;
    const centerY = offsetY + windowHeight / 2;
    const minRadius = Math.min(windowWidth, windowHeight) / 2;

    // Corner radius (inches -> pixels), used for rectangle/square only
    const safeCornerRadiusIn = Math.max(0, parseFloat(cornerRadiusIn) || 0);
    const pxPerInX = widthIn > 0 ? (windowWidth / widthIn) : 0;
    const pxPerInY = heightIn > 0 ? (windowHeight / heightIn) : 0;
    const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);
    const cornerRadiusPx = Math.min(minRadius, safeCornerRadiusIn * (pxPerIn || 0));
    
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
    } else {
        // Rectangle (default)
        glassShape = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: windowWidth,
            height: windowHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            cornerRadius: cornerRadiusPx > 0 ? cornerRadiusPx : 0,
            listening: false,
        });
    }
    layer.add(glassShape);

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

    // Annotations - Reference format: "Thickness: 5mm" and "Edge: Polished"
    const formatEdge = edgeWork.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    const formatThickness = thickness.replace('mm', '') + 'mm'; // Ensure mm format
    
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
        'patterned': 'patterned'
    };
    return mapping[normalized] || 'clear';
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
        'none': 'frameless'
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
        'square': 'rectangle' // Square is just a rectangle with equal sides
    };
    return mapping[normalized] || 'rectangle';
}

// --- INITIAL RENDER & UPDATES ---
function renderCustomState() {
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
    
    // 1. Draw the visual representation
    // Pass original values and units for labels, but use converted inches for visual size
    renderWindow(
        widthIn, // Converted to inches for visual size
        heightIn, // Converted to inches for visual size
        widthUnit, // Width unit for width label
        currentShape,
        currentGlassType,
        currentThickness,
        currentEdgeWork,
        currentFrameType,
        currentDimensions.width.value, // Original width value for label
        currentDimensions.height.value, // Original height value for label
        heightUnit, // Height unit for height label
        currentCornerRadius // Corner radius in inches
    );

    // 2. NEW: Update the estimated price immediately
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
        '5mm',      // Force Standard Thickness
        'flat-polish', // Force Standard Edge
        'vinyl'     // Force Standard Frame
    );
}

// Export for use in dynamic_customization.js
window.renderStandardState = renderStandardState;
window.renderWindow = renderWindow;
window.renderCustomState = renderCustomState;
window.renderMultiPanelProduct = renderMultiPanelProduct;
window.shouldUseMultiPanelRendering = shouldUseMultiPanelRendering;
window.extractPanelCount = extractPanelCount;
window.normalizeGlassType = normalizeGlassType;
window.normalizeFrameType = normalizeFrameType;
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
window.dimensionsLocked = dimensionsLocked;

// Initialize pricing database on load
window.onload = function() {
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
    updateBreadcrumbs(currentStep);

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
    resetBreadcrumbsToStandard();

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
if (inputHeight && btnUnitHeight) {
    inputHeight.addEventListener('input', (e) => {
        updateDimensions('height', e.target.value, btnUnitHeight.dataset.currentUnit);
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
    btn.addEventListener('click', (e) => { e.stopPropagation(); document.querySelectorAll('.unit-dropdown').forEach(d => d !== dropdown && d.classList.add('hidden-step')); dropdown.classList.toggle('hidden-step'); });
    dropdown.querySelectorAll('.unit-option').forEach(opt => {
        opt.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetUnit = opt.dataset.value;
            const currentUnit = btn.dataset.currentUnit;
            btn.innerHTML = `${unitMap[targetUnit].name} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
            const val = parseFloat(input.value);
            if (!isNaN(val)) { input.value = Math.round((val * unitMap[currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
            btn.dataset.currentUnit = targetUnit;
            const otherType = dimensionType === 'height' ? 'width' : 'height';
            const otherBtn = document.getElementById(`btn-unit-${otherType}`);
            const otherInput = document.getElementById(`input-${otherType}`);
            if (otherBtn.dataset.currentUnit !== targetUnit) {
                const otherVal = parseFloat(otherInput.value);
                if (!isNaN(otherVal)) { otherInput.value = Math.round((otherVal * unitMap[otherBtn.dataset.currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
                otherBtn.dataset.currentUnit = targetUnit;
                otherBtn.innerHTML = `${unitMap[targetUnit].name} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
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
    // Hide all steps first
    [step1, step2, step3].forEach(s => s.classList.add('hidden-step'));

    // Show the target step
    if (targetStep === 1) step1.classList.remove('hidden-step');
    if (targetStep === 2) step2.classList.remove('hidden-step');
    if (targetStep === 3) step3.classList.remove('hidden-step');

    // Update UI
    updateActionArea(targetStep);
    updateBreadcrumbs(targetStep);

    // Update currentStep AFTER UI
    currentStep = targetStep;
}


function updateActionArea(step) {
    if (step === 1) { backGroup.classList.add('hidden-step'); nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`; nextNote.innerText = 'Glass Type & Thickness'; backNote.innerText = ''; }
    if (step === 2) { backGroup.classList.remove('hidden-step'); nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`; backNote.innerText = 'Glass Shape'; nextNote.innerText = 'Edge Work & Frame Type'; }
    if (step === 3) { backGroup.classList.remove('hidden-step'); nextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`; backNote.innerText = 'Type & Thickness'; nextNote.innerText = ''; }
}

function updateBreadcrumbs(step) {
    crumbMain.innerText = 'Glass Shape'; crumbMain.classList.add('active');
    removeCrumb('crumb-step2'); removeCrumb('crumb-step3');
    if (step >= 2) { crumbMain.classList.remove('active'); addBreadcrumb('Type & Thickness', 'crumb-step2', step === 2); }
    if (step === 3) { document.getElementById('crumb-step2')?.classList.remove('active'); addBreadcrumb('Edge Work & Frame', 'crumb-step3', true); }
}

function resetBreadcrumbsToStandard() {
    crumbMain.innerText = 'Standard';
    crumbMain.classList.add('active');
    removeCrumb('crumb-step2');
    removeCrumb('crumb-step3');
    currentStep = 1; // Reset currentStep for Standard Mode
}


function addBreadcrumb(text, id, isActive) {
    if (document.getElementById(id)) return;
    const newChevron = document.createElement('span'); newChevron.className = 'chevron-right'; newChevron.id = 'chevron-' + id;
    const newCrumb = document.createElement('span'); newCrumb.className = isActive ? 'active' : ''; newCrumb.id = id; newCrumb.innerText = text;
    breadcrumbsContainer.appendChild(newChevron); breadcrumbsContainer.appendChild(newCrumb);
}

function removeCrumb(id) {
    document.getElementById(id)?.remove();
    document.getElementById('chevron-' + id)?.remove();
}

// Modal & File Logic
function closeUploadModal() { uploadModal.classList.add('hidden-step'); }
openModalBtn.addEventListener('click', () => { uploadModal.classList.remove('hidden-step'); });
modalCloseBtn.addEventListener('click', closeUploadModal);
modalCancelBtn.addEventListener('click', closeUploadModal);
modalDoneBtn.addEventListener('click', closeUploadModal);

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
            id: Date.now() + Math.random(), name: file.name, size: file.size, progress: 0,
            status: 'uploading', isError: file.size > MAX_FILE_SIZE_MB * 1024 * 1024, extension: fileExtension
        };
        uploadedFiles.push(newFile);
        renderFileItem(newFile);
        if (newFile.isError) { newFile.status = 'error'; updateFileItem(newFile); }
        else { simulateUpload(newFile); }
    });
}

function simulateUpload(file) {
    let progress = 0;
    const uploadTimer = setInterval(() => {
        progress += 2;
        if (progress >= 100) { clearInterval(uploadTimer); file.progress = 100; file.status = 'completed'; }
        else { file.progress = progress; }
        updateFileItem(file);
    }, 30);
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
    if (uploadedFiles.length === 0) uploadedFilesContainer.innerHTML = '<p class="placeholder-text">No files uploaded yet.</p>';
}

// --- PRICING LOGIC (Philippines Context) ---
// Price per square inch based on product base price
// Initialize pricingDatabase early to avoid hoisting issues
let pricingDatabase = null;

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
    
    // Also try to get from active option card in DOM
    const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
    if (fieldContainer) {
        const activeCard = fieldContainer.querySelector('.option-card.active');
        if (activeCard && activeCard.dataset.value) {
            return activeCard.dataset.value;
        }
    }
    
    return legacyMappings[fieldId] || null;
}

// Store calculated price breakdown
let priceBreakdown = {
    baseArea: 0,
    fieldPrices: {}, // Store prices for each field { fieldId: { option: price, label: "..." } }
    total: 0
};

function calculateTotal() {
    // Initialize pricing database if not already done
    if (!pricingDatabase) {
        initializePricingDatabase();
    }
    
    // Safety check - if still null, use defaults
    if (!pricingDatabase) {
        console.warn('Pricing database not initialized, using defaults');
        return pricingDatabase?.minimumPrice || 1500;
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
    if (product && product.tagPrices) {
        // Iterate through all fields that have tag prices in database
        for (const fieldId in product.tagPrices) {
            const selectedValue = getSelectedValueForField(fieldId);
            if (selectedValue) {
                const optionPrice = getPriceFromDatabase(fieldId, selectedValue);
                // Include all prices (including 0 and negative values)
                totalFieldPrices += optionPrice;
                priceBreakdown.fieldPrices[fieldId] = {
                    option: selectedValue,
                    price: optionPrice
                };
            }
        }
    }
    
    // Also check all active option-cards in the DOM to ensure we catch any selections
    // This ensures we get selections even if selectedCustomizationValues isn't updated
    const allFieldContainers = document.querySelectorAll('[data-field-id]');
    allFieldContainers.forEach(container => {
        const fieldId = container.dataset.fieldId;
        const activeCard = container.querySelector('.option-card.active');
        if (activeCard && activeCard.dataset.value) {
            const optionValue = activeCard.dataset.value;
            // Check if we already processed this field
            if (!priceBreakdown.fieldPrices[fieldId]) {
                const optionPrice = getPriceFromDatabase(fieldId, optionValue);
                totalFieldPrices += optionPrice;
                priceBreakdown.fieldPrices[fieldId] = {
                    option: optionValue,
                    price: optionPrice
                };
                console.log(`Added price for ${fieldId}.${optionValue}: ${optionPrice}`);
            } else {
                // Update if the option changed
                const currentOption = priceBreakdown.fieldPrices[fieldId].option;
                if (currentOption !== optionValue) {
                    // Remove old price
                    totalFieldPrices -= priceBreakdown.fieldPrices[fieldId].price;
                    // Add new price
                    const optionPrice = getPriceFromDatabase(fieldId, optionValue);
                    totalFieldPrices += optionPrice;
                    priceBreakdown.fieldPrices[fieldId] = {
                        option: optionValue,
                        price: optionPrice
                    };
                    console.log(`Updated price for ${fieldId}: ${currentOption} -> ${optionValue}, price: ${optionPrice}`);
                }
            }
        }
    });
    
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
    // Initialize pricing database if needed
    if (!pricingDatabase) {
        initializePricingDatabase();
    }
    
    // 1. Calculate total (also updates priceBreakdown)
    const total = calculateTotal();

    // 2. Update main price display
    const priceValue = document.getElementById('total-price');
    if (priceValue) {
        priceValue.textContent = formatPrice(total);
    }

    // 3. Update breakdown details
    updatePriceBreakdown();
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
    
    // Update base area cost
    const costArea = document.getElementById('cost-area');
    if (costArea) costArea.textContent = formatPrice(priceBreakdown.baseArea);

    // Field ID to HTML element mapping (for legacy fields and common dynamic fields)
    const fieldMappings = {
        'shape': { labelId: 'label-shape', costId: 'cost-shape', displayName: 'Shape' },
        'glassType': { labelId: 'label-type', costId: 'cost-type', displayName: 'Glass Type' },
        'thickness': { labelId: 'label-thickness', costId: 'cost-thickness', displayName: 'Thickness' },
        'frameType': { labelId: 'label-frame', costId: 'cost-frame', displayName: 'Frame' },
        'edgeWork': { labelId: 'label-edge', costId: 'cost-edge', displayName: 'Edge Work' },
        'frameColor': { labelId: 'label-frame', costId: 'cost-frame', displayName: 'Frame' },
        'edgeFinish': { labelId: 'label-edge', costId: 'cost-edge', displayName: 'Edge Work' },
        'mountingMethod': { labelId: 'label-edge', costId: 'cost-edge', displayName: 'Mounting Method' } // Fallback mapping
    };

    // Update each field that has a price from database
    for (const fieldId in priceBreakdown.fieldPrices) {
        const fieldData = priceBreakdown.fieldPrices[fieldId];
        const mapping = fieldMappings[fieldId];
        
        if (mapping) {
            // Update legacy fields with specific HTML IDs
            const labelEl = document.getElementById(mapping.labelId);
            const costEl = document.getElementById(mapping.costId);
            
            if (labelEl) {
                // Capitalize first letter of option name, handle camelCase
                let optionName = fieldData.option;
                // Convert camelCase to Title Case (e.g., "flatPolish" -> "Flat Polish")
                optionName = optionName.replace(/([A-Z])/g, ' $1').trim();
                optionName = optionName.charAt(0).toUpperCase() + optionName.slice(1);
                labelEl.textContent = optionName;
            }
            
            if (costEl) {
                if (fieldData.price > 0) {
                    costEl.textContent = '+' + formatPrice(fieldData.price);
                } else if (fieldData.price < 0) {
                    costEl.textContent = formatPrice(fieldData.price); // Negative prices show as-is
                } else {
                    // Price is 0 - determine appropriate text based on field type
                    if (fieldId === 'glassType' || fieldId === 'thickness') {
                        costEl.textContent = 'Standard';
                    } else {
                        costEl.textContent = 'Included';
                    }
                }
            }
        } else {
            // This is a dynamic field not in legacy mapping - try to find it in DOM
            const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
            if (fieldContainer) {
                // For dynamic fields, we might need to update a custom breakdown row
                // For now, log it for debugging
                console.log(`Dynamic field ${fieldId} with option ${fieldData.option} has price:`, fieldData.price);
            }
        }
    }
    
    // Helper function to get display name for a field
    function getFieldDisplayName(fieldId) {
        // Try to get from active option card
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
        return fieldMappings[fieldId]?.displayName || fieldId;
    }
    
    // Update fields that might not be in the legacy mapping
    for (const fieldId in priceBreakdown.fieldPrices) {
        if (!fieldMappings[fieldId]) {
            // This is a dynamic field - we might need to create or update a breakdown row
            // For now, we'll log it - in the future, we could dynamically add rows
            console.log(`Dynamic field ${fieldId} has price:`, priceBreakdown.fieldPrices[fieldId]);
        }
    }
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

// Store the design image data globally for cart submission
let currentDesignImageData = null;

// --- SUMMARY VIEW LOGIC ---

function showOrderSummary() {
    // 1. Hide Builder UI
    customWrapper.classList.add('hidden-step');
    standardWrapper.classList.add('hidden-step');
    priceBox.classList.add('hidden-step');
    document.querySelector('.build-toggle').classList.add('hidden-step');
    document.getElementById('standard-subtitle').classList.add('hidden-step');

    // --- Hide Related Products only (Keep Testimonials visible) ---
    document.getElementById('related-products-section').classList.add('hidden-step');
    // Keep testimonials visible - don't hide them

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

    // 4. Update Summary Data with price breakdown
    const shapeData = pricingDatabase.prices.shapes[currentShape];
    const typeData = pricingDatabase.prices.glassTypes[currentGlassType];
    const thickData = pricingDatabase.prices.thickness[currentThickness];
    const frameData = pricingDatabase.prices.frames[currentFrameType];
    const edgeData = pricingDatabase.prices.edges[currentEdgeWork];

    // Shape
    document.getElementById('sum-shape').textContent = shapeData.label;
    const sumShapePrice = document.getElementById('sum-shape-price');
    if (sumShapePrice) {
        sumShapePrice.textContent = priceBreakdown.shapeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.shapeAddon) 
            : shapeData.desc;
    }

    // Dimensions
    document.getElementById('sum-dim').textContent =
        `${currentDimensions.width.value}${currentDimensions.width.unit} × ${currentDimensions.height.value}${currentDimensions.height.unit}`;
    const sumDimPrice = document.getElementById('sum-dim-price');
    if (sumDimPrice) {
        sumDimPrice.textContent = 'Base: ' + formatPrice(priceBreakdown.baseArea);
    }

    // Glass Type
    document.getElementById('sum-type').textContent = typeData.label;
    const sumTypePrice = document.getElementById('sum-type-price');
    if (sumTypePrice) {
        sumTypePrice.textContent = priceBreakdown.typeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.typeAddon)
            : typeData.desc;
    }

    // Thickness
    document.getElementById('sum-thick').textContent = thickData.label;
    const sumThickPrice = document.getElementById('sum-thick-price');
    if (sumThickPrice) {
        if (priceBreakdown.thicknessAddon !== 0) {
            sumThickPrice.textContent = (priceBreakdown.thicknessAddon > 0 ? '+' : '') + formatPrice(priceBreakdown.thicknessAddon);
        } else {
            sumThickPrice.textContent = thickData.desc;
        }
    }

    // Edge Work
    document.getElementById('sum-edge').textContent = edgeData.label;
    const sumEdgePrice = document.getElementById('sum-edge-price');
    if (sumEdgePrice) {
        sumEdgePrice.textContent = priceBreakdown.edgeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.edgeAddon)
            : edgeData.desc;
    }

    // Frame Type
    document.getElementById('sum-frame').textContent = frameData.label;
    const sumFramePrice = document.getElementById('sum-frame-price');
    if (sumFramePrice) {
        sumFramePrice.textContent = priceBreakdown.frameAddon > 0 
            ? '+' + formatPrice(priceBreakdown.frameAddon)
            : frameData.desc;
    }

    // Engraving - Check both custom step-3 and standard wrapper
    let engravingInput = document.querySelector('#step-3 .engraving-section input');
    if (!engravingInput || !engravingInput.value) {
        engravingInput = document.querySelector('#standard-wrapper .engraving-section input');
    }
    const engravingText = engravingInput ? engravingInput.value : '';
    document.getElementById('sum-engrave').textContent = engravingText || 'None';

    // 5. Update Total Price
    const totalPrice = calculateTotal();
    document.getElementById('sum-total').textContent = formatPrice(totalPrice);

    // 6. Update Breadcrumbs
    crumbMain.innerText = 'Review Order';
    crumbMain.classList.add('active');
    removeCrumb('crumb-step2');
    removeCrumb('crumb-step3');
}

function editConfiguration() {
    // Hide Summary
    document.getElementById('summary-wrapper').classList.add('hidden-step');

    // --- Show Related Products again (Testimonials should already be visible) ---
    document.getElementById('related-products-section').classList.remove('hidden-step');
    // Testimonials are always visible, no need to show/hide

    // Show Toggle and Subtitle
    document.querySelector('.build-toggle').classList.remove('hidden-step');

    // Determine which wrapper to show based on mode
    if (isStandardMode) {
        standardWrapper.classList.remove('hidden-step');
        document.getElementById('standard-subtitle').classList.remove('hidden-step');
    } else {
        customWrapper.classList.remove('hidden-step');
        priceBox.classList.remove('hidden-step');
        // Return to Step 3 to allow immediate editing
        goToStep(3);
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

// 2. Finalize Button (Standard Flow)
// FIND the onclick attribute in the HTML for the Standard finalize button
// OR add this listener (recommended to remove onclick="alert..." from HTML first)
const stdFinalizeBtn = document.querySelector('#standard-wrapper .next-btn');
if (stdFinalizeBtn) {
    stdFinalizeBtn.onclick = null; // Remove inline alert
    stdFinalizeBtn.addEventListener('click', () => {
        console.log("Finalizing Standard Order...");
        showOrderSummary();
        logOrderSummary();
    });
}

// 3. Edit Order Button
document.getElementById('edit-order-btn').addEventListener('click', editConfiguration);


// --- 2D PREVIEW MODAL LOGIC ---
// (This handles the pop-up when clicking "2D Preview")
const previewLabel = document.querySelector('.preview-label');
const previewModal = document.getElementById('preview-modal');
const zoomedImg = document.getElementById('zoomed-preview-img');
const previewCloseBtn = document.getElementById('preview-close-btn');
const downloadDesignBtn = document.getElementById('download-design-btn');

// Check if elements exist to avoid errors
if (previewLabel && previewModal && zoomedImg) {
    // Open Modal
    previewLabel.addEventListener('click', () => {
        // Generate a high-quality image from the Konva Stage
        const dataUrl = getKonvaImageData(3);
        zoomedImg.src = dataUrl;
        previewModal.classList.remove('hidden-step');
    });

    // Close Modal (Click Outside)
    previewModal.addEventListener('click', (e) => {
        if (e.target === previewModal) {
            previewModal.classList.add('hidden-step');
        }
    });

    // Close button
    if (previewCloseBtn) {
        previewCloseBtn.addEventListener('click', () => {
            previewModal.classList.add('hidden-step');
        });
    }

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
window.getDesignImageData = function() {
    return currentDesignImageData || getKonvaImageData(3);
};

// Get current customization state for cart
window.getCustomizationState = function() {
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



