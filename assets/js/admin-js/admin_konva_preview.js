// =====================================================
// ADMIN KONVA.JS PREVIEW
// Shows admin what customers will see in real-time
// Syncs with customization fields and standard sizes
// =====================================================

let adminKonvaStage = null;
let adminKonvaLayer = null;
let adminPreviewInitialized = false;

// Default preview state
let adminPreviewState = {
    shape: 'rectangle',
    glassType: 'tempered',
    thickness: '5mm',
    edgeWork: 'flat-polish',
    frameType: 'vinyl',
    cornerRadius: 0, // inches (rectangle/square only)
    dimensions: {
        height: { value: 45, unit: 'in' },
        width: { value: 35, unit: 'in' }
    }
};

// Visual configuration (synced with customization_fields_presets_summary.md)
const glassStyles = {
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

// Synced with customization_fields_presets_summary.md
const frameStyles = {
    // Preset frame colors/materials
    'white': { color: '#FFFFFF', width: 4 },
    'black': { color: '#000000', width: 4 },
    'silver': { color: '#C0C0C0', width: 3 },
    'bronze': { color: '#CD7F32', width: 3 },
    'wood': { color: '#795548', width: 6 },
    'aluminum': { color: '#90A4AE', width: 3 },
    // Legacy frame types
    'vinyl': { color: '#333333', width: 4 }
};

/**
 * Reset Konva preview to show placeholder message
 */
function resetAdminKonvaPreview() {
    const container = document.getElementById('admin-konva-preview-container');
    if (!container) return;
    
    // Destroy existing Konva stage if it exists
    if (adminKonvaStage) {
        adminKonvaStage.destroy();
        adminKonvaStage = null;
        adminKonvaLayer = null;
        adminPreviewInitialized = false;
    }
    
    // Show placeholder message
    container.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">Select category and subcategory to see preview</p>';
}

/**
 * Initialize Konva preview for admin
 */
function initAdminKonvaPreview() {
    const container = document.getElementById('admin-konva-preview-container');
    if (!container) return;
    
    // Clear existing content
    container.innerHTML = '';
    
    const STAGE_SIZE = 400;
    const PADDING = 40;
    const DRAWING_SIZE = STAGE_SIZE - PADDING * 2;
    
    // Create stage
    adminKonvaStage = new Konva.Stage({
        container: 'admin-konva-preview-container',
        width: STAGE_SIZE,
        height: STAGE_SIZE,
    });
    
    adminKonvaLayer = new Konva.Layer();
    adminKonvaStage.add(adminKonvaLayer);
    
    adminPreviewInitialized = true;
    
    // Initial render
    updateAdminPreview();
}

/**
 * Updates the admin preview based on current state
 */
function updateAdminPreview() {
    if (!adminPreviewInitialized || !adminKonvaLayer) {
        initAdminKonvaPreview();
        return;
    }
    
    adminKonvaLayer.destroyChildren();
    
    const { dimensions, shape, glassType, thickness, edgeWork, frameType, cornerRadius } = adminPreviewState;
    
    // Convert dimensions to inches for rendering
    let widthIn = dimensions.width.value;
    let heightIn = dimensions.height.value;
    
    if (dimensions.width.unit === 'cm') {
        widthIn = widthIn / 2.54;
    } else if (dimensions.width.unit === 'mm') {
        widthIn = widthIn / 25.4;
    }
    
    if (dimensions.height.unit === 'cm') {
        heightIn = heightIn / 2.54;
    } else if (dimensions.height.unit === 'mm') {
        heightIn = heightIn / 25.4;
    }
    
    const STAGE_SIZE = 400;
    const PADDING = 40;
    const DRAWING_SIZE = STAGE_SIZE - PADDING * 2;
    
    // Calculate aspect ratio and scale
    const aspectRatio = widthIn / heightIn;
    let drawWidth, drawHeight;
    
    if (aspectRatio > 1) {
        drawWidth = DRAWING_SIZE;
        drawHeight = DRAWING_SIZE / aspectRatio;
    } else {
        drawHeight = DRAWING_SIZE;
        drawWidth = DRAWING_SIZE * aspectRatio;
    }
    
    const startX = (STAGE_SIZE - drawWidth) / 2;
    const startY = (STAGE_SIZE - drawHeight) / 2;
    
    // Normalize values (handle preset values)
    const normalizedGlassType = normalizeGlassType(glassType);
    const normalizedFrameType = normalizeFrameType(frameType);
    const normalizedShape = normalizeShape(shape);
    
    // Get glass style
    const glassStyle = glassStyles[normalizedGlassType] || glassStyles['clear'];
    const frameStyle = frameStyles[normalizedFrameType] || frameStyles['white'];
    
    // Corner radius (inches -> pixels), used for rectangle/square only
    const safeCornerRadiusIn = Math.max(0, parseFloat(cornerRadius) || 0);
    const pxPerInX = widthIn > 0 ? (drawWidth / widthIn) : 0;
    const pxPerInY = heightIn > 0 ? (drawHeight / heightIn) : 0;
    const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);
    const cornerRadiusPx = Math.min(minRadius, safeCornerRadiusIn * (pxPerIn || 0));

    // Draw glass shape based on preset shapes
    let glassShape;
    const centerX = startX + drawWidth / 2;
    const centerY = startY + drawHeight / 2;
    const minRadius = Math.min(drawWidth, drawHeight) / 2;
    
    if (normalizedShape === 'round' || normalizedShape === 'circle') {
        // Circle
        glassShape = new Konva.Circle({
            x: centerX,
            y: centerY,
            radius: minRadius,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width
        });
    } else if (normalizedShape === 'oval' || normalizedShape === 'ellipse') {
        // Ellipse
        glassShape = new Konva.Ellipse({
            x: centerX,
            y: centerY,
            radiusX: drawWidth / 2,
            radiusY: drawHeight / 2,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width
        });
    } else if (normalizedShape === 'triangle') {
        // Triangle - 3-sided polygon
        const points = [
            centerX, startY,                    // Top point
            startX, startY + drawHeight,        // Bottom left
            startX + drawWidth, startY + drawHeight // Bottom right
        ];
        glassShape = new Konva.Line({
            points: points,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            closed: true,
            listening: false
        });
    } else if (normalizedShape === 'pentagon') {
        // Pentagon - 5-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 5,
            radius: minRadius,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            listening: false
        });
    } else if (normalizedShape === 'hexagon') {
        // Hexagon - 6-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 6,
            radius: minRadius,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            listening: false
        });
    } else if (normalizedShape === 'octagon') {
        // Octagon - 8-sided regular polygon
        glassShape = new Konva.RegularPolygon({
            x: centerX,
            y: centerY,
            sides: 8,
            radius: minRadius,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            listening: false
        });
    } else if (normalizedShape === 'star') {
        // Star - 5-pointed star
        glassShape = new Konva.Star({
            x: centerX,
            y: centerY,
            numPoints: 5,
            innerRadius: minRadius * 0.5,
            outerRadius: minRadius,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            listening: false
        });
    } else if (normalizedShape === 'diamond') {
        // Diamond - 4-sided polygon rotated 45 degrees
        const points = [
            centerX, startY,                    // Top
            startX + drawWidth, centerY,        // Right
            centerX, startY + drawHeight,        // Bottom
            startX, centerY                    // Left
        ];
        glassShape = new Konva.Line({
            points: points,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            closed: true,
            listening: false
        });
    } else {
        // Rectangle (default)
        glassShape = new Konva.Rect({
            x: startX,
            y: startY,
            width: drawWidth,
            height: drawHeight,
            fill: glassStyle.fill,
            opacity: glassStyle.opacity,
            stroke: frameStyle.color,
            strokeWidth: frameStyle.width,
            cornerRadius: cornerRadiusPx > 0 ? cornerRadiusPx : 0
        });
    }
    
    adminKonvaLayer.add(glassShape);
    
    // Draw Interior Panels for rectangular shapes (Reference: 3 columns x 2 rows)
    if (normalizedShape === 'rectangle' || normalizedShape === 'rectangular') {
        const paneWidth = drawWidth / 3;
        const paneHeight = drawHeight / 2;
        const paneStrokeWidth = Math.max(1, frameStyle.width - 2);

        // Draw vertical dividers (2 lines for 3 columns)
        for (let i = 1; i < 3; i++) {
            const dividerX = startX + paneWidth * i;
            adminKonvaLayer.add(new Konva.Line({
                points: [dividerX, startY, dividerX, startY + drawHeight],
                stroke: frameStyle.color,
                strokeWidth: paneStrokeWidth,
                listening: false,
            }));
        }

        // Draw horizontal divider (1 line for 2 rows)
        const dividerY = startY + paneHeight;
        adminKonvaLayer.add(new Konva.Line({
            points: [startX, dividerY, startX + drawWidth, dividerY],
            stroke: frameStyle.color,
            strokeWidth: paneStrokeWidth,
            listening: false,
        }));

        // Draw circular dots in bottom row (center of bottom-left and bottom-middle panes)
        adminKonvaLayer.add(new Konva.Circle({
            x: startX + paneWidth / 2,
            y: dividerY + paneHeight / 2,
            radius: 3,
            fill: frameStyle.color,
            listening: false,
        }));
        adminKonvaLayer.add(new Konva.Circle({
            x: startX + paneWidth * 1.5,
            y: dividerY + paneHeight / 2,
            radius: 3,
            fill: frameStyle.color,
            listening: false,
        }));
    }
    
    // Draw frame
    const frameRect = new Konva.Rect({
        x: startX - frameStyle.width,
        y: startY - frameStyle.width,
        width: drawWidth + (frameStyle.width * 2),
        height: drawHeight + (frameStyle.width * 2),
        fill: 'transparent',
        stroke: frameStyle.color,
        strokeWidth: frameStyle.width,
        cornerRadius: normalizedShape === 'rectangle' ? 0 : 5
    });
    adminKonvaLayer.add(frameRect);
    
    // Draw Dimensions (Reference style: extension lines with dashed dimension line and labels)
    const dimColor = '#333';
    const DIM_EXTENSION = 20; // Extension line length
    const DIM_LINE_OFFSET = 15; // Distance from glass panel to dimension line

    // Width Dimension (at top) - Reference: horizontal dashed line with width label
    // Left extension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX, startY, startX, startY - DIM_LINE_OFFSET - DIM_EXTENSION], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Right extension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX + drawWidth, startY, startX + drawWidth, startY - DIM_LINE_OFFSET - DIM_EXTENSION], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Horizontal dashed dimension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX, startY - DIM_LINE_OFFSET, startX + drawWidth, startY - DIM_LINE_OFFSET], 
        stroke: dimColor, 
        strokeWidth: 1.5, 
        dash: [5, 3],
        listening: false
    }));
    // Width label
    const widthText = `${widthIn.toFixed(1)}"`;
    adminKonvaLayer.add(new Konva.Text({
        x: startX + drawWidth / 2,
        y: startY - DIM_LINE_OFFSET - 8,
        text: widthText,
        fontSize: 11,
        fontFamily: 'Arial, sans-serif',
        fill: dimColor,
        align: 'center',
        offsetX: (widthText.length * 6) / 2,
        listening: false,
    }));

    // Height Dimension (on right side) - Reference: vertical dashed line with height label
    // Top extension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX + drawWidth, startY, startX + drawWidth + DIM_LINE_OFFSET + DIM_EXTENSION, startY], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Bottom extension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX + drawWidth, startY + drawHeight, startX + drawWidth + DIM_LINE_OFFSET + DIM_EXTENSION, startY + drawHeight], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Vertical dashed dimension line
    adminKonvaLayer.add(new Konva.Line({ 
        points: [startX + drawWidth + DIM_LINE_OFFSET, startY, startX + drawWidth + DIM_LINE_OFFSET, startY + drawHeight], 
        stroke: dimColor, 
        strokeWidth: 1.5, 
        dash: [5, 3],
        listening: false
    }));
    // Height label (rotated)
    const heightText = `${heightIn.toFixed(1)}"`;
    adminKonvaLayer.add(new Konva.Text({
        x: startX + drawWidth + DIM_LINE_OFFSET + 8,
        y: startY + drawHeight / 2,
        text: heightText,
        fontSize: 11,
        fontFamily: 'Arial, sans-serif',
        fill: dimColor,
        align: 'center',
        rotation: 90,
        offsetX: (heightText.length * 6) / 2,
        listening: false,
    }));
    
    // Draw type and thickness label (Reference format: "Thickness: 5mm")
    const formatThickness = thickness.replace(/mm+$/g, '') + 'mm'; // Ensure mm format
    const formatEdge = edgeWork ? edgeWork.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') : '';
    const typeLabel = new Konva.Text({
        x: startX + drawWidth / 2,
        y: startY - 25,
        text: `${glassType.charAt(0).toUpperCase() + glassType.slice(1)} - Thickness: ${formatThickness}`,
        fontSize: 12,
        fontFamily: 'Arial',
        fill: '#666',
        align: 'center',
        offsetX: 80
    });
    adminKonvaLayer.add(typeLabel);
    
    // Draw edge work label below dimensions if available
    if (formatEdge) {
        const edgeLabel = new Konva.Text({
            x: startX + drawWidth / 2,
            y: startY + drawHeight + 40,
            text: `Edge: ${formatEdge}`,
            fontSize: 11,
            fontFamily: 'Arial',
            fill: '#666',
            align: 'center',
            offsetX: 40
        });
        adminKonvaLayer.add(edgeLabel);
    }
    
    adminKonvaLayer.draw();
}

