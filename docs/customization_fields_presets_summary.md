# Customization Fields Presets Summary

This document summarizes the default customization field configurations for each product subcategory.

## Field Types
- **Tags**: Multiple selection options (e.g., Glass Type, Frame Color)
- **Number**: Numeric input with min/step values (e.g., Thickness, Size)
- **Checkbox**: Yes/No option (e.g., Screen, Soft-close)

---

## 1. Windows Category

### Windows → Sliding
**Step 1:**
- Glass Type (Tags): Clear, Tinted, Laminated
- Frame Color/Material (Tags): White, Black, Silver, Bronze, Wood, Aluminum

**Step 2:**
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)

### Windows → Awning
- Glass Type (Tags): Clear, Tinted, Laminated
- Frame Color/Material (Tags): White, Black, Silver, Bronze, Wood, Aluminum
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)

### Windows → Casement
- Glass Type (Tags): Clear, Tinted, Laminated
- Frame Color/Material (Tags): White, Black, Silver, Bronze, Wood, Aluminum
- Thickness (mm) (Number): min: 1, step: 0.1
- Screen (Checkbox)

### Windows → Fixed Glass
- Glass Type (Tags): Clear, Tinted, Laminated
- Frame Color/Material (Tags): White, Black, Silver, Bronze, Wood, Aluminum
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

- **Step Numbers**: Only `Windows_Sliding` has explicit step numbers assigned (Step 1 and Step 2). Other subcategories will default to Step 1 for all fields unless configured.
- **Customization**: Admins can modify these presets through the "Manage Customization Fields" button in the product add/edit form.
- **Storage**: Presets are stored in `customization_field_configs` table and can be overridden per subcategory.
- **Default Values**: If no custom configuration exists, these presets are used automatically.
