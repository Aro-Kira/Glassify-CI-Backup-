# Customization Fields Presets Summary

This document summarizes the default customization field configurations for each product subcategory.

## Field Types
- **Tags**: Multiple selection options (e.g., Glass Type, Frame Color)
- **Number**: Numeric input with min/step values (e.g., Thickness, Size)
- **Checkbox**: Yes/No option (e.g., Screen, Soft-close)

---

## 1. Windows Category

### Windows → Sliding
**Step names:** Window Type · Sliding System & Size · Frame & Glass · Hardware & Accessories

**Step 1:**
- Number of Panels (Tags): 2 Panels, 4 Panels
- Transom Type (Tags): None, Fixed Transom Head (Fixed glass at top), Fixed Transom Sill (Fixed glass at bottom)

**Step 2:**
- Track System (Tags): 2 Tracks, 3 Tracks
- Panel Configuration (Tags): S | S (Sliding | Sliding), F | S (Fixed | Sliding), S | S | S | S (All Sliding), F | S | S | F (Fixed | Sliding | Sliding | Fixed)

**Step 3:**
- Frame Color (Tags): Hanalok, White, Black, Gray, Wood Finish
- Glass Type (Tags): Clear, Ultra Clear, Bronze, Light Green, Dark Gray, Copperfree Mirror, Euro Gray, Ford Blue, Reflective: Clear, Reflective: Gray, Reflective: Light Blue, Reflective: Dark Blue, Reflective: Light Green, Reflective: Dark Green, Reflective: Light Bronze, Tempered: Clear, Tempered: Bronze
- Glass Thickness (Tags): 6mm

**Step 4:**
- Lock Type (Tags): Center Lok 904 Big, Flushlok #12, Durable Flushlok, New Auto Flushlock
- Roller Type (Tags): Single Panel Roller, Blue Single Roller, Blue Double Roller
- Screen (Tags): With Screen, Without Screen

### Windows → Awning
**Step names:** Basic Options · Configuration & Details

**Step 1:**
- Glass Type (Tags): Clear, Tinted, Tinted (bronze/brown), Frosted, Low-E, Laminated
- Frame Color/Material (Tags): White, Black, Brown, Silver, Bronze, Custom colors
- Operation (Tags): Awning (crank-out), Awning (push-out)

**Step 2:**
- Size Configuration (Tags): Single panel, Multiple panels
- Opening Direction (Tags): Top-hinged
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)

### Windows → Casement
**Step names:** Basic Options · Panel Configuration · Advanced Options

**Step 1:**
- Glass Type (Tags): Clear, Tinted, Frosted, Low-E, Laminated
- Frame Color/Material (Tags): White, Black, Brown (wood-grain), Silver, Bronze, Custom colors
- Operation (Tags): Casement (hinge side configurable)

**Step 2:**
- Number of Panels (Tags): Single panel, Multiple panels
- Hinge Side (Tags): Left-hinged, Right-hinged
- Configuration (Tags): Two casement windows with fixed transom, Custom configurations

**Step 3:**
- Transom Options (Tags): Different transom sizes, Shapes, Mullion options
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)

### Windows → Fixed Glass
**Step names:** Basic Options · Installation & Details

**Step 1:**
- Glass Type (Tags): Clear, Tinted, Frosted, Low-E, Reflective coatings, Safety glass, Laminated
- Frame Color/Material (Tags): White, Black, Dark Grey/Black, Brown, Silver, Bronze, Custom colors
- Configuration (Tags): Fixed corner glass, Various angles (90°, 135°, custom), Standard fixed

**Step 2:**
- Usage (Tags): Structural/architectural feature (non-operable), Standard fixed
- Installation Method (Tags): Various integration methods, Standard mounting
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)
---

## 2. Doors Category

### Doors → Sliding
- Glass Type (Tags): Clear, Tinted, Laminated
- Handle Type (Tags): Type A, Type B, Type C
- Lock Type (Tags): Type A, Type B, Type C
- Soft-close (Checkbox)

### Doors → Frameless
- Glass Type (Tags): Clear, Tinted, Laminated
- Handle Type (Tags): Type A, Type B, Type C
- Lock Type (Tags): Type A, Type B, Type C
- Soft-close (Checkbox)

---

## 3. Glass Partitions & Enclosures Category

### Partitions → Frameless Glass
- Layout (Tags): L-shape, Straight, U-shape
- Glass Thickness (mm) (Number): min: 1, step: 0.1
- Finish (Tags): Clear, Frosted, Patterned
- Hardware Color (Tags): Black, Silver, Gold, White, Bronze

