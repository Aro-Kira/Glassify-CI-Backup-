# Konva.js Default Options Reference

This reference documents all default Konva.js configuration options, visual styles, and shape properties used in the 2D customization canvas.

> **📌 For Developers:** This document lists the default values used when rendering glass products in the Konva canvas. Admin-configured visual styles will override these defaults when available.

## Stage & Layer Configuration

### Stage Defaults
| Property | Default Value | Description |
| --- | --- | --- |
| `container` | `'konva-container'` | DOM element ID for the canvas container |
| `width` | `konvaWrapper.offsetWidth` | Stage width (matches container width) |
| `height` | `konvaWrapper.offsetWidth` | Stage height (matches container width, square) |

### Layer Defaults
| Property | Default Value | Description |
| --- | --- | --- |
| Layer Type | `Konva.Layer` | Single layer for all shapes |
| Auto-draw | `true` | Layer automatically redraws on changes |

### Canvas Layout Constants
| Constant | Default Value | Description |
| --- | --- | --- |
| `PADDING` | `40` | Padding around drawing area (pixels) |
| `DRAWING_SIZE` | `STAGE_SIZE - PADDING * 2` | Available drawing area |
| `DIM_OFFSET` | `15` | Offset for dimension lines from glass panel |

## Glass Styles (Fill & Opacity)

Default glass type visual styles. These are fallback values that can be overridden by admin-configured visual configs.

### Standard Glass Types
| Glass Type | Fill Color | Opacity | Notes |
| --- | --- | --- | --- |
| `clear` | `#E0F2F1` | `0.9` | Light teal/cyan |
| `tinted` | `#546E7A` | `0.7` | Blue-gray |
| `laminated` | `#CFD8DC` | `0.95` | Light gray |
| `laminated safety glass` | `#CFD8DC` | `0.95` | Light gray (safety variant) |
| `tempered` | `#E0F2F1` | `0.9` | Same as clear |
| `double` | `#B2DFDB` | `0.9` | Light teal |
| `low-e` | `#Dcedc8` | `0.85` | Light green |
| `low-E` | `#Dcedc8` | `0.85` | Light green (capitalized variant) |
| `frosted` | `#FFFFFF` | `0.95` | White |
| `fully frosted` | `#FFFFFF` | `0.95` | Fully frosted glass |
| `frosted (full or partial)` | `#FFFFFF` | `0.95` | Frosted full or partial |
| `patterned` | `#E8E8E8` | `0.9` | Light gray |
| `safety glass` | `#CFD8DC` | `0.95` | Safety glass variant |
| `reflective coatings` | `rgba(200, 200, 200, 0.6)` | `0.85` | Reflective coating |
| `clear with frosted sticker` | `#E0F2F1` | `0.9` | Clear with frosted overlay |
| `10mm frosted tempered` | `#FFFFFF` | `0.95` | 10mm frosted tempered glass |
| `10mm Frosted Tempered` | `#FFFFFF` | `0.95` | 10mm Frosted Tempered (capitalized) |
| `bulletproof` | `#CFD8DC` | `0.98` | Bulletproof safety glass |
| `Bulletproof` | `#CFD8DC` | `0.98` | Bulletproof (capitalized) |
| `Copper Free and Lead Free Mirror` | `rgba(192, 192, 192, 0.8)` | `0.9` | Copper-free mirror (from JSON) |

### Windows-Specific Glass Types
| Glass Type | Fill Color | Opacity | Notes |
| --- | --- | --- | --- |
| `ultra clear` | `rgba(255, 255, 255, 0.1)` | `0.9` | Nearly transparent |
| `bronze` | `rgba(205, 127, 50, 0.4)` | `0.7` | Bronze tint |
| `light green` | `rgba(144, 238, 144, 0.4)` | `0.7` | Light green tint |
| `dark gray` | `rgba(105, 105, 105, 0.5)` | `0.6` | Dark gray |
| `copperfree mirror` | `rgba(192, 192, 192, 0.8)` | `0.9` | Silver mirror |
| `euro gray` | `rgba(169, 169, 169, 0.5)` | `0.7` | European gray |
| `ford blue` | `rgba(70, 130, 180, 0.5)` | `0.7` | Blue tint |
| `ordinary` | `#E0F2F1` | `0.9` | Standard clear |
| `reflective` | `rgba(200, 200, 200, 0.6)` | `0.85` | Reflective coating |
| `frosted/smoked` | `rgba(220, 220, 220, 0.7)` | `0.8` | Frosted/smoked effect |
| `frosted/smoke` | `rgba(220, 220, 220, 0.7)` | `0.8` | Alternative spelling |

