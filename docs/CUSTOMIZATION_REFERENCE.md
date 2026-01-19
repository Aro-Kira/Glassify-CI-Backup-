# Customization Catalog

This reference lists every main category, its supported subcategories, and the default customization fields (with their control type and allowed values) that are used when the admin configures products.

## Windows

### Sliding
**Step names:** Window Type · Sliding System & Size · Frame & Glass · Hardware & Accessories

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Window Type (1) | Number of Panels | `tags` | 2 Panels \\| 4 Panels |
| Window Type (1) | Transom Type (Top / Bottom Fixed Panel) | `tags` | None \\| Fixed Transom Head (Fixed glass at top) \\| Fixed Transom Sill (Fixed glass at bottom) |
| Sliding System & Size (2) | Track System (Sliding Rail Count) | `tags` | 2 Tracks \\| 3 Tracks |
| Sliding System & Size (2) | Panel Configuration | `tags` | S \\| S (Sliding \\| Sliding) \\| F \\| S (Fixed \\| Sliding) \\| S \\| S \\| S \\| S (All Sliding) \\| F \\| S \\| S \\| F (Fixed \\| Sliding \\| Sliding \\| Fixed) |
| Frame & Glass (3) | Frame Color | `tags` | Hanalok \\| White \\| Black \\| Gray \\| Wood Finish |
| Frame & Glass (3) | Glass Type | `tags` | Clear \\| Ultra Clear \\| Bronze \\| Light Green \\| Dark Gray \\| Copperfree Mirror \\| Euro Gray \\| Ford Blue \\| Reflective: Clear \\| Reflective: Gray \\| Reflective: Light Blue \\| Reflective: Dark Blue \\| Reflective: Light Green \\| Reflective: Dark Green \\| Reflective: Light Bronze \\| Tempered: Clear \\| Tempered: Bronze |
| Frame & Glass (3) | Glass Thickness | `tags` | 6mm |
| Hardware & Accessories (4) | Lock Type | `tags` | Center Lok 904 Big \\| Flushlok #12 \\| Durable Flushlok \\| New Auto Flushlock |
| Hardware & Accessories (4) | Roller Type | `tags` | Single Panel Roller \\| Blue Single Roller \\| Blue Double Roller |
| Hardware & Accessories (4) | Screen | `tags` | With Screen \\| Without Screen |

### Awning
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Tinted (bronze/brown) \\| Frosted \\| Low-E \\| Laminated |
| Basic Options (1) | Frame Color/Material | `tags` | White \\| Black \\| Brown \\| Silver \\| Bronze \\| Custom colors |
| Basic Options (1) | Operation | `tags` | Awning (crank-out) \\| Awning (push-out) |
| Configuration & Details (2) | Size Configuration | `tags` | Single panel \\| Multiple panels |
| Configuration & Details (2) | Opening Direction | `tags` | Top-hinged |
| Configuration & Details (2) | Thickness (mm) | `number` | min 1 · step 0.1 |
| Configuration & Details (2) | Screen | `checkbox` | |

### Casement
**Step names:** Basic Options · Panel Configuration · Advanced Options

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Frosted \\| Low-E \\| Laminated |
| Basic Options (1) | Frame Color/Material | `tags` | White \\| Black \\| Brown (wood-grain) \\| Silver \\| Bronze \\| Custom colors |
| Basic Options (1) | Operation | `tags` | Casement (hinge side configurable) |
| Panel Configuration (2) | Number of Panels | `tags` | Single panel \\| Multiple panels |
| Panel Configuration (2) | Hinge Side | `tags` | Left-hinged \\| Right-hinged |
| Panel Configuration (2) | Configuration | `tags` | Two casement windows with fixed transom \\| Custom configurations |
| Advanced Options (3) | Transom Options | `tags` | Different transom sizes \\| Shapes \\| Mullion options |
| Advanced Options (3) | Thickness (mm) | `number` | min 1 · step 0.1 |
| Advanced Options (3) | Screen | `checkbox` | |

