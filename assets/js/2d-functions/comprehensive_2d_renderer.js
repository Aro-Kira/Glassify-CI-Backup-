/**
 * Comprehensive 2D Konva.js Renderer for All Product Types
 * 
 * This module provides 2D visualization for all customization options
 * defined in default-customization-fields.json
 * 
 * Supports:
 * - Windows: Sliding, Awning, Casement, Fixed Glass
 * - Doors: Sliding, Swing Door, Bi-fold Door, Frameless, Patch Fitting
 * - Partitions: Frameless Glass, Shower Enclosure, Fixed Glass
 * - Specialty: Mirrors, Top Glass, Glass Board
 * - Commercial: Storefront, Glass Balcony, Stair Railings
 */

// Import required styles and configurations
// These should be available from the main 2d_customization.js file
// We'll access them from window scope at render time to avoid conflicts

// Function to get current values (will be called at render time)
function getGlobalStyles() {
    return {
        glassStyles: window.glassStyles || (typeof glassStyles !== 'undefined' ? glassStyles : {}),
        frameStyles: window.frameStyles || (typeof frameStyles !== 'undefined' ? frameStyles : {}),
        STAGE_SIZE: window.STAGE_SIZE || (typeof STAGE_SIZE !== 'undefined' ? STAGE_SIZE : 500),
        DRAWING_SIZE: window.DRAWING_SIZE || (typeof DRAWING_SIZE !== 'undefined' ? DRAWING_SIZE : 420),
        PADDING: window.PADDING || (typeof PADDING !== 'undefined' ? PADDING : 40)
    };
}

// Helper to get render context (from parameter or global)
function getRenderContext(renderContext) {
    return renderContext || getGlobalStyles();
}

/**
 * Main renderer function that routes to appropriate product-specific renderer
 * @param {Object} productData - Product information and customization values
 * @param {Object} dimensions - Width, height, units
 * @param {Object} layer - Konva layer to draw on
 */