### Reflective Glass Variants
| Glass Type | Fill Color | Opacity | Notes |
| --- | --- | --- | --- |
| `reflective: clear` | `rgba(255, 255, 255, 0.6)` | `0.9` | Clear reflective |
| `reflective: gray` | `rgba(169, 169, 169, 0.6)` | `0.8` | Gray reflective |
| `reflective: light blue` | `rgba(173, 216, 230, 0.6)` | `0.8` | Light blue reflective |
| `reflective: dark blue` | `rgba(0, 0, 139, 0.6)` | `0.8` | Dark blue reflective |
| `reflective: light green` | `rgba(50, 205, 50, 0.6)` | `0.8` | Light green reflective |
| `reflective: dark green` | `rgba(0, 100, 0, 0.6)` | `0.8` | Dark green reflective |
| `reflective: light bronze` | `rgba(205, 127, 50, 0.6)` | `0.8` | Light bronze reflective |

### Tempered Glass Variants
| Glass Type | Fill Color | Opacity | Notes |
| --- | --- | --- | --- |
| `tempered: clear` | `rgba(255, 255, 255, 0.2)` | `0.9` | Clear tempered |
| `tempered: bronze` | `rgba(205, 127, 50, 0.3)` | `0.8` | Bronze tempered |

### Mirror-Specific Tint Options
| Glass Type | Fill Color | Opacity | Notes |
| --- | --- | --- | --- |
| `mirror-clear` | `rgba(224, 242, 241, 0.9)` | `0.95` | Clear mirror |
| `mirror-bronze` | `rgba(205, 127, 50, 0.6)` | `0.7` | Bronze mirror |
| `mirror-grey` | `rgba(96, 125, 139, 0.5)` | `0.6` | Grey mirror |
| `mirror-grey-smoked` | `rgba(96, 125, 139, 0.5)` | `0.6` | Grey smoked mirror |
| `mirror-smoked` | `rgba(96, 125, 139, 0.5)` | `0.6` | Smoked mirror |
| `mirror-black` | `rgba(38, 50, 56, 0.7)` | `0.8` | Black mirror |
| `copper free and lead free mirror` | `rgba(192, 192, 192, 0.8)` | `0.9` | Copper-free mirror (standard mirror type) |
| `colored glass` | `rgba(200, 150, 200, 0.6)` | `0.7` | Colored glass variant |

## Frame Styles (Stroke & Width)

Default frame color and stroke width configurations. These are fallback values that can be overridden by admin-configured visual configs.

### Standard Frame Colors
| Frame Type | Color | Width | Notes |
| --- | --- | --- | --- |
| `white` | `#FFFFFF` | `4` | White frame |
| `black` | `#000000` | `4` | Black frame |
| `silver` | `#C0C0C0` | `3` | Silver frame |
| `bronze` | `#CD7F32` | `3` | Bronze frame |
| `gold` | `#FFD700` | `4` | Gold frame |
| `rose-gold` | `#B76E79` | `4` | Rose gold frame |
| `wood` | `#795548` | `6` | Wood finish |
| `brown` | `#8B4513` | `4` | Brown frame |
| `brown (wood-look)` | `#8B4513` | `4` | Brown wood-look finish |
| `aluminum` | `#90A4AE` | `3` | Aluminum |
| `chrome` | `#E8E8E8` | `3` | Chrome |
| `brushed-nickel` | `#A8A9AD` | `3` | Brushed nickel |
| `stainless-steel` | `#C9CCD1` | `3` | Stainless steel |
| `stainless mirror finish` | `#D4D4D4` | `3` | Stainless mirror finish |
| `dark grey/black` | `#2C2C2C` | `4` | Dark grey/black frame |
| `custom-color` | `#888888` | `4` | Custom/generic color |
| `custom colors` | `#888888` | `4` | Custom colors (plural variant) |

