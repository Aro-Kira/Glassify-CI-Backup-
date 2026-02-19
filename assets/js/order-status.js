// order_status.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. RETRIEVE DATA FROM BROWSER MEMORY
    const rawData = localStorage.getItem('glassOrderData');
    
    if (!rawData) {
        // No local storage data — silently skip rendering 2D preview.
        return;
    }

    let orderData = null;
    try {
        orderData = JSON.parse(rawData);
    } catch (err) {
        // Invalid JSON in storage; clear the key to avoid repeated errors and exit
        try { localStorage.removeItem('glassOrderData'); } catch (e) {}
        return;
    }

    // 2. POPULATE TEXT FIELDS
    // Helper functions to make text look nice (e.g. "vinyl" -> "Vinyl")
    function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
    function formatText(str) { return str ? str.split('-').map(word => capitalize(word)).join(' ') : ''; }

    // Fill in the Summary Table
    document.getElementById('view-shape').textContent = capitalize(orderData.shape);
    document.getElementById('view-dim').textContent = `${orderData.dimensions.width.value}${orderData.dimensions.width.unit} x ${orderData.dimensions.height.value}${orderData.dimensions.height.unit}`;
    document.getElementById('view-type').textContent = capitalize(orderData.glassType);
    document.getElementById('view-thick').textContent = orderData.thickness; 
    document.getElementById('view-edge').textContent = formatText(orderData.edgeWork);
    document.getElementById('view-frame').textContent = capitalize(orderData.frameType);
    document.getElementById('view-engrave').textContent = orderData.engraving || 'None';

    // Format Price to Pesos
    const formatter = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
    document.getElementById('view-total').textContent = formatter.format(orderData.price);


    // 3. RENDER KONVA (THE 2D FIGURE)
    var KONVA_CONTAINER_ID = 'konva-container';
    const konvaWrapper = document.getElementById(KONVA_CONTAINER_ID);
    
    if(konvaWrapper) {
        const STAGE_SIZE = konvaWrapper.offsetWidth;
        const PADDING = 40;
        const DRAWING_SIZE = STAGE_SIZE - PADDING * 2;
        const DIM_OFFSET = 15;

        // Colors/Styles Configuration
        const glassStyles = {
            'tempered':  { fill: '#E0F2F1', opacity: 0.9 },
            'laminated': { fill: '#CFD8DC', opacity: 0.95 },
            'double':    { fill: '#B2DFDB', opacity: 0.9 },
            'low-e':     { fill: '#Dcedc8', opacity: 0.85 },
            'tinted':    { fill: '#546E7A', opacity: 0.7 },
            'frosted':   { fill: '#FFFFFF', opacity: 0.95 }
        };

        const frameStyles = {
            'vinyl':    { color: '#333333', width: 4 },
            'aluminum': { color: '#90A4AE', width: 3 },
            'wood':     { color: '#795548', width: 6 }
        };

        // Setup Canvas
        const stage = new Konva.Stage({
            container: KONVA_CONTAINER_ID,
            width: STAGE_SIZE,
            height: STAGE_SIZE,
        });

        const layer = new Konva.Layer();
        stage.add(layer);

        // Drawing Logic
        function renderWindow(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType) {
            const actualRatio = widthIn / heightIn;
            let windowWidth, windowHeight;
            
            // Calculate Aspect Ratio to fit in box
            if (actualRatio > 1) { 
                windowWidth = DRAWING_SIZE;
                windowHeight = DRAWING_SIZE / actualRatio;
            } else { 
                windowHeight = DRAWING_SIZE;
                windowWidth = DRAWING_SIZE * actualRatio;
            }

            const offsetX = (STAGE_SIZE - windowWidth) / 2;
            const offsetY = (STAGE_SIZE - windowHeight) / 2;

            const gStyle = glassStyles[glassType] || glassStyles['tempered'];
            const fStyle = frameStyles[frameType] || frameStyles['vinyl'];

            // Draw Frame
            const frame = new Konva.Rect({
                x: offsetX, y: offsetY,
                width: windowWidth, height: windowHeight,
                fill: gStyle.fill, opacity: gStyle.opacity,
                stroke: fStyle.color, strokeWidth: fStyle.width,
            });
            layer.add(frame);
            
            // Draw Interior Lines (Visual decoration)
            const paneWidth = windowWidth / 3;
            const paneStrokeWidth = Math.max(1, fStyle.width - 2); 
            for (let i = 1; i < 3; i++) {
                const dividerX = offsetX + paneWidth * i;
                layer.add(new Konva.Line({
                    points: [dividerX, offsetY, dividerX, offsetY + windowHeight],
                    stroke: fStyle.color, strokeWidth: paneStrokeWidth,
                }));
                const ventY = offsetY + windowHeight * 0.25;
                layer.add(new Konva.Line({
                    points: [dividerX - paneWidth, ventY, dividerX, ventY],
                    stroke: fStyle.color, strokeWidth: paneStrokeWidth,
                }));
                layer.add(new Konva.Circle({
                    x: dividerX - (paneWidth / 2), y: offsetY + windowHeight * 0.75,
                    radius: 3, fill: fStyle.color,
                }));
            }
            const ventY = offsetY + windowHeight * 0.25;
            layer.add(new Konva.Line({
                points: [offsetX + paneWidth * 2, ventY, offsetX + windowWidth, ventY],
                stroke: fStyle.color, strokeWidth: paneStrokeWidth,
            }));

            // Draw Dimensions
            const dimColor = '#003b4d';
            // Width Label
            layer.add(new Konva.Text({
                x: offsetX + windowWidth / 2,
                y: offsetY - DIM_OFFSET - 12,
                text: `${widthIn}${unit}`,
                fontSize: 10, fontFamily: 'Montserrat', fill: dimColor,
                offsetX: (widthIn.toString().length * 6) / 2,
            }));
            // Height Label
            layer.add(new Konva.Text({
                x: offsetX + windowWidth + DIM_OFFSET + 8,
                y: offsetY + windowHeight / 2,
                text: `${heightIn}${unit}`,
                fontSize: 10, fontFamily: 'Montserrat', fill: dimColor,
                rotation: 90, offsetX: (heightIn.toString().length * 6) / 2,
            }));
            
            layer.draw();
        }

        // Execute the drawing immediately
        renderWindow(
            orderData.dimensions.width.value, 
            orderData.dimensions.height.value, 
            orderData.dimensions.width.unit,
            orderData.shape,
            orderData.glassType,
            orderData.thickness,
            orderData.edgeWork,
            orderData.frameType
        );
    }
});