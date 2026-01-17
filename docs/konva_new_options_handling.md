# How Konva.js Handles New Customization Field Options

## Current Behavior When Admin Adds New Options

When an admin adds a new option to a customization field (e.g., adds "Gold" to Frame Color or "Triple-pane" to Glass Type), here's what happens:

### 1. **UI Rendering** ✅ Works
- The new option is dynamically rendered in the UI via `dynamic_customization.js`
- Users can see and select the new option
- The option appears in the customization interface

### 2. **Konva.js Visualization** ⚠️ Limited Support

#### Normalization Functions (Hardcoded Mappings)

Konva.js uses normalization functions to map option values to visual styles:

**Glass Type Normalization** (`normalizeGlassType`):
```javascript
// Located in: assets/js/2d-functions/2d_customization.js (lines 301-315)
function normalizeGlassType(glassType) {
    const mapping = {
        'clear': 'clear',
        'tinted': 'tinted',
        'laminated': 'laminated',
        'tempered': 'tempered',
        'double': 'double',
        'low-e': 'low-e',
        'frosted': 'frosted',
        'patterned': 'patterned'
    };
    return mapping[normalized] || 'clear'; // ⚠️ Falls back to 'clear'
}
```

**Frame Type Normalization** (`normalizeFrameType`):
```javascript
// Located in: assets/js/2d-functions/2d_customization.js (lines 321-334)
function normalizeFrameType(frameType) {
    const mapping = {
        'white': 'white',
        'black': 'black',
        'silver': 'silver',
        'bronze': 'bronze',
        'wood': 'wood',
        'aluminum': 'aluminum',
        'vinyl': 'vinyl'
    };
    return mapping[normalized] || 'white'; // ⚠️ Falls back to 'white'
}
```

**Shape Normalization** (`normalizeShape`):
```javascript
// Located in: assets/js/2d-functions/2d_customization.js (lines 340-352)
function normalizeShape(shape) {
    const mapping = {
        'rectangle': 'rectangle',
        'rectangular': 'rectangle',
        'round': 'round',
        'circle': 'round',
        'oval': 'oval',
        'ellipse': 'oval'
    };
    return mapping[normalized] || 'rectangle'; // ⚠️ Falls back to 'rectangle'
}
```

#### Visual Style Definitions (Hardcoded)

Even if normalization works, visual styles are hardcoded:

**Glass Styles** (lines 73-84):
```javascript
const glassStyles = {
    'clear': { fill: '#E0F2F1', opacity: 0.9 },
    'tinted': { fill: '#546E7A', opacity: 0.7 },
    'laminated': { fill: '#CFD8DC', opacity: 0.95 },
    'tempered': { fill: '#E0F2F1', opacity: 0.9 },
    'double': { fill: '#B2DFDB', opacity: 0.9 },
    'low-e': { fill: '#Dcedc8', opacity: 0.85 },
    'frosted': { fill: '#FFFFFF', opacity: 0.95 },
    'patterned': { fill: '#E8E8E8', opacity: 0.9 }
};
```

**Frame Styles** (lines 87-97):
```javascript
const frameStyles = {
    'white': { color: '#FFFFFF', width: 4 },
    'black': { color: '#000000', width: 4 },
    'silver': { color: '#C0C0C0', width: 3 },
    'bronze': { color: '#CD7F32', width: 3 },
    'wood': { color: '#795548', width: 6 },
    'aluminum': { color: '#90A4AE', width: 3 },
    'vinyl': { color: '#333333', width: 4 }
};
```

### 3. **What Happens with New Options**

#### Scenario: Admin adds "Gold" to Frame Color field

1. ✅ **UI**: "Gold" option appears and can be selected
2. ⚠️ **Normalization**: `normalizeFrameType("gold")` → returns `'white'` (fallback)
3. ⚠️ **Visual**: Konva renders with white frame color (not gold)
4. ✅ **Data**: Selection is saved correctly in database