### Legacy Frame Types
| Frame Type | Color | Width | Notes |
| --- | --- | --- | --- |
| `vinyl` | `#333333` | `4` | Vinyl frame (legacy) |
| `frameless` | `transparent` | `0` | No frame |

### Windows-Specific Frame Colors
| Frame Type | Color | Width | Notes |
| --- | --- | --- | --- |
| `powder coated white` | `#F8F8F8` | `4` | Powder coated white |
| `Powder Coated White` | `#F8F8F8` | `4` | Powder Coated White (capitalized) |
| `analok` | `#F5F5DC` | `4` | Analok finish |
| `Analok` | `#F5F5DC` | `4` | Analok (capitalized) |
| `matte gray` | `#6B6B6B` | `4` | Matte gray |
| `Matte Gray` | `#6B6B6B` | `4` | Matte Gray (capitalized) |
| `matte black` | `#1A1A1A` | `4` | Matte black |
| `Matte Black` | `#1A1A1A` | `4` | Matte Black (capitalized) |
| `wood finish` | `#8B4513` | `4` | Wood finish |
| `Wood Finish` | `#8B4513` | `4` | Wood Finish (capitalized) |
| `hanalok` | `#F5F5DC` | `4` | Hanalok finish |
| `gray` | `#808080` | `4` | Gray |
| `grey` | `#808080` | `4` | Grey (alternative spelling) |
| `Dark Grey/Black` | `#2C2C2C` | `4` | Dark Grey/Black |
| `Analok (dark/bronze finish)` | `#8B4513` | `4` | Analok dark/bronze finish variant |
| `Stainless Mirror Finish` | `#D4D4D4` | `3` | Stainless Mirror Finish |

### Mirror-Specific Frame Types
| Frame Type | Color | Width | Notes |
| --- | --- | --- | --- |
| `standard-frame` | `#333333` | `6` | Standard mirror frame |
| `thin-frame` | `#333333` | `3` | Thin mirror frame |
| `grid-frame` | `#333333` | `4` | Grid pattern frame |
| `machine polished edges` | `transparent` | `0` | Machine polished edges (frameless) |
| `beveled edge` | `transparent` | `0` | Beveled edge (frameless) |
| `framed` | `#333333` | `6` | Framed mirror |
| `frameless` | `transparent` | `0` | Frameless mirror |

## Shape Defaults

Default properties for Konva shapes used to render glass panels.

### Rectangle (Default Shape)
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `offsetX` | Left position |
| `y` | `offsetY` | Top position |
| `width` | `windowWidth` | Calculated from dimensions |
| `height` | `windowHeight` | Calculated from dimensions |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `cornerRadius` | `0` or array `[topLeft, topRight, bottomRight, bottomLeft]` | Individual or linked corners |
| `listening` | `false` | No event handling |

### Circle
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `radius` | `minRadius` | Minimum of width/height divided by 2 |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Ellipse
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `radiusX` | `windowWidth / 2` | Half width |
| `radiusY` | `windowHeight / 2` | Half height |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Triangle (Line-based)
| Property | Default Value | Notes |
| --- | --- | --- |
| `points` | `[centerX, offsetY, offsetX, offsetY + windowHeight, offsetX + windowWidth, offsetY + windowHeight]` | 3 points |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `closed` | `true` | Closed shape |
| `listening` | `false` | No event handling |

### Pentagon (RegularPolygon)
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `sides` | `5` | 5-sided polygon |
| `radius` | `minRadius` | Minimum of width/height divided by 2 |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Hexagon (RegularPolygon)
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `sides` | `6` | 6-sided polygon |
| `radius` | `minRadius` | Minimum of width/height divided by 2 |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Octagon (RegularPolygon)
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `sides` | `8` | 8-sided polygon |
| `radius` | `minRadius` | Minimum of width/height divided by 2 |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Star
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `centerX` | Center X position |
| `y` | `centerY` | Center Y position |
| `numPoints` | `5` | 5-pointed star |
| `innerRadius` | `minRadius * 0.5` | Half of outer radius |
| `outerRadius` | `minRadius` | Minimum of width/height divided by 2 |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Diamond (Line-based)
| Property | Default Value | Notes |
| --- | --- | --- |
| `points` | `[centerX, offsetY, offsetX + windowWidth, centerY, centerX, offsetY + windowHeight, offsetX, centerY]` | 4 points |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `closed` | `true` | Closed shape |
| `listening` | `false` | No event handling |

