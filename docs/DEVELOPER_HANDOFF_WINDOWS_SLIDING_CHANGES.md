# Developer Handoff: Windows Sliding Customization Fields Update

## 📋 Overview

This document summarizes the changes made to the **Windows Sliding** customization fields system. The customization fields were completely overhauled to match professional 900 Series sliding window specifications with a structured 4-step configuration process.

**Date of Changes:** January 18, 2026  
**Impact:** High - Affects all Windows > Sliding product customizations

---

## 🎯 What Changed

### Before
- Simple 4-field customization:
  - Glass Type (3 options: Clear, Tinted, Laminated)
  - Frame Color/Material (6 options)
  - Thickness (number input)
  - Screen (checkbox)

### After
- **Complete 4-step professional configuration:**
  - **Step 1: Window Type** - Panel count and transom options
  - **Step 2: Sliding System & Size** - Track system and panel configuration
  - **Step 3: Frame & Glass** - Frame colors, 17 glass types, thickness
  - **Step 4: Hardware & Accessories** - Lock types, roller types, screen options

**Total Fields:** 10 fields organized across 4 steps  
**Glass Types:** Expanded from 3 to 17 options  
**Hardware Options:** Added professional lock and roller configurations

---

## 📁 Files Modified

### 1. **Controller: `application/controllers/CustomizationFieldsCon.php`**
   - Updated `Windows_Sliding` field configuration
   - Changed from simple fields to detailed 4-step process
   - Added `stepNumber` property to each field

### 2. **Database Script: `database/scripts/update_windows_sliding_customization_fields.sql`**
   - SQL script to update/insert field configurations in `customization_field_configs` table
   - Includes both field configurations and step names
   - Uses `ON DUPLICATE KEY UPDATE` for safe execution

### 3. **Reference Documentation: `docs/CUSTOMIZATION_REFERENCE.md`**
   - Complete reference of all customization fields
   - Windows > Sliding section (lines 7-21) documents the new structure

### 4. **JavaScript: `assets/js/2d-functions/2d_customization.js`**
   - Updated glass style definitions to support new glass types
   - Added visual configurations for reflective and tempered glass variants

---

## 🚀 What the Next Developer Needs to Do

### Step 1: Run Database Migration
**CRITICAL:** The database must be updated before the new customization fields will work.

```sql
-- Run this script:
database/scripts/update_windows_sliding_customization_fields.sql
```

**What it does:**
- Updates `customization_field_configs` table with new Windows_Sliding configuration
- Adds step names: `["Window Type", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]`
- Safe to run multiple times (uses `ON DUPLICATE KEY UPDATE`)

**How to run:**
1. Open phpMyAdmin or your MySQL client
2. Select the `glassify` database (or your database name)
3. Import/execute the SQL file: `database/scripts/update_windows_sliding_customization_fields.sql`
4. Verify: Check that `customization_field_configs` table has `FieldKey = 'Windows_Sliding'`

### Step 2: Verify Configuration
Test that the API returns the new fields:

```bash
# Test the API endpoint
GET /customizationFields/get?fieldKey=Windows_Sliding
```

**Expected Response:**
```json
{
  "status": "success",
  "fields": [
    {
      "type": "tags",
      "label": "Number of Panels",
      "id": "numberOfPanels",
      "options": ["2 Panels", "4 Panels"],
      "stepNumber": 1
    },
    // ... 9 more fields
  ],
  "stepNames": [
    "Window Type",
    "Sliding System & Size",
    "Frame & Glass",
    "Hardware & Accessories"
  ]
}
```

### Step 3: Test Frontend Integration
1. Navigate to a Windows > Sliding product in the shop
2. Open the customization/2D modeling interface
3. Verify that:
   - Fields appear in 4 steps (not all at once)
   - Step names display correctly
   - All 10 fields are present
   - Glass type dropdown shows 17 options
   - Panel configuration options display correctly

---

## 🏗️ System Architecture

### How Customization Fields Work

1. **Database Storage:**
   - Table: `customization_field_configs`
   - Stores field configurations as JSON in `FieldConfig` column
   - Keyed by `FieldKey` (e.g., `Windows_Sliding`)

2. **API Endpoint:**
   - Controller: `CustomizationFieldsCon::get()`
   - Route: `/customizationFields/get?fieldKey=Windows_Sliding`
   - Returns: JSON with fields array and stepNames

3. **Frontend Integration:**
   - JavaScript loads fields dynamically from API
   - Groups fields by `stepNumber`
   - Displays step names from `stepNames` array
   - Renders appropriate control types (`tags`, `number`, `checkbox`)

