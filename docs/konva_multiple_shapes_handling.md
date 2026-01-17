# How Konva.js Handles Multiple Shapes

## Current State: Limited Shape Support

### Currently Supported Shapes (Only 3)

The system currently supports only **3 basic shapes**:

1. **Rectangle** (default fallback)
2. **Circle/Round**
3. **Oval/Ellipse**

### Code Location

**Shape Normalization** (`assets/js/2d-functions/2d_customization.js`, lines 340-352):
```javascript
function normalizeShape(shape) {
    if (!shape) return 'rectangle';
    const normalized = shape.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        'rectangle': 'rectangle',
        'rectangular': 'rectangle',
        'round': 'round',
        'circle': 'round',
        'oval': 'oval',
        'ellipse': 'oval'
    };
    return mapping[normalized] || 'rectangle'; // ⚠️ All unknown shapes → rectangle
}
```

**Shape Rendering** (`assets/js/2d-functions/2d_customization.js`, lines 140-182):
```javascript
// Draw glass shape based on preset shapes
let glassShape;
const centerX = offsetX + windowWidth / 2;
const centerY = offsetY + windowHeight / 2;

if (normalizedShape === 'round' || normalizedShape === 'circle') {
    // Circle
    const radius = Math.min(windowWidth, windowHeight) / 2;
    glassShape = new Konva.Circle({
        x: centerX,
        y: centerY,
        radius: radius,
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
} else {
    // Rectangle (default) - ALL OTHER SHAPES FALL HERE
    glassShape = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: windowWidth,
        height: windowHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
}
layer.add(glassShape);
```

## What Happens When Admin Adds New Shapes?

### Scenario: Admin adds "Triangle", "Pentagon", "Star", "Hexagon"

1. ✅ **UI**: All new shapes appear as options and can be selected
2. ⚠️ **Normalization**: All unknown shapes → mapped to `'rectangle'` (fallback)
3. ⚠️ **Visual**: Konva renders ALL new shapes as rectangles
4. ✅ **Data**: Shape selection is saved correctly in database

**Result**: Users can select "Triangle" but see a rectangle in the preview! 😞

## Konva.js Shape Capabilities

Konva.js **CAN** support many more shapes:

### Built-in Konva Shapes:
- ✅ `Konva.Rect` - Rectangle (currently used)
- ✅ `Konva.Circle` - Circle (currently used)
- ✅ `Konva.Ellipse` - Ellipse (currently used)
- ✅ `Konva.Polygon` - Any polygon (triangle, pentagon, hexagon, etc.)
- ✅ `Konva.Star` - Star shapes
- ✅ `Konva.Wedge` - Pie/wedge slices
- ✅ `Konva.Arrow` - Arrow shapes
- ✅ `Konva.Line` - Lines (with `closed: true` for polygons)
- ✅ `Konva.RegularPolygon` - Regular polygons (equilateral)

## Solution: Extend Shape Support

### Option 1: Add Common Shapes to Normalization (Quick Fix)

Add support for common shapes by extending the normalization and rendering:

**1. Update `normalizeShape` function:**
```javascript
function normalizeShape(shape) {
    if (!shape) return 'rectangle';
    const normalized = shape.toLowerCase().replace(/\s+/g, '-');
    const mapping = {
        // Existing
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
```

**2. Extend `renderWindow` function to handle new shapes:**
```javascript
// In renderWindow function, replace the shape rendering section:

if (normalizedShape === 'round' || normalizedShape === 'circle') {
    // Circle (existing)
    const radius = Math.min(windowWidth, windowHeight) / 2;
    glassShape = new Konva.Circle({
        x: centerX,
        y: centerY,
        radius: radius,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
} else if (normalizedShape === 'oval' || normalizedShape === 'ellipse') {
    // Ellipse (existing)
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
        radius: Math.min(windowWidth, windowHeight) / 2,
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
        radius: Math.min(windowWidth, windowHeight) / 2,
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
        radius: Math.min(windowWidth, windowHeight) / 2,
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
        innerRadius: Math.min(windowWidth, windowHeight) / 4,
        outerRadius: Math.min(windowWidth, windowHeight) / 2,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
} else if (normalizedShape === 'diamond') {
    // Diamond - 4-sided polygon rotated 45 degrees
    const halfWidth = windowWidth / 2;
    const halfHeight = windowHeight / 2;
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
    // Rectangle (default fallback)
    glassShape = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: windowWidth,
        height: windowHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
}
```

### Option 2: Dynamic Shape System (Advanced)

Create a shape registry that can be extended dynamically:

**1. Create Shape Registry:**
```javascript
// Shape registry with rendering functions
const shapeRegistry = {
    'rectangle': {
        render: (centerX, centerY, offsetX, offsetY, windowWidth, windowHeight, gStyle, fStyle) => {
            return new Konva.Rect({
                x: offsetX,
                y: offsetY,
                width: windowWidth,
                height: windowHeight,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
        }
    },
    'circle': {
        render: (centerX, centerY, offsetX, offsetY, windowWidth, windowHeight, gStyle, fStyle) => {
            const radius = Math.min(windowWidth, windowHeight) / 2;
            return new Konva.Circle({
                x: centerX,
                y: centerY,
                radius: radius,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                listening: false,
            });
        }
    },
    'triangle': {
        render: (centerX, centerY, offsetX, offsetY, windowWidth, windowHeight, gStyle, fStyle) => {
            const points = [
                centerX, offsetY,
                offsetX, offsetY + windowHeight,
                offsetX + windowWidth, offsetY + windowHeight
            ];
            return new Konva.Line({
                points: points,
                fill: gStyle.fill,
                opacity: gStyle.opacity,
                stroke: fStyle.color,
                strokeWidth: fStyle.width,
                closed: true,
                listening: false,
            });
        }
    },
    // Add more shapes as needed...
};

// Function to register new shapes dynamically
function registerShape(shapeName, renderFunction) {
    shapeRegistry[shapeName] = { render: renderFunction };
}

// Updated renderWindow function
function renderWindow(...) {
    // ... existing code ...
    
    const normalizedShape = normalizeShape(shape);
    const shapeHandler = shapeRegistry[normalizedShape] || shapeRegistry['rectangle'];
    
    glassShape = shapeHandler.render(
        centerX, centerY, offsetX, offsetY, 
        windowWidth, windowHeight, gStyle, fStyle
    );
    
    layer.add(glassShape);
    // ... rest of code ...
}
```