### Arched (Rectangle with Rounded Top)
| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `offsetX` | Left position |
| `y` | `offsetY` | Top position |
| `width` | `windowWidth` | Calculated from dimensions |
| `height` | `windowHeight` | Calculated from dimensions |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `cornerRadius` | `[topRadius, topRadius, 0, 0]` | Rounded top corners only |
| `listening` | `false` | No event handling |

## Dimension Lines Defaults

Default properties for dimension lines and labels displayed around glass panels.

### Dimension Line Properties
| Property | Default Value | Notes |
| --- | --- | --- |
| `stroke` | CSS `--primary-dark` or `#333` | Dimension line color |
| `strokeWidth` | `1.5` | Line thickness |
| `dash` | `[5, 3]` | Dashed pattern (for dimension lines) |
| `listening` | `false` | No event handling |

### Extension Line Properties
| Property | Default Value | Notes |
| --- | --- | --- |
| `stroke` | CSS `--primary-dark` or `#333` | Extension line color |
| `strokeWidth` | `1.5` | Line thickness |
| `listening` | `false` | No event handling |
| `DIM_EXTENSION` | `20` | Extension line length (pixels) |
| `DIM_LINE_OFFSET` | `15` | Distance from glass to dimension line |

### Dimension Text Properties
| Property | Default Value | Notes |
| --- | --- | --- |
| `fontSize` | `11` | Text size |
| `fontFamily` | `'Montserrat, Arial'` | Font family |
| `fontStyle` | `'normal'` | Font style |
| `fill` | CSS `--primary-dark` or `#333` | Text color |
| `align` | `'center'` | Text alignment |
| `offsetX` | `(text.length * 6) / 2` | Horizontal offset for centering |
| `listening` | `false` | No event handling |

### Width Dimension (Top)
- Position: Above glass panel
- Extension lines: Vertical, extending upward from left and right corners
- Dimension line: Horizontal dashed line
- Label: Centered above dimension line

### Height Dimension (Right Side)
- Position: To the right of glass panel
- Extension lines: Horizontal, extending rightward from top and bottom corners
- Dimension line: Vertical dashed line
- Label: Rotated 90°, centered on dimension line

## Annotation Text Defaults

Default properties for annotation text (thickness, edge work) displayed below glass panels.

| Property | Default Value | Notes |
| --- | --- | --- |
| `x` | `offsetX + windowWidth / 2` | Centered horizontally |
| `y` | `offsetY + windowHeight + 15` | 15px below glass panel |
| `fontSize` | `11` | Text size |
| `fontStyle` | `'bold'` | Bold text |
| `fontFamily` | `'Montserrat'` | Font family |
| `fill` | `#555` | Dark gray text |
| `offsetX` | `(text.length * 6) / 2` | Horizontal offset for centering |
| `listening` | `false` | No event handling |

**Format:** `"Thickness: {thickness}  |  Edge: {edgeWork}"`

## Pattern Overlay Defaults

Default properties for pattern overlays (lines, grid, dots, frosted, rain).

### Line Pattern
| Property | Default Value | Notes |
| --- | --- | --- |
| `stroke` | Pattern color | From config |
| `strokeWidth` | `0.5` | Thin lines |
| `opacity` | `0.3` | Semi-transparent |
| `listening` | `false` | No event handling |
| Spacing | `Math.max(5, 30 / density)` | Based on density (1-20) |

### Grid Pattern
| Property | Default Value | Notes |
| --- | --- | --- |
| `stroke` | Pattern color | From config |
| `strokeWidth` | `0.5` | Thin lines |
| `opacity` | `0.3` | Semi-transparent |
| `listening` | `false` | No event handling |
| Spacing | `Math.max(5, 30 / density)` | Based on density (1-20) |

