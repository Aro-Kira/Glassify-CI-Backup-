-- =====================================================
-- Update Windows_Sliding Customization Fields
-- Syncs database with customization_defaults.php and customization_defaults.js
-- Based on CUSTOMIZATION_REFERENCE.md lines 4-21
-- =====================================================

-- Update or Insert Windows_Sliding field configuration
INSERT INTO `customization_field_configs` 
(`Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`)
VALUES (
  'Windows',
  'Sliding',
  'Windows_Sliding',
  '[
    {
      "type": "tags",
      "label": "Number of Panels",
      "id": "numberOfPanels",
      "options": ["2 Panels", "4 Panels"],
      "stepNumber": 1
    },
    {
      "type": "tags",
      "label": "Transom Type (Top / Bottom Fixed Panel)",
      "id": "transomTypeTopBottomFixedPanel",
      "options": [
        "None",
        "Fixed Transom Head (Fixed glass at top)",
        "Fixed Transom Sill (Fixed glass at bottom)"
      ],
      "stepNumber": 1
    },
    {
      "type": "tags",
      "label": "Track System (Sliding Rail Count)",
      "id": "trackSystemSlidingRailCount",
      "options": ["2 Tracks", "3 Tracks"],
      "stepNumber": 2
    },
    {
      "type": "tags",
      "label": "Panel Configuration",
      "id": "panelConfiguration",
      "options": [
        "S | S (Sliding | Sliding)",
        "F | S (Fixed | Sliding)",
        "S | S | S | S (All Sliding)",
        "F | S | S | F (Fixed | Sliding | Sliding | Fixed)"
      ],
      "stepNumber": 2
    },
    {
      "type": "tags",
      "label": "Frame Color",
      "id": "frameColor",
      "options": ["Hanalok", "White", "Black", "Gray", "Wood Finish"],
      "stepNumber": 3
    },
    {
      "type": "tags",
      "label": "Glass Type",
      "id": "glassType",
      "options": [
        "Clear",
        "Ultra Clear",
        "Bronze",
        "Light Green",
        "Dark Gray",
        "Copperfree Mirror",
        "Euro Gray",
        "Ford Blue",
        "Reflective: Clear",
        "Reflective: Gray",
        "Reflective: Light Blue",
        "Reflective: Dark Blue",
        "Reflective: Light Green",
        "Reflective: Dark Green",
        "Reflective: Light Bronze",
        "Tempered: Clear",
        "Tempered: Bronze"
      ],
      "stepNumber": 3
    },
    {
      "type": "tags",
      "label": "Glass Thickness",
      "id": "glassThickness",
      "options": ["6mm"],
      "stepNumber": 3
    },
    {
      "type": "tags",
      "label": "Lock Type",
      "id": "lockType",
      "options": [
        "Center Lok 904 Big",
        "Flushlok #12",
        "Durable Flushlok",
        "New Auto Flushlock"
      ],
      "stepNumber": 4
    },
    {
      "type": "tags",
      "label": "Roller Type",
      "id": "rollerType",
      "options": [
        "Single Panel Roller",
        "Blue Single Roller",
        "Blue Double Roller"
      ],
      "stepNumber": 4
    },
    {
      "type": "tags",
      "label": "Screen",
      "id": "screen",
      "options": ["With Screen", "Without Screen"],
      "stepNumber": 4
    }
  ]',
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `FieldConfig` = VALUES(`FieldConfig`),
  `Updated_Date` = NOW();

-- Update step names if they exist
INSERT INTO `customization_field_configs` 
(`Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`)
VALUES (
  'Windows',
  'Sliding',
  'Windows_Sliding_stepNames',
  '["Window Type", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]',
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `FieldConfig` = VALUES(`FieldConfig`),
  `Updated_Date` = NOW();