**2. Load Custom Shapes from Database:**
```javascript
// Load shape configurations from database
async function loadCustomShapes() {
    const response = await fetch('/api/get-custom-shapes');
    const customShapes = await response.json();
    
    customShapes.forEach(shapeConfig => {
        registerShape(shapeConfig.name, (centerX, centerY, offsetX, offsetY, windowWidth, windowHeight, gStyle, fStyle) => {
            // Use shapeConfig.points or shapeConfig.type to render
            if (shapeConfig.type === 'polygon') {
                return new Konva.Line({
                    points: shapeConfig.points,
                    fill: gStyle.fill,
                    opacity: gStyle.opacity,
                    stroke: fStyle.color,
                    strokeWidth: fStyle.width,
                    closed: true,
                    listening: false,
                });
            }
            // Handle other shape types...
        });
    });
}
```

### Option 3: Admin Shape Configuration UI

Allow admins to configure custom shapes visually:

1. **Shape Builder UI**: Admin can draw/define custom polygon points
2. **Shape Preview**: Show preview of how shape will render
3. **Database Storage**: Store shape definitions (points, type) in database
4. **Dynamic Loading**: Load and render custom shapes at runtime

## Implementation Checklist

To add support for multiple shapes:

### Files to Update:

1. ✅ **`assets/js/2d-functions/2d_customization.js`**
   - Update `normalizeShape()` function
   - Extend `renderWindow()` function with new shape cases
   - Add shape-specific dimension handling (e.g., lock dimensions for regular polygons)

2. ✅ **`assets/js/admin-js/admin_konva_preview.js`**
   - Mirror shape rendering logic for admin preview
   - Update `normalizeShape()` function

3. ✅ **`assets/js/2d-functions/dynamic_customization.js`**
   - Update `updateKonvaFromField()` to handle new shapes
   - Add shape-specific logic (e.g., auto-lock dimensions for regular polygons)

4. ⚠️ **Database Schema** (if using Option 2 or 3)
   - Add `shape_config` column to customization fields table
   - Store shape definitions (points, type, etc.)

5. ⚠️ **Backend** (if using Option 2 or 3)
   - Add API endpoints to fetch custom shapes
   - Add admin UI for shape configuration

## Special Considerations

### 1. Dimension Locking for Regular Polygons

Some shapes (like circles, stars, regular polygons) should have locked dimensions (width = height):

```javascript
// In dynamic_customization.js, updateKonvaFromField()
if (konvaParam === 'shape') {
    const shapeValue = value.toLowerCase().replace(/\s+/g, '-');
    
    // Shapes that require equal dimensions
    const equalDimensionShapes = ['round', 'circle', 'star', 'pentagon', 'hexagon', 'octagon'];
    
    if (equalDimensionShapes.includes(shapeValue)) {
        // Auto-lock dimensions
        if (typeof window.lockDimensionsForRoundShape === 'function') {
            window.lockDimensionsForRoundShape();
        }
    } else {
        // Unlock for other shapes
        if (typeof window.unlockDimensionsIfNotRound === 'function') {
            window.unlockDimensionsIfNotRound();
        }
    }
}
```

### 2. Dimension Labels for Different Shapes

Some shapes may need different dimension display:
- **Circle**: Show diameter or radius
- **Regular Polygons**: Show side length or radius
- **Triangle**: Show base and height

### 3. Interior Panels for Non-Rectangular Shapes

Currently, interior panels (3x2 grid) are only drawn for rectangles. Consider:
- Skip interior panels for non-rectangular shapes
- Or create shape-specific interior patterns

## Current Workaround

Until multiple shapes are implemented:

1. **For Admins**: Only add shapes that match existing mappings (Rectangle, Round, Oval)
2. **For Developers**: Manually add new shapes to normalization and rendering functions
3. **Documentation**: Keep a list of supported shapes in `customization_fields_presets_summary.md`

## Summary

**Current State**: 
- ✅ Only 3 shapes supported: Rectangle, Circle, Oval
- ⚠️ All new shapes fall back to rectangle rendering
- ⚠️ No way to add custom shapes dynamically

**Konva.js Capability**: 
- ✅ Supports many shapes: Polygon, Star, Wedge, Arrow, RegularPolygon, etc.
- ✅ Can render any custom polygon with defined points

**Recommendation**: 
- **Quick Fix**: Implement Option 1 (add common shapes to normalization)
- **Long-term**: Implement Option 2 (dynamic shape system) for full flexibility

**Impact**: 
- Users can select new shapes but see rectangles in preview
- May cause confusion if visual doesn't match selection
- Data integrity is maintained (correct shape names saved)