### Partitions → Shower Enclosure
- Layout (Tags): L-shape, Straight, U-shape
- Glass Thickness (mm) (Number): min: 1, step: 0.1
- Finish (Tags): Clear, Frosted, Patterned
- Hardware Color (Tags): Black, Silver, Gold, White, Bronze

### Partitions → Fixed Glass
- Layout (Tags): L-shape, Straight, U-shape
- Glass Thickness (mm) (Number): min: 1, step: 0.1
- Finish (Tags): Clear, Frosted, Patterned
- Hardware Color (Tags): Black, Silver, Gold, White, Bronze

---

## 4. Mirrors & Specialty Glass Category

### Specialty → Mirrors
- Shape (Tags): Round, Rectangle, Oval
- Edge Finish (Tags): Beveled, Polished, Raw
- Mounting Method (Tags): Wall-mounted, Stand, Adhesive

### Specialty → Top Glass
- Shape (Tags): Round, Rectangle, Oval
- Edge Finish (Tags): Beveled, Polished, Raw
- Mounting Method (Tags): Wall-mounted, Stand, Adhesive

### Specialty → Glass Board
- Shape (Tags): Round, Rectangle, Oval
- Edge Finish (Tags): Beveled, Polished, Raw
- Mounting Method (Tags): Wall-mounted, Stand, Adhesive

---

## 5. Cabinets & Furniture Category

### Cabinets → Kitchen Cabinet
- Material (Tags): Wood, MDF, Metal, Glass
- Finish (Tags): Matte, Glossy, Laminate
- Door Type (Tags): Glass, Solid
- Accessories (Tags): Handles, Locks, Soft-close

### Cabinets → Wardrobe Cabinet
- Material (Tags): Wood, MDF, Metal, Glass
- Finish (Tags): Matte, Glossy, Laminate
- Door Type (Tags): Glass, Solid
- Accessories (Tags): Handles, Locks, Soft-close

---

## 6. Commercial & Exterior Category

### Commercial → Storefront
- Safety Glass Type (Tags): Tempered, Laminated, Bulletproof
- Handrail Type (Tags): Stainless steel, Aluminum, Glass
- Mounting System (Tags): Clamp, Bolt, Adhesive

### Commercial → Glass Balcony
- Safety Glass Type (Tags): Tempered, Laminated, Bulletproof
- Handrail Type (Tags): Stainless steel, Aluminum, Glass
- Mounting System (Tags): Clamp, Bolt, Adhesive

### Commercial → Stair Railings
- Safety Glass Type (Tags): Tempered, Laminated, Bulletproof
- Handrail Type (Tags): Stainless steel, Aluminum, Glass
- Mounting System (Tags): Clamp, Bolt, Adhesive

---

## Common Field Patterns

### Windows Subcategories
All window types share the same 4 fields:
1. Glass Type (3 options)
2. Frame Color/Material (6 options)
3. Thickness (number input)
4. Screen (checkbox)

### Doors Subcategories
All door types share the same 4 fields:
1. Glass Type (3 options)
2. Handle Type (3 options)
3. Lock Type (3 options)
4. Soft-close (checkbox)

### Partitions Subcategories
All partition types share the same 4 fields:
1. Layout (3 options)
2. Glass Thickness (number input)
3. Finish (3 options)
4. Hardware Color (5 options)

### Specialty Glass Subcategories
All specialty types share the same 3 fields:
1. Shape (3 options)
2. Edge Finish (3 options)
3. Mounting Method (3 options)

### Cabinets Subcategories
All cabinet types share the same 4 fields:
1. Material (4 options)
2. Finish (3 options)
3. Door Type (2 options)
4. Accessories (3 options)

### Commercial Subcategories
All commercial types share the same 3 fields:
1. Safety Glass Type (3 options)
2. Handrail Type (3 options)
3. Mounting System (3 options)

---

## Notes

- **Step Numbers**: Every Windows subcategory follows the breakdown described above (Sliding: four steps, Awning: two, Casement: three, Fixed Glass: two) so their presets inherit the documented stage order by default.
- **Customization**: Admins can modify these presets through the "Manage Customization Fields" button in the product add/edit form.
- **Storage**: Presets are stored in `customization_field_configs` table and can be overridden per subcategory.
- **Default Values**: If no custom configuration exists, these presets are used automatically.
