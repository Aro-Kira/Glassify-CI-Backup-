# Customization Catalog

This reference lists every main category, its supported subcategories, and the default customization fields (with their control type and allowed values) that are used when the admin configures products.

> **📌 For Developers:** If you're implementing or modifying customization fields, see [`DEVELOPER_HANDOFF_WINDOWS_SLIDING_CHANGES.md`](./DEVELOPER_HANDOFF_WINDOWS_SLIDING_CHANGES.md) for implementation details, database setup, and troubleshooting guide.

##Direct Order

## Windows

### Sliding
**Step names:** Window Type · Sliding System & Size · Frame & Glass · Hardware & Accessories

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Number of Panels | `tags` | 2 Panels \| 4 Panels |
| Step 1 (1) | Transom Type (Top / Bottom Fixed Panel) | `tags` | None \| Fixed Transom Head (Fixed glass at top) \| Fixed Transom Sill (Fixed glass at bottom) |
| Step 2 (2) | Track System (Sliding Rail Count) | `tags` | 2 Tracks \| 3 Tracks |
| Step 2 (2) | Panel Configuration | `tags` | S | S (Sliding | Sliding) \| F | S (Fixed | Sliding) \| S | S | S | S (All Sliding) \| F | S | S | F (Fixed | Sliding | Sliding | Fixed) |
| Step 3 (3) | Frame Color | `tags` | Powder Coated White \| Analok \| Matte Gray \| Matte Black \| Wood Finish |
| Step 3 (3) | Glass Type | `tags` | Ordinary \| Tempered \| Reflective |
| Step 3 (3) | Glass Color | `tags` | Clear \| Bronze \| Frosted \| Smoked |
| Step 3 (3) | Glass Thickness | `tags` | 6mm \| 8mm \| 10mm \| 12mm |
| Step 4 (4) | Lock Type | `tags` | Center Lok 904 Big \| Flushlok #12 \| Durable Flushlok \| New Auto Flushlock |
| Step 4 (4) | Roller Type | `tags` | Single Panel Roller \| Blue Single Roller \| Blue Double Roller |
| Step 4 (4) | Screen | `tags` | With Screen \| Without Screen |

**Notes:**
- Thickness options vary by series: 798 Series (6mm), 900 Series (6mm-8mm), 868 Series (6mm, 8mm, 10mm, 12mm), 130 Series (6mm, 8mm, 10mm, 12mm)

### Awning
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Glass Type | `tags` | Ordinary \| Tempered \| Reflective |
| Step 1 (1) | Glass Color | `tags` | Clear \| Bronze \| Frosted \| Smoked |
| Step 1 (1) | Frame Color/Material | `tags` | Powder Coated White \| Analok \| Matte Gray \| Matte Black \| Wood Finish |
| Step 1 (1) | Operation | `tags` | Awning (crank-out) \| Awning (push-out) |
| Step 2 (2) | Size Configuration | `tags` | Single panel \| Multiple panels |
| Step 2 (2) | Opening Direction | `tags` | Top-hinged |
| Step 2 (2) | Thickness (mm) | `tags` | 6mm \| 8mm \| 10mm \| 12mm |
| Step 2 (2) | Screen | `tags` | With Screen \| Without Screen |

**Notes:**
- Thickness options vary by series: 38 Series (6mm), 50 Series (6mm-8mm), 60/85/75 Series (6mm, 8mm, 10mm, 12mm)
- Rows and Columns fields are conditional: they only appear when "Multiple panels" is selected in Size Configuration
- Customers can customize the panel grid layout by entering their preferred number of rows and columns

### Casement
**Step names:** Basic Options · Panel Configuration · Advanced Options

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Glass Type | `tags` | Ordinary \| Tempered \| Reflective |
| Step 1 (1) | Glass Color | `tags` | Clear \| Bronze \| Frosted \| Smoked |
| Step 1 (1) | Frame Color/Material | `tags` | Powder Coated White \| Analok \| Matte Gray \| Matte Black \| Wood Finish |
| Step 1 (1) | Operation | `tags` | Casement (hinge side configurable) |
| Step 2 (2) | Number of Panels | `tags` | Single panel \| Multiple panels |
| Step 2 (2) | Hinge Side | `tags` | Left-hinged \| Right-hinged |
| Step 2 (2) | Configuration | `tags` | Two casement windows with fixed transom \| Custom configurations |
| Step 3 (3) | Transom Options | `tags` | Different transom sizes \| Shapes \| Mullion options |
| Step 3 (3) | Thickness (mm) | `tags` | 6mm \| 8mm \| 10mm \| 12mm |
| Step 3 (3) | Screen | `tags` | With Screen \| Without Screen |

