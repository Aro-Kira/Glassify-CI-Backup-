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
    // ✅ CRITICAL FIX: Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    console.log('[2D Renderer] Routing product:', {
        category: category,
        productType: productType,
        hasCustomization: Object.keys(customizationValues).length > 0
    });

    // Route to appropriate renderer based on product type
    if (category.includes('Windows')) {
        console.log('[2D Renderer] Routing to Windows renderer');
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
        console.log('[2D Renderer] Routing to Doors renderer');
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
        console.log('[2D Renderer] Routing to Partitions renderer');
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
        console.log('[2D Renderer] Routing to Specialty renderer for:', productType);
        if (productType.includes('Mirror')) {
            console.log('[2D Renderer] Rendering Mirrors');
            renderSpecialtyMirrors(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Top Glass')) {
            console.log('[2D Renderer] Rendering Top Glass');
            renderSpecialtyTopGlass(productData, dimensions, layer, renderContext);
        } else if (productType.includes('Glass Board')) {
            console.log('[2D Renderer] Rendering Glass Board');
            renderSpecialtyGlassBoard(productData, dimensions, layer, renderContext);
        } else {
            console.log('[2D Renderer] Rendering Generic Specialty');
            renderGenericSpecialty(productData, dimensions, layer, renderContext);
        }
    } else if (category.includes('Commercial')) {
        console.log('[2D Renderer] Routing to Commercial renderer');
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
        console.warn('[2D Renderer] Category not recognized, using generic renderer. Category:', category);
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
    // 1. EXTRACT CONTEXT & STYLES
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = renderContext || getGlobalStyles();
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || '2 Panels');
    const transomType = customizationValues.transomType || 'None';
    const trackSystem = customizationValues.trackSystem || '2 Tracks';
    const panelConfiguration = customizationValues.panelConfiguration || 'S | S (Sliding | Sliding)';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const glassThickness = customizationValues.glassThickness || '6mm';
    const screen = customizationValues.screen || 'Without Screen';

    // 2. SCALE CALCULATIONS (Canvas Constraints)
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

    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);
    const panelTypes = parsePanelConfiguration(panelConfiguration, numberOfPanels);

    // 3. TRANSOM CALCULATIONS (h1 vs h2 Logic)
    const hasTransom = transomType && transomType.toLowerCase() !== 'none';
    const isFixedTransomHead = hasTransom && transomType.toLowerCase().includes('head');
    const isFixedTransomSill = hasTransom && transomType.toLowerCase().includes('sill');

    // Get input values directly from DOM for calculation
    const h1Input = document.getElementById('input-h1');
    const h2Input = document.getElementById('input-h2');
    let h1Val = h1Input ? parseFloat(h1Input.value) : 0;
    let h2Val = h2Input ? parseFloat(h2Input.value) : 0;
    const totalInputHeight = height; // This is the master height (e.g., 45in)

    let finalMainHeight, finalTransomHeight;

    if (hasTransom) {
        // SCALING GUARD: Ensure h1 + h2 = Total Height
        const sum = h1Val + h2Val;
        if (sum > totalInputHeight || sum < totalInputHeight) {
            // Scale proportionally to fit the total height
            const scaleFactor = totalInputHeight / (sum || 1);
            h1Val = h1Val * scaleFactor;
            h2Val = h2Val * scaleFactor;
        }

        // Convert to canvas pixel dimensions
        finalMainHeight = (h1Val / totalInputHeight) * totalHeight;
        finalTransomHeight = (h2Val / totalInputHeight) * totalHeight;
    } else {
        finalMainHeight = totalHeight;
        finalTransomHeight = 0;
    }

    const panelWidth = totalWidth / numberOfPanels;

    // 4. RENDERING PANELS
    for (let i = 0; i < numberOfPanels; i++) {
        const panelX = offsetX + (i * panelWidth);
        const panelType = panelTypes[i] || 'sliding';

        // Draw Transom Section
        if (hasTransom) {
            const transomY = isFixedTransomHead ? offsetY : offsetY + finalMainHeight;
            layer.add(new Konva.Rect({
                x: panelX, y: transomY, width: panelWidth, height: finalTransomHeight,
                fill: '#5faaff', opacity: 1, stroke: fStyle.color, strokeWidth: fStyle.width
            }));
            // Apply pattern to transom (use product glassType/color)
            applyGlassTypePattern(layer, panelX, transomY, panelWidth, finalTransomHeight, glassType, glassColor);
            // Transom Label 'F'
            addCenteredText(layer, panelX + panelWidth/2, transomY + finalTransomHeight/2, 'F', 14, '#FFFFFF');
        }

        // Draw Main Section
        const mainY = isFixedTransomHead ? offsetY + finalTransomHeight : offsetY;
        const isFixedMain = panelType === 'fixed';
        
        layer.add(new Konva.Rect({
            x: panelX, y: mainY, width: panelWidth, height: finalMainHeight,
            fill: isFixedMain ? '#5faaff' : gStyle.fill,
            opacity: isFixedMain ? 0.5 : gStyle.opacity,
            stroke: fStyle.color, strokeWidth: fStyle.width
        }));

        // Always apply glass type / visual pattern to the main section (including fixed panels)
        applyGlassTypePattern(layer, panelX, mainY, panelWidth, finalMainHeight, glassType, glassColor);

        // Main Label 'S' or 'F'
        addCenteredText(layer, panelX + panelWidth/2, mainY + finalMainHeight/2, isFixedMain ? 'F' : 'S', 20, isFixedMain ? '#FFFFFF' : '#333333');

        // Vertical Divider
        if (i < numberOfPanels - 1) {
            layer.add(new Konva.Line({
                points: [panelX + panelWidth, offsetY, panelX + panelWidth, offsetY + totalHeight],
                stroke: fStyle.color, strokeWidth: fStyle.width * 1.5
            }));
        }
    }

    // Horizontal Transom Divider
    if (hasTransom) {
        const dividerY = isFixedTransomHead ? offsetY + finalTransomHeight : offsetY + finalMainHeight;
        layer.add(new Konva.Line({
            points: [offsetX, dividerY, offsetX + totalWidth, dividerY],
            stroke: fStyle.color, strokeWidth: fStyle.width * 1.5
        }));
    }

    // 5. ANNOTATIONS (h1, h2, and Metadata)
    if (hasTransom) {
        drawTransomAnnotations(layer, offsetX, offsetY, finalMainHeight, finalTransomHeight, isFixedTransomHead, h1Val, h2Val, unit);
    }

    // Base Width/Height annotations
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, width, unit, height, unit, renderContext);

    // Thickness & Frame Metadata
    const annotationText = `Thickness: ${glassThickness}  |  Frame: ${frameColor}`;
    layer.add(new Konva.Text({
        x: offsetX + totalWidth / 2, y: offsetY + totalHeight + 25,
        text: annotationText, fontSize: 11, fontStyle: 'bold', fill: '#555', align: 'center'
    }).offsetX(annotationText.length * 3));



    // 6. SCREEN PATTERN
    if (screen && screen.toLowerCase().includes('with screen')) {
        drawScreenPattern(layer, offsetX, offsetY, totalWidth, totalHeight);
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
}

// HELPER: DRAW H1 & H2 DIMENSIONS
function drawTransomAnnotations(layer, offsetX, offsetY, mainH_px, transH_px, isHead, h1Val, h2Val, unit) {
    const xPos = offsetX - 10;
    // Determine Y centers based on Sill vs Head
    const h1_Y = isHead ? offsetY + transH_px + (mainH_px / 2) : offsetY + (mainH_px / 2);
    const h2_Y = isHead ? offsetY + (transH_px / 2) : offsetY + mainH_px + (transH_px / 2);

    const drawLine = (yCenter, heightPx, label, color) => {
        if (heightPx <= 0) return;
        const top = yCenter - heightPx/2;
        const bottom = yCenter + heightPx/2;

        layer.add(new Konva.Line({ points: [xPos, top, xPos, bottom], stroke: color, strokeWidth: 1, dash: [4, 2] }));
        layer.add(new Konva.Line({ points: [xPos-5, top, xPos+5, top], stroke: color, strokeWidth: 1 }));
        layer.add(new Konva.Line({ points: [xPos-5, bottom, xPos+5, bottom], stroke: color, strokeWidth: 1 }));
        const txt = new Konva.Text({
            x: xPos - 15, y: yCenter, text: `${label.toFixed(1)}${unit}`,
            fontSize: 11, fontStyle: 'bold', fill: color, rotation: -90, align: 'center'
        });
        txt.offsetX(txt.width() / 2);
        txt.offsetY(txt.height() / 2);
        layer.add(txt);
    };

    drawLine(h1_Y, mainH_px, h1Val, '#4A90E2'); // h1 (Sliding/Blue)
    drawLine(h2_Y, transH_px, h2Val, '#28a745'); // h2 (Transom/Green)
}

