# Comprehensive 2D Renderer Usage Guide

This guide explains how to use the comprehensive 2D Konva.js renderer that supports all customization options from `default-customization-fields.json`.

## Overview

The comprehensive 2D renderer provides visualization for all product types and their customization options:

- **Windows**: Sliding, Awning, Casement, Fixed Glass
- **Doors**: Sliding, Swing Door, Bi-fold Door, Frameless, Patch Fitting
- **Partitions**: Frameless Glass, Shower Enclosure, Fixed Glass
- **Specialty**: Mirrors, Top Glass, Glass Board
- **Commercial**: Storefront, Glass Balcony, Stair Railings

## Files

1. **`comprehensive_2d_renderer.js`** - Main renderer with all product-specific rendering functions
2. **`comprehensive_renderer_integration.js`** - Integration wrapper for existing system

## Basic Usage

### Method 1: Direct Rendering

```javascript
// Ensure the renderer is loaded
// <script src="assets/js/2d-functions/comprehensive_2d_renderer.js"></script>

// Get your Konva layer
const layer = new Konva.Layer();
stage.add(layer);

// Prepare product data
const productData = {
    category: 'Windows',
    productType: 'Sliding',
    customizationValues: {
        numberOfPanels: '2 Panels',
        transomType: 'None',
        trackSystem: '2 Tracks',
        panelConfiguration: 'S | S (Sliding | Sliding)',
        frameColor: 'Powder Coated White',
        glassType: 'Ordinary',
        glassColor: 'Clear',
        glassThickness: '6mm',
        lockType: 'Center Lok 904 Big',
        rollerType: 'Single Panel Roller',
        screen: 'Without Screen'
    }
};

const dimensions = {
    width: 60,  // inches
    height: 48, // inches
    unit: 'in'
};

// Render
Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

### Method 2: Auto-Detection

```javascript
// Load integration wrapper
// <script src="assets/js/2d-functions/comprehensive_renderer_integration.js"></script>

// Just provide customization values - the system will auto-detect product type
const customizationValues = {
    numberOfPanels: '4 Panels',
    trackSystem: '3 Tracks',
    panelConfiguration: 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)',
    frameColor: 'Matte Black',
    glassType: 'Tempered',
    glassColor: 'Bronze',
    glassThickness: '10mm'
};

const dimensions = {
    width: 72,
    height: 60,
    unit: 'in'
};

// Auto-render based on customization values
autoRenderFromCustomization(customizationValues, dimensions, layer);
```

### Method 3: Integration with Existing System

```javascript
// Replace existing renderWindow calls with enhanced version
renderWindowEnhanced(
    widthIn, heightIn, unit, shape, glassType, thickness, 
    edgeWork, frameType, originalWidth, originalHeight, 
    heightUnit, cornerRadiusIn
);
```

## Product-Specific Examples

### Windows Sliding

```javascript
const productData = {
    category: 'Windows',
    productType: 'Sliding',
    customizationValues: {
        numberOfPanels: '4 Panels',
        transomType: 'Fixed Transom Head (Fixed glass at top)',
        trackSystem: '3 Tracks',
        panelConfiguration: 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)',
        frameColor: 'Powder Coated White',
        glassType: 'Tempered',
        glassColor: 'Clear',
        glassThickness: '8mm',
        lockType: 'Flushlok #12',
        rollerType: 'Blue Double Roller',
        screen: 'With Screen'
    }
};

Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

### Doors Frameless

```javascript
const productData = {
    category: 'Doors',
    productType: 'Frameless',
    customizationValues: {
        glassType: 'Tempered',
        doorType: 'Double swing',
        doorSwing: 'Left-hinged',
        fixedPanels: 'With fixed side panel (left or right)',
        handleType: 'Various pull handles',
        hardwareFinish: 'Matte Black',
        gridPattern: 'French window style grid',
        glassTreatment: 'Frosted stripes (horizontal/vertical)',
        softClose: true
    }
};

Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

### Partitions Shower Enclosure

```javascript
const productData = {
    category: 'Partitions',
    productType: 'Shower Enclosure',
    customizationValues: {
        series: 'Fixed with Sliding Shower Enclosure',
        layout: 'L-shape',
        configuration: 'Sliding with fixed panels',
        glassType: 'Tempered',
        glassColor: 'Clear with Frosted Sticker (Middle Portion)',
        hardwareFinish: 'Matte Black Hardware',
        glassTreatment: 'Frosted sticker (customizable patterns, opacity, colors)',
        glassThickness: '10mm',
        handleStyle: 'Square matte black',
        doorSwing: 'Left-hinged'
    }
};

Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

### Specialty Mirrors