**Notes:**
- Thickness options vary by series: 38 Series (6mm), 50 Series (6mm-8mm), 60/85/75 Series (6mm, 8mm, 10mm, 12mm)

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

##Site-Assessment Order

## Doors

### Sliding
**Step names:** Basic Options · Operation & Configuration · Hardware & Features

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Glass Type | `tags` | Clear \| Tinted \| Frosted \| Low-E \| Tempered \| Laminated \| Laminated safety glass |
| Step 1 (1) | Frame Material/Color | `tags` | Aluminum \| Black \| White \| Bronze \| Brown (wood-look) \| Silver \| Custom colors |
| Step 1 (1) | Panel Count | `tags` | 2-panel \| 3-panel \| 4-panel \| More panels |
| Step 2 (2) | Operation | `tags` | Sliding (single) \| Sliding (double) \| Sliding (multi-track) |
| Step 2 (2) | Panel Configuration | `tags` | Central sliding panels with fixed outer panels \| All sliding \| 2 sliding + 2 fixed \| 2 sliding only \| 3 sliding \| Custom |
| Step 3 (3) | Handle Type | `tags` | Various pull handles \| Knob handles \| Square handles \| Bar-style \| Round \| Square matte black |
| Step 3 (3) | Hardware Finish | `tags` | Chrome/Stainless Steel \| Polished Chrome/Stainless Steel \| Black Matte \| Gold \| Brushed Nickel \| Bronze |
| Step 3 (3) | Soft-close | `checkbox` |  |

### Swing Door
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Series | `tags` | 68 Series \\| ED & FD100 |
| Basic Options (1) | Glass Type | `tags` | Ordinary \\| Tempered \\| Reflective |
| Basic Options (1) | Glass Color | `tags` | Clear \\| Bronze \\| Frosted/Smoked |
| Basic Options (1) | Frame Color/Material | `tags` | Powder Coated White \\| Analok \\| Matte Gray \\| Matte Black \\| Wood Finish |
| Configuration & Details (2) | Thickness (mm) | `tags` | 6mm \\| 8mm \\| 10mm \\| 12mm |

### Bi-fold Door
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Series | `tags` | 45 Series \\| 75 Series |
| Basic Options (1) | Glass Type | `tags` | Ordinary \\| Tempered \\| Reflective |
| Basic Options (1) | Glass Color | `tags` | Clear \\| Bronze \\| Frosted/Smoked |
| Basic Options (1) | Frame Color/Material | `tags` | Powder Coated White \\| Analok \\| Matte Gray \\| Matte Black \\| Wood Finish |
| Configuration & Details (2) | Thickness (mm) | `tags` | 6mm \\| 8mm \\| 10mm \\| 12mm |

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