function renderProduct2D(productData, dimensions, layer) {
    if (!layer) {
        console.error('[2D Renderer] No Konva layer provided');
        return;
    }

    // Get current global values at render time (use local variables to avoid conflicts)
    const globals = getGlobalStyles();
    // Store in a context object that will be passed to all render functions
    const renderContext = {
        glassStyles: globals.glassStyles,
        frameStyles: globals.frameStyles,
        STAGE_SIZE: globals.STAGE_SIZE,
        DRAWING_SIZE: globals.DRAWING_SIZE,
        PADDING: globals.PADDING
    };

    // Clear layer
    layer.destroyChildren();

    const productType = productData.productType || productData.type || '';
    const category = productData.category || '';
    const customizationValues = productData.customizationValues || {};

    // Route to appropriate renderer based on product type
    if (category.includes('Windows')) {
        if (productType.includes('Sliding')) {
            renderWindowsSliding(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Awning')) {
            renderWindowsAwning(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Casement')) {
            renderWindowsCasement(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Fixed Glass') || productType.includes('Fixed')) {
            renderWindowsFixedGlass(productData, dimensions, layer, renderContext);
        } else {
            renderGenericWindow(productData, dimensions, layer, renderContext);
        }
    } else if (category.includes('Doors')) {
        if (productType.includes('Sliding')) {
            renderDoorsSliding(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Swing')) {
            renderDoorsSwing(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Bi-fold')) {
            renderDoorsBifold(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Frameless')) {
            renderDoorsFrameless(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Patch Fitting') || productType.includes('Patch')) {
            renderDoorsPatchFitting(productData, dimensions, layer, renderContext);
        } else {
            renderGenericDoor(productData, dimensions, layer, renderContext);
        }
    } else if (category.includes('Partitions')) {
        if (productType.includes('Frameless Glass')) {
            renderPartitionsFramelessGlass(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Shower Enclosure') || productType.includes('Shower')) {
            renderPartitionsShowerEnclosure(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Fixed Glass') || productType.includes('Fixed')) {
            renderPartitionsFixedGlass(productData, dimensions, layer, renderContext);
        } else {
            renderGenericPartition(productData, dimensions, layer, renderContext);
        }
    } else if (category.includes('Specialty')) {
        if (productType.includes('Mirror')) {
            renderSpecialtyMirrors(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Top Glass')) {
            renderSpecialtyTopGlass(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Glass Board')) {
            renderSpecialtyGlassBoard(productData, dimensions, layer, renderContext);
        } else {
            renderGenericSpecialty(productData, dimensions, layer, renderContext);
        }
    } else if (category.includes('Commercial')) {
        if (productType.includes('Storefront')) {
            renderCommercialStorefront(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Balcony')) {
            renderCommercialGlassBalcony(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Stair') || productType.includes('Railing')) {
            renderCommercialStairRailings(productData, dimensions, layer, renderContext);
        } else {
            renderGenericCommercial(productData, dimensions, layer, renderContext);
        }
    } else {
        // Fallback to generic renderer
        renderGenericProduct(productData, dimensions, layer, renderContext);
    }
}

// ============================================================================
// WINDOWS RENDERERS
// ============================================================================

/**
 * Render Windows Sliding configuration
 */
function renderWindowsSliding(productData, dimensions, layer, renderContext) {
    // Extract context values
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = renderContext || getGlobalStyles();
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    // Extract customization values
    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || '2 Panels');
    const transomType = customizationValues.transomType || 'None';
    const trackSystem = customizationValues.trackSystem || '2 Tracks';
    const panelConfiguration = customizationValues.panelConfiguration || 'S | S (Sliding | Sliding)';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const glassThickness = customizationValues.glassThickness || '6mm';
    const lockType = customizationValues.lockType || 'Center Lok 904 Big';
    const rollerType = customizationValues.rollerType || 'Single Panel Roller';
    const screen = customizationValues.screen || 'Without Screen';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // Parse panel configuration
    const panelTypes = parsePanelConfiguration(panelConfiguration, numberOfPanels);
    const hasTransom = transomType && transomType.toLowerCase() !== 'none';
    const isFixedTransomHead = hasTransom && transomType.toLowerCase().includes('head');
    const isFixedTransomSill = hasTransom && transomType.toLowerCase().includes('sill');

    // Get original dimensions from productData or use current dimensions
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    const originalHeightUnit = heightUnit;
    
    // Handle transom - get h1 and h2 values from inputs if available
    let h1Value = null;
    let h1Unit = heightUnit;
    let h2Value = null;
    let h2Unit = heightUnit;
    
    // Check if h1 input exists and is visible
    const h1InputGroup = typeof document !== 'undefined' ? (document.getElementById('input-group-h1') || null) : null;
    const h1Input = typeof document !== 'undefined' ? (document.getElementById('input-h1') || null) : null;
    const h1UnitBtn = typeof document !== 'undefined' ? (document.getElementById('btn-unit-h1') || null) : null;
    
    // Check if h2 input exists and is visible
    const h2InputGroup = typeof document !== 'undefined' ? (document.getElementById('input-group-h2') || null) : null;
    const h2Input = typeof document !== 'undefined' ? (document.getElementById('input-h2') || null) : null;
    const h2UnitBtn = typeof document !== 'undefined' ? (document.getElementById('btn-unit-h2') || null) : null;
    
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
    
    // Convert to millimeters for calculation
    const unitMap = {
        'in': { toMm: 25.4 },
        'cm': { toMm: 10 },
        'mm': { toMm: 1 }
    };
    
    function convertToMm(value, unit) {
        const unitInfo = unitMap[unit.toLowerCase()] || unitMap['in'];
        return value * unitInfo.toMm;
    }
    
    let transomHeight = 0;
    let mainHeight = totalHeight;
    let transomY = 0;
    
    if (hasTransom) {
        const totalHeightInMm = convertToMm(originalHeight, originalHeightUnit);
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
            transomHeight = totalHeight * clampedTransomRatio;
            mainHeight = totalHeight * clampedSlidingRatio;
            transomY = offsetY;
        } else if (isFixedTransomSill) {
            // Fixed transom at bottom, sliding section at top
            transomHeight = totalHeight * clampedTransomRatio;
            mainHeight = totalHeight * clampedSlidingRatio;
            transomY = offsetY + mainHeight;
        }
    }

    const panelWidth = totalWidth / numberOfPanels;

    // Draw transom section if present
    if (hasTransom) {
        for (let i = 0; i < numberOfPanels; i++) {
            const panelX = offsetX + (i * panelWidth);
            
            // Transom is always fixed
            const transomRect = new Konva.Rect({
                x: panelX,
                y: transomY,
                width: panelWidth,
                height: transomHeight,
                fill: '#4A90E2',
                opacity: 0.8,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(transomRect);

            // Add "F" label
            const label = new Konva.Text({
                x: panelX + panelWidth / 2,
                y: transomY + transomHeight / 2,
                text: 'F',
                fontSize: Math.max(12, transomHeight / 10),
                fontFamily: 'Arial',
                fontStyle: 'bold',
                fill: '#FFFFFF',
                align: 'center',
                offsetX: 6,
                offsetY: 8,
                listening: false,
            });
            layer.add(label);

            // Panel divider
            if (i < numberOfPanels - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, transomY, panelX + panelWidth, transomY + transomHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
        }
    }

    // Draw main section
    const mainY = hasTransom && isFixedTransomHead ? offsetY + transomHeight : offsetY;
    
    for (let i = 0; i < numberOfPanels; i++) {
        const panelX = offsetX + (i * panelWidth);
        const panelType = panelTypes[i] || 'sliding';

        if (panelType === 'fixed') {
            // Fixed panel
            const fixedRect = new Konva.Rect({
                x: panelX,
                y: mainY,
                width: panelWidth,
                height: mainHeight,
                fill: '#4A90E2',
                opacity: 0.8,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(fixedRect);

            // Centered, capped "F" label (prevent oversized letters on large panels)
            const handleX = panelX + panelWidth / 2;
            const handleY = mainY + mainHeight / 2;

            // Use a fraction of panel height but cap to a maximum for visual consistency
            const labelFontSize = Math.max(12, Math.min(mainHeight * 0.18, 24));

            const label = new Konva.Text({
                x: handleX,
                y: handleY,
                text: 'F',
                fontSize: labelFontSize,
                fontFamily: 'Arial',
                fontStyle: 'bold',
                fill: '#FFFFFF',
                align: 'center',
                listening: false,
            });

            // Precisely center text using measured dimensions instead of fixed offsets
            label.offsetX(label.width() / 2);
            label.offsetY(label.height() / 2);

            layer.add(label);
        } else {
            // Sliding panel
            const glassRect = new Konva.Rect({
                x: panelX,
                y: mainY,
                width: panelWidth,
                height: mainHeight,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(glassRect);

            // "S" label for sliding - only show when no transom is selected
            if (!hasTransom || (hasTransom && (isFixedTransomHead || isFixedTransomSill))) {
                const handleX = panelX + panelWidth / 2;
                const handleY = mainY + mainHeight / 2;

                // Cap the size of the "S" so it doesn't become oversized on large panels.
                // Use a sensible fraction of the panel height and an upper bound.
                const labelFontSize = Math.max(12, Math.min(mainHeight * 0.18, 24));

                const label = new Konva.Text({
                    x: handleX,
                    y: handleY,
                    text: 'S',
                    fontSize: labelFontSize,
                    fontFamily: 'Arial',
                    fontStyle: 'bold',
                    fill: '#333333',
                    align: 'center',
                    listening: false,
                });

                // Center the text precisely by using measured width/height
                // (avoid relying on fixed offset multipliers that break on different fonts/sizes)
                label.offsetX(label.width() / 2);
                label.offsetY(label.height() / 2);

                layer.add(label);
            }
        }

        // Panel divider
        if (i < numberOfPanels - 1) {
            const divider = new Konva.Line({
                points: [panelX + panelWidth, mainY, panelX + panelWidth, mainY + mainHeight],
                stroke: fStyle.color,
                strokeWidth: fStyle.width * 1.5,
                listening: false,
            });
            layer.add(divider);
        }
    }

    // Draw transom divider if present
    if (hasTransom) {
        const dividerY = isFixedTransomHead ? offsetY + transomHeight : offsetY + mainHeight;
        const divider = new Konva.Line({
            points: [offsetX, dividerY, offsetX + totalWidth, dividerY],
            stroke: fStyle.color,
            strokeWidth: fStyle.width * 1.5,
            listening: false,
        });
        layer.add(divider);
    }

    // Add track system indicator
    if (trackSystem === '3 Tracks') {
        const trackY = mainY + mainHeight;
        for (let i = 0; i < 3; i++) {
            const trackX = offsetX + (totalWidth / 4) * (i + 1);
            const trackLine = new Konva.Line({
                points: [trackX, trackY, trackX, trackY + 5],
                stroke: '#666666',
                strokeWidth: 2,
                listening: false,
            });
            layer.add(trackLine);
        }
    }

    // Add screen indicator if present
    if (screen && screen.toLowerCase().includes('with screen')) {
        drawScreenPattern(layer, offsetX, mainY, totalWidth, mainHeight);
    }
    
    // Draw dimension lines (measurement grid)
    // originalWidth, originalHeight, widthUnit, and heightUnit are already declared above
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);
    
    // Draw h1 and h2 transom dimensions if transom is present
    if (hasTransom) {
        drawTransomDimensions(layer, offsetX, offsetY, totalWidth, totalHeight,
                              isFixedTransomHead, transomHeight, mainHeight,
                              originalHeight, heightUnit, renderContext);
    }
    
    // Draw annotations (thickness, frame color)
    if (glassThickness || frameColor) {
        const formatThickness = glassThickness || '6mm';
        // Handle frameColor as string or array (tags field can be either)
        let frameColorValue = frameColor;
        if (Array.isArray(frameColorValue)) {
            frameColorValue = frameColorValue[0] || '';
        }
        const formatFrame = frameColorValue ? String(frameColorValue) : '';
        const annotationText = formatFrame ? 
            `Thickness: ${formatThickness}  |  Frame: ${formatFrame}` : 
            `Thickness: ${formatThickness}`;
        
        layer.add(new Konva.Text({
            x: offsetX + totalWidth / 2,
            y: offsetY + totalHeight + 15,
            text: annotationText,
            fontSize: 11,
            fontStyle: 'bold',
            fontFamily: 'Montserrat, Arial',
            fill: '#555',
            align: 'center',
            offsetX: (annotationText.length * 6) / 2,
            listening: false,
        }));
    }
}

/**
 * Render Windows Awning configuration
 */
function renderWindowsAwning(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || '38 Series';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    // Handle frameColor as string or array (tags field can be either)
    let frameColor = customizationValues.frameColor || 'Powder Coated White';
    if (Array.isArray(frameColor)) {
        frameColor = frameColor[0] || 'Powder Coated White';
    }
    const operation = customizationValues.operation || 'Awning (crank-out)';
    const sizeConfiguration = customizationValues.sizeConfiguration || 'Single panel';
    const openingDirection = customizationValues.openingDirection || 'Top-hinged';
    const thickness = customizationValues.thickness || '6mm';
    const screen = customizationValues.screen || false;

    // Get rows and columns from number inputs
    let rows = 1;
    let cols = 1;
    
    // Check if we have direct values from number inputs
    if (customizationValues.panelRows !== undefined && customizationValues.panelRows !== null) {
        rows = parseInt(customizationValues.panelRows) || 1;
    }
    if (customizationValues.panelColumns !== undefined && customizationValues.panelColumns !== null) {
        cols = parseInt(customizationValues.panelColumns) || 1;
    }
    
    // Fallback: try to get from DOM inputs if not in customizationValues
    if (rows === 1 && cols === 1 && typeof document !== 'undefined') {
        const rowsInput = document.getElementById('panelRows');
        const colsInput = document.getElementById('panelColumns');
        if (rowsInput && rowsInput.value) {
            rows = parseInt(rowsInput.value) || 1;
        }
        if (colsInput && colsInput.value) {
            cols = parseInt(colsInput.value) || 1;
        }
    }
    
    // If single panel is selected, use 1x1
    if (sizeConfiguration && sizeConfiguration.toLowerCase().includes('single')) {
        rows = 1;
        cols = 1;
    }
    
    // Ensure minimum values
    rows = Math.max(1, rows);
    cols = Math.max(1, cols);

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // Calculate panel dimensions
    const panelWidth = totalWidth / cols;
    const panelHeight = totalHeight / rows;

    // Draw panels in grid
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const panelX = offsetX + (col * panelWidth);
            const panelY = offsetY + (row * panelHeight);
            
            // Draw panel frame
            const panelRect = new Konva.Rect({
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
            layer.add(panelRect);

            // Draw internal ^ pattern (inverted V shape)
            const centerX = panelX + panelWidth / 2;
            const topY = panelY;
            const bottomLeftX = panelX;
            const bottomRightX = panelX + panelWidth;
            const bottomY = panelY + panelHeight;
            
            // Left side of ^ (from bottom-left to top-center)
            const chevronLeft = new Konva.Line({
                points: [bottomLeftX, bottomY, centerX, topY],
                stroke: '#0066CC',
                strokeWidth: 1.5,
                dash: [4, 3],
                listening: false,
            });
            layer.add(chevronLeft);
            
            // Right side of ^ (from bottom-right to top-center)
            const chevronRight = new Konva.Line({
                points: [bottomRightX, bottomY, centerX, topY],
                stroke: '#0066CC',
                strokeWidth: 1.5,
                dash: [4, 3],
                listening: false,
            });
            layer.add(chevronRight);

            // Draw hinge indicator at top of each panel (top-hinged awning)
            if (row === 0) {
                const hingeLine = new Konva.Line({
                    points: [panelX, panelY, panelX + panelWidth, panelY],
                    stroke: '#FF6B6B',
                    strokeWidth: 2,
                    listening: false,
                });
                layer.add(hingeLine);
            }

            // Draw panel dividers (vertical)
            if (col < cols - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, panelY, panelX + panelWidth, panelY + panelHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
            
            // Draw panel dividers (horizontal)
            if (row < rows - 1) {
                const divider = new Konva.Line({
                    points: [panelX, panelY + panelHeight, panelX + panelWidth, panelY + panelHeight],
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width * 1.5,
                    listening: false,
                });
                layer.add(divider);
            }
        }
    }

    // Add screen if present
    if (screen && screen.toLowerCase().includes('with screen')) {
        drawScreenPattern(layer, offsetX, offsetY, totalWidth, totalHeight);
    }
    
    // Draw dimension lines (H and W as shown in reference image)
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);

    // Draw annotations (thickness, frame color)
    if (thickness || frameColor) {
        // Normalize thickness so values like 6 or '6' or '6mm' become '6mm'
        let formatThickness = (thickness === undefined || thickness === null) ? '6mm' : String(thickness);
        formatThickness = formatThickness.replace(/mm$/i, '');
        formatThickness = `${formatThickness}mm`;
        // Handle frameColor as string or array (tags field can be either)
        let frameColorValue = frameColor;
        if (Array.isArray(frameColorValue)) {
            frameColorValue = frameColorValue[0] || '';
        }
        const formatFrame = frameColorValue ? String(frameColorValue) : '';
        const annotationText = formatFrame ? 
            `Thickness: ${formatThickness}  |  Frame: ${formatFrame}` : 
            `Thickness: ${formatThickness}`;
        
        layer.add(new Konva.Text({
            x: offsetX + totalWidth / 2,
            y: offsetY + totalHeight + 15,
            text: annotationText,
            fontSize: 11,
            fontStyle: 'bold',
            fontFamily: 'Montserrat, Arial',
            fill: '#555',
            align: 'center',
            offsetX: (annotationText.length * 6) / 2,
            listening: false,
        }));
    }
}

/**
 * Render Windows Casement configuration
 */
function renderWindowsCasement(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || '38 Series';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const operation = customizationValues.operation || 'Casement (hinge side configurable)';
    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || 'Single panel');
    const hingeSide = customizationValues.hingeSide || 'Left-hinged';
    const configuration = customizationValues.configuration || '';
    const transomOptions = customizationValues.transomOptions || '';
    const thickness = customizationValues.thickness || '6mm';
    const screen = customizationValues.screen || false;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    const panelWidth = totalWidth / numberOfPanels;

    // Support per-panel hinge side input. Accept:
    // - customizationValues.hingeSides as pipe-separated string, e.g. 'Left|Right|Left'
    // - an array of hinge sides
    // - fallback to single hingeSide value for all panels
    const hingeSidesRaw = customizationValues.hingeSides || customizationValues.hingeSide || hingeSide;
    let hingeSidesArray = [];
    if (Array.isArray(hingeSidesRaw)) {
        hingeSidesArray = hingeSidesRaw.map(s => String(s || '').trim());
    } else if (typeof hingeSidesRaw === 'string') {
        // split by pipe if multiple provided, otherwise single-value array
        hingeSidesArray = hingeSidesRaw.includes('|') ? hingeSidesRaw.split('|').map(s => s.trim()) : [hingeSidesRaw.trim()];
    } else {
        hingeSidesArray = [String(hingeSidesRaw)];
    }

    // Draw panels
    for (let i = 0; i < numberOfPanels; i++) {
        const panelX = offsetX + (i * panelWidth);
        
        // Draw panel
        const panelRect = new Konva.Rect({
            x: panelX,
            y: offsetY,
            width: panelWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: fStyle.color,
            strokeWidth: fStyle.width,
            listening: false,
        });
        layer.add(panelRect);

        // Determine hinge for this panel (per-panel or fallback)
        const hingeForPanel = hingeSidesArray[i] || hingeSidesArray[0] || hingeSide;
        const isLeft = String(hingeForPanel).toLowerCase().includes('left');

        // Draw hinge on appropriate side
        const hingeX = isLeft ? panelX : panelX + panelWidth;
        const hingeLine = new Konva.Line({
            points: [hingeX, offsetY, hingeX, offsetY + totalHeight],
            stroke: '#FF6B6B',
            strokeWidth: 3,
            listening: false,
        });
        layer.add(hingeLine);

        // Draw opening arc
        const arcCenterX = isLeft ? panelX : panelX + panelWidth;
        const arcCenterY = offsetY + totalHeight / 2;
        const arcRadius = panelWidth * 0.4;
        
        const arc = new Konva.Arc({
            x: arcCenterX,
            y: arcCenterY,
            innerRadius: 0,
            outerRadius: arcRadius,
            angle: 90,
            rotation: isLeft ? 0 : 180,
            fill: 'rgba(255, 107, 107, 0.2)',
            stroke: '#FF6B6B',
            strokeWidth: 2,
            listening: false,
        });
        layer.add(arc);

        // Panel divider
        if (i < numberOfPanels - 1) {
            const divider = new Konva.Line({
                points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                stroke: fStyle.color,
                strokeWidth: fStyle.width * 1.5,
                listening: false,
            });
            layer.add(divider);
        }
    }

    // Add screen if present
    if (screen) {
        drawScreenPattern(layer, offsetX, offsetY, totalWidth, totalHeight);
    }
    
    // Draw dimension lines
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);
}

/**
 * Render Windows Fixed Glass configuration
 */
function renderWindowsFixedGlass(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const glassType = customizationValues.glassType || 'Clear';
    const frameColor = customizationValues.frameColor || 'White';
    const configuration = customizationValues.configuration || 'Standard fixed';
    const usage = customizationValues.usage || 'Standard fixed';
    const installationMethod = customizationValues.installationMethod || 'Standard mounting';
    const thickness = customizationValues.thickness || 6;
    const screen = customizationValues.screen || false;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType);
    const fStyle = getFrameStyle(frameColor);

    // Draw fixed glass panel
    const glassRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
    layer.add(glassRect);

    // Add "FIXED" label
    const fixedLabel = new Konva.Text({
        x: offsetX + totalWidth / 2,
        y: offsetY + totalHeight / 2,
        text: 'FIXED',
        fontSize: Math.max(16, totalHeight / 6),
        fontFamily: 'Arial',
        fontStyle: 'bold',
        fill: '#333333',
        align: 'center',
        offsetX: 30,
        offsetY: 10,
        listening: false,
    });
    layer.add(fixedLabel);

    // Add screen if present
    if (screen) {
        drawScreenPattern(layer, offsetX, offsetY, totalWidth, totalHeight);
    }
    
    // Draw dimension lines
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);
}

// ============================================================================
// DOORS RENDERERS
// ============================================================================

/**
 * Render Doors Sliding configuration
 */
function renderDoorsSliding(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const glassType = customizationValues.glassType || 'Clear';
    const frameColor = customizationValues.frameColor || 'Aluminum';
    const panelCount = extractPanelCount(customizationValues.panelCount || '2-panel');
    const operation = customizationValues.operation || 'Sliding (single)';
    const panelConfiguration = customizationValues.panelConfiguration || 'All sliding';
    const handleType = customizationValues.handleType || 'Various pull handles';
    const hardwareFinish = customizationValues.hardwareFinish || 'Chrome/Stainless Steel';
    const softClose = customizationValues.softClose || false;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType);
    const fStyle = getFrameStyle(frameColor);

    // Parse panel configuration
    const panelTypes = parsePanelConfiguration(panelConfiguration, panelCount);
    const panelWidth = totalWidth / panelCount;

    // Draw panels
    for (let i = 0; i < panelCount; i++) {
        const panelX = offsetX + (i * panelWidth);
        const panelType = panelTypes[i] || 'sliding';

        if (panelType === 'fixed') {
            // Fixed panel
            const fixedRect = new Konva.Rect({
                x: panelX,
                y: offsetY,
                width: panelWidth,
                height: totalHeight,
                fill: '#4A90E2',
                opacity: 0.8,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(fixedRect);

            const label = new Konva.Text({
                x: panelX + panelWidth / 2,
                y: offsetY + totalHeight / 2,
                text: 'F',
                fontSize: Math.max(14, totalHeight / 10),
                fontFamily: 'Arial',
                fontStyle: 'bold',
                fill: '#FFFFFF',
                align: 'center',
                offsetX: 6,
                offsetY: 8,
                listening: false,
            });
            layer.add(label);
        } else {
            // Sliding panel
            const glassRect = new Konva.Rect({
                x: panelX,
                y: offsetY,
                width: panelWidth,
                height: totalHeight,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
            layer.add(glassRect);

            // Handle indicator
            const handleX = panelX + panelWidth / 2;
            const handleY = offsetY + totalHeight / 2;
            
            const handleRect = new Konva.Rect({
                x: handleX - 15,
                y: handleY - 3,
                width: 30,
                height: 6,
                fill: '#333333',
                opacity: 0,
                cornerRadius: 3,
                listening: false,
            });
            layer.add(handleRect);

            // "S" label
            const labelFontSize = Math.max(12, totalHeight / 6);
            const label = new Konva.Text({
                x: handleX,
                y: handleY,
                text: 'S',
                fontSize: labelFontSize,
                fontFamily: 'Arial',
                fontStyle: 'bold',
                fill: '#333333',
                align: 'center',
                verticalAlign: 'middle',
                offsetX: labelFontSize * 0.35,
                offsetY: labelFontSize * 0.5,
                listening: false,
            });
            layer.add(label);
        }

        // Panel divider
        if (i < panelCount - 1) {
            const divider = new Konva.Line({
                points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                stroke: fStyle.color,
                strokeWidth: fStyle.width * 1.5,
                listening: false,
            });
            layer.add(divider);
        }
    }

    // Draw track at bottom
    const trackY = offsetY + totalHeight;
    const trackLine = new Konva.Line({
        points: [offsetX, trackY, offsetX + totalWidth, trackY],
        stroke: '#666666',
        strokeWidth: 3,
        listening: false,
    });
    layer.add(trackLine);
}

/**
 * Render Doors Swing Door configuration
 */
function renderDoorsSwing(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || '68 Series';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const thickness = customizationValues.thickness || '6mm';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // Draw door
    const doorRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
    layer.add(doorRect);

    // Draw hinge on left side
    const hingeLine = new Konva.Line({
        points: [offsetX, offsetY, offsetX, offsetY + totalHeight],
        stroke: '#FF6B6B',
        strokeWidth: 4,
        listening: false,
    });
    layer.add(hingeLine);

    // Draw opening arc
    const arc = new Konva.Arc({
        x: offsetX,
        y: offsetY + totalHeight / 2,
        innerRadius: 0,
        outerRadius: totalWidth * 0.5,
        angle: 90,
        rotation: 0,
        fill: 'rgba(255, 107, 107, 0.2)',
        stroke: '#FF6B6B',
        strokeWidth: 2,
        listening: false,
    });
    layer.add(arc);

}

/**
 * Render Doors Bi-fold configuration
 */
function renderDoorsBifold(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || '45 Series';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const thickness = customizationValues.thickness || '6mm';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // Bi-fold typically has 2 panels
    const panelWidth = totalWidth / 2;

    // Draw left panel (fixed side)
    const leftPanel = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: panelWidth,
        height: totalHeight,
        fill: '#4A90E2',
        opacity: 0.8,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
    layer.add(leftPanel);

    // Draw right panel (folding)
    const rightPanel = new Konva.Rect({
        x: offsetX + panelWidth,
        y: offsetY,
        width: panelWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
    layer.add(rightPanel);

    // Draw center hinge
    const centerHinge = new Konva.Line({
        points: [offsetX + panelWidth, offsetY, offsetX + panelWidth, offsetY + totalHeight],
        stroke: '#FF6B6B',
        strokeWidth: 3,
        listening: false,
    });
    layer.add(centerHinge);

    // Draw folding arc
    const arc = new Konva.Arc({
        x: offsetX + panelWidth,
        y: offsetY + totalHeight / 2,
        innerRadius: 0,
        outerRadius: panelWidth * 0.6,
        angle: 90,
        rotation: 0,
        fill: 'rgba(255, 107, 107, 0.2)',
        stroke: '#FF6B6B',
        strokeWidth: 2,
        listening: false,
    });
    layer.add(arc);
}

/**
 * Render Doors Frameless configuration
 */
function renderDoorsFrameless(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const glassType = customizationValues.glassType || 'Clear';
    const doorType = customizationValues.doorType || 'Single swing';
    const doorSwing = customizationValues.doorSwing || 'Left swing';
    const fixedPanels = customizationValues.fixedPanels || 'Without fixed panels';
    const configuration = customizationValues.configuration || '';
    const handleType = customizationValues.handleType || 'Various pull handles';
    const hardwareFinish = customizationValues.hardwareFinish || 'Polished Chrome/Stainless Steel';
    const gridPattern = customizationValues.gridPattern || '';
    const glassTreatment = customizationValues.glassTreatment || '';
    const installation = customizationValues.installation || 'Standard';
    const hardware = customizationValues.hardware || '';
    const softClose = customizationValues.softClose || false;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType);
    const fStyle = { color: 'transparent', width: 0 }; // Frameless

    // Determine if single or double door
    const isDouble = doorType.toLowerCase().includes('double');
    const isLeftSwing = doorSwing.toLowerCase().includes('left');

    if (isDouble) {
        // Double door
        const doorWidth = totalWidth / 2;
        
        // Left door
        const leftDoor = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: doorWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: '#CCCCCC',
            strokeWidth: 1,
            listening: false,
        });
        layer.add(leftDoor);

        // Right door
        const rightDoor = new Konva.Rect({
            x: offsetX + doorWidth,
            y: offsetY,
            width: doorWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: '#CCCCCC',
            strokeWidth: 1,
            listening: false,
        });
        layer.add(rightDoor);

        // Draw hinges
        const leftHinge = new Konva.Line({
            points: [offsetX, offsetY, offsetX, offsetY + totalHeight],
            stroke: '#FF6B6B',
            strokeWidth: 3,
            listening: false,
        });
        layer.add(leftHinge);

        const rightHinge = new Konva.Line({
            points: [offsetX + totalWidth, offsetY, offsetX + totalWidth, offsetY + totalHeight],
            stroke: '#FF6B6B',
            strokeWidth: 3,
            listening: false,
        });
        layer.add(rightHinge);
    } else {
        // Single door
        const doorRect = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: totalWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: '#CCCCCC',
            strokeWidth: 1,
            listening: false,
        });
        layer.add(doorRect);

        // Draw hinge on appropriate side
        const hingeX = isLeftSwing ? offsetX : offsetX + totalWidth;
        const hingeLine = new Konva.Line({
            points: [hingeX, offsetY, hingeX, offsetY + totalHeight],
            stroke: '#FF6B6B',
            strokeWidth: 3,
            listening: false,
        });
        layer.add(hingeLine);
    }

    // Add grid pattern if specified
    if (gridPattern) {
        drawGridPattern(layer, offsetX, offsetY, totalWidth, totalHeight, gridPattern);
    }
}

/**
 * Render Doors Patch Fitting configuration
 */
function renderDoorsPatchFitting(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || 'Frameless Door';
    const glassType = customizationValues.glassType || 'Tempered';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Stainless Mirror Finish';
    const thickness = customizationValues.thickness || '10mm-12mm';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // Draw frameless door with minimal hardware
    const doorRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: '#CCCCCC',
        strokeWidth: 1,
        listening: false,
    });
    layer.add(doorRect);

    // Draw patch fittings (small hardware points)
    const patchSize = 8;
    const patches = [
        { x: offsetX + 20, y: offsetY + 20 },
        { x: offsetX + 20, y: offsetY + totalHeight - 20 },
        { x: offsetX + totalWidth - 20, y: offsetY + 20 },
        { x: offsetX + totalWidth - 20, y: offsetY + totalHeight - 20 },
    ];

    patches.forEach(patch => {
        const patchRect = new Konva.Rect({
            x: patch.x - patchSize / 2,
            y: patch.y - patchSize / 2,
            width: patchSize,
            height: patchSize,
            fill: fStyle.color,
            opacity: 0.9,
            listening: false,
        });
        layer.add(patchRect);
    });
}

// ============================================================================
// PARTITIONS RENDERERS
// ============================================================================

/**
 * Render Partitions Frameless Glass configuration
 */
function renderPartitionsFramelessGlass(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const layout = customizationValues.layout || 'Straight';
    const glassType = customizationValues.glassType || 'Clear';
    const finish = customizationValues.finish || 'Clear';
    const configuration = customizationValues.configuration || 'Single partition';
    const hardwareColor = customizationValues.hardwareColor || 'Black';
    const mountingHardware = customizationValues.mountingHardware || 'Standard mounting';
    const glassThickness = customizationValues.glassThickness || 10;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, finish);
    const hStyle = getFrameStyle(hardwareColor);

    // Draw frameless glass panel
    const glassRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: '#CCCCCC',
        strokeWidth: 1,
        listening: false,
    });
    layer.add(glassRect);

    // Draw mounting hardware (clamps/brackets)
    const clampSize = 6;
    const clamps = [
        { x: offsetX, y: offsetY + 30 },
        { x: offsetX, y: offsetY + totalHeight - 30 },
        { x: offsetX + totalWidth, y: offsetY + 30 },
        { x: offsetX + totalWidth, y: offsetY + totalHeight - 30 },
    ];

    clamps.forEach(clamp => {
        const clampRect = new Konva.Rect({
            x: clamp.x - clampSize / 2,
            y: clamp.y - clampSize / 2,
            width: clampSize,
            height: clampSize,
            fill: hStyle.color,
            opacity: 0.9,
            listening: false,
        });
        layer.add(clampRect);
    });
}

/**
 * Render Partitions Shower Enclosure configuration
 */
function renderPartitionsShowerEnclosure(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || 'Fixed Frameless Shower Partition';
    const layout = customizationValues.layout || 'Straight';
    const configuration = customizationValues.configuration || 'Single sliding';
    const glassType = customizationValues.glassType || 'Tempered';
    const glassColor = customizationValues.glassColor || 'Clear';
    const hardwareFinish = customizationValues.hardwareFinish || 'Mirror/Stainless Hardware';
    const glassTreatment = customizationValues.glassTreatment || 'Clear';
    const glassThickness = customizationValues.glassThickness || '10mm';
    const handleStyle = customizationValues.handleStyle || 'Various pull handles';
    const doorSwing = customizationValues.doorSwing || 'Left-hinged';
    const mounting = customizationValues.mounting || 'Standard mounting';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, glassColor);
    const hStyle = getFrameStyle(hardwareFinish);

    // Parse configuration to determine panel setup
    const isSliding = configuration.toLowerCase().includes('sliding');
    const isSwing = configuration.toLowerCase().includes('swing');
    const isFixed = configuration.toLowerCase().includes('fixed');
    const panelCount = extractPanelCount(configuration) || 1;

    if (isSliding && panelCount > 1) {
        // Sliding door configuration
        const panelWidth = totalWidth / panelCount;
        
        for (let i = 0; i < panelCount; i++) {
            const panelX = offsetX + (i * panelWidth);
            
            const panelRect = new Konva.Rect({
                x: panelX,
                y: offsetY,
                width: panelWidth,
                height: totalHeight,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: '#CCCCCC',
                strokeWidth: 1,
                listening: false,
            });
            layer.add(panelRect);

            // Handle indicator
            if (i < panelCount - 1) {
                const handleX = panelX + panelWidth / 2;
                const handleY = offsetY + totalHeight / 2;
                
                const handleRect = new Konva.Rect({
                    x: handleX - 12,
                    y: handleY - 2,
                    width: 24,
                    height: 4,
                    fill: hStyle.color,
                    opacity: 0.9,
                    cornerRadius: 2,
                    listening: false,
                });
                layer.add(handleRect);
            }

            // Panel divider
            if (i < panelCount - 1) {
                const divider = new Konva.Line({
                    points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                    stroke: '#CCCCCC',
                    strokeWidth: 1,
                    listening: false,
                });
                layer.add(divider);
            }
        }

        // Draw track at bottom
        const trackLine = new Konva.Line({
            points: [offsetX, offsetY + totalHeight, offsetX + totalWidth, offsetY + totalHeight],
            stroke: hStyle.color,
            strokeWidth: 2,
            listening: false,
        });
        layer.add(trackLine);
    } else if (isSwing) {
        // Swing door configuration
        const doorRect = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: totalWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: '#CCCCCC',
            strokeWidth: 1,
            listening: false,
        });
        layer.add(doorRect);

        // Draw hinge
        const isLeftHinged = doorSwing.toLowerCase().includes('left');
        const hingeX = isLeftHinged ? offsetX : offsetX + totalWidth;
        const hingeLine = new Konva.Line({
            points: [hingeX, offsetY, hingeX, offsetY + totalHeight],
            stroke: '#FF6B6B',
            strokeWidth: 3,
            listening: false,
        });
        layer.add(hingeLine);
    } else {
        // Fixed partition
        const glassRect = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: totalWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: '#CCCCCC',
            strokeWidth: 1,
            listening: false,
        });
        layer.add(glassRect);
    }
}

/**
 * Render Partitions Fixed Glass configuration
 */
function renderPartitionsFixedGlass(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const layout = customizationValues.layout || 'Straight';
    const glassType = customizationValues.glassType || 'Clear';
    const finish = customizationValues.finish || 'Clear';
    const configuration = customizationValues.configuration || 'Single partition';
    const mountingHardware = customizationValues.mountingHardware || 'Standard mounting';
    const hardwareColor = customizationValues.hardwareColor || 'Stainless Steel';
    const glassThickness = customizationValues.glassThickness || 10;

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, finish);
    const hStyle = getFrameStyle(hardwareColor);

    // Draw fixed glass panel
    const glassRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: '#CCCCCC',
        strokeWidth: 1,
        listening: false,
    });
    layer.add(glassRect);

    // Draw mounting hardware
    const clampSize = 6;
    const clamps = [
        { x: offsetX, y: offsetY + 30 },
        { x: offsetX, y: offsetY + totalHeight - 30 },
        { x: offsetX + totalWidth, y: offsetY + 30 },
        { x: offsetX + totalWidth, y: offsetY + totalHeight - 30 },
    ];

    clamps.forEach(clamp => {
        const clampRect = new Konva.Rect({
            x: clamp.x - clampSize / 2,
            y: clamp.y - clampSize / 2,
            width: clampSize,
            height: clampSize,
            fill: hStyle.color,
            opacity: 0.9,
            listening: false,
        });
        layer.add(clampRect);
    });
}