#### Scenario: Admin adds "Triple-pane" to Glass Type

1. ✅ **UI**: "Triple-pane" option appears and can be selected
2. ⚠️ **Normalization**: `normalizeGlassType("triple-pane")` → returns `'clear'` (fallback)
3. ⚠️ **Visual**: Konva renders with clear glass style (not triple-pane specific)
4. ✅ **Data**: Selection is saved correctly in database

### 4. **Current Limitations**

1. **Hardcoded Mappings**: New options not in the mapping fall back to defaults
2. **No Dynamic Styles**: Visual styles are hardcoded, so new options can't have custom colors/appearances
3. **Silent Fallbacks**: No warning to admin that new option won't render correctly
4. **Inconsistent Visualization**: What user selects ≠ what they see in preview

## Recommended Solutions

### Option 1: Dynamic Style System (Recommended)

Allow admins to configure visual styles when adding options:

1. **Database Enhancement**: Add `visual_config` column to customization field options table
   ```sql
   ALTER TABLE customization_field_options 
   ADD COLUMN visual_config JSON;
   -- Example: {"fill": "#FFD700", "opacity": 0.9, "stroke": "#FFA500", "strokeWidth": 3}
   ```

2. **Update Normalization Functions**: Make them check database first, then fallback
   ```javascript
   async function normalizeGlassType(glassType) {
       // Check database for custom visual config
       const customConfig = await getVisualConfigFromDB('glassType', glassType);
       if (customConfig) return customConfig;
       
       // Fallback to hardcoded mapping
       return mapping[normalized] || 'clear';
   }
   ```

3. **Dynamic Style Loading**: Load styles from database on page load
   ```javascript
   async function loadDynamicStyles() {
       const customStyles = await fetchCustomStylesFromDB();
       Object.assign(glassStyles, customStyles.glass);
       Object.assign(frameStyles, customStyles.frame);
   }
   ```

### Option 2: Admin Configuration UI

Add visual style configuration in admin panel when adding options:

- Color picker for frame colors
- Opacity slider for glass types
- Preview of how it will look

### Option 3: Smart Fallbacks with Warnings

Keep current system but add warnings:

```javascript
function normalizeGlassType(glassType) {
    const normalized = glassType.toLowerCase().replace(/\s+/g, '-');
    const mapped = mapping[normalized];
    
    if (!mapped) {
        console.warn(`Glass type "${glassType}" not found in mapping, using default "clear"`);
        // Optionally show admin notification
        if (window.isAdmin) {
            showAdminWarning(`New glass type "${glassType}" needs visual configuration`);
        }
    }
    
    return mapped || 'clear';
}
```

## Files That Need Updates

If implementing dynamic styles:

1. **Database Schema**: `database/scripts/add_customization_fields_tables.sql`
2. **Backend Model**: `application/models/Product_model.php` (add visual config methods)
3. **Admin Controller**: `application/controllers/ProductCon.php` (handle visual config)
4. **Frontend Normalization**: `assets/js/2d-functions/2d_customization.js`
5. **Admin Preview**: `assets/js/admin-js/admin_konva_preview.js`
6. **Dynamic Renderer**: `assets/js/2d-functions/dynamic_customization.js`

## Current Workaround

Until dynamic styles are implemented:

1. **For Admins**: Only add options that match existing mappings
2. **For Developers**: Manually add new options to normalization functions and style objects
3. **Documentation**: Keep a list of supported options in `customization_fields_presets_summary.md`

## Summary

**Current State**: 
- ✅ New options work in UI and database
- ⚠️ New options fall back to default visual styles in Konva.js
- ⚠️ No way to configure custom visual styles for new options

**Impact**: 
- Users can select new options but see default visualizations
- May cause confusion if visual doesn't match selection
- Data integrity is maintained (correct values saved)

**Recommendation**: Implement Option 1 (Dynamic Style System) for full flexibility.