4. **Data Flow:**
   ```
   Database (customization_field_configs)
     ↓
   API (CustomizationFieldsCon)
     ↓
   Frontend JavaScript (dynamic_customization.js)
     ↓
   User Interface (2DModeling.php)
   ```

---

## 📊 Field Structure Reference

### Step 1: Window Type
| Field | Control | Options |
|-------|---------|---------|
| Number of Panels | `tags` | 2 Panels, 4 Panels |
| Transom Type | `tags` | None, Fixed Transom Head, Fixed Transom Sill |

### Step 2: Sliding System & Size
| Field | Control | Options |
|-------|---------|---------|
| Track System | `tags` | 2 Tracks, 3 Tracks |
| Panel Configuration | `tags` | S\|S, F\|S, S\|S\|S\|S, F\|S\|S\|F |

### Step 3: Frame & Glass
| Field | Control | Options |
|-------|---------|---------|
| Frame Color | `tags` | Hanalok, White, Black, Gray, Wood Finish |
| Glass Type | `tags` | 17 options (Clear, Ultra Clear, Bronze, Reflective types, Tempered types, etc.) |
| Glass Thickness | `tags` | 6mm |

### Step 4: Hardware & Accessories
| Field | Control | Options |
|-------|---------|---------|
| Lock Type | `tags` | Center Lok 904 Big, Flushlok #12, Durable Flushlok, New Auto Flushlock |
| Roller Type | `tags` | Single Panel Roller, Blue Single Roller, Blue Double Roller |
| Screen | `tags` | With Screen, Without Screen |

---

## ⚠️ Important Notes

### Backward Compatibility
- **Existing customizations:** Old customizations in the database will still work
- **New customizations:** Will use the new 4-step structure
- **Migration:** No automatic migration of old customizations needed (they remain valid)

### Glass Type Visual Configurations
- New glass types (Reflective, Tempered variants) have visual styles defined in `2d_customization.js`
- Admin can configure visual styles via the admin panel (stored in `tag_visual_configs` table)
- Fallback styles are defined in `glassStyles` object

### Panel Configuration Format
- Uses notation: `S | S` (Sliding | Sliding), `F | S` (Fixed | Sliding)
- Frontend JavaScript parses this to render visual indicators
- Supports 2-panel and 4-panel configurations

### Step Numbers
- Each field has a `stepNumber` property (1-4)
- Frontend groups fields by step number
- Step names are stored separately in `Windows_Sliding_stepNames` key

---

## 🔍 Troubleshooting

### Issue: Fields not appearing
**Solution:**
1. Check if database script was run
2. Verify `customization_field_configs` table has `Windows_Sliding` entry
3. Check browser console for API errors
4. Verify API endpoint returns correct data

### Issue: Step names not showing
**Solution:**
1. Check if `Windows_Sliding_stepNames` entry exists in database
2. Verify step names JSON is valid: `["Window Type", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]`

### Issue: Glass types not rendering correctly
**Solution:**
1. Check `glassStyles` object in `2d_customization.js`
2. Verify visual configs are loaded from `tag_visual_configs` table
3. Check browser console for missing style warnings

---

## 📚 Related Documentation

- **Complete Field Reference:** `docs/CUSTOMIZATION_REFERENCE.md` (lines 7-21)
- **All Changes Summary:** `docs/ALL_CHANGES.md`
- **Quick Summary:** `CHANGES_SUMMARY.md`
- **Database Schema:** Check `customization_field_configs` table structure

---

## ✅ Checklist for Next Developer

- [ ] Run `database/scripts/update_windows_sliding_customization_fields.sql`
- [ ] Verify database has `Windows_Sliding` entry in `customization_field_configs`
- [ ] Test API endpoint: `/customizationFields/get?fieldKey=Windows_Sliding`
- [ ] Test frontend: Open a Windows > Sliding product customization
- [ ] Verify all 4 steps appear with correct step names
- [ ] Verify all 10 fields are present and functional
- [ ] Test glass type selection (should show 17 options)
- [ ] Test panel configuration rendering
- [ ] Check browser console for any JavaScript errors
- [ ] Test saving a customization with new fields

---

## 🎓 Key Takeaways

1. **Database-driven:** Field configurations are stored in database, not hardcoded
2. **Step-based:** Fields are organized into steps for better UX
3. **Extensible:** Easy to add more fields or steps by updating database
4. **Professional specs:** Matches real-world 900 Series sliding window specifications
5. **Backward compatible:** Old customizations still work, new ones use new structure

---

## 📞 Questions?

If you encounter issues or need clarification:
1. Check the troubleshooting section above
2. Review `docs/CUSTOMIZATION_REFERENCE.md` for field specifications
3. Check browser console and server logs for errors
4. Verify database structure matches expected schema

---

**Last Updated:** January 18, 2026  
**Maintained By:** Development Team