// ============================================================================
// SPECIALTY RENDERERS
// ============================================================================

/**
 * Render Specialty Mirrors configuration
 */
function renderSpecialtyMirrors(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const series = customizationValues.series || 'Rectangle/Square Framed Mirror';
    const shape = customizationValues.shape || 'Rectangle';
    const cornerRadius = customizationValues.cornerRadius || 0;
    const frameType = customizationValues.frameType || 'Framed';
    const frameColor = customizationValues.frameColor || 'White';
    const glassType = customizationValues.glassType || 'Copper Free and Lead Free Mirror';
    const thickness = customizationValues.thickness || '6mm';
    const tintFinish = customizationValues.tintFinish || '';
    const orientation = customizationValues.orientation || 'Vertical';
    const style = customizationValues.style || '';
    const gridPattern = customizationValues.gridPattern || '';
    const arrangement = customizationValues.arrangement || 'Individually';
    const lighting = customizationValues.lighting || '';
    const ledColorTemperature = customizationValues.ledColorTemperature || '';
    const control = customizationValues.control || '';
    const additionalFeatures = customizationValues.additionalFeatures || '';
    const mountingMethod = customizationValues.mountingMethod || 'Wall-mounted';
    const quantity = customizationValues.quantity || '';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType, tintFinish);
    const fStyle = getFrameStyle(frameColor);
    const isFrameless = frameType.toLowerCase().includes('frameless');

    // Draw mirror based on shape
    let mirrorShape;
    const centerX = offsetX + totalWidth / 2;
    const centerY = offsetY + totalHeight / 2;
    const minRadius = Math.min(totalWidth, totalHeight) / 2;

    if (shape.toLowerCase().includes('round') || shape.toLowerCase().includes('circle')) {
        mirrorShape = new Konva.Circle({
            x: centerX,
            y: centerY,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: isFrameless ? 'transparent' : fStyle.color,
            strokeWidth: isFrameless ? 0 : fStyle.width,
            listening: false,
        });
    } else if (shape.toLowerCase().includes('oval') || shape.toLowerCase().includes('ellipse')) {
        mirrorShape = new Konva.Ellipse({
            x: centerX,
            y: centerY,
            radiusX: totalWidth / 2,
            radiusY: totalHeight / 2,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: isFrameless ? 'transparent' : fStyle.color,
            strokeWidth: isFrameless ? 0 : fStyle.width,
            listening: false,
        });
    } else {
        // Rectangle/Square - Support individual corner radius values
        let cornerRadiusPx = 0;
        let cornerRadiusArray = null;
        
        // Check if cornerRadius is an object with individual corners
        const cornerRadiusData = customizationValues.cornerRadius || customizationValues.CornerRadius || cornerRadius;
        
        if (typeof cornerRadiusData === 'object' && cornerRadiusData !== null && !Array.isArray(cornerRadiusData)) {
            // Individual corner radius values from object (stored in inches)
            const pxPerInX = width > 0 ? (totalWidth / width) : 0;
            const pxPerInY = height > 0 ? (totalHeight / height) : 0;
            const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);
            
            const topLeft = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.topLeft || 0)) * (pxPerIn || 0));
            const topRight = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.topRight || 0)) * (pxPerIn || 0));
            const bottomRight = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.bottomRight || 0)) * (pxPerIn || 0));
            const bottomLeft = Math.min(minRadius, Math.max(0, parseFloat(cornerRadiusData.bottomLeft || 0)) * (pxPerIn || 0));
            
            cornerRadiusArray = [topLeft, topRight, bottomRight, bottomLeft];
        } else {
            // Single value (linked mode) - convert to array format
            const safeCornerRadiusIn = Math.max(0, parseFloat(cornerRadiusData) || 0);
            const pxPerInX = width > 0 ? (totalWidth / width) : 0;
            const pxPerInY = height > 0 ? (totalHeight / height) : 0;
            const pxPerIn = Math.min(pxPerInX || 0, pxPerInY || 0);
            cornerRadiusPx = Math.min(minRadius, safeCornerRadiusIn * (pxPerIn || 0));
            cornerRadiusArray = [cornerRadiusPx, cornerRadiusPx, cornerRadiusPx, cornerRadiusPx];
        }
        
        const hasCornerRadius = cornerRadiusArray && cornerRadiusArray.some(radius => radius > 0);
        mirrorShape = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: totalWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: isFrameless ? 'transparent' : fStyle.color,
            strokeWidth: isFrameless ? 0 : fStyle.width,
            cornerRadius: hasCornerRadius ? cornerRadiusArray : 0,
            listening: false,
        });
    }
    layer.add(mirrorShape);

    // Add grid pattern if specified
    if (gridPattern) {
        drawGridPattern(layer, offsetX, offsetY, totalWidth, totalHeight, gridPattern);
    }

    // Add LED lighting indicator if present
    if (lighting && lighting.toLowerCase().includes('led')) {
        const ledIndicator = new Konva.Circle({
            x: offsetX + 20,
            y: offsetY + 20,
            radius: 5,
            fill: '#FFD700',
            opacity: 0.8,
            listening: false,
        });
        layer.add(ledIndicator);
    }

    // Draw dimension lines (width and height) for mirrors
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight,
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);

    // Draw annotations (thickness, edge/frame based on frame type)
    const formatThickness = thickness || '6mm';
    let annotationParts = [`Thickness: ${formatThickness}`];
    
    if (isFrameless) {
        // For frameless mirrors, show edge finish
        // Check frameColor first (since edge options are stored there when frameless)
        // Then check edgeFinish/edgeWork as fallback
        let edgeFinish = '';
        const frameColorValue = (frameColor || '').toLowerCase();
        const edgeOptions = ['machine polished edges', 'beveled edge'];
        
        // Check if frameColor contains an edge finish option
        const isEdgeInFrameColor = frameColor && (
            frameColorValue.includes('polished') || 
            frameColorValue.includes('beveled') ||
            edgeOptions.some(opt => frameColorValue === opt.toLowerCase() || frameColorValue.includes(opt.toLowerCase()))
        );
        
        if (isEdgeInFrameColor) {
            edgeFinish = frameColor;
        } else {
            edgeFinish = customizationValues.edgeFinish || customizationValues.edgeWork || '';
        }
        
        if (edgeFinish) {
            const formatEdge = edgeFinish.split('-').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)).join(' ') || edgeFinish;
            annotationParts.push(`Edge: ${formatEdge}`);
        }
    } else {
        // For framed mirrors, show frame color
        // Only show frame color if it's not an edge option
        const frameColorValue = (frameColor || '').toLowerCase();
        const isEdgeOption = frameColorValue.includes('polished') || frameColorValue.includes('beveled');
        
        if (frameColor && !isEdgeOption) {
            annotationParts.push(`Frame: ${frameColor}`);
        }
    }
    
    if (annotationParts.length > 0) {
        const annotationText = annotationParts.join('  |  ');
        layer.add(new Konva.Text({
            x: offsetX + totalWidth / 2,
            y: offsetY + totalHeight + 15,
            text: annotationText,
            fontSize: 11,
            fontStyle: 'bold',
            fontFamily: 'Montserrat, Arial',
            fill: '#555',
            align: 'center',
            offsetX: (annotationText.length * 6) / 2,
            listening: false,
        }));
    }
    
    // Draw corner radius annotations for rectangle/square shapes
    if (shape.toLowerCase().includes('rectangle') || shape.toLowerCase().includes('square')) {
        drawCornerRadiusAnnotationsForMirrors(customizationValues, offsetX, offsetY, totalWidth, totalHeight, shape, layer);
    }
}