// HELPER: CENTERED TEXT
function addCenteredText(layer, x, y, string, size, color) {
    const t = new Konva.Text({ x: x, y: y, text: string, fontSize: size, fontStyle: 'bold', fill: color });
    t.offsetX(t.width() / 2);
    t.offsetY(t.height() / 2);
    layer.add(t);
}

/**
 * Render Windows Awning configuration
 */
function renderWindowsAwning(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

            // Apply glass type pattern
            applyGlassTypePattern(layer, panelX, panelY, panelWidth, panelHeight, glassType, glassColor);

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


// Helper: "//" reflection marks - top-left and bottom-right corners, highly visible with adaptive colors
// IMPORTANT: These are drawn in a SEPARATE GROUP with full opacity to avoid glass panel opacity affecting visibility
function drawGlassReflections(layer, x, y, w, h, glassColor) {
    // Create a SEPARATE GROUP for reflections with NO opacity inheritance from glass
    const reflectionGroup = new Konva.Group({ 
        x: 0, 
        y: 0,
        listening: false,
        // CRITICAL: Group has full opacity so child lines aren't dimmed by parent glass opacity
        opacity: 1
    });
    
    // Determine stroke color based on glass color (adaptive for contrast)
    let strokeColor = '#000000'; // Default to black
    
    if (glassColor) {
        const gc = String(glassColor).toLowerCase();
        // Light colors = use black stroke
        if (gc.includes('clear')|| gc.includes('Clear') || gc.includes('frosted') || gc.includes('light') ) {
            strokeColor = '#000000';
        }
        else if (gc.includes('bronze')) {
            strokeColor = '#00000070';
        }
        // Dark colors = use white stroke
        else if (gc.includes('smoked') || gc.includes('tinted') || gc.includes('dark')) {
            strokeColor = '#00000070';
        }
    }
    
    // Maximum visibility: thick stroke, FULL opacity (not affected by glass opacity)
    const config = { stroke: strokeColor, strokeWidth: 4, opacity: 1 };
    
    // TOP-LEFT CORNER
    const tlX = x + 15;
    const tlY = y + 15;
    reflectionGroup.add(new Konva.Line({ points: [tlX - 12, tlY + 12, tlX + 4, tlY - 4], ...config }));
    reflectionGroup.add(new Konva.Line({ points: [tlX - 4, tlY + 12, tlX + 12, tlY - 4], ...config }));
    
    // BOTTOM-RIGHT CORNER
    const brX = x + w - 15;
    const brY = y + h - 15;
    reflectionGroup.add(new Konva.Line({ points: [brX - 12, brY + 12, brX + 4, brY - 4], ...config }));
    reflectionGroup.add(new Konva.Line({ points: [brX - 4, brY + 12, brX + 12, brY - 4], ...config }));
    
    // Add to layer
    layer.add(reflectionGroup);
    
    // Move to top of layer so it's rendered last (on top)
    reflectionGroup.moveToTop();
}

// Helper: Bottom Text Annotation
function renderBottomLabel(layer, offsetX, offsetY, totalWidth, totalHeight, thickness, frameColor) {
    const formatThickness = String(thickness).replace(/mm$/i, '') + 'mm';
    const label = `Thickness: ${formatThickness}  |  Frame: ${frameColor}`;
    layer.add(new Konva.Text({
        x: offsetX, y: offsetY + totalHeight + 25,
        width: totalWidth, text: label,
        fontSize: 12, fontStyle: 'bold', fontFamily: 'Arial',
        fill: '#444', align: 'center', listening: false
    }));
}

/* function renderWindowsCasement(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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
} */

/**
 * Render Windows Fixed Glass configuration
 */


function renderWindowsCasement(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
    // ✅ FIXED: Use same pattern as sliding/awning to ensure proper glass type/color separation
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Hanalok';
    const transomType = customizationValues.transomType || 'None';
    const numberOfPanels = parseInt(customizationValues.panelConfiguration) || 1;
    const thickness = customizationValues.thickness || '6mm';
    
    // Calculate Drawing Bounds
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

    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);

    // --- H1/H2 LOGIC PORTED FROM SLIDING ---
    const hasTransom = transomType && transomType.toLowerCase() !== 'none';
    const originalHeight = productData.originalHeight || dimensions.height;
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    let h1Value = null; // Main section height
    let h2Value = null; // Transom height
    
    const h1Input = typeof document !== 'undefined' ? document.getElementById('input-h1') : null;
    const h2Input = typeof document !== 'undefined' ? document.getElementById('input-h2') : null;
    
    if (h1Input && h1Input.value) h1Value = parseFloat(h1Input.value);
    if (h2Input && h2Input.value) h2Value = parseFloat(h2Input.value);

    const unitMap = { 'in': 25.4, 'cm': 10, 'mm': 1 };
    const toMm = (val, unit) => val * (unitMap[unit.toLowerCase()] || 25.4);
    
    let transomHeight = 0;
    let mainHeight = totalHeight;

    if (hasTransom) {
        const totalHeightInMm = toMm(originalHeight, heightUnit);
        let transomHeightMm = null;
        let mainHeightMm = null;

        if (h1Value) mainHeightMm = toMm(h1Value, heightUnit);
        if (h2Value) transomHeightMm = toMm(h2Value, heightUnit);

        // Auto-calculation logic
        if (transomHeightMm && !mainHeightMm) {
            mainHeightMm = Math.max(0.1, totalHeightInMm - transomHeightMm);
        } else if (mainHeightMm && !transomHeightMm) {
            transomHeightMm = Math.max(0.1, totalHeightInMm - mainHeightMm);
        } else if (!transomHeightMm && !mainHeightMm) {
            transomHeightMm = totalHeightInMm * 0.3;
            mainHeightMm = totalHeightInMm * 0.7;
        }

        const transomRatio = transomHeightMm / (transomHeightMm + mainHeightMm);
        const mainRatio = mainHeightMm / (transomHeightMm + mainHeightMm);

        transomHeight = totalHeight * transomRatio;
        mainHeight = totalHeight * mainRatio;
    }

    const panelWidth = totalWidth / numberOfPanels;

    // 1. Draw Transom Panels (Top)
    if (hasTransom) {
        for (let i = 0; i < numberOfPanels; i++) {
            const tx = offsetX + (i * panelWidth);
            
            // Draw glass fill rect with reduced opacity so pattern shows through
            layer.add(new Konva.Rect({
                x: tx, y: offsetY,
                width: panelWidth, height: transomHeight,
                fill: gStyle.fill, opacity: gStyle.opacity * 0.7, stroke: fStyle.color,
                strokeWidth: fStyle.width, listening: false
            }));
            
            // Then apply glass type pattern on top for visibility
            applyGlassTypePattern(layer, tx, offsetY, panelWidth, transomHeight, glassType, glassColor);
        }
    }

    // 2. Draw Main Casement Panels (Bottom)
    const mainOffsetY = offsetY + transomHeight;
    for (let i = 0; i < numberOfPanels; i++) {
        const px = offsetX + (i * panelWidth);
        
        // Draw glass fill rect with reduced opacity so pattern shows through
        layer.add(new Konva.Rect({
            x: px, y: mainOffsetY,
            width: panelWidth, height: mainHeight,
            fill: gStyle.fill, opacity: gStyle.opacity * 0.7, stroke: fStyle.color,
            strokeWidth: fStyle.width, listening: false
        }));

        // Then apply glass type pattern on top for visibility
        applyGlassTypePattern(layer, px, mainOffsetY, panelWidth, mainHeight, glassType, glassColor);

        // Dashed "V" opening indicator (Casement Style)
        const padding = 15;
        layer.add(new Konva.Line({
            points: [
                px + padding, mainOffsetY + padding,
                px + panelWidth - padding, mainOffsetY + (mainHeight / 2),
                px + padding, mainOffsetY + mainHeight - padding
            ],
            stroke: fStyle.color, strokeWidth: 1.5, dash: [8, 5], listening: false
        }));
    }

    // 3. Final Outer Frame
    layer.add(new Konva.Rect({
        x: offsetX - 2, y: offsetY - 2,
        width: totalWidth + 4, height: totalHeight + 4,
        stroke: fStyle.color, strokeWidth: fStyle.width, listening: false
    }));

    // 4. Annotations & Dimensions
    if (hasTransom) {
        drawTransomDimensions(layer, offsetX, offsetY, totalWidth, totalHeight,
                                true, transomHeight, mainHeight,
                                originalHeight, heightUnit, renderContext);
    }

    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, 
                       width, dimensions.unit, height, dimensions.unit, renderContext);

    renderBottomLabel(layer, offsetX, offsetY, totalWidth, totalHeight, thickness, frameColor);
}