### Dots Pattern
| Property | Default Value | Notes |
| --- | --- | --- |
| `radius` | `1` | Small dots |
| `fill` | Pattern color | From config |
| `opacity` | `0.4` | Semi-transparent |
| `listening` | `false` | No event handling |
| Spacing | `Math.max(5, 30 / density)` | Based on density (1-20) |

### Frosted Pattern
| Property | Default Value | Notes |
| --- | --- | --- |
| `radius` | `Math.random() * 2 + 0.5` | Random size (0.5-2.5) |
| `fill` | `#FFFFFF` | White dots |
| `opacity` | `Math.random() * 0.3 + 0.1` | Random opacity (0.1-0.4) |
| `listening` | `false` | No event handling |
| Count | `density * 20` | Number of dots based on density |

### Rain Pattern
| Property | Default Value | Notes |
| --- | --- | --- |
| `radiusX` | `1` | Horizontal radius |
| `radiusY` | `dropLen / 4` | Vertical radius (varies) |
| `fill` | `#FFFFFF` | White drops |
| `opacity` | `Math.random() * 0.4 + 0.1` | Random opacity (0.1-0.5) |
| `listening` | `false` | No event handling |
| Count | `density * 10` | Number of drops based on density |
| Drop Length | `Math.random() * 10 + 5` | Random length (5-15) |

## Multi-Panel Product Defaults

Default properties for multi-panel products (sliding doors, windows with multiple panels).

### Fixed Panel (Transom)
| Property | Default Value | Notes |
| --- | --- | --- |
| `fill` | `#4A90E2` | Darker blue for fixed panels |
| `opacity` | `0.8` | Semi-transparent |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Fixed Panel Label
| Property | Default Value | Notes |
| --- | --- | --- |
| `text` | `'F'` | "F" for Fixed |
| `fontSize` | `Math.max(12, Math.min(16, height / 3))` | Responsive size |
| `fontFamily` | `'Montserrat, Arial'` | Font family |
| `fontStyle` | `'bold'` | Bold text |
| `fill` | `#FFFFFF` | White text |
| `align` | `'center'` | Centered |
| `offsetX` | `8` | Horizontal offset |
| `offsetY` | `8` | Vertical offset |
| `listening` | `false` | No event handling |

### Sliding Panel
| Property | Default Value | Notes |
| --- | --- | --- |
| `fill` | From `glassStyles` | Based on glass type |
| `opacity` | From `glassStyles` | Based on glass type |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | From `frameStyles.width` | Frame width |
| `listening` | `false` | No event handling |

### Sliding Panel Label
| Property | Default Value | Notes |
| --- | --- | --- |
| `text` | `'S'` | "S" for Sliding |
| `fontSize` | `Math.max(12, Math.min(16, height / 3))` | Responsive size |
| `fontFamily` | `'Montserrat, Arial'` | Font family |
| `fontStyle` | `'bold'` | Bold text |
| `fill` | `#FFFFFF` | White text |
| `align` | `'center'` | Centered |
| `offsetX` | `8` | Horizontal offset |
| `offsetY` | `8` | Vertical offset |
| `listening` | `false` | No event handling |

### Panel Divider Line
| Property | Default Value | Notes |
| --- | --- | --- |
| `stroke` | From `frameStyles.color` | Frame color |
| `strokeWidth` | `frameStyles.width * 1.5` | 1.5x frame width |
| `listening` | `false` | No event handling |

### Handle Circle (Sliding Panel Indicator)
| Property | Default Value | Notes |
| --- | --- | --- |
| `radius` | `6` | Small circle |
| `fill` | `#333333` | Dark gray |
| `opacity` | `0.8` | Semi-transparent |
| `listening` | `false` | No event handling |

## Advanced Visual Effects

Default properties for advanced visual effects (gradients, shadows, patterns).

### Gradient Effects
| Property | Default Value | Notes |
| --- | --- | --- |
| `effectType` | `'gradient'` | Gradient effect type |
| `gradientDirection` | `'vertical'`, `'horizontal'`, `'diagonal'`, `'radial'` | Gradient direction |
| `gradientEnd` | Color value | End color for gradient |