/**
 * Draw corner radius annotations on the Konva canvas for mirrors
 * Shows radius values and labels at each corner
 */
function drawCornerRadiusAnnotationsForMirrors(customizationValues, offsetX, offsetY, windowWidth, windowHeight, shape, layer) {
    if (!customizationValues || !layer) return;
    
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
        switch(unit) {
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
 * Render Specialty Top Glass configuration
 */
function renderSpecialtyTopGlass(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const shape = customizationValues.shape || 'Rectangle';
    const edgeFinish = customizationValues.edgeFinish || 'Polished';
    const mountingMethod = customizationValues.mountingMethod || 'Wall-mounted';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle('Clear');
    const edgeColor = edgeFinish.toLowerCase().includes('beveled') ? '#E8E8E8' : '#FFFFFF';

    // Draw glass based on shape
    const centerX = offsetX + totalWidth / 2;
    const centerY = offsetY + totalHeight / 2;
    const minRadius = Math.min(totalWidth, totalHeight) / 2;

    let glassShape;
    if (shape.toLowerCase().includes('round') || shape.toLowerCase().includes('circle')) {
        glassShape = new Konva.Circle({
            x: centerX,
            y: centerY,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: edgeColor,
            strokeWidth: 2,
            listening: false,
        });
    } else if (shape.toLowerCase().includes('oval')) {
        glassShape = new Konva.Ellipse({
            x: centerX,
            y: centerY,
            radiusX: totalWidth / 2,
            radiusY: totalHeight / 2,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: edgeColor,
            strokeWidth: 2,
            listening: false,
        });
    } else {
        glassShape = new Konva.Rect({
            x: offsetX,
            y: offsetY,
            width: totalWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: edgeColor,
            strokeWidth: 2,
            listening: false,
        });
    }
    layer.add(glassShape);
}

/**
 * Render Specialty Glass Board configuration
 */
function renderSpecialtyGlassBoard(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const shape = customizationValues.shape || 'Rectangle';
    const edgeFinish = customizationValues.edgeFinish || 'Polished';
    const cornerRadius = customizationValues.cornerRadius || 0;
    const mountingMethod = customizationValues.mountingMethod || 'Wall-mounted';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle('Clear');
    const edgeColor = edgeFinish.toLowerCase().includes('beveled') ? '#E8E8E8' : '#FFFFFF';

    // Draw glass board
    const cornerRadiusPx = cornerRadius ? (cornerRadius * (totalWidth / width)) : 0;
    const boardRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: edgeColor,
        strokeWidth: 2,
        cornerRadius: cornerRadiusPx,
        listening: false,
    });
    layer.add(boardRect);
}