### Fixed Glass
**Step names:** Basic Options · Installation & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Frosted \\| Low-E \\| Reflective coatings \\| Safety glass \\| Laminated |
| Basic Options (1) | Frame Color/Material | `tags` | White \\| Black \\| Dark Grey/Black \\| Brown \\| Silver \\| Bronze \\| Custom colors |
| Basic Options (1) | Configuration | `tags` | Fixed corner glass \\| Various angles (90°, 135°, custom) \\| Standard fixed |
| Installation & Details (2) | Usage | `tags` | Structural/architectural feature (non-operable) \\| Standard fixed |
| Installation & Details (2) | Installation Method | `tags` | Various integration methods \\| Standard mounting |
| Installation & Details (2) | Thickness (mm) | `number` | min 1 · step 0.1 |
| Installation & Details (2) | Screen | `checkbox` | |

## Doors

### Sliding
**Step names:** Basic Options · Operation & Configuration · Hardware & Features

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Frosted \\| Low-E \\| Tempered \\| Laminated \\| Laminated safety glass |
| Basic Options (1) | Frame Material/Color | `tags` | Aluminum \\| Black \\| White \\| Bronze \\| Brown (wood-look) \\| Silver \\| Custom colors |
| Basic Options (1) | Panel Count | `tags` | 2-panel \\| 3-panel \\| 4-panel \\| More panels |
| Operation & Configuration (2) | Operation | `tags` | Sliding (single) \\| Sliding (double) \\| Sliding (multi-track) |
| Operation & Configuration (2) | Panel Configuration | `tags` | Central sliding panels with fixed outer panels \\| All sliding \\| 2 sliding + 2 fixed \\| 2 sliding only \\| 3 sliding \\| Custom |
| Hardware & Features (3) | Handle Type | `tags` | Various pull handles \\| Knob handles \\| Square handles \\| Bar-style \\| Round \\| Square matte black |
| Hardware & Features (3) | Hardware Finish | `tags` | Chrome/Stainless Steel \\| Polished Chrome/Stainless Steel \\| Black Matte \\| Gold \\| Brushed Nickel \\| Bronze |
| Hardware & Features (3) | Soft-close | `checkbox` | |

### Frameless
**Step names:** Basic Options · Panel Configuration · Design & Hardware · Glass Treatment & Installation

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Frosted \\| Laminated \\| Laminated safety glass |
| Basic Options (1) | Door Type | `tags` | Single swing \\| Double swing \\| Single French door \\| Double French doors |
| Basic Options (1) | Door Swing | `tags` | Left swing \\| Right swing \\| Left-hinged \\| Right-hinged |
| Panel Configuration (2) | Fixed Panels | `tags` | With fixed side/transom panels \\| Without fixed panels \\| 0 fixed panels \\| 1 fixed panel \\| 2 fixed panels \\| More fixed panels \\| With fixed side panel (left or right) \\| With fixed transom \\| Both |
| Panel Configuration (2) | Configuration | `tags` | With fixed side panel (left or right) \\| With fixed transom \\| Both \\| Single swing door \\| Double swing door |
| Design & Hardware (3) | Handle Style | `tags` | Various pull handle designs \\| Various pull handles \\| Decorative handles |
| Design & Hardware (3) | Hardware Finish | `tags` | Polished Chrome/Stainless Steel \\| Matte Black \\| Brushed Nickel \\| Gold \\| Chrome/Stainless Steel |
| Design & Hardware (3) | Grid Pattern | `tags` | Internal grids \\| External grids \\| Colonial \\| Prairie \\| Custom grid designs \\| French type grid |
| Glass Treatment & Installation (4) | Glass Treatment | `tags` | Frosted stripes (horizontal/vertical) \\| Custom patterns \\| Colors \\| Frosted sticker (customizable patterns, opacity, colors) |
| Glass Treatment & Installation (4) | Installation | `tags` | Patch fittings (minimalist hardware) \\| Standard |
| Glass Treatment & Installation (4) | Hardware | `tags` | Push/pull handles \\| Locks \\| Closers \\| Multi-point locks |
| Glass Treatment & Installation (4) | Soft-close | `checkbox` | |

## Glass Partitions & Enclosures

