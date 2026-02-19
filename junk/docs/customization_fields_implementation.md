# Customization Fields Implementation Guide

## Overview

The admin product add/edit form now includes comprehensive customization options from the product catalog while maintaining the same UI layout and functionality.

## What Was Changed

### Updated Function: `initializeDefaultCustomizationFields()`

The function in `assets/js/admin-js/products.js` has been enhanced to include all customization options from the product catalog. The existing UI structure remains unchanged - fields are still dynamically generated when category/subcategory is selected.

## How It Works

### 1. **Same UI Layout**
- The form layout in `admin_product.php` remains unchanged
- Fields are still displayed in the "Customize Build" tab
- The "Manage Customization Fields" button still works the same way
- All existing functionality is preserved

### 2. **Enhanced Options**
Each category/subcategory now includes comprehensive options:

#### **Windows**
- **Sliding**: Enhanced with panel counts, operation types, grid patterns, and more glass types
- **Awning**: Added operation types, size configurations, opening directions
- **Casement**: Added hinge side options, transom configurations
- **Fixed Glass**: Added corner configurations, installation methods, usage types

#### **Doors**
- **Sliding**: Enhanced with panel counts, configurations, hardware finishes, handle styles
- **Frameless**: Added door types, fixed panel options, grid patterns, glass treatments, installation methods

#### **Glass Partitions & Enclosures**
- **Frameless Glass**: Added mounting hardware options, configurations
- **Shower Enclosure**: Comprehensive options for layouts, glass treatments, hardware finishes, handle styles
- **Fixed Glass**: Added mounting hardware and finish options

#### **Mirrors & Specialty Glass**
- **Mirrors**: Comprehensive options including shapes, frame types, lighting, LED controls, mounting methods, and more
- **Top Glass**: Enhanced with more edge finish options
- **Glass Board**: Enhanced with more edge finish options

#### **Cabinets & Furniture**
- **Kitchen Cabinet**: Extensive options including materials, frames, hinges, finishes, door styles, lighting, organizers, and accessories
- **Wardrobe Cabinet**: Enhanced with more material and finish options

#### **Commercial & Exterior**
- **Storefront**: Added glass types and hardware finishes
- **Glass Balcony**: Maintained existing options
- **Stair Railings**: Maintained existing options

## Field Types

The system supports the same field types as before:

1. **Tags** (`type: "tags"`): Multi-select tag-style options
   - Users can select multiple options
   - Options can be added/edited/removed via "Manage Customization Fields"
   - Each tag can have an associated price

2. **Number** (`type: "number"`): Numeric input fields
   - Used for dimensions, thickness, corner radius, etc.
   - Supports min, max, and step values

3. **Checkbox** (`type: "checkbox"`): Boolean options
   - Used for yes/no features like "Screen", "Soft-close"

## Usage

### For Admins Adding Products:

1. **Select Order Type**: Choose "Direct Order" or "Site Assessment Order"
2. **Select Category**: Choose from available categories (filtered by order type)
3. **Select Subcategory**: Choose the specific product type
4. **Customization Fields Appear**: All relevant fields are automatically displayed
5. **Manage Fields (Optional)**: Click "Manage Customization Fields" to:
   - Add new options to existing fields
   - Edit option names
   - Remove options
   - Set prices for each option

### Field Structure

Each field follows this structure:
```javascript
{
  type: "tags" | "number" | "checkbox",
  label: "Display Label",
  id: "fieldId",
  options: ["Option 1", "Option 2", ...],  // For tags only
  min: 0,      // For number only
  step: 0.1,   // For number only
  stepNumber: 1  // Optional: for grouping fields
}
```

## Data Storage

- Customization fields are stored in `localStorage` for quick access
- Fields can be saved to the database via the "Manage Customization Fields" modal
- Saved configurations persist across sessions

## Backward Compatibility

- All existing products continue to work
- Existing saved field configurations are preserved
- The system falls back to default fields if no custom configuration exists
- Database structure remains unchanged

## Benefits

1. **Comprehensive Options**: All catalog options are now available
2. **Flexible Management**: Admins can still customize fields per category/subcategory
3. **Same UI/UX**: No learning curve - same interface as before
4. **Extensible**: Easy to add more options in the future
5. **Price Management**: Each option can have its own price

## Example: Adding a Window Product

1. Select "Site Assessment Order"
2. Select Category: "Windows"
3. Select Subcategory: "Sliding"
4. Fields automatically appear:
   - Glass Type (tags): Clear, Tinted, Frosted, Low-E, etc.
   - Frame Color/Material (tags): White, Black, Brown, etc.
   - Number of Panels (tags): 2-panel, 3-panel, 4-panel, etc.
   - Operation (tags): Sliding (left-to-right), Sliding (right-to-left), etc.
   - Grid Pattern (tags): Standard, French type, etc.
   - Thickness (number): Enter in mm
   - Screen (checkbox): Yes/No

5. Admin can select multiple options for tag fields
6. Admin can click "Manage Customization Fields" to add/edit/remove options
7. Save the product with all customization options

## Future Enhancements

Potential improvements:
- Add field grouping/collapsible sections
- Add field dependencies (e.g., show "Grid Pattern" only if "French type" is selected)
- Add field validation rules
- Add field descriptions/help text
- Add image previews for options

## Notes

- The catalog JSON file (`docs/product_catalog_json.json`) serves as reference documentation
- Options are consolidated from multiple similar products in the same category
- Some options may be specific to certain product variants - admins can customize as needed
- The "Manage Customization Fields" feature allows fine-tuning options per category/subcategory

---

**Last Updated**: 2026-01-15  
**Version**: 2.0 (Enhanced with Catalog Options)