### Shadow Effects
| Property | Default Value | Notes |
| --- | --- | --- |
| `effectType` | `'shadow'` | Shadow effect type |
| `shadowColor` | `#000000` | Black shadow |
| `shadowBlur` | `10` | Blur radius |
| `shadowOffset` | `{ x: 5, y: 5 }` | Shadow offset |
| `shadowOpacity` | `0.3` | Shadow opacity |

### Edge Styles
| Property | Default Value | Notes |
| --- | --- | --- |
| `edgeStyle` | `'solid'`, `'dashed'`, `'dotted'` | Edge style |
| `dash` (dashed) | `[10, 5]` | Dash pattern |
| `dash` (dotted) | `[2, 4]` | Dot pattern |

## Application State Defaults

Default values for application state variables.

| Variable | Default Value | Notes |
| --- | --- | --- |
| `currentShape` | `'rectangle'` | Default shape |
| `currentGlassType` | `'tempered'` | Default glass type |
| `currentThickness` | `'5mm'` | Default thickness |
| `currentEdgeWork` | `'flat-polish'` | Default edge work |
| `currentFrameType` | `'vinyl'` | Default frame type |
| `currentCornerRadius` | `0` | Default corner radius (inches) |
| `cornerRadiusLinked` | `true` | All corners use same value |
| `currentDimensions.height.value` | `45` | Default height |
| `currentDimensions.height.unit` | `'in'` | Default height unit |
| `currentDimensions.width.value` | `35` | Default width |
| `currentDimensions.width.unit` | `'in'` | Default width unit |

## Thickness Options

Default thickness values used across different product categories. These values are commonly available in customization fields.

### Standard Thickness Options
| Thickness | Usage | Notes |
| --- | --- | --- |
| `6mm` | Windows (798 Series, 900 Series, 38 Series), Mirrors, Awning/Casement Windows | Standard thickness |
| `8mm` | Windows (900 Series, 50 Series, 60/85/75 Series), Awning/Casement Windows | Medium thickness |
| `10mm` | Windows (868 Series, 130 Series, 60/85/75 Series), Shower Enclosures, Patch Fitting, Awning/Casement Windows | Thicker glass |
| `12mm` | Windows (868 Series, 130 Series, 60/85/75 Series), Awning/Casement Windows | Extra thick glass |
| `10mm-12mm` | Patch Fitting | Range for frameless products |
| Custom (number input) | Windows Fixed Glass, Partitions | Configurable thickness with min: 1, step: 0.1 |

### Thickness by Product Series
- **798 Series (Windows)**: 6mm
- **900 Series (Windows)**: 6mm, 8mm
- **868 Series (Windows)**: 6mm, 8mm, 10mm, 12mm
- **130 Series (Windows)**: 6mm, 8mm, 10mm, 12mm
- **38 Series (Windows)**: 6mm
- **50 Series (Windows)**: 6mm, 8mm
- **60/85/75 Series (Windows)**: 6mm, 8mm, 10mm, 12mm
- **Awning Windows**: 6mm, 8mm, 10mm, 12mm
- **Casement Windows**: 6mm, 8mm, 10mm, 12mm
- **Swing Door (68 Series, ED & FD100)**: 6mm, 8mm, 10mm, 12mm
- **Bi-fold Door (45 Series, 75 Series)**: 6mm, 8mm, 10mm, 12mm
- **Shower Enclosures**: 10mm (fixed)
- **Mirrors**: 6mm (fixed)
- **Patch Fitting**: 10mm-12mm
- **Windows Fixed Glass**: Custom (number input)
- **Partitions (Frameless/Fixed Glass)**: Custom (number input)

## Unit Conversion Defaults

| Unit | Name | Conversion to MM |
| --- | --- | --- |
| `'in'` | Inches | `25.4` |
| `'cm'` | Centimeters | `10` |
| `'mm'` | Millimeters | `1` |

## Product Category Mapping

This section maps customization reference categories to their commonly used glass types and frame options.

### Windows Category
- **Glass Types**: Ordinary, Tempered, Reflective, Clear, Tinted, Frosted, Low-E, Reflective coatings, Safety glass, Laminated
- **Glass Colors**: Clear, Bronze, Frosted/Smoked
- **Frame Colors**: Powder Coated White, Analok, Matte Gray, Matte Black, Wood Finish, White, Black, Dark Grey/Black, Brown, Silver, Bronze, Custom colors

