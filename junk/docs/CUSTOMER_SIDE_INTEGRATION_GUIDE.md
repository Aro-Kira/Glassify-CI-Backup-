# Customer-Side Integration Guide - Comprehensive 2D Renderer

This guide shows you exactly how to integrate the comprehensive 2D renderer into your existing customer-side Konva.js implementation.

## Step 1: Add Script Tags to Your View

In `application/views/shop/2DModeling.php`, add the comprehensive renderer scripts **after** the Konva.js script and **before** your existing `2d_customization.js`:

```php
<!-- Existing Konva.js -->
<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>

<!-- ADD THESE TWO LINES -->
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_2d_renderer.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_renderer_integration.js'); ?>"></script>

<!-- Your existing 2d_customization.js -->
<script src="<?php echo base_url('assets/js/2d-functions/2d_customization.js'); ?>"></script>
```

## Step 2: Modify renderCustomState Function

In `assets/js/2d-functions/2d_customization.js`, find the `renderCustomState()` function (around line 2547) and modify it to use the comprehensive renderer when appropriate.

### Option A: Replace Entire Function (Recommended)

Replace the existing `renderCustomState()` function with this enhanced version:

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
    // Use comprehensive renderer if:
    // 1. Product has a category (Windows, Doors, Partitions, Specialty, Commercial)
    // 2. OR customization values contain product-specific fields
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
            productType: productData.type || productData.ProductName || '',
            customizationValues: {
                ...customizationValues,
                // Include legacy fields for compatibility
                shape: currentShape,
                glassType: currentGlassType,
                thickness: currentThickness,
                edgeWork: currentEdgeWork,
                frameColor: currentFrameType,
                cornerRadius: customizationValues.cornerRadius || currentCornerRadius
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

### Option B: Add Wrapper Function (Safer - No Breaking Changes)

If you want to keep the existing function intact, add this wrapper function instead:

```javascript
// Add this new function after renderCustomState
function renderCustomStateEnhanced() {
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
        customizationValues.trackSystem;

    if (shouldUseComprehensive && typeof Comprehensive2DRenderer !== 'undefined') {
        const dimensions = { width: widthIn, height: heightIn, unit: 'in' };
        const productInfo = {
            category: productData.category || '',
            productType: productData.type || productData.ProductName || '',
            customizationValues: {
                ...customizationValues,
                shape: currentShape,
                glassType: currentGlassType,
                thickness: currentThickness,
                edgeWork: currentEdgeWork,
                frameColor: currentFrameType
            }
        };
        Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, layer);
    } else {
        // Call original function
        renderCustomState();
    }
}

// Then replace all calls to renderCustomState() with renderCustomStateEnhanced()
// Or just override it:
window.renderCustomState = renderCustomStateEnhanced;
```

## Step 3: Ensure Product Data is Available

Make sure your product data includes the category. In your controller (`ShopCon.php` or wherever you load the product), ensure the product object has a `category` field:

```php
// In your controller
$data['product'] = $this->Product_model->get_product($id);
// Make sure the product object has a 'category' field
// If not, add it:
if (isset($data['product'])) {
    $data['product']->category = $data['product']->Category ?? $data['product']->category ?? '';
}
```

Then in your view, make sure it's available to JavaScript:

```javascript
// In 2DModeling.php, add this script tag (before other scripts)
<script>
    // Make product data available globally
    window.selectedProduct = {
        id: <?= isset($product) && $product ? $product->Product_ID : 'null' ?>,
        name: <?= isset($product) && $product ? json_encode($product->ProductName) : 'null' ?>,
        category: <?= isset($product) && $product ? json_encode($product->Category ?? $product->category ?? '') : 'null' ?>,
        type: <?= isset($product) && $product ? json_encode($product->ProductName ?? '') : 'null' ?>
    };
</script>
```

## Step 4: Test the Integration

1. **Test with a Windows product:**
   - Select a product with category "Windows"
   - Choose "Sliding" type
   - Set numberOfPanels to "4 Panels"
   - The renderer should show 4 panels with sliding indicators

2. **Test with a Doors product:**
   - Select a product with category "Doors"
   - Choose "Frameless" type
   - The renderer should show frameless door configuration

3. **Test fallback:**
   - Select a product without a category or without product-specific fields
   - Should fall back to existing `renderWindow()` function

## Step 5: Handle Customization Value Updates

The comprehensive renderer automatically reads from `window.selectedCustomizationValues`. Make sure your existing code updates this object when users make selections.

Your existing code should already do this, but verify that when a user selects an option, it updates:

```javascript
// Example: When user selects numberOfPanels
window.selectedCustomizationValues = window.selectedCustomizationValues || {};
window.selectedCustomizationValues.numberOfPanels = '4 Panels';

// Then trigger re-render
if (typeof renderCustomState === 'function') {
    renderCustomState();
}
```

## Complete Integration Example

Here's a complete example showing how everything works together:

```javascript
// In your customization field change handler
document.addEventListener('change', function(e) {
    const field = e.target.closest('[data-field-id]');
    if (field) {
        const fieldId = field.dataset.fieldId;
        const selectedValue = e.target.value || e.target.dataset.value;
        
        // Update selectedCustomizationValues
        window.selectedCustomizationValues = window.selectedCustomizationValues || {};
        window.selectedCustomizationValues[fieldId] = selectedValue;
        
        // Re-render with comprehensive renderer
        if (typeof renderCustomState === 'function') {
            renderCustomState(); // This now uses comprehensive renderer when appropriate
        }
    }
});
```

## Troubleshooting

### Renderer not working
- Check browser console for errors
- Verify scripts are loaded in correct order
- Ensure `Comprehensive2DRenderer` is defined: `console.log(typeof Comprehensive2DRenderer)`

### Wrong product type detected
- Manually set product category in `window.selectedProduct.category`
- Check that customization values match JSON field names

### Fallback to old renderer
- This is normal for products without specific categories
- Old renderer still works for basic products

### Styles not applying
- Ensure `glassStyles` and `frameStyles` are defined (from `2d_customization.js`)
- Check that style keys match your customization values

## Advanced: Custom Product Type Detection

If you want more control over when to use the comprehensive renderer, you can add custom detection logic:

```javascript
function shouldUseComprehensiveRenderer(productData, customizationValues) {
    // Custom logic here
    if (productData.category === 'Windows' && customizationValues.numberOfPanels) {
        return true;
    }
    if (productData.category === 'Doors' && customizationValues.doorType) {
        return true;
    }
    // Add more conditions...
    return false;
}
```

## Summary

1. ✅ Add script tags to `2DModeling.php`
2. ✅ Modify `renderCustomState()` to use comprehensive renderer
3. ✅ Ensure product data includes category
4. ✅ Test with different product types
5. ✅ Verify customization values are updated correctly

The comprehensive renderer will automatically handle all product types from your JSON file, while falling back to the existing renderer for basic products.