function renderWindowsFixedGlass(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Apply glass type pattern (pass panel fill to allow pattern color adaptation)
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, gStyle.fill);

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
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = renderContext || getGlobalStyles();
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
    // Extract customization values
    const numberOfPanels = extractPanelCount(customizationValues.numberOfPanels || '2 Panels');
    const transomType = customizationValues.transomType || 'None';
    const trackSystem = customizationValues.trackSystem || '2 Tracks';
    const panelConfiguration = customizationValues.panelConfiguration || 'S | S (Sliding | Sliding)';
    const frameColor = customizationValues.frameColor || 'Powder Coated White';
    const glassType = customizationValues.glassType || 'Ordinary';
    const glassColor = customizationValues.glassColor || 'Clear';
    const glassThickness = customizationValues.glassThickness || '6mm';
    const screen = customizationValues.screen || 'Without Screen';

    // Calculate canvas ratios
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

    const gStyle = getGlassStyle(glassType, glassColor, glassStyles);
    const fStyle = getFrameStyle(frameColor, frameStyles);
    const panelTypes = parsePanelConfiguration(panelConfiguration, numberOfPanels);
    
    const hasTransom = transomType && transomType.toLowerCase() !== 'none';
    const isFixedTransomHead = hasTransom && transomType.toLowerCase().includes('head');
    const isFixedTransomSill = hasTransom && transomType.toLowerCase().includes('sill');

    const originalHeight = productData.originalHeight || dimensions.height;
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    
    // --- TRANSOM HEIGHT LOGIC ---
    let h1Value = null; let h2Value = null;
    const h1Input = typeof document !== 'undefined' ? document.getElementById('input-h1') : null;
    const h2Input = typeof document !== 'undefined' ? document.getElementById('input-h2') : null;

    if (h1Input && h1Input.value) h1Value = parseFloat(h1Input.value);
    if (h2Input && h2Input.value) h2Value = parseFloat(h2Input.value);

    const unitMap = { 'in': 25.4, 'cm': 10, 'mm': 1 };
    function convertToMm(val, u) { return val * (unitMap[u.toLowerCase()] || 25.4); }
    
    let transomHeight = 0;
    let mainHeight = totalHeight;
    let transomY = 0;

    if (hasTransom) {
        const totalHeightMm = convertToMm(originalHeight, heightUnit);
        let tHeightMm = h2Value ? convertToMm(h2Value, heightUnit) : (h1Value ? totalHeightMm - convertToMm(h1Value, heightUnit) : totalHeightMm * 0.3);
        let sHeightMm = totalHeightMm - tHeightMm;

        const tRatio = tHeightMm / totalHeightMm;
        const sRatio = sHeightMm / totalHeightMm;

        transomHeight = totalHeight * tRatio;
        mainHeight = totalHeight * sRatio;
        transomY = isFixedTransomHead ? offsetY : offsetY + mainHeight;

        // Set display values for annotations if they weren't explicitly provided
        if (h1Value === null) h1Value = originalHeight * sRatio;
        if (h2Value === null) h2Value = originalHeight * tRatio;
    }

    const panelWidth = totalWidth / numberOfPanels;

    // --- DRAWING: TRANSOM ---
    if (hasTransom) {
        for (let i = 0; i < numberOfPanels; i++) {
            const panelX = offsetX + (i * panelWidth);
            layer.add(new Konva.Rect({
                x: panelX, y: transomY, width: panelWidth, height: transomHeight,
                fill: '#4A90E2', opacity: 0.8, stroke: fStyle.color, strokeWidth: fStyle.width
            }));
            // Apply pattern to transom
            applyGlassTypePattern(layer, panelX, transomY, panelWidth, transomHeight, glassType, glassColor);
            addCenteredText(layer, panelX + panelWidth/2, transomY + transomHeight/2, 'F', Math.max(10, transomHeight/5), '#FFF');
        }
    }

    // --- DRAWING: MAIN PANELS ---
    const mainY = (hasTransom && isFixedTransomHead) ? offsetY + transomHeight : offsetY;
    for (let i = 0; i < numberOfPanels; i++) {
        const panelX = offsetX + (i * panelWidth);
        const type = panelTypes[i] || 'sliding';
        
        layer.add(new Konva.Rect({
            x: panelX, y: mainY, width: panelWidth, height: mainHeight,
            fill: type === 'fixed' ? '#4A90E2' : gStyle.fill,
            opacity: type === 'fixed' ? 0.8 : gStyle.opacity,
            stroke: fStyle.color, strokeWidth: fStyle.width
        }));

        // Always apply glass type / visual pattern (including fixed panels)
        applyGlassTypePattern(layer, panelX, mainY, panelWidth, mainHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

        const label = type === 'fixed' ? 'F' : 'S';
        const labelColor = type === 'fixed' ? '#FFF' : '#333';
        addCenteredText(layer, panelX + panelWidth/2, mainY + mainHeight/2, label, Math.min(mainHeight * 0.18, 24), labelColor);
    }

    // --- DRAWING: ANNOTATIONS & DIMENSIONS ---
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight, dimensions.width, dimensions.unit, originalHeight, heightUnit, renderContext);
    
    if (hasTransom) {
        drawTransomAnnotations(layer, offsetX, offsetY, mainHeight, transomHeight, isFixedTransomHead, h1Value, h2Value, heightUnit);
    }

    // Screen Pattern
    if (screen && screen.toLowerCase().includes('with screen')) {
        drawScreenPattern(layer, offsetX, mainY, totalWidth, mainHeight);
    }

    const annotationText = `Thickness: ${glassThickness}  |  Frame: ${frameColor}`;
    layer.add(new Konva.Text({
        x: offsetX + totalWidth / 2, y: offsetY + totalHeight + 25,
        text: annotationText, fontSize: 11, fontStyle: 'bold', fill: '#555', align: 'center'
    }).offsetX(annotationText.length * 3));

}


/**
 * Render Doors Swing Door configuration
 */