### Patch Fitting
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Basic Options (1) | Series | `tags` | Frameless Fixed Glass \\| Frameless Door |
| Basic Options (1) | Glass Type | `tags` | Tempered \\| Reflective |
| Basic Options (1) | Glass Color | `tags` | Clear \\| Bronze \\| Frosted/Smoked |
| Basic Options (1) | Frame Color/Material | `tags` | Stainless Mirror Finish |
| Configuration & Details (2) | Thickness (mm) | `tags` | 10mm-12mm |



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
| Basic Options (1) | Series | `tags` | Arched Fixed Frameless Shower Partition \\| Fixed Frameless with Curved Corner Shower Partition \\| Fixed Frameless Shower Partition \\| Fixed with Swing Shower Enclosure \\| Fixed with Sliding Shower Enclosure \\| Fixed Framed Shower Partition \\| Swing Door Shower Enclosure \\| Corner Swing Shower Enclosure \\| Corner Sliding Shower Enclosure \\| Corner Double Sliding Shower Enclosure \\| Bay Swing Shower Enclosure \\| 2 Fixed and 1 Sliding Shower Enclosure \\| 2 Fixed and 1 Swing Shower Enclosure |
| Basic Options (1) | Layout | `tags` | L-shape \\| Straight \\| U-shape \\| L-type \\| Neo-angle \\| Square \\| Bay \\| Other corner layouts |
| Basic Options (1) | Configuration | `tags` | Fixed and swing \\| Swing with small fixed glass \\| Single sliding door \\| Double sliding doors \\| Sliding with fixed panels \\| Single sliding \\| Double sliding \\| With fixed panels \\| 2 fixed panels \\| 3 fixed panels \\| Custom configurations |
| Basic Options (1) | Glass Type | `tags` | Tempered |
| Basic Options (1) | Glass Color | `tags` | Clear \\| Clear with Frosted Sticker (Middle Portion) \\| 10mm Frosted Tempered |
| Basic Options (1) | Hardware Finish | `tags` | Mirror/Stainless Hardware \\| Matte Black Hardware |
| Glass Treatment (2) | Glass Treatment | `tags` | Frosted sticker (customizable patterns, opacity, colors) \\| Clear \\| Custom patterns \\| Heights (top clear, bottom frosted) \\| Colors |
| Glass Treatment (2) | Glass Thickness (mm) | `tags` | 10mm |
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



##Direct Order

## Mirrors & Specialty Glass

### Mirrors
**Step names:** Basic Options · Configuration & Details

| Step | Field | Control | Options |
| --- | --- | --- | --- |
| Step 1 (1) | Series | `tags` | Rectangle/Square Framed Mirror \| Rectangle/Square Frameless Mirror \| Oval Framed Mirror \| Oval Frameless Mirror \| Arched Framed Mirror \| Arched Frameless Mirror |
| Step 1 (1) | Shape | `tags` | Round \| Rectangle \| Oval \| Circle \| Square \| Rectangular with rounded edges \| Rectangular with arched top \| Custom shapes |
| Step 1 (1) | Corner Radius (in) | `number` | min 0 · step 0.1 |
| Step 1 (1) | Frame Type | `tags` | Frameless \| Framed |
| Step 2 (2) | Frame Material/Color | `tags` | White \| Black \| Gold \| Machine Polished Edges \| Beveled Edge |
| Step 2 (2) | Glass Type | `tags` | Copper Free and Lead Free Mirror |
| Step 2 (2) | Thickness (mm) | `tags` | 6mm |
| Step 2 (2) | Tint/Finish | `tags` | Bronze tint/color \| Grey tint (smoked) \| Colored glass |
| Step 3 (3) | Orientation | `tags` | Vertical \| Horizontal \| Vertical/Full-body |
| Step 3 (3) | Style | `tags` | French Type (grid/paneled design) |
| Step 3 (3) | Grid Pattern | `tags` | French window style grid |
| Step 3 (3) | Arrangement | `tags` | Can be displayed as triptych \| Individually |
| Step 4 (4) | Lighting | `tags` | Integrated LED lighting \| Backlighting \| Front lighting \| Integrated LED options |
| Step 4 (4) | LED Color/Temperature | `tags` | Warm white \| Cool white \| Tunable white \| RGB |
| Step 4 (4) | Control | `tags` | Touch sensor button \| Dimmer \| Defogger |
| Step 4 (4) | Additional Features | `tags` | Defogger \| Dimmer |
| Step 5 (5) | Mounting Method | `tags` | Wall-mounted \| Stand \| Adhesive \| Leaning \| Wall-mounted (often fixed above vanity) \| Fixed wall mount \| Integrated hanger \| Rope hanger \| Chain |
| Step 5 (5) | Quantity | `tags` | Available in sets (3 sets, or individually) |

**Notes:**
- Frame Color options depend on Series:
  - Framed Mirrors (Rectangle/Square, Oval, Arched): White, Black, Gold
  - Frameless Mirrors (Rectangle/Square, Oval, Arched): Machine Polished Edges, Beveled Edge
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



