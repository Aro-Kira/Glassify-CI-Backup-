# Quick Integration Code - Copy & Paste

## Step 1: Add to 2DModeling.php

Find this line in `application/views/shop/2DModeling.php`:
```php
<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
```

Add these two lines **immediately after** it:
```php
<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
<!-- Comprehensive 2D Renderer -->
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_2d_renderer.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_renderer_integration.js'); ?>"></script>
```

## Step 2: Add Product Data Script

In `2DModeling.php`, find where you set `window.selectedProduct` (around line 900-1000), or add this script tag **before** your other JavaScript files:

```javascript
<script>
    // Make product data available for comprehensive renderer
    window.selectedProduct = window.selectedProduct || {};
    <?php if (isset($product) && $product): ?>
    window.selectedProduct = {
        id: <?= $product->Product_ID ?? 'null' ?>,
        name: <?= json_encode($product->ProductName ?? '') ?>,
        category: <?= json_encode($product->Category ?? $product->category ?? '') ?>,
        type: <?= json_encode($product->ProductName ?? '') ?>
    };
    <?php endif; ?>
</script>
```

## Step 3: Modify renderCustomState Function

In `assets/js/2d-functions/2d_customization.js`, find the `renderCustomState()` function (around line 2547) and **replace it entirely** with this:

```javascript
function renderCustomState() {
    // Quick sync: Check if the DOM has an active shape that differs from currentShape
    const activeShapeCard = document.querySelector('[data-field-id="shape"] .option-card.active, .option-card[data-shape].active');
    if (activeShapeCard) {
        const cardShape = activeShapeCard.getAttribute('data-shape');
        if (cardShape && cardShape !== currentShape) {
            currentShape = cardShape;
        }
    }

    // Get dimensions
    const heightValue = parseFloat(inputHeight?.value || 45);
    const widthValue = parseFloat(inputWidth?.value || 35);
    const heightUnit = btnUnitHeight?.getAttribute('data-current-unit') || 'in';
    const widthUnit = btnUnitWidth?.getAttribute('data-current-unit') || 'in';

    // Convert to inches for rendering
    const heightIn = convertToMm(heightValue, heightUnit) / 25.4;
    const widthIn = convertToMm(widthValue, widthUnit) / 25.4;

    // Get customization values
    const customizationValues = window.selectedCustomizationValues || {};
    
    // Get product data
    const productData = window.selectedProduct || {};
    
    // Check if we should use comprehensive renderer
    const shouldUseComprehensive = 
        (productData.category && (
            productData.category.includes('Windows') ||
            productData.category.includes('Doors') ||
            productData.category.includes('Partitions') ||
            productData.category.includes('Specialty') ||
            productData.category.includes('Commercial')
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
            productType: productData.type || productData.name || '',
            customizationValues: {
                ...customizationValues,
                // Include legacy fields for compatibility
                shape: currentShape,
                glassType: currentGlassType,
                thickness: currentThickness,
                edgeWork: currentEdgeWork,
                frameColor: currentFrameType,
                cornerRadius: customizationValues.cornerRadius || customizationValues.CornerRadius || currentCornerRadius
            }
        };

        // Render with comprehensive renderer
        Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, layer);
    } else {
        // Fall back to existing renderWindow function
        const cornerRadiusValue = (customizationValues.cornerRadius || 
                                    customizationValues.CornerRadius || 
                                    currentCornerRadius);
        
        renderWindow(
            widthIn,
            heightIn,
            widthUnit,
            currentShape,
            currentGlassType,
            currentThickness,
            currentEdgeWork,
            currentFrameType,
            widthValue,
            heightValue,
            heightUnit,
            cornerRadiusValue
        );
    }
}
```

## That's It!

After these three steps:
1. ✅ Scripts are loaded
2. ✅ Product data is available
3. ✅ renderCustomState uses comprehensive renderer

The system will automatically:
- Use comprehensive renderer for Windows, Doors, Partitions, Specialty, Commercial products
- Fall back to existing renderer for basic products
- Handle all customization options from your JSON file

## Testing

1. Open a product page with category "Windows"
2. Select "4 Panels" in numberOfPanels
3. You should see 4 panels rendered in the Konva canvas
4. Select different panel configurations - they should update in real-time

## Troubleshooting

If it doesn't work, check browser console:
```javascript
// Check if renderer is loaded
console.log(typeof Comprehensive2DRenderer); // Should be "object"

// Check product data
console.log(window.selectedProduct); // Should have category

// Check customization values
console.log(window.selectedCustomizationValues); // Should have your selections
```