function renderDoorsSwing(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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
    // Apply pattern to fixed left panel
    applyGlassTypePattern(layer, offsetX, offsetY, panelWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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

    // Apply glass type pattern to folding panel
    applyGlassTypePattern(layer, offsetX + panelWidth, offsetY, panelWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    const { DRAWING_SIZE, STAGE_SIZE } = ctx;
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    const glassType = customizationValues.glassType || 'Clear';
    const doorType = customizationValues.doorType || 'Single swing';
    const doorSwing = customizationValues.doorSwing || 'Left swing';
    const fixedPanels = customizationValues.fixedPanels || 'None';
    const hardwareFinish = customizationValues.hardwareFinish || 'Polished Chrome/Stainless Steel';

    const getVal = (id) => {
        const el = document.getElementById(`input-${id}`);
        if (el && el.value) {
            return parseFloat(el.value);
        }
        return null;
    };

    const isVisible = (id) => {
        const group = document.getElementById(`input-group-${id}`);
        return group && !group.classList.contains('hidden-step');
    };

    const h1Val = getVal('h1'); // Door Height
    const h2Val = getVal('h2'); // Transom Height
    const w1Val = getVal('w1'); // Door Width (Now correctly mapped to w1)
    const w2Val = getVal('w2'); // Left Panel Width
    const w3Val = getVal('w3'); // Right Panel Width

    const h1Visible = isVisible('h1');
    const h2Visible = isVisible('h2');
    const w1Visible = isVisible('w1');
    const w2Visible = isVisible('w2');
    const w3Visible = isVisible('w3');
    
    // DEBUG: Log all values
    console.log('🚪 [renderDoorsFrameless] h1Val:', h1Val, 'h1Visible:', h1Visible, 'h2Val:', h2Val, 'h2Visible:', h2Visible);
    console.log('🚪 [renderDoorsFrameless] w1Val:', w1Val, 'w1Visible:', w1Visible, 'w2Val:', w2Val, 'w2Visible:', w2Visible, 'w3Val:', w3Val, 'w3Visible:', w3Visible);

    const actualRatio = width / height;
    let totalWidth = actualRatio > 1 ? DRAWING_SIZE : DRAWING_SIZE * actualRatio;
    let totalHeight = actualRatio > 1 ? DRAWING_SIZE / actualRatio : DRAWING_SIZE;
    const startX = (STAGE_SIZE - totalWidth) / 2;
    const startY = (STAGE_SIZE - totalHeight) / 2;

    const gStyle = getGlassStyle(glassType);
    const hardwareColor = hardwareFinish.toLowerCase().includes('black') ? '#333333' : '#A0A0A0';

    const hasTransom = fixedPanels === 'Transom Only' || fixedPanels === 'Both';
    let transomHeight, mainAreaHeight;

    if (hasTransom && h1Val && h2Val) {
        const ratio = h2Val / (h1Val + h2Val);
        transomHeight = totalHeight * ratio;
        mainAreaHeight = totalHeight - transomHeight;
    } else {
        transomHeight = hasTransom ? totalHeight * 0.20 : 0;
        mainAreaHeight = totalHeight - transomHeight;
    }

    const isDoubleDoor = doorType.toLowerCase().includes('double');
    const isLeftHinged = doorSwing.toLowerCase().includes('left');
    let panels = [];

    // Helper: w1 (Door) is always the primary reference for the ratio
    const getWidthRatio = (val, fallback) => {
        const sum = (w1Val || 0) + (w2Val || 0) + (w3Val || 0);
        return (val && sum > 0) ? (val / sum) : fallback;
    };

    if (fixedPanels === '2 Panels' || fixedPanels === 'Both') {
        const leftW = totalWidth * getWidthRatio(w2Val, 0.25);
        const rightW = totalWidth * getWidthRatio(w3Val, 0.25);
        const doorW = totalWidth - leftW - rightW;
        
        panels.push({ type: 'fixed', width: leftW, label: 'w2', dimensionValue: w2Val }); // Left
        addDoorLeafs(panels, doorW, isDoubleDoor, isLeftHinged, 'w1', w1Val); // Door
        panels.push({ type: 'fixed', width: rightW, label: 'w3', dimensionValue: w3Val }); // Right

    } else if (fixedPanels === 'Fixed Side (Left)') {
        const leftW = totalWidth * getWidthRatio(w2Val, 0.30);
        const doorW = totalWidth - leftW;
        panels.push({ type: 'fixed', width: leftW, label: 'w2', dimensionValue: w2Val });
        addDoorLeafs(panels, doorW, isDoubleDoor, isLeftHinged, 'w1', w1Val);

    } else if (fixedPanels === 'Fixed Side (Right)') {
        const rightW = totalWidth * getWidthRatio(w3Val, 0.30);
        const doorW = totalWidth - rightW;
        addDoorLeafs(panels, doorW, isDoubleDoor, isLeftHinged, 'w1', w1Val);
        panels.push({ type: 'fixed', width: rightW, label: 'w3', dimensionValue: w3Val });

    } else {
        addDoorLeafs(panels, totalWidth, isDoubleDoor, isLeftHinged, 'w1', w1Val);
    }

    function addDoorLeafs(targetArray, availableWidth, isDouble, leftHinged, label, dimensionValue) {
        if (isDouble) {
            targetArray.push({ type: 'door', width: availableWidth / 2, hinge: 'left', label: label, dimensionValue: dimensionValue });
            targetArray.push({ type: 'door', width: availableWidth / 2, hinge: 'right', label: label, dimensionValue: dimensionValue });
        } else {
            targetArray.push({ type: 'door', width: availableWidth, hinge: leftHinged ? 'left' : 'right', label: label, dimensionValue: dimensionValue });
        }
    }

    if (hasTransom) {
        drawFixedPanel(startX, startY, totalWidth, transomHeight);
        // Always show h2 annotation if h2 has a value, otherwise use fallback
        const h2Display = h2Val !== null && h2Val > 0 ? h2Val.toFixed(2) : (height * 0.2).toFixed(1);
        if (h2Visible || h2Val !== null) {
            console.log('🚪 [Annotation] Drawing h2 with value:', h2Display, 'visible:', h2Visible);
            drawSubDimension(layer, startX - 20, startY, transomHeight, 'h2', h2Display, 'vertical');
        }
    }

    let currentX = startX;
    const mainY = startY + transomHeight;
    // Always show h1 annotation if h1 has a value, otherwise use fallback
    const h1Display = h1Val !== null && h1Val > 0 ? h1Val.toFixed(2) : (height - (hasTransom ? height*0.2 : 0)).toFixed(1);
    if (h1Visible || h1Val !== null) {
        console.log('🚪 [Annotation] Drawing h1 with value:', h1Display, 'visible:', h1Visible);
        drawSubDimension(layer, startX - 20, mainY, mainAreaHeight, 'h1', h1Display, 'vertical');
    }

    panels.forEach(p => {
        if (p.type === 'door') {
            drawDoorPanel(currentX, mainY, p.width, mainAreaHeight, p.hinge);
        } else {
            drawFixedPanel(currentX, mainY, p.width, mainAreaHeight);
        }
        
        if (p.label) {
            // Always draw annotation if input has a value OR is visible
            let displayVal = "—"; // default fallback
            
            if (p.label === 'w1') {
                // Use dimensionValue if available (actual input value), otherwise use calculated value
                displayVal = p.dimensionValue !== null && p.dimensionValue > 0 ? p.dimensionValue.toFixed(2) : "Door";
                if (p.dimensionValue !== null || w1Visible) {
                    console.log('🚪 [Annotation] Drawing w1 with dimensionValue:', p.dimensionValue, 'displayVal:', displayVal, 'visible:', w1Visible);
                    drawSubDimension(layer, currentX, mainY + mainAreaHeight + 20, p.width, p.label, displayVal, 'horizontal');
                }
            } else if (p.label === 'w2') {
                displayVal = p.dimensionValue !== null && p.dimensionValue > 0 ? p.dimensionValue.toFixed(2) : "—";
                if (p.dimensionValue !== null || w2Visible) {
                    console.log('🚪 [Annotation] Drawing w2 with dimensionValue:', p.dimensionValue, 'displayVal:', displayVal, 'visible:', w2Visible);
                    drawSubDimension(layer, currentX, mainY + mainAreaHeight + 20, p.width, p.label, displayVal, 'horizontal');
                }
            } else if (p.label === 'w3') {
                displayVal = p.dimensionValue !== null && p.dimensionValue > 0 ? p.dimensionValue.toFixed(2) : "—";
                if (p.dimensionValue !== null || w3Visible) {
                    console.log('🚪 [Annotation] Drawing w3 with dimensionValue:', p.dimensionValue, 'displayVal:', displayVal, 'visible:', w3Visible);
                    drawSubDimension(layer, currentX, mainY + mainAreaHeight + 20, p.width, p.label, displayVal, 'horizontal');
                }
            }
        }
        currentX += p.width;
    });

    drawDimensionLines(layer, startX, startY, totalWidth, totalHeight, width, 'in', height, 'in', renderContext);

    function drawSubDimension(layer, x, y, length, label, value, orientation) {
        // Corrected Color Mapping to match your screenshots
        let color = '#999'; 
        if (orientation === 'vertical') {
            color = (label === 'h1') ? '#00AA00' : '#FF4444'; // h1=Green, h2=Red
        } else {
            if (label === 'w1') color = '#00aeff'; // Door = Blue
            if (label === 'w2') color = '#ffa600'; // Left = Orange
            if (label === 'w3') color = '#ff00dd'; // Right = Yellow
        }
        
        const points = orientation === 'vertical' ? [x, y, x, y + length] : [x, y, x + length, y];
        layer.add(new Konva.Line({ points: points, stroke: color, strokeWidth: 2, dash: [2, 2] }));

        layer.add(new Konva.Text({
            x: orientation === 'vertical' ? x - 45 : x + length / 2 - 20,
            y: orientation === 'vertical' ? y + length / 2 - 5 : y + 5,
            text: `${label}: ${value}`,
            fontSize: 10,
            fontStyle: 'bold',
            fill: color,
            listening: false
        }));
    }

    function drawDoorPanel(x, y, w, h, hingeSide) {
        layer.add(new Konva.Rect({ x, y, width: w, height: h, fill: gStyle.fill, opacity: gStyle.opacity, stroke: '#ccc', strokeWidth: 1 }));
        
        // Apply glass type pattern
        applyGlassTypePattern(layer, x, y, w, h, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));
        
        const fitW = 20, fitH = 8, fitX = hingeSide === 'left' ? x : x + w - fitW;
        layer.add(new Konva.Rect({ x: fitX, y: y, width: fitW, height: fitH, fill: hardwareColor }));
        layer.add(new Konva.Rect({ x: fitX, y: y + h - fitH, width: fitW, height: fitH, fill: hardwareColor }));
        const handleX = hingeSide === 'left' ? x + w - 15 : x + 15;
        layer.add(new Konva.Rect({ x: handleX - 2, y: y + h * 0.3, width: 4, height: h * 0.4, fill: hardwareColor, cornerRadius: 2 }));
        const midY = y + (h / 2);
        const pts = hingeSide === 'left' ? [x, midY, x + w, y, x + w, y + h, x, midY] : [x + w, midY, x, y, x, y + h, x + w, midY];
        layer.add(new Konva.Line({ points: pts, stroke: 'red', strokeWidth: 1.2, dash: [6, 4] }));
    }

    function drawFixedPanel(x, y, w, h) {
        layer.add(new Konva.Rect({ x, y, width: w, height: h, fill: gStyle.fill, opacity: gStyle.opacity, stroke: '#ccc', strokeWidth: 1 }));
        // Apply glass type pattern for fixed panel
        applyGlassTypePattern(layer, x, y, w, h, glassType, (gStyle && gStyle.fill) || '');
        const cW = 12;
        layer.add(new Konva.Rect({ x: x + (w - cW) / 2, y, width: cW, height: 4, fill: hardwareColor }));
        layer.add(new Konva.Rect({ x: x + (w - cW) / 2, y: y + h - 4, width: cW, height: 4, fill: hardwareColor }));
    }
}

// ============================================================================
// HELPER FUNCTION: Update input visibility for door sub-dimensions
// ============================================================================
/**
 * Function to update input visibility for h1, h2, w1, w2, w3 inputs
 * Called when Fixed Panels selection changes in renderDoorsFrameless
 * @param {string} selectedOption - The selected Fixed Panels option
 */
function updateInputVisibility(selectedOption) {
    console.log('🚪 [updateInputVisibility] Called with option:', selectedOption);
    
    const allGroups = ['h1', 'h2', 'w1', 'w2', 'w3'];
    const normalizedOption = selectedOption ? selectedOption.toString().trim() : '';
    
    const visibilityMap = {
        'None': [],
        'Fixed Side (Left)': ['w1', 'w2'],
        'Fixed Side (Right)': ['w1', 'w3'],
        '2 Panels': ['w1', 'w2', 'w3'],
        'Transom Only': ['h1', 'h2'],   
        'Both': ['h1', 'h2', 'w1', 'w2', 'w3']
    };

    let visibleInputs = visibilityMap[normalizedOption] || [];
    console.log('🚪 [updateInputVisibility] Normalized option:', normalizedOption);
    console.log('🚪 [updateInputVisibility] Visible inputs should be:', visibleInputs);
    
    // NOTE: We DO NOT touch the main .dimensions-container (Height/Width inputs)
    // Those always stay visible. We only manage the sub-dimensions (h1, h2, w1, w2, w3)

    // 2. Show/Hide individual sub-dimension groups
    allGroups.forEach(id => {
        const group = document.getElementById(`input-group-${id}`);
        console.log(`🚪 [updateInputVisibility] input-group-${id} found:`, !!group);
        
        if (group) {
            if (visibleInputs.includes(id)) {
                group.classList.remove('hidden-step');
                // Force visibility with inline styles
                group.style.display = 'flex';
                group.style.visibility = 'visible';
                group.style.opacity = '1';
                console.log(`🚪 [updateInputVisibility] ✅ input-group-${id} made VISIBLE`);
            } else {
                group.classList.add('hidden-step');
                // Force hidden with inline styles
                group.style.display = 'none';
                console.log(`🚪 [updateInputVisibility] ❌ input-group-${id} made HIDDEN`);
                const input = document.getElementById(`input-${id}`);
                if (input) input.value = ''; 
            }
        } else {
            console.log(`🚪 [updateInputVisibility] ⚠️ input-group-${id} element NOT found in DOM`);
        }
    });
    
    console.log('🚪 [updateInputVisibility] Complete');
}

/* function renderDoorsFrameless(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;
    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Draw swing direction arrows (dashed diagonal lines)
    drawSwingDirectionArrows(layer, offsetX, offsetY, totalWidth, totalHeight, isDouble, isLeftSwing);

    // Draw dimension lines (width and height) for frameless door
    const originalWidth = productData.originalWidth || dimensions.width;
    const originalHeight = productData.originalHeight || dimensions.height;
    const widthUnit = productData.widthUnit || dimensions.unit || 'in';
    const heightUnit = productData.heightUnit || dimensions.unit || 'in';
    drawDimensionLines(layer, offsetX, offsetY, totalWidth, totalHeight,
                       originalWidth, widthUnit, originalHeight, heightUnit, renderContext);
} */

/**
 * Render Doors Patch Fitting configuration
 */
function renderDoorsPatchFitting(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;

    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    const series = customizationValues.series || 'Frameless Door';
    const glassType = customizationValues.glassType || 'Tempered';
    const glassColor = customizationValues.glassColor || 'Clear';
    const frameColor = customizationValues.frameColor || 'Stainless Mirror Finish';
    const thickness = customizationValues.thickness || '10mm-12mm';

    // Get door type and swing direction
    const doorType = customizationValues.doorType || 'Single swing';
    const doorSwing = customizationValues.doorSwing || 'Left swing';
    const isDouble = doorType.toLowerCase().includes('double');
    const isLeftSwing = doorSwing.toLowerCase().includes('left');

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

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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

    // Draw swing direction arrows (dashed diagonal lines)
    drawSwingDirectionArrows(layer, offsetX, offsetY, totalWidth, totalHeight, isDouble, isLeftSwing);
}

/**
 * Draw swing direction arrows for frameless doors
 * Double doors: Always show ">|<" pattern (outward pointing)
 * Single doors: "V" pattern based on swing direction
 */
function drawSwingDirectionArrows(layer, offsetX, offsetY, totalWidth, totalHeight, isDouble, isLeftSwing) {
    const arrowLength = Math.min(totalWidth, totalHeight) * 0.5; // 50% of smaller dimension for better visibility
    const strokeColor = '#FF0000'; // Bright red color for maximum visibility
    const strokeWidth = 3; // Thicker lines
    const dashPattern = [8, 4]; // Dash pattern

    if (isDouble) {
        // Double door: ">|<" pattern - always pointing outward, no swing direction consideration
        const centerY = offsetY + totalHeight / 2;

        // Left door arrow (always pointing right ">")
        const leftHingeX = offsetX;
        const leftHingeY = centerY;
        const leftArrowX = leftHingeX + arrowLength; // Always point right
        const leftArrowTopY = leftHingeY - arrowLength / 2;
        const leftArrowBottomY = leftHingeY + arrowLength / 2;

        // Right door arrow (always pointing left "<")
        const rightHingeX = offsetX + totalWidth;
        const rightHingeY = centerY;
        const rightArrowX = rightHingeX - arrowLength; // Always point left
        const rightArrowTopY = rightHingeY - arrowLength / 2;
        const rightArrowBottomY = rightHingeY + arrowLength / 2;

        // Draw left door arrow
        const leftArrowTop = new Konva.Line({
            points: [leftHingeX, leftHingeY, leftArrowX, leftArrowTopY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });
        const leftArrowBottom = new Konva.Line({
            points: [leftHingeX, leftHingeY, leftArrowX, leftArrowBottomY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });

        // Draw right door arrow
        const rightArrowTop = new Konva.Line({
            points: [rightHingeX, rightHingeY, rightArrowX, rightArrowTopY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });
        const rightArrowBottom = new Konva.Line({
            points: [rightHingeX, rightHingeY, rightArrowX, rightArrowBottomY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });

        layer.add(leftArrowTop);
        layer.add(leftArrowBottom);
        layer.add(rightArrowTop);
        layer.add(rightArrowBottom);
    } else {
        // Single door: "V" pattern
        const newarrowLength = Math.min(totalWidth, totalHeight) * 1;
        const hingeX = isLeftSwing ? offsetX : offsetX + totalWidth;
        const hingeY = offsetY + totalHeight / 2;
        const arrowX = hingeX + (isLeftSwing ? newarrowLength : -newarrowLength);
        const arrowTopY = hingeY - newarrowLength / 2;
        const arrowBottomY = hingeY + newarrowLength / 2;
       
        // Draw V-shaped arrow
        const arrowTop = new Konva.Line({
            points: [hingeX, hingeY, arrowX, arrowTopY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });
        const arrowBottom = new Konva.Line({
            points: [hingeX, hingeY, arrowX, arrowBottomY],
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            dash: dashPattern,
            listening: false,
        });

        layer.add(arrowTop);
        layer.add(arrowBottom);
    }
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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

            // Apply glass type pattern
            applyGlassTypePattern(layer, panelX, offsetY, panelWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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

        // Apply glass type pattern
        applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
    const series = customizationValues.series || 'Rectangle/Square Framed Mirror';
    const shape = customizationValues.shape || 'Rectangle';
    const cornerRadius = customizationValues.cornerRadius || 0;
    const frameType = customizationValues.frameType || 'Framed';
    const frameColor = customizationValues.frameColor || 'White';
    const glassType = customizationValues.glassType || 'Copper Free and Lead Free Mirror';
    const thickness = customizationValues.thickness || '6mm';
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
    const gStyle = getGlassStyle(glassType);
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

    // Apply glass type pattern to mirror
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
        // For frameless mirrors, show edge finish from the edgeFinish field
        let edgeFinish = customizationValues.edgeFinish || customizationValues.edgeWork || '';
        
        if (edgeFinish) {
            annotationParts.push(`Edge: ${edgeFinish}`);
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
        drawCornerRadiusAnnotationsForSpecialty(customizationValues, offsetX, offsetY, totalWidth, totalHeight, shape, layer);
    }
}

/**
 * Draw corner radius annotations on the Konva canvas for specialty products
 * Shows radius values and labels at each corner (used for mirrors, top glass, etc.)
 */
function drawCornerRadiusAnnotationsForSpecialty(customizationValues, offsetX, offsetY, windowWidth, windowHeight, shape, layer) {
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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    // Use same comprehensive logic as mirrors but with top glass defaults
    const series = customizationValues.series || 'Top Glass';
    const shape = customizationValues.shape || 'Rectangle';
    const cornerRadius = customizationValues.cornerRadius || 0;
    const frameType = customizationValues.frameType || 'Frameless'; // Top glass is typically frameless
    const frameColor = customizationValues.frameColor || 'Polished'; // Edge finish stored here for frameless
    const glassType = customizationValues.glassType || 'Clear'; // Top glass is typically clear
    const thickness = customizationValues.thickness || '6mm';
    const orientation = customizationValues.orientation || 'Horizontal'; // Top glass often horizontal
    const style = customizationValues.style || '';
    const gridPattern = customizationValues.gridPattern || '';
    const arrangement = customizationValues.arrangement || 'Individually';
    const lighting = customizationValues.lighting || '';
    const ledColorTemperature = customizationValues.ledColorTemperature || '';
    const control = customizationValues.control || '';
    const additionalFeatures = customizationValues.additionalFeatures || '';
    const mountingMethod = customizationValues.mountingMethod || 'Wall-mounted';
    const quantity = customizationValues.quantity || '';

    console.log('[2D Renderer] renderSpecialtyTopGlass - shape:', shape, 'glassType:', glassType, 'frameType:', frameType);

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
    const isFrameless = frameType.toLowerCase().includes('frameless');
    
    console.log('[2D Renderer] TopGlass styles loaded - gStyle:', gStyle, 'fStyle:', fStyle);

    // Draw glass based on shape
    let glassShape;
    const centerX = offsetX + totalWidth / 2;
    const centerY = offsetY + totalHeight / 2;
    const minRadius = Math.min(totalWidth, totalHeight) / 2;

    console.log('[2D Renderer] TopGlass dimensions - width:', width, 'height:', height, 'totalWidth:', totalWidth, 'totalHeight:', totalHeight, 'minRadius:', minRadius, 'gStyle:', gStyle);

    if (shape.toLowerCase().includes('round') || shape.toLowerCase().includes('circle')) {
        glassShape = new Konva.Circle({
            x: centerX,
            y: centerY,
            radius: minRadius,
            fill: gStyle.fill,
            opacity: gStyle.opacity,
            stroke: isFrameless ? 'transparent' : fStyle.color,
            strokeWidth: isFrameless ? 0 : fStyle.width,
            listening: false,
        });
        console.log('[2D Renderer] Created Circle shape - centerX:', centerX, 'centerY:', centerY, 'radius:', minRadius, 'fill:', gStyle.fill, 'opacity:', gStyle.opacity);
    } else if (shape.toLowerCase().includes('oval') || shape.toLowerCase().includes('ellipse')) {
        glassShape = new Konva.Ellipse({
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
        console.log('[2D Renderer] Created Ellipse shape - centerX:', centerX, 'centerY:', centerY, 'radiusX:', totalWidth / 2, 'radiusY:', totalHeight / 2);
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
        glassShape = new Konva.Rect({
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
    layer.add(glassShape);
    console.log('[2D Renderer] Added glassShape to layer');

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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

    // Draw dimension lines (width and height) for top glass
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
        // For frameless top glass, show edge finish from the edgeFinish field
        let edgeFinish = customizationValues.edgeFinish || customizationValues.edgeWork || '';
        
        if (edgeFinish) {
            annotationParts.push(`Edge: ${edgeFinish}`);
        }
    } else {
        // For framed top glass, show frame color
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
        drawCornerRadiusAnnotationsForSpecialty(customizationValues, offsetX, offsetY, totalWidth, totalHeight, shape, layer);
    }
}

/**
 * Render Specialty Glass Board configuration
 */
function renderSpecialtyGlassBoard(productData, dimensions, layer, renderContext) {
    const ctx = getRenderContext(renderContext);
    const { DRAWING_SIZE, STAGE_SIZE, glassStyles, frameStyles } = ctx;

    const { width, height, unit } = dimensions;
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};

    // Use same comprehensive logic as mirrors but with glass board defaults
    const series = customizationValues.series || 'Glass Board';
    const shape = customizationValues.shape || 'Rectangle';
    const cornerRadius = customizationValues.cornerRadius || 0;
    const frameType = customizationValues.frameType || 'Frameless'; // Glass boards are typically frameless
    const frameColor = customizationValues.frameColor || 'Polished'; // Edge finish stored here for frameless
    const glassType = customizationValues.glassType || 'Clear'; // Glass boards are typically clear
    const thickness = customizationValues.thickness || '6mm';
    const orientation = customizationValues.orientation || 'Vertical'; // Glass boards often vertical
    const style = customizationValues.style || '';
    const gridPattern = customizationValues.gridPattern || '';
    const arrangement = customizationValues.arrangement || 'Single';
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
    const gStyle = getGlassStyle(glassType);
    const fStyle = getFrameStyle(frameColor);
    const isFrameless = frameType.toLowerCase().includes('frameless');

    // Draw glass board based on shape
    let glassBoardShape;
    const centerX = offsetX + totalWidth / 2;
    const centerY = offsetY + totalHeight / 2;
    const minRadius = Math.min(totalWidth, totalHeight) / 2;

    if (shape.toLowerCase().includes('round') || shape.toLowerCase().includes('circle')) {
        glassBoardShape = new Konva.Circle({
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
        glassBoardShape = new Konva.Ellipse({
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
        glassBoardShape = new Konva.Rect({
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
    layer.add(glassBoardShape);

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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

    // Draw dimension lines (width and height) for glass board
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
        // For frameless glass board, show edge finish from the edgeFinish field
        let edgeFinish = customizationValues.edgeFinish || customizationValues.edgeWork || '';
        
        if (edgeFinish) {
            annotationParts.push(`Edge: ${edgeFinish}`);
        }
    } else {
        // For framed glass board, show frame color
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
        drawCornerRadiusAnnotationsForSpecialty(customizationValues, offsetX, offsetY, totalWidth, totalHeight, shape, layer);
    }
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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

        // Apply glass type pattern
        applyGlassTypePattern(layer, panelX, offsetY, panelWidth, totalHeight, glassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

        // Apply glass type pattern
        applyGlassTypePattern(layer, panelX, offsetY, panelWidth, totalHeight, safetyGlassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    // ✅ Use window.selectedCustomizationValues (live UI state) instead of productData.customizationValues (stale)
    const customizationValues = window.selectedCustomizationValues || {};
    
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

    // Apply glass type pattern
    applyGlassTypePattern(layer, offsetX, offsetY, totalWidth, totalHeight * 0.3, safetyGlassType, (typeof glassColor !== 'undefined' ? glassColor : (gStyle && gStyle.fill) || ''));

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
    const DEFAULT_GLASS = { fill: '#E0F2F1', opacity: 0.9 };
    let glassStyles = styles || window.glassStyles || {};
    
    // Ensure glassStyles has at least default values if empty
    if (!glassStyles || Object.keys(glassStyles).length === 0) {
        glassStyles = {
            'clear': { fill: '#E0F2F1', opacity: 0.9 },
            'tempered': { fill: '#E0F2F1', opacity: 0.9 },
            'laminated': { fill: '#CFD8DC', opacity: 0.95 },
            'double': { fill: '#B2DFDB', opacity: 0.9 },
            'low-e': { fill: '#Dcedc8', opacity: 0.85 },
            'tinted': { fill: '#546E7A', opacity: 0.7 },
            'frosted': { fill: '#FFFFFF', opacity: 0.95 }
        };
    }
    
    const normalizedType = (glassType || '').toLowerCase();
    const normalizedColor = (glassColor || '').toLowerCase();
    
    // Also check windowsVisualConfigs for glass styles
    const wvc = (typeof window !== 'undefined' && window.windowsVisualConfigs) || {};
    const wvcGlass = wvc.glassType || {};
    const wvcColor = wvc.glassColor || {};

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
    if (normalizedColor.includes('frosted') || normalizedColor.includes('frost')) {
        const result = glassStyles['frosted'] || glassStyles['clear'];
        if (result) return result;
    }
    if (normalizedColor.includes('smoked')){
        const result = glassStyles['smoked'] || glassStyles['clear'];
        if (result) return result;
    }
    // Try type lookup
    if (glassStyles[normalizedType]) {
        return glassStyles[normalizedType];
    }

    // Try windowsVisualConfigs lookup (case-insensitive)
    for (const [key, val] of Object.entries(wvcGlass)) {
        if (key.toLowerCase() === normalizedType && val && val.fill !== undefined) return val;
    }
    for (const [key, val] of Object.entries(wvcColor)) {
        if (key.toLowerCase() === normalizedColor && val && val.fill !== undefined) return val;
    }

    // Default – guaranteed valid object
    return glassStyles['clear'] || DEFAULT_GLASS;
}

/**
 * Determine a pattern stroke color that contrasts with the glass fill.
 * Accepts hex (#RRGGBB) or rgb(...) strings and returns an rgba(...) string.
 */
/**
 * Calculate relative luminance of a color
 * Uses WCAG 2.0 formula for better contrast determination
 */
function calculateLuminance(r, g, b) {
    // Convert to 0-1 range
    const [rs, gs, bs] = [r/255, g/255, b/255];
    
    // Apply gamma correction
    const rLinear = rs <= 0.03928 ? rs / 12.92 : Math.pow((rs + 0.055) / 1.055, 2.4);
    const gLinear = gs <= 0.03928 ? gs / 12.92 : Math.pow((gs + 0.055) / 1.055, 2.4);
    const bLinear = bs <= 0.03928 ? bs / 12.92 : Math.pow((bs + 0.055) / 1.055, 2.4);
    
    // WCAG 2.0 luminance formula
    return 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear;
}

/**
 * Parse color string to RGB values
 */
function parseColorToRGB(fillColor) {
    let r = 224, g = 242, b = 241; // default light cyan
    
    try {
        const fc = String(fillColor).trim();
        if (fc.startsWith('#')) {
            const hex = fc.substring(1);
            if (hex.length === 3) {
                r = parseInt(hex[0] + hex[0], 16);
                g = parseInt(hex[1] + hex[1], 16);
                b = parseInt(hex[2] + hex[2], 16);
            } else if (hex.length >= 6) {
                r = parseInt(hex.substring(0,2), 16);
                g = parseInt(hex.substring(2,4), 16);
                b = parseInt(hex.substring(4,6), 16);
            }
        } else if (fc.startsWith('rgb')) {
            const nums = fc.replace(/[^0-9,]/g, '').split(',').map(n=>parseInt(n,10)||0);
            r = nums[0]||r; 
            g = nums[1]||g; 
            b = nums[2]||b;
        }
    } catch (e) {
        console.warn('Error parsing color:', fillColor);
    }
    
    return { r, g, b };
}


/**
 * Get frame style based on color/material
 */
function getFrameStyle(frameColor, styles = null) {
    const DEFAULT_FRAME = { color: '#FFFFFF', width: 4 };
    let frameStyles = styles || window.frameStyles || {};
    
    // Ensure frameStyles has at least default values if empty
    if (!frameStyles || Object.keys(frameStyles).length === 0) {
        frameStyles = {
            'vinyl': { color: '#333333', width: 4 },
            'aluminum': { color: '#90A4AE', width: 3 },
            'wood': { color: '#795548', width: 6 },
            'polished': { color: 'transparent', width: 0 },
            'beveled': { color: 'transparent', width: 0 }
        };
    }
    
    // Handle array input (tags field can be an array)
    if (Array.isArray(frameColor)) {
        frameColor = frameColor[0] || '';
    }
    
    // Normalize the frame color string
    const normalized = (frameColor || '').toLowerCase().trim();
    
    // Also check windowsVisualConfigs for frame styles
    const wvc = (typeof window !== 'undefined' && window.windowsVisualConfigs) || {};
    const wvcFrame = wvc.frameColor || {};

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
    
    // Try windowsVisualConfigs lookup (case-insensitive)
    for (const [key, val] of Object.entries(wvcFrame)) {
        if (key.toLowerCase() === normalized && val && val.color !== undefined) return val;
    }
    for (const [key, val] of Object.entries(wvcFrame)) {
        const keyLc = key.toLowerCase();
        if (normalized.includes(keyLc) || keyLc.includes(normalized)) {
            if (val && val.color !== undefined) return val;
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

    // Default fallback – guaranteed valid object
    return frameStyles['white'] || frameStyles['powder coated white'] || DEFAULT_FRAME;
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
 * Draw frosted glass pattern overlay - uses light blue fill with diagonal dashes
 */
function drawFrostedGlassPattern(layer, x, y, width, height, glassColor) {
    console.log('🔍 Drawing Frosted Pattern - x:', x, 'y:', y, 'width:', width, 'height:', height);
    
    const patternGroup = new Konva.Group({
        x: x,
        y: y,
        listening: false,
    });
    
    // Add light blue background fill
    patternGroup.add(new Konva.Rect({ 
        x: 0, y: 0, width: width, height: height, 
        fill: '#D0E8F2', 
        opacity: 0.2, 
        listening: false 
    }));
    
    // Responsive spacing based on panel size (similar to tempered pattern)
    const minDim = Math.min(Math.max(8, Math.floor(Math.min(width, height))), 200);
    const dashLength = Math.max(8, Math.round(minDim * 0.12));
    const verticalSpacing = Math.max(10, Math.round(minDim * 0.16));
    const horizontalSpacing = Math.max(10, Math.round(minDim * 0.18));

    // Pattern color for frosted glass - subtle and light
    let strokeColor = 'rgba(0, 41, 54, 0.99)'; // Light blue-gray
    let lineOpacity = 0.6;
    
    if (glassColor) {
        const gc = String(glassColor).toLowerCase().trim();
        if (gc.includes('bronze') || gc.includes('tinted') || gc.includes('smoked') || gc.includes('dark')) {
            // Lighter pattern for dark glass colors
            strokeColor = 'rgb(34, 170, 224)';
            lineOpacity = 0.7;
        } else {
            // Blue pattern for clear/light glass colors
            strokeColor = 'rgb(2, 90, 134)';
            lineOpacity = 0.6;
        }
    }

    // Create scattered diagonal pattern with pseudo-random offsets
    for (let py = 0; py < height; py += verticalSpacing) {
        for (let px = 0; px < width; px += horizontalSpacing) {
            // Offset some dashes pseudo-randomly for natural scattered look
            const offset = ((px + 1) * (py + 1)) % 11;
            const dashX = px + (offset > 6 ? Math.round(minDim * 0.03) : 0);
            const dashY = py + (offset > 4 ? Math.round(minDim * 0.02) : 0);
            
            if (dashX < width && dashY < height) {
                const line = new Konva.Line({
                    points: [dashX, dashY, dashX + dashLength, dashY + (dashLength * 0.3)], // Slight diagonal
                    stroke: strokeColor,
                    strokeWidth: Math.max(2, Math.round(dashLength * 0.15)),
                    opacity: lineOpacity,
                    listening: false,
                });
                patternGroup.add(line);
            }
        }
    }

    // Clip pattern to panel bounds to avoid overflow
    patternGroup.clipX(0);
    patternGroup.clipY(0);
    patternGroup.clipWidth(width);
    patternGroup.clipHeight(height);

    layer.add(patternGroup);
    console.log('✅ Frosted pattern added to layer with light blue fill');
}

/**
 * Draw tempered glass pattern overlay - scattered horizontal dashes
 * Matches reference: scattered ---- marks across surface
 */
function drawTemperedGlassPattern(layer, x, y, width, height, glassColor) {
    console.log('🔍 Drawing Tempered Pattern - x:', x, 'y:', y, 'width:', width, 'height:', height, 'glassColor:', glassColor);
    
    const patternGroup = new Konva.Group({
        x: x,
        y: y,
        listening: false,
    });
    
    // Responsive spacing based on panel size
    const minDim = Math.min(Math.max(8, Math.floor(Math.min(width, height))), 200);
    const dashLength = Math.max(8, Math.round(minDim * 0.12));
    const verticalSpacing = Math.max(10, Math.round(minDim * 0.16));
    const horizontalSpacing = Math.max(10, Math.round(minDim * 0.18));

    // Determine pattern color based on glass color - visible on both clear and bronze
    let strokeColor = '#000000';  // Default dark color for clear glass
    let lineOpacity = 0.9;
    
    if (glassColor) {
        const gc = String(glassColor).toLowerCase().trim();
        if (gc.includes('bronze') || gc.includes('tinted') || gc.includes('smoked') || gc.includes('dark')) {
            // Light pattern for dark glass colors
            strokeColor = '#C8B4A0';  // Light tan/beige
            lineOpacity = 0.85;
        } else {
            // Dark pattern for clear/light glass colors
            strokeColor = '#000000';  // Pure black for maximum contrast
            lineOpacity = 0.9;
        }
    }

    // Create scattered pattern with irregular offsets for natural look
    for (let py = 0; py < height; py += verticalSpacing) {
        for (let px = 0; px < width; px += horizontalSpacing) {
            // Offset some dashes pseudo-randomly for natural scattered look
            const offset = ((px + 1) * (py + 1)) % 11;
            const dashX = px + (offset > 6 ? Math.round(minDim * 0.03) : 0);
            const dashY = py + (offset > 4 ? Math.round(minDim * 0.02) : 0);
            
            if (dashX < width && dashY < height) {
                const line = new Konva.Line({
                    points: [dashX, dashY, dashX + dashLength, dashY],
                    stroke: strokeColor,
                    strokeWidth: Math.max(2, Math.round(dashLength * 0.15)),
                    opacity: lineOpacity,
                    listening: false,
                });
                patternGroup.add(line);
            }
        }
    }

    // Clip pattern to panel bounds to avoid overflow
    patternGroup.clipX(0);
    patternGroup.clipY(0);
    patternGroup.clipWidth(width);
    patternGroup.clipHeight(height);

    layer.add(patternGroup);
    console.log('✅ Tempered pattern added to layer with color:', strokeColor);
}

/**
 * Draw glass type pattern based on glass type
 * Applies appropriate visual representation for different glass types
 */
function applyGlassTypePattern(layer, x, y, width, height, glassType, glassColor) {
    if (!glassType) {
        console.log('⚠️ No glassType provided to applyGlassTypePattern');
        return;
    }

    const glassTypeLower = String(glassType).toLowerCase().trim();
    console.log('🎨 applyGlassTypePattern - glassType:', glassType, 'normalized:', glassTypeLower);
    
    if (glassTypeLower.includes('frosted')) {
        console.log('→ Drawing FROSTED pattern');
        drawFrostedGlassPattern(layer, x, y, width, height, glassColor);
    } else if (glassTypeLower.includes('tempered')) {
        console.log('→ Drawing TEMPERED pattern');
        drawTemperedGlassPattern(layer, x, y, width, height, glassColor);
    } else if (glassTypeLower.includes('reflective')) {
        console.log('→ Drawing REFLECTIVE pattern');
        // Reflective uses enhanced reflection marks
        drawEnhancedGlassReflections(layer, x, y, width, height, glassColor);
    } else {
        console.log('→ No pattern for type:', glassTypeLower);
    }
    // 'Ordinary', 'Clear' and other types don't have special patterns

    // Ensure frame strokes (rect outlines) remain on top of any newly added pattern.
    // Move any Rect with a stroke that intersects this pattern area to the top of the layer.
    try {
        const rectsIntersect = (ax, ay, aw, ah, bx, by, bw, bh) => {
            return ax < bx + bw && ax + aw > bx && ay < by + bh && ay + ah > by;
        };

        if (layer && typeof layer.getChildren === 'function') {
            const children = layer.getChildren();
            // children is an array, iterate using standard for loop
            for (let i = 0; i < children.length; i++) {
                const child = children[i];
                try {
                    if (!child) continue;
                    if (child.getClassName && child.getClassName() === 'Rect') {
                        // check if rect has a stroke defined
                        const stroke = child.stroke ? child.stroke() : (child.attrs && child.attrs.stroke);
                        if (stroke) {
                            const cx = (typeof child.x === 'function') ? child.x() : (child.attrs && child.attrs.x) || 0;
                            const cy = (typeof child.y === 'function') ? child.y() : (child.attrs && child.attrs.y) || 0;
                            const cw = (typeof child.width === 'function') ? child.width() : (child.attrs && child.attrs.width) || 0;
                            const ch = (typeof child.height === 'function') ? child.height() : (child.attrs && child.attrs.height) || 0;
                            if (rectsIntersect(cx, cy, cw, ch, x, y, width, height)) {
                                child.moveToTop();
                            }
                        }
                    }
                } catch (e) {
                    // ignore per-instance errors; do not break render
                }
            }
        }
    } catch (err) {
        console.warn('applyGlassTypePattern: error while reordering strokes', err);
    }
}

/**
 * Draw enhanced reflection marks for reflective glass
 * Uses simple "//" marks at center of panel
 */
function drawEnhancedGlassReflections(layer, x, y, width, height, glassColor) {
    drawGlassReflections(layer, x, y, width, height, glassColor);
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
    
    // Also export updateInputVisibility globally for dynamic form field visibility control
    window.updateInputVisibility = updateInputVisibility;
}