```javascript
const productData = {
    category: 'Specialty',
    productType: 'Mirrors',
    customizationValues: {
        series: 'Rectangle/Square Framed Mirror',
        shape: 'Rectangle',
        cornerRadius: 2, // inches
        frameType: 'Framed',
        frameColor: 'Gold',
        glassType: 'Copper Free and Lead Free Mirror',
        thickness: '6mm',
        tintFinish: 'Bronze tint/color',
        orientation: 'Vertical',
        gridPattern: 'French window style grid',
        lighting: 'Integrated LED lighting',
        ledColorTemperature: 'Warm white',
        control: 'Touch sensor button',
        mountingMethod: 'Wall-mounted'
    }
};

Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

### Commercial Storefront

```javascript
const productData = {
    category: 'Commercial',
    productType: 'Storefront',
    customizationValues: {
        glassType: 'Clear',
        safetyGlassType: 'Tempered',
        handrailType: 'Stainless steel',
        mountingSystem: 'Clamp',
        hardwareFinish: 'Polished Chrome/Stainless Steel'
    }
};

Comprehensive2DRenderer.renderProduct2D(productData, dimensions, layer);
```

## Available Renderer Functions

### Main Router
- `renderProduct2D(productData, dimensions, layer)` - Main function that routes to appropriate renderer

### Windows Renderers
- `renderWindowsSliding(productData, dimensions, layer)`
- `renderWindowsAwning(productData, dimensions, layer)`
- `renderWindowsCasement(productData, dimensions, layer)`
- `renderWindowsFixedGlass(productData, dimensions, layer)`

### Doors Renderers
- `renderDoorsSliding(productData, dimensions, layer)`
- `renderDoorsSwing(productData, dimensions, layer)`
- `renderDoorsBifold(productData, dimensions, layer)`
- `renderDoorsFrameless(productData, dimensions, layer)`
- `renderDoorsPatchFitting(productData, dimensions, layer)`

### Partitions Renderers
- `renderPartitionsFramelessGlass(productData, dimensions, layer)`
- `renderPartitionsShowerEnclosure(productData, dimensions, layer)`
- `renderPartitionsFixedGlass(productData, dimensions, layer)`

### Specialty Renderers
- `renderSpecialtyMirrors(productData, dimensions, layer)`
- `renderSpecialtyTopGlass(productData, dimensions, layer)`
- `renderSpecialtyGlassBoard(productData, dimensions, layer)`

### Commercial Renderers
- `renderCommercialStorefront(productData, dimensions, layer)`
- `renderCommercialGlassBalcony(productData, dimensions, layer)`
- `renderCommercialStairRailings(productData, dimensions, layer)`

## Customization Values Mapping

The renderer automatically maps customization values from the JSON to visual elements:

### Panel Configurations
- `"S | S"` → Two sliding panels
- `"F | S"` → Fixed panel, sliding panel
- `"F | S | S | F"` → Fixed, sliding, sliding, fixed

### Glass Types
- Automatically uses colors from `glassStyles` object
- Supports all glass types from JSON: Ordinary, Tempered, Reflective, Clear, Tinted, Frosted, etc.

### Frame Colors
- Automatically uses colors from `frameStyles` object
- Supports all frame colors: Powder Coated White, Analok, Matte Gray, Matte Black, Wood Finish, etc.

### Visual Indicators
- **Sliding panels**: "S" label with handle circle
- **Fixed panels**: "F" label with darker blue fill
- **Hinges**: Red lines on appropriate sides
- **Tracks**: Gray lines at bottom
- **Screens**: Dotted pattern overlay
- **Grids**: Grid pattern overlay for mirrors/doors

## Integration with Existing Code

To integrate with your existing `2d_customization.js`:

1. **Load the renderer scripts** (in order):
```html
<script src="assets/js/2d-functions/comprehensive_2d_renderer.js"></script>
<script src="assets/js/2d-functions/comprehensive_renderer_integration.js"></script>
```

2. **Option A**: Use enhanced renderer alongside existing:
```javascript
// Your existing code continues to work
renderWindow(...);

// New code can use comprehensive renderer
renderWithComprehensiveRenderer({
    dimensions: { width: 60, height: 48, unit: 'in' },
    productData: { category: 'Windows', productType: 'Sliding' },
    customizationValues: { numberOfPanels: '4 Panels', ... }
});
```

3. **Option B**: Replace existing renderer (uncomment in integration file):
```javascript
// In comprehensive_renderer_integration.js, uncomment:
// window.renderWindow = renderWindowEnhanced;
```

## Dependencies

The renderer requires:
- **Konva.js** - For 2D canvas rendering
- **glassStyles** object - From `2d_customization.js` (or define your own)
- **frameStyles** object - From `2d_customization.js` (or define your own)
- **Global constants**: `STAGE_SIZE`, `DRAWING_SIZE`, `PADDING`

## Notes

- The renderer automatically handles aspect ratios
- Panel configurations are parsed from strings like `"S | S | S | S"`
- Transom sections are automatically calculated and rendered
- All visual styles fall back to defaults if not found
- The renderer is extensible - you can add new product types by adding new render functions

## Troubleshooting

### Renderer not found
- Ensure `comprehensive_2d_renderer.js` is loaded before use
- Check browser console for loading errors

### Styles not applying
- Ensure `glassStyles` and `frameStyles` are defined
- Check that style keys match customization values (case-insensitive)

### Wrong product type detected
- Manually specify `category` and `productType` in `productData`
- Use `renderProduct2D` directly instead of auto-detection

### Layer issues
- Ensure Konva stage and layer are initialized
- Pass layer explicitly: `renderProduct2D(productData, dimensions, layer)`