/**
 * Syncs customization fields with preview
 */
function syncAdminPreviewWithFields() {
    const container = document.getElementById('customizationFields');
    if (!container) return;
    
    // Get all field containers
    const fieldContainers = container.querySelectorAll('[data-field-id]');
    
    fieldContainers.forEach(fieldContainer => {
        const fieldId = fieldContainer.dataset.fieldId;
        
        // Handle tag fields (admin uses .tag.selected, not .option-card.active)
        const selectedTags = fieldContainer.querySelectorAll('.tag.selected');
        if (selectedTags.length > 0) {
            const selectedValue = selectedTags[0].dataset.value?.toLowerCase() || selectedTags[0].textContent.trim().toLowerCase();
            
            // Map common field IDs to preview state
            if (fieldId === 'glassType' || fieldId === 'glass' || fieldId.includes('glass')) {
                adminPreviewState.glassType = mapGlassType(selectedValue);
            } else if (fieldId === 'frameColor' || fieldId === 'frameType' || fieldId === 'frame' || fieldId.includes('frame')) {
                adminPreviewState.frameType = mapFrameType(selectedValue);
            } else if (fieldId === 'shape' || fieldId.includes('shape')) {
                adminPreviewState.shape = mapShape(selectedValue);
            } else if (fieldId === 'edgeFinish' || fieldId === 'edge' || fieldId.includes('edge')) {
                adminPreviewState.edgeWork = mapEdgeWork(selectedValue);
            }
        }
        
        // Handle number fields
        const numberInput = fieldContainer.querySelector('input[type="number"]');
        if (numberInput) {
            if (fieldId === 'thickness' || fieldId.includes('thickness')) {
                const thicknessValue = parseFloat(numberInput.value) || 5;
                adminPreviewState.thickness = `${thicknessValue}mm`;
            } else if (fieldId === 'cornerRadius' || fieldId === 'radius' || fieldId.toLowerCase().includes('radius')) {
                // Corner radius in inches (admin preview only)
                const radiusValue = parseFloat(numberInput.value) || 0;
                adminPreviewState.cornerRadius = radiusValue;
            } else if (fieldId === 'size' || fieldId.includes('size') || fieldId.includes('width') || fieldId.includes('height')) {
                // Handle dimension fields if they exist
                const value = parseFloat(numberInput.value);
                if (fieldId.includes('width')) {
                    adminPreviewState.dimensions.width.value = value || 35;
                } else if (fieldId.includes('height')) {
                    adminPreviewState.dimensions.height.value = value || 45;
                }
            }
        }
        
        // Handle checkbox fields
        const checkbox = fieldContainer.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.checked) {
            // Checkboxes might affect preview in the future
        }
    });
    
    // Update preview
    updateAdminPreview();
}

