# Konva.js Capabilities in Glassify-CI System

This document lists all the capabilities and features that Konva.js provides in the Glassify-CI system for 2D product visualization.

## Core Konva.js Components Used

### 1. **Stage (Canvas Container)**
- Creates the main canvas container
- Manages canvas dimensions (width/height)
- Container ID: `'konva-container'`
- Responsive sizing based on container width
- Square aspect ratio by default

### 2. **Layer**
- Single layer for all shapes
- Auto-draw enabled (redraws automatically on changes)
- Manages all visual elements
- Can be cleared and redrawn dynamically

### 3. **Shapes Rendered**

#### **Rectangle (`Konva.Rect`)**
- Primary shape for glass panels
- Supports:
  - Custom fill colors (glass types)
  - Opacity/transparency
  - Stroke (frame borders)
  - Stroke width (frame thickness)
  - Corner radius (individual or linked corners)
  - Position (x, y)
  - Dimensions (width, height)

#### **Circle (`Konva.Circle`)**
- Used for:
  - Handle indicators on sliding panels
  - Circular glass products
  - Hardware indicators
- Properties: radius, fill, stroke, position

#### **Ellipse (`Konva.Ellipse`)**
- Used for:
  - Oval-shaped glass products
  - Pattern overlays (rain pattern)
- Properties: radiusX, radiusY, fill, opacity

#### **Line (`Konva.Line`)**
- Used for:
  - Dimension lines (dashed)
  - Extension lines
  - Panel dividers
  - Track systems
  - Grid patterns
  - Frame borders
  - Opening arcs (for hinged doors/windows)
- Properties: points array, stroke, strokeWidth, dash pattern

#### **Text (`Konva.Text`)**
- Used for:
  - Dimension labels (width/height)
  - Panel labels ("F" for Fixed, "S" for Sliding)
  - Annotation text (thickness, edge work)
  - Product information
- Properties: fontSize, fontFamily, fontStyle, fill, align, rotation

#### **RegularPolygon (`Konva.RegularPolygon`)**
- Used for:
  - Pentagon (5-sided)
  - Hexagon (6-sided)
  - Octagon (8-sided)
- Properties: sides, radius, fill, stroke

#### **Star (`Konva.Star`)**
- Used for decorative star-shaped glass products
- Properties: numPoints, innerRadius, outerRadius, fill, stroke

#### **Shape (`Konva.Shape`)**
- Custom shapes for:
  - Highlight paths
  - Shadow effects
  - Complex patterns

## Visual Features Implemented