### Frameless Glass
**Step names:** Basic Options · Configuration & Hardware

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Layout | `tags` | L-shape \\| Straight \\| U-shape \\| L-type \\| Neo-angle \\| Square \\| Bay \\| Other corner layouts |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Frosted \\| Tinted \\| Frosted (full or partial) \\| Clear with frosted sticker \\| Fully frosted |
| Basic Options (1) | Finish | `tags` | Clear \\| Frosted \\| Patterned |
| Configuration & Hardware (2) | Configuration | `tags` | Single partition \\| Multiple partitions \\| 2 fixed panels \\| 3 fixed panels \\| Custom configurations |
| Configuration & Hardware (2) | Hardware Color | `tags` | Black \\| Silver \\| Gold \\| White \\| Bronze \\| Chrome/Stainless Steel \\| Black Matte \\| Brushed Nickel \\| Stainless Steel |
| Configuration & Hardware (2) | Mounting Hardware | `tags` | Stainless Fixed Bracket \\| Gold U-Channel \\| Analok U-Channel (anodized aluminum) \\| Stainless U-Channel \\| Other bracket types \\| Standard mounting |
| Configuration & Hardware (2) | Glass Thickness (mm) | `number` | min 1 · step 0.1 |

### Shower Enclosure
**Step names:** Basic Options · Glass Treatment · Hardware & Installation

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Layout | `tags` | L-shape \\| Straight \\| U-shape \\| L-type \\| Neo-angle \\| Square \\| Bay \\| Other corner layouts |
| Basic Options (1) | Configuration | `tags` | Fixed and swing \\| Swing with small fixed glass \\| Single sliding door \\| Double sliding doors \\| Sliding with fixed panels \\| Single sliding \\| Double sliding \\| With fixed panels \\| 2 fixed panels \\| 3 fixed panels \\| Custom configurations |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Frosted \\| Tinted \\| Frosted (full or partial) \\| Clear with frosted sticker \\| Fully frosted \\| Custom frosting patterns \\| Frosted (full or partial with custom patterns/heights) |
| Glass Treatment (2) | Glass Treatment | `tags` | Frosted sticker (customizable patterns, opacity, colors) \\| Clear \\| Custom patterns \\| Heights (top clear, bottom frosted) \\| Colors |
| Glass Treatment (2) | Glass Thickness (mm) | `number` | min 1 · step 0.1 |
| Hardware & Installation (3) | Hardware Finish | `tags` | Chrome/Stainless Steel \\| Black Matte \\| Gold \\| Brushed Nickel \\| Polished Chrome/Stainless Steel \\| Matte Black (handles, hinges, connectors) \\| Matte Black (rail, rollers, handles) \\| Matte Black (hinges, handle, top bracing bar) \\| Stainless Steel \\| Black \\| Silver \\| Bronze |
| Hardware & Installation (3) | Handle Style | `tags` | Various pull handle designs \\| Various pull handles \\| Knob handles \\| Square handles \\| Square matte black \\| Round \\| Bar-style |
| Hardware & Installation (3) | Door Swing | `tags` | Left-hinged \\| Right-hinged \\| Left swing \\| Right swing |
| Hardware & Installation (3) | Mounting | `tags` | Standard mounting \\| Custom mounting methods |

### Fixed Glass
**Step names:** Basic Options · Configuration & Hardware

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Layout | `tags` | L-shape \\| Straight \\| U-shape |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Frosted \\| Tinted |
| Basic Options (1) | Finish | `tags` | Clear \\| Frosted \\| Patterned |
| Configuration & Hardware (2) | Configuration | `tags` | Single partition \\| Multiple partitions \\| 2 fixed panels \\| 3 fixed panels \\| Custom configurations |
| Configuration & Hardware (2) | Mounting Hardware | `tags` | Stainless Fixed Bracket \\| Gold U-Channel \\| Analok U-Channel (anodized aluminum) \\| Stainless U-Channel \\| Other bracket types \\| Standard mounting |
| Configuration & Hardware (2) | Hardware Finish | `tags` | Stainless Steel \\| Black \\| Gold \\| Silver \\| Bronze \\| Analok (dark/bronze finish) \\| Chrome/Stainless Steel |
| Configuration & Hardware (2) | Glass Thickness (mm) | `number` | min 1 · step 0.1 |