// ============================================================================
// COMMERCIAL RENDERERS
// ============================================================================

/**
 * Render Commercial Storefront configuration
 */
function renderCommercialStorefront(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const glassType = customizationValues.glassType || 'Clear';
    const safetyGlassType = customizationValues.safetyGlassType || 'Tempered';
    const handrailType = customizationValues.handrailType || 'Stainless steel';
    const mountingSystem = customizationValues.mountingSystem || 'Clamp';
    const hardwareFinish = customizationValues.hardwareFinish || 'Polished Chrome/Stainless Steel';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(glassType);
    const hStyle = getFrameStyle(hardwareFinish);

    // Draw storefront glass panels (typically multiple panels)
    const panelCount = 3; // Typical storefront has multiple panels
    const panelWidth = totalWidth / panelCount;

    for (let i = 0; i < panelCount; i++) {
        const panelX = offsetX + (i * panelWidth);
        
        const panelRect = new Konva.Rect({
            x: panelX,
            y: offsetY,
            width: panelWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: hStyle.color,
            strokeWidth: hStyle.width,
            listening: false,
        });
        layer.add(panelRect);

        // Panel divider
        if (i < panelCount - 1) {
            const divider = new Konva.Line({
                points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                stroke: hStyle.color,
                strokeWidth: hStyle.width * 1.5,
                listening: false,
            });
            layer.add(divider);
        }
    }

    // Draw handrail at top
    const handrailY = offsetY;
    const handrailLine = new Konva.Line({
        points: [offsetX, handrailY, offsetX + totalWidth, handrailY],
        stroke: hStyle.color,
        strokeWidth: 4,
        listening: false,
    });
    layer.add(handrailLine);
}