/**
 * Maps glass type values to preview state
 * Synced with customization_fields_presets_summary.md
 */
function mapGlassType(value) {
    if (!value) return 'clear';
    const normalized = value.toLowerCase().replace(/\s+/g, '-');
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
 * Maps frame type/color values to preview state
 * Synced with customization_fields_presets_summary.md
 */
function mapFrameType(value) {
    if (!value) return 'white';
    const normalized = value.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'white': 'white',
        'black': 'black',
        'silver': 'silver',
        'bronze': 'bronze',
        'wood': 'wood',
        'aluminum': 'aluminum',
        'vinyl': 'vinyl' // Legacy support
    };
    return mapping[normalized] || 'white';
}

/**
 * Maps shape values to preview state
 * Synced with customization_fields_presets_summary.md
 */
function mapShape(value) {
    if (!value) return 'rectangle';
    const normalized = value.toLowerCase().replace(/\s+/g, '-');
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

/**
 * Maps edge work values to preview state
 * Synced with customization_fields_presets_summary.md
 */
function mapEdgeWork(value) {
    if (!value) return 'flat-polish';
    const normalized = value.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'flat-polish': 'flat-polish',
        'flat-polish': 'flat-polish',
        'beveled': 'beveled',
        'polished': 'polished',
        'raw': 'raw'
    };
    return mapping[normalized] || 'flat-polish';
}