### 1. **Glass Type Visualization**
Konva renders different glass types with unique colors and opacity:
- **Clear Glass**: Light teal (#E0F2F1), 0.9 opacity
- **Tinted Glass**: Blue-gray (#546E7A), 0.7 opacity
- **Frosted Glass**: White (#FFFFFF), 0.95 opacity
- **Tempered Glass**: Light teal (#E0F2F1), 0.9 opacity
- **Laminated Glass**: Light gray (#CFD8DC), 0.95 opacity
- **Low-E Glass**: Light green (#Dcedc8), 0.85 opacity
- **Reflective Glass**: Gray with transparency
- **Bulletproof Glass**: Light gray (#CFD8DC), 0.98 opacity
- And 30+ more glass type variants

### 2. **Frame Visualization**
- Frame colors: White, Black, Silver, Bronze, Gold, Rose Gold, Wood, Brown, Aluminum, Chrome, Stainless Steel, and more
- Frame stroke width: 3-6 pixels (varies by frame type)
- Frameless option: Transparent stroke (0 width)

### 3. **Shape Support**
Konva can render glass products in multiple shapes:
- ✅ Rectangle (default)
- ✅ Square
- ✅ Circle
- ✅ Ellipse
- ✅ Triangle
- ✅ Pentagon
- ✅ Hexagon
- ✅ Octagon
- ✅ Star
- ✅ Diamond
- ✅ Arched (rectangle with rounded top)

### 4. **Multi-Panel Products**
- **Panel Configuration**: Renders multiple panels (2, 3, 4+ panels)
- **Panel Types**: 
  - Fixed panels (F) - darker blue color
  - Sliding panels (S) - standard glass color
- **Panel Dividers**: Lines between panels
- **Panel Labels**: Text indicators ("F" or "S")

### 5. **Transom Support**
- **Fixed Transom Head**: Fixed glass at top
- **Fixed Transom Sill**: Fixed glass at bottom
- **Transom Dividers**: Horizontal lines separating transom sections
- **Height Calculations**: Dynamic height based on h1/h2 inputs

### 6. **Dimension Lines & Labels**
- **Width Dimension**: Horizontal dashed line above glass
- **Height Dimension**: Vertical dashed line to the right
- **Extension Lines**: Small lines extending from corners
- **Dimension Labels**: Text showing measurements with units
- **Auto-positioning**: Adjusts based on glass panel position

### 7. **Hardware Visualization**
- **Handles**: Small circles indicating handle positions
- **Hinges**: Lines or arcs showing hinge locations
- **Locks**: Visual indicators for lock types
- **Track Systems**: Lines representing sliding tracks
- **Mounting Hardware**: Visual representation of mounting points

### 8. **Pattern Overlays**
Konva can render various pattern overlays:
- **Line Pattern**: Horizontal/vertical lines
- **Grid Pattern**: Crosshatch grid
- **Dots Pattern**: Dotted overlay
- **Frosted Pattern**: Random white dots
- **Rain Pattern**: Elliptical drops
- **Density Control**: Adjustable pattern density (1-20)

### 9. **Screen Visualization**
- Screen pattern overlay when "With Screen" is selected
- Grid-like pattern to represent screen mesh

### 10. **Track System Rendering**
- Visual representation of track systems (2 Tracks, 3 Tracks, etc.)
- Lines showing track positions
- Panel alignment with tracks

### 11. **Opening Arcs**
- For hinged windows/doors (Awning, Casement, Swing)
- Arc lines showing opening direction
- Left/right hinge indication

### 12. **LED Lighting Indicators**
- For mirrors with LED lighting
- Visual indicators showing LED strip positions
- Color representation for different LED colors

### 13. **Grid Patterns**
- French window style grids
- Custom grid configurations
- Grid overlay on glass panels

### 14. **Annotation Text**
- Thickness information display
- Edge work information
- Positioned below glass panel
- Bold text styling

### 15. **Visual Effects**
- **Gradients**: Vertical, horizontal, diagonal, radial gradients
- **Shadows**: Drop shadows with blur and offset
- **Edge Styles**: Solid, dashed, dotted borders
- **Opacity Control**: Semi-transparent effects

## Product Type Support

Konva renders 2D visualizations for:

### Windows (4 types)
- Sliding Windows (multi-panel, transoms, tracks)
- Awning Windows (hinged top, opening arcs)
- Casement Windows (side-hinged, multiple panels)
- Fixed Glass Windows

### Doors (5 types)
- Sliding Doors
- Swing Doors (hinged, opening arcs)
- Bi-fold Doors
- Frameless Doors
- Patch Fitting Doors

### Partitions (3 types)
- Frameless Glass Partitions
- Shower Enclosures
- Fixed Glass Partitions

### Specialty Products (3 types)
- Mirrors (with grids, lighting, frames)
- Top Glass (tabletop)
- Glass Board

### Commercial Products (3 types)
- Storefront
- Glass Balcony
- Stair Railings

## Advanced Features

### 1. **Dynamic Styling**
- Admin-configurable visual styles (stored in database)
- Fallback to default styles if not configured
- Real-time style updates

### 2. **Aspect Ratio Handling**
- Maintains product aspect ratio
- Scales to fit canvas while preserving proportions
- Responsive to container size

### 3. **Unit Conversion**
- Supports inches (in), centimeters (cm), millimeters (mm)
- Automatic conversion for rendering
- Dimension labels show original units

### 4. **Corner Radius**
- Individual corner radius control
- Linked corners mode (all corners same)
- Rounded rectangle support

### 5. **Real-time Updates**
- Canvas redraws when customization values change
- Layer clearing and regeneration
- No page refresh needed

### 6. **Performance Optimization**
- Single layer for all elements
- Efficient shape management
- Optimized rendering pipeline

## Technical Capabilities

### Canvas Management
- ✅ Stage creation and configuration
- ✅ Layer management
- ✅ Shape grouping
- ✅ Dynamic clearing and redrawing
- ✅ Responsive sizing

### Shape Manipulation
- ✅ Position control (x, y)
- ✅ Size control (width, height, radius)
- ✅ Rotation
- ✅ Scaling
- ✅ Opacity control
- ✅ Color management (fill, stroke)

### Visual Styling
- ✅ Fill colors (glass types)
- ✅ Stroke colors (frames)
- ✅ Stroke width
- ✅ Opacity/transparency
- ✅ Corner radius
- ✅ Dash patterns
- ✅ Font styling (text)

### Event Handling
- ✅ Shape listening (can be enabled/disabled)
- ✅ Layer events
- ✅ Stage events

### Export Capabilities
- ✅ Canvas can be exported to image
- ✅ Data URL generation
- ✅ PNG/JPEG export support

## Integration Points

1. **2D Customization Page**: Main customer-facing customization interface
2. **Order Status Page**: Displays order visualization
3. **Admin Product Management**: Preview product configurations
4. **Product Catalog**: Visual previews

## Configuration

All Konva defaults are documented in:
- `docs/KONVA_DEFAULT_OPTIONS_REFERENCE.md` - Complete reference of all default values
- `assets/data/default-customization-fields.json` - Customization options mapping

## Summary

Konva.js in this system provides a **complete 2D visualization engine** that:
- Renders all product types with accurate visual representation
- Supports 15+ different shapes
- Handles complex multi-panel configurations
- Displays dimensions, annotations, and hardware
- Applies visual styles for glass types and frames
- Supports patterns, overlays, and effects
- Provides real-time updates without page refresh
- Maintains aspect ratios and responsive sizing

The system uses Konva.js as the **primary rendering engine** for all 2D product visualizations, making it a core component of the customization and order management workflow.