/**
 * Render Commercial Glass Balcony configuration
 */
function renderCommercialGlassBalcony(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const safetyGlassType = customizationValues.safetyGlassType || 'Tempered';
    const handrailType = customizationValues.handrailType || 'Stainless steel';
    const mountingSystem = customizationValues.mountingSystem || 'Clamp';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(safetyGlassType);
    const hStyle = getFrameStyle(handrailType);

    // Draw glass panels
    const panelCount = 4;
    const panelWidth = totalWidth / panelCount;

    for (let i = 0; i < panelCount; i++) {
        const panelX = offsetX + (i * panelWidth);
        
        const panelRect = new Konva.Rect({
            x: panelX,
            y: offsetY,
            width: panelWidth,
            height: totalHeight,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: hStyle.color,
            strokeWidth: 2,
            listening: false,
        });
        layer.add(panelRect);

        // Panel divider
        if (i < panelCount - 1) {
            const divider = new Konva.Line({
                points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                stroke: hStyle.color,
                strokeWidth: 2,
                listening: false,
            });
            layer.add(divider);
        }
    }

    // Draw handrail at top
    const handrailY = offsetY;
    const handrailLine = new Konva.Line({
        points: [offsetX, handrailY, offsetX + totalWidth, handrailY],
        stroke: hStyle.color,
        strokeWidth: 5,
        listening: false,
    });
    layer.add(handrailLine);
}