/**
 * Normalize glass type values from presets
 */
function normalizeGlassType(glassType) {
    return mapGlassType(glassType);
}

/**
 * Normalize frame type/color values from presets
 */
function normalizeFrameType(frameType) {
    return mapFrameType(frameType);
}

/**
 * Normalize shape values from presets
 */
function normalizeShape(shape) {
    return mapShape(shape);
}

/**
 * Syncs standard size selection with preview
 */
function syncAdminPreviewWithStandardSize(seriesId, measurement) {
    if (!measurement) return;
    
    // Update dimensions from standard size
    adminPreviewState.dimensions = {
        width: { value: measurement.width, unit: 'cm' },
        height: { value: measurement.height, unit: 'cm' }
    };
    
    updateAdminPreview();
}

/**
 * Sets up event listeners for field changes
 */
function setupAdminPreviewListeners() {
    const container = document.getElementById('customizationFields');
    if (!container) return;
    
    // Use event delegation for dynamically added fields
    // Admin uses .tag.selected, not .option-card.active
    container.addEventListener('click', (e) => {
        if (e.target.closest('.tag') || e.target.closest('.option-card')) {
            setTimeout(syncAdminPreviewWithFields, 150);
        }
    });
    
    container.addEventListener('change', (e) => {
        if (e.target.type === 'number' || e.target.type === 'checkbox') {
            setTimeout(syncAdminPreviewWithFields, 150);
        }
    });
    
    container.addEventListener('input', (e) => {
        if (e.target.type === 'number') {
            setTimeout(syncAdminPreviewWithFields, 150);
        }
    });
    
    // Also listen for tag selection changes via MutationObserver
    const observer = new MutationObserver(() => {
        setTimeout(syncAdminPreviewWithFields, 200);
    });
    
    observer.observe(container, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class']
    });
}