## Mirrors & Specialty Glass

### Mirrors
**Step names:** 

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Shape | `tags` | Round \| Oval \| Square \| Rectangle \| Arched |
| Step 1 (1) | Frame Type | `tags` | Frameless \| Standard Frame |
| Step 1 (1) | Frame Color | `tags` | Gold \| Black \| White |
| Step 2 (2) | Edge Finish | `tags` | Beveled \| Machine Polished |
| Step 2 (2) | Mounting Method | `tags` | Wall-mounted \| Freestanding \| Leaning \| Adhesive \| Hanging |
| Step 3 (3) | Lighting | `tags` | None \| LED Backlight \| LED Front Light |
| Step 3 (3) | LED Color | `tags` | Warm White \| Cool White \| Daylight |
| Step 3 (3) | Smart Features | `tags` | Touch Dimmer \| Defogger \| Motion Sensor |

**Notes:**
- Frame Color only appears when Frame Type = "Framed"
- Edge Finish only appears when Frame Type = "Frameless"
- Glass Type and Thickness are fixed values (Copper Free and Lead Free Mirror, 6mm)
- Corner radius only appears when Shape = Square, Rectangle

### Top Glass
**Step names:** Basic Options · Details & Installation

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Shape | `tags` | Round \\| Rectangle \\| Oval \\| Square \\| Custom shapes |
| Basic Options (1) | Edge Finish | `tags` | Beveled \\| Polished \\| Raw \\| Beveled edge \\| Flat polished edge \\| Pencil edge |
| Details & Installation (2) | Mounting Method | `tags` | Wall-mounted \\| Stand \\| Adhesive |
**Notes:**
- Corner radius only appears when Shape = Square, Rectangle

### Glass Board
**Step names:** Basic Options · Details & Installation

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Shape | `tags` | Round \\| Rectangle \\| Oval \\| Square \\| Custom shapes |
| Basic Options (1) | Edge Finish | `tags` | Beveled \\| Polished \\| Raw \\| Beveled edge \\| Flat polished edge \\| Pencil edge |
| Details & Installation (2) | Corner Radius (in) | `number` | min 0 · step 0.1 |
| Details & Installation (2) | Mounting Method | `tags` | Wall-mounted \\| Stand \\| Adhesive |
**Notes:**
- Corner radius only appears when Shape = Square, Rectangle
## Commercial & Exterior

### Storefront
**Step names:** Basic Options · Hardware & Installation

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Glass Type | `tags` | Clear \\| Tinted \\| Frosted \\| Laminated safety glass |
| Basic Options (1) | Safety Glass Type | `tags` | Tempered \\| Laminated \\| Bulletproof \\| Laminated safety glass |
| Hardware & Installation (2) | Handrail Type | `tags` | Stainless steel \\| Aluminum \\| Glass |
| Hardware & Installation (2) | Mounting System | `tags` | Clamp \\| Bolt \\| Adhesive \\| Patch fittings (minimalist hardware) |
| Hardware & Installation (2) | Hardware Finish | `tags` | Polished Chrome/Stainless Steel \\| Matte Black \\| Brushed Nickel \\| Gold |

### Glass Balcony
**Step names:** Basic Options

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Safety Glass Type | `tags` | Tempered \\| Laminated \\| Bulletproof |
| Basic Options (1) | Handrail Type | `tags` | Stainless steel \\| Aluminum \\| Glass |
| Basic Options (1) | Mounting System | `tags` | Clamp \\| Bolt \\| Adhesive |

### Stair Railings
**Step names:** Basic Options

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Safety Glass Type | `tags` | Tempered \\| Laminated \\| Bulletproof |
| Basic Options (1) | Handrail Type | `tags` | Stainless steel \\| Aluminum \\| Glass |
| Basic Options (1) | Mounting System | `tags` | Clamp \\| Bolt \\| Adhesive |