/**
 * Render Commercial Stair Railings configuration
 */
function renderCommercialStairRailings(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    const customizationValues = productData.customizationValues || {};
    
    const safetyGlassType = customizationValues.safetyGlassType || 'Tempered';
    const handrailType = customizationValues.handrailType || 'Stainless steel';
    const mountingSystem = customizationValues.mountingSystem || 'Clamp';

    // Calculate dimensions
    const actualRatio = width / height;
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

    // Get styles
    const gStyle = getGlassStyle(safetyGlassType);
    const hStyle = getFrameStyle(handrailType);

    // Draw stair railing (angled)
    const railingRect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight * 0.3, // Railing is typically thinner
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: hStyle.color,
        strokeWidth: 2,
        listening: false,
    });
    layer.add(railingRect);

    // Draw handrail on top
    const handrailY = offsetY;
    const handrailLine = new Konva.Line({
        points: [offsetX, handrailY, offsetX + totalWidth, handrailY],
        stroke: hStyle.color,
        strokeWidth: 5,
        listening: false,
    });
    layer.add(handrailLine);

    // Draw support posts
    const postCount = 3;
    const postSpacing = totalWidth / (postCount + 1);
    for (let i = 1; i <= postCount; i++) {
        const postX = offsetX + (postSpacing * i);
        const postLine = new Konva.Line({
            points: [postX, offsetY, postX, offsetY + totalHeight * 0.3],
            stroke: hStyle.color,
            strokeWidth: 3,
            listening: false,
        });
        layer.add(postLine);
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Draw dimension lines (measurement grid) for width and height
 */
function drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, widthValue, widthUnit, heightValue, heightUnit, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { STAGE_SIZE } = ctx;
    
    // Get dimension color from CSS or use default
    const dimColor = (typeof getComputedStyle !== 'undefined' && document.documentElement) 
        ? getComputedStyle(document.documentElement).getPropertyValue('--primary-dark').trim() || '#333'
        : '#333';
    const DIM_EXTENSION = 20; // Extension line length
    const DIM_LINE_OFFSET = 15; // Distance from glass panel to dimension line
    
    // Width dimension (top)
    // Left extension line
    layer.add(new Konva.Line({ 
        points: [offsetX, offsetY, offsetX, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Right extension line
    layer.add(new Konva.Line({ 
        points: [offsetX + totalWidth, offsetY, offsetX + totalWidth, offsetY - DIM_LINE_OFFSET - DIM_EXTENSION], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Horizontal dashed dimension line
    layer.add(new Konva.Line({ 
        points: [offsetX, offsetY - DIM_LINE_OFFSET, offsetX + totalWidth, offsetY - DIM_LINE_OFFSET], 
        stroke: dimColor, 
        strokeWidth: 1.5, 
        dash: [5, 3],
        listening: false
    }));
    // Width label
    const widthText = `${widthValue}${widthUnit}`;
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
    // Top extension line
    layer.add(new Konva.Line({ 
        points: [offsetX + totalWidth, offsetY, offsetX + totalWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Bottom extension line
    layer.add(new Konva.Line({ 
        points: [offsetX + totalWidth, offsetY + totalHeight, offsetX + totalWidth + DIM_LINE_OFFSET + DIM_EXTENSION, offsetY + totalHeight], 
        stroke: dimColor, 
        strokeWidth: 1.5,
        listening: false
    }));
    // Vertical dashed dimension line
    layer.add(new Konva.Line({ 
        points: [offsetX + totalWidth + DIM_LINE_OFFSET, offsetY, offsetX + totalWidth + DIM_LINE_OFFSET, offsetY + totalHeight], 
        stroke: dimColor, 
        strokeWidth: 1.5, 
        dash: [5, 3],
        listening: false
    }));
    // Height label
    const heightText = `${heightValue}${heightUnit}`;
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
}

/**
 * Draw h1 (sliding section height) and h2 (transom height) dimension lines
 */
function drawTransomDimensions(layer, offsetX, offsetY, totalWidth, totalHeight, 
                                isFixedTransomHead, transomHeight, mainHeight,
                                originalHeight, heightUnit, renderContext) {
    const ctx = getRenderContext(renderContext);
    const DIM_EXTENSION = 20;
    
    // Get h1 and h2 values from input elements
    const h1InputGroup = typeof document !== 'undefined' ? (document.getElementById('input-group-h1') || null) : null;
    const h1Input = typeof document !== 'undefined' ? (document.getElementById('input-h1') || null) : null;
    const h1UnitBtn = typeof document !== 'undefined' ? (document.getElementById('btn-unit-h1') || null) : null;
    const h2InputGroup = typeof document !== 'undefined' ? (document.getElementById('input-group-h2') || null) : null;
    const h2Input = typeof document !== 'undefined' ? (document.getElementById('input-h2') || null) : null;
    const h2UnitBtn = typeof document !== 'undefined' ? (document.getElementById('btn-unit-h2') || null) : null;
    
    let h1Value = null;
    let h1Unit = heightUnit;
    let h2Value = null;
    let h2Unit = heightUnit;
    
    // Get h1 value
    if (h1Input && h1Input.value && h1InputGroup && 
        !h1InputGroup.classList.contains('hidden-step') && 
        h1InputGroup.style.display !== 'none') {
        const h1InputValue = parseFloat(h1Input.value);
        if (!isNaN(h1InputValue) && h1InputValue > 0) {
            h1Value = h1InputValue;
            if (h1UnitBtn) {
                h1Unit = h1UnitBtn.getAttribute('data-current-unit') || heightUnit;
            }
        }
    }
    
    // Get h2 value
    if (h2Input && h2Input.value && h2InputGroup && 
        !h2InputGroup.classList.contains('hidden-step') && 
        h2InputGroup.style.display !== 'none') {
        const h2InputValue = parseFloat(h2Input.value);
        if (!isNaN(h2InputValue) && h2InputValue > 0) {
            h2Value = h2InputValue;
            if (h2UnitBtn) {
                h2Unit = h2UnitBtn.getAttribute('data-current-unit') || heightUnit;
            }
        }
    }
    
    // Draw h1 dimension (sliding section height) - Blue color
    if (h1Value !== null && h1Value > 0) {
        const innerHeightColor = '#0066CC'; // Blue for h1
        const INNER_DIM_OFFSET = 25;
        
        const innerHeightStartY = isFixedTransomHead ? offsetY + transomHeight : offsetY;
        const innerHeightEndY = isFixedTransomHead ? offsetY + totalHeight : offsetY + transomHeight;
        
        // Extension lines
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
        // Dashed dimension line
        layer.add(new Konva.Line({ 
            points: [offsetX - INNER_DIM_OFFSET, innerHeightStartY, offsetX - INNER_DIM_OFFSET, innerHeightEndY], 
            stroke: innerHeightColor, 
            strokeWidth: 1.5, 
            dash: [5, 3],
            listening: false
        }));
        // Label
        const formattedH1 = h1Value.toFixed(1);
        const h1Text = `${formattedH1}${h1Unit}`;
        layer.add(new Konva.Text({
            x: offsetX - INNER_DIM_OFFSET - 18,
            y: innerHeightStartY + (innerHeightEndY - innerHeightStartY) / 2,
            text: h1Text,
            fontSize: 11,
            fontFamily: 'Montserrat, Arial',
            fontStyle: 'normal',
            fill: innerHeightColor,
            align: 'center',
            rotation: 90,
            offsetX: (h1Text.length * 6) / 2,
            listening: false,
        }));
    }
    
    // Draw h2 dimension (transom height) - Green color
    if (h2Value !== null && h2Value > 0) {
        const transomHeightColor = '#00AA00'; // Green for h2
        const H2_DIM_OFFSET = 50; // Further left than h1
        
        const transomHeightStartY = isFixedTransomHead ? offsetY : offsetY + mainHeight;
        const transomHeightEndY = isFixedTransomHead ? offsetY + transomHeight : offsetY + totalHeight;
        
        // Extension lines
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
        // Dashed dimension line
        layer.add(new Konva.Line({ 
            points: [offsetX - H2_DIM_OFFSET, transomHeightStartY, offsetX - H2_DIM_OFFSET, transomHeightEndY], 
            stroke: transomHeightColor, 
            strokeWidth: 1.5, 
            dash: [5, 3],
            listening: false
        }));
        // Label
        const formattedH2 = h2Value.toFixed(1);
        const h2Text = `${formattedH2}${h2Unit}`;
        layer.add(new Konva.Text({
            x: offsetX - H2_DIM_OFFSET - 18,
            y: transomHeightStartY + (transomHeightEndY - transomHeightStartY) / 2,
            text: h2Text,
            fontSize: 11,
            fontFamily: 'Montserrat, Arial',
            fontStyle: 'normal',
            fill: transomHeightColor,
            align: 'center',
            rotation: 90,
            offsetX: (h2Text.length * 6) / 2,
            listening: false,
        }));
    }
}

/**
 * Extract panel count from string
 */
function extractPanelCount(panelString) {
    if (!panelString) return 2;
    // If a number is provided directly, accept it
    if (typeof panelString === 'number' && isFinite(panelString)) {
        return Math.max(1, parseInt(panelString, 10));
    }

    // Handle string inputs: 'Single', '2 Panels', '3', etc.
    if (typeof panelString === 'string') {
        const trimmed = panelString.trim().toLowerCase();
        if (trimmed === 'single' || trimmed === 'single panel') return 1;
        const match = trimmed.match(/(\d+)/);
        if (match) return Math.max(1, parseInt(match[1], 10));
    }

    // Fallback default
    return 2;
}

/**
 * Parse panel configuration string
 */
function parsePanelConfiguration(configString, panelCount) {
    if (!configString) {
        return new Array(panelCount).fill('sliding');
    }

    // Remove descriptive text in parentheses
    let config = configString.replace(/\([^)]*\)/g, '').trim();
    
    // Split by pipe character
    const parts = config.split('|').map(p => p.trim()).filter(p => p.length > 0);
    
    const panelTypes = parts.map(part => {
        if (part.toUpperCase().includes('S') && !part.toUpperCase().includes('F')) {
            return 'sliding';
        } else if (part.toUpperCase().includes('F')) {
            return 'fixed';
        }
        return 'sliding';
    });

    // Ensure correct count
    if (panelTypes.length !== panelCount) {
        if (panelTypes.length < panelCount) {
            const pattern = [...panelTypes];
            while (panelTypes.length < panelCount) {
                panelTypes.push(...pattern);
            }
            panelTypes.splice(panelCount);
        } else {
            panelTypes.splice(panelCount);
        }
    }

    return panelTypes;
}

/**
 * Get glass style based on type and color
 */
function getGlassStyle(glassType, glassColor, styles = null) {
    const glassStyles = styles || window.glassStyles || {};
    const normalizedType = (glassType || '').toLowerCase();
    const normalizedColor = (glassColor || '').toLowerCase();
    
    // Try combined lookup
    const combinedKey = `${normalizedType} ${normalizedColor}`.trim();
    if (glassStyles[combinedKey]) {
        return glassStyles[combinedKey];
    }

    // Try color-specific variants
    if (normalizedColor === 'bronze' && glassStyles['bronze']) {
        return glassStyles['bronze'];
    }
    if (normalizedColor === 'clear' && glassStyles['clear']) {
        return glassStyles['clear'];
    }
    if (normalizedColor.includes('frosted') || normalizedColor.includes('smoked')) {
        return glassStyles['frosted'] || glassStyles['clear'];
    }
    if (normalizedColor.includes('smoked')){
        return glassStyles['smoked'] || glassStyles['clear'];
    }
    // Try type lookup
    if (glassStyles[normalizedType]) {
        return glassStyles[normalizedType];
    }

    // Default
    return glassStyles['clear'] || { fill: '#E0F2F1', opacity: 0.9 };
}

/**
 * Get frame style based on color/material
 */
function getFrameStyle(frameColor, styles = null) {
    const frameStyles = styles || window.frameStyles || {};
    
    // Handle array input (tags field can be an array)
    if (Array.isArray(frameColor)) {
        frameColor = frameColor[0] || '';
    }
    
    // Normalize the frame color string
    const normalized = (frameColor || '').toLowerCase().trim();
    
    // Direct match
    if (frameStyles[normalized]) {
        return frameStyles[normalized];
    }

    // Try to match partial strings (e.g., "powder coated white" matches "powder coated white")
    for (const [key, style] of Object.entries(frameStyles)) {
        const keyNormalized = key.toLowerCase().trim();
        if (normalized === keyNormalized || 
            normalized.includes(keyNormalized) || 
            keyNormalized.includes(normalized)) {
            return style;
        }
    }
    
    // Try common variations and mappings
    const colorMappings = {
        'powder coated white': ['powder coated white', 'powder-coated-white', 'white'],
        'analok': ['analok', 'hanalok'],
        'matte gray': ['matte gray', 'matte-gray', 'gray', 'grey'],
        'matte black': ['matte black', 'matte-black', 'black'],
        'wood finish': ['wood finish', 'wood-finish', 'wood']
    };
    
    for (const [baseColor, variations] of Object.entries(colorMappings)) {
        if (variations.some(v => normalized.includes(v) || v.includes(normalized))) {
            if (frameStyles[baseColor]) {
                return frameStyles[baseColor];
            }
        }
    }

    // Default fallback
    return frameStyles['white'] || frameStyles['powder coated white'] || { color: '#FFFFFF', width: 4 };
}

/**
 * Draw screen pattern overlay
 */
function drawScreenPattern(layer, x, y, width, height) {
    const patternSize = 8;
    const patternGroup = new Konva.Group({
        x: x,
        y: y,
        listening: false,
    });

    for (let py = 0; py < height; py += patternSize) {
        for (let px = 0; px < width; px += patternSize) {
            if ((px / patternSize + py / patternSize) % 2 === 0) {
                const dot = new Konva.Circle({
                    x: px + patternSize / 2,
                    y: py + patternSize / 2,
                    radius: 1,
                    fill: '#666666',
                    opacity: 0.3,
                    listening: false,
                });
                patternGroup.add(dot);
            }
        }
    }

    layer.add(patternGroup);
}

/**
 * Draw grid pattern overlay
 */
function drawGridPattern(layer, x, y, width, height, patternType) {
    const gridGroup = new Konva.Group({
        x: x,
        y: y,
        listening: false,
    });

    // Simple grid pattern
    const gridSpacing = Math.min(width, height) / 4;
    
    // Vertical lines
    for (let i = 1; i < 4; i++) {
        const line = new Konva.Line({
            points: [i * gridSpacing, 0, i * gridSpacing, height],
            stroke: '#CCCCCC',
            strokeWidth: 1,
            opacity: 0.5,
            listening: false,
        });
        gridGroup.add(line);
    }

    // Horizontal lines
    for (let i = 1; i < 4; i++) {
        const line = new Konva.Line({
            points: [0, i * gridSpacing, width, i * gridSpacing],
            stroke: '#CCCCCC',
            strokeWidth: 1,
            opacity: 0.5,
            listening: false,
        });
        gridGroup.add(line);
    }

    layer.add(gridGroup);
}

/**
 * Generic renderers for fallback
 */
function renderGenericWindow(productData, dimensions, layer, renderContext) {
    renderWindowsFixedGlass(productData, dimensions, layer, renderContext);
}

function renderGenericDoor(productData, dimensions, layer, renderContext) {
    renderDoorsSwing(productData, dimensions, layer, renderContext);
}

function renderGenericPartition(productData, dimensions, layer, renderContext) {
    renderPartitionsFixedGlass(productData, dimensions, layer, renderContext);
}

function renderGenericSpecialty(productData, dimensions, layer, renderContext) {
    renderSpecialtyTopGlass(productData, dimensions, layer, renderContext);
}

function renderGenericCommercial(productData, dimensions, layer, renderContext) {
    renderCommercialStorefront(productData, dimensions, layer, renderContext);
}

function renderGenericProduct(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    
    const actualRatio = width / height;
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

    const gStyle = getGlassStyle('Clear', '', glassStyles);
    
    const rect = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: totalWidth,
        height: totalHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: '#CCCCCC',
        strokeWidth: 2,
        listening: false,
    });
    layer.add(rect);
    
    // Draw dimension lines
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        renderProduct2D,
        renderWindowsSliding,
        renderWindowsAwning,
        renderWindowsCasement,
        renderWindowsFixedGlass,
        renderDoorsSliding,
        renderDoorsSwing,
        renderDoorsBifold,
        renderDoorsFrameless,
        renderDoorsPatchFitting,
        renderPartitionsFramelessGlass,
        renderPartitionsShowerEnclosure,
        renderPartitionsFixedGlass,
        renderSpecialtyMirrors,
        renderSpecialtyTopGlass,
        renderSpecialtyGlassBoard,
        renderCommercialStorefront,
        renderCommercialGlassBalcony,
        renderCommercialStairRailings,
    };
}

// Make available globally
if (typeof window !== 'undefined') {
    window.Comprehensive2DRenderer = {
        renderProduct2D,
        renderWindowsSliding,
        renderWindowsAwning,
        renderWindowsCasement,
        renderWindowsFixedGlass,
        renderDoorsSliding,
        renderDoorsSwing,
        renderDoorsBifold,
        renderDoorsFrameless,
        renderDoorsPatchFitting,
        renderPartitionsFramelessGlass,
        renderPartitionsShowerEnclosure,
        renderPartitionsFixedGlass,
        renderSpecialtyMirrors,
        renderSpecialtyTopGlass,
        renderSpecialtyGlassBoard,
        renderCommercialStorefront,
        renderCommercialGlassBalcony,
        renderCommercialStairRailings,
    };
}