### Doors Category
- **Glass Types**: Clear, Tinted, Frosted, Low-E, Tempered, Laminated, Laminated safety glass, Ordinary, Reflective
- **Glass Colors**: Clear, Bronze, Frosted/Smoked
- **Frame Colors**: Powder Coated White, Analok, Matte Gray, Matte Black, Wood Finish, Aluminum, Black, White, Bronze, Brown (wood-look), Silver, Custom colors, Stainless Mirror Finish

### Glass Partitions & Enclosures Category
- **Glass Types**: Clear, Frosted, Tinted, Frosted (full or partial), Clear with frosted sticker, Fully frosted, Tempered
- **Glass Colors**: Clear, Clear with Frosted Sticker (Middle Portion), 10mm Frosted Tempered
- **Hardware Colors**: Black, Silver, Gold, White, Bronze, Chrome/Stainless Steel, Black Matte, Brushed Nickel, Stainless Steel, Mirror/Stainless Hardware, Matte Black Hardware

### Mirrors & Specialty Glass Category
- **Glass Types**: Copper Free and Lead Free Mirror
- **Tint/Finish**: Bronze tint/color, Grey tint (smoked), Colored glass
- **Frame Types**: Frameless, Framed
- **Frame Colors**: White, Black, Gold (for framed), Machine Polished Edges, Beveled Edge (for frameless)

## Mapping from default-customization-fields.json

The Konva defaults are synchronized with options defined in `assets/data/default-customization-fields.json`. Key mappings:

### Glass Type Field Mapping
- **glassType** field options map to `glassStyles` object
- Common options: `Ordinary`, `Tempered`, `Reflective`, `Clear`, `Tinted`, `Frosted`, `Low-E`, `Laminated`, `Safety glass`, `Bulletproof`
- Both lowercase and original capitalization variants are supported
- Glass colors (bronze, clear, frosted/smoked) may combine with glass types for visual representation

### Frame Color Field Mapping
- **frameColor** field options map to `frameStyles` object
- Windows: `Powder Coated White`, `Analok`, `Matte Gray`, `Matte Black`, `Wood Finish`
- Doors: `Aluminum`, `Black`, `White`, `Bronze`, `Brown (wood-look)`, `Silver`, `Custom colors`
- Mirrors: `White`, `Black`, `Gold`, `Machine Polished Edges`, `Beveled Edge`
- Patch Fitting: `Stainless Mirror Finish`
- Both lowercase and original capitalization variants are supported

### Thickness Field Mapping
- **thickness** / **glassThickness** field options: `6mm`, `8mm`, `10mm`, `12mm`, `10mm-12mm`
- Some products use number input for custom thickness (Windows Fixed Glass, Partitions)
- Thickness values are displayed in annotation text below the canvas preview

## Notes

- **Admin Override:** All default values can be overridden by admin-configured visual configs stored in the database.
- **Dynamic Loading:** Visual configs are loaded via `loadDynamicVisualConfigs()` function when product data is available.
- **Fallback Behavior:** If a glass type or frame type is not found in the styles objects, the system falls back to sensible defaults:
  - Glass: `glassStyles['clear']` (light teal, 0.9 opacity)
  - Frame: Color matching based on name, or `#FFFFFF` with width `4`
- **Shape Support:** Not all shapes support all properties (e.g., `cornerRadius` only applies to rectangles).
- **Pattern Density:** Pattern density ranges from 1-20, affecting spacing and count of pattern elements.
- **Case Sensitivity:** Glass type and frame type matching is typically case-insensitive, but exact matches are preferred. Both lowercase and original capitalization variants are provided in the defaults.
- **Thickness Variations:** Thickness options vary by product series and category. See Thickness Options section above.
- **Mirror Edge Finishes:** Frameless mirrors use transparent frames with edge finish indicators (Machine Polished Edges, Beveled Edge).
- **JSON Synchronization:** When adding new options to `default-customization-fields.json`, ensure corresponding defaults are added to both `glassStyles` and `frameStyles` objects in `2d_customization.js` with appropriate color/opacity values.