/**
 * Initialize preview when popup opens
 */
function initAdminPreviewOnPopupOpen() {
    // Wait a bit for DOM to be ready
    setTimeout(() => {
        initAdminKonvaPreview();
        setupAdminPreviewListeners();
        
        // Sync with existing fields if any
        syncAdminPreviewWithFields();
    }, 300);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Listen for popup open
        const addBtn = document.querySelector('.add-product-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => {
                setTimeout(initAdminPreviewOnPopupOpen, 500);
            });
        }
        
        // Also listen for category/subcategory changes
        const categorySelect = document.getElementById('productCategory');
        const subcategorySelect = document.getElementById('productSubcategory');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', () => {
                setTimeout(initAdminPreviewOnPopupOpen, 500);
            });
        }
        
        if (subcategorySelect) {
            subcategorySelect.addEventListener('change', () => {
                setTimeout(initAdminPreviewOnPopupOpen, 500);
            });
        }
    });
} else {
    // DOM already loaded
    const addBtn = document.querySelector('.add-product-btn');
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            setTimeout(initAdminPreviewOnPopupOpen, 500);
        });
    }
}

/**
 * Sets up listeners for standard size clicks
 */
function setupStandardSizePreviewListeners() {
    const container = document.getElementById('standardSeriesContainer');
    if (!container) return;
    
    // Use event delegation for dynamically added standard sizes
    container.addEventListener('click', (e) => {
        const sizeCard = e.target.closest('.size-card, .measurement-item');
        if (sizeCard) {
            // Extract measurement data from the card
            const dimensions = sizeCard.querySelector('.measurement-dimensions');
            if (dimensions) {
                const text = dimensions.textContent;
                // Parse "80cm × 100cm" format
                const match = text.match(/([\d.]+)cm\s*×\s*([\d.]+)cm/);
                if (match) {
                    const width = parseFloat(match[1]);
                    const height = parseFloat(match[2]);
                    
                    adminPreviewState.dimensions = {
                        width: { value: width, unit: 'cm' },
                        height: { value: height, unit: 'cm' }
                    };
                    
                    updateAdminPreview();
                }
            }
        }
    });
}

// Export functions for use in products.js
window.initAdminKonvaPreview = initAdminKonvaPreview;
window.resetAdminKonvaPreview = resetAdminKonvaPreview;
window.updateAdminPreview = updateAdminPreview;
window.syncAdminPreviewWithFields = syncAdminPreviewWithFields;
window.syncAdminPreviewWithStandardSize = syncAdminPreviewWithStandardSize;
window.setupStandardSizePreviewListeners = setupStandardSizePreviewListeners;
