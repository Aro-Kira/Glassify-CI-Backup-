-- =====================================================
-- Migration: Update Mirror Customization Fields
-- Description: Clean up redundant options in Specialty_Mirrors configuration
-- Date: 2026-01-17
-- =====================================================

-- This migration updates the customization field configuration for Mirrors
-- to remove redundant/duplicate options and organize the fields better.

-- Changes made:
-- 1. Shape: Removed "Circle" (duplicate of "Round"), simplified verbose options
-- 2. Frame Type: Removed colors from type field, kept only frame styles
-- 3. Frame Color: Consolidated duplicate color options
-- 4. Edge Finish: Removed duplicate variations (Beveled/Beveled edge, etc.)
-- 5. Tint: Renamed and added "Clear" option
-- 6. Orientation: Simplified options
-- 7. Size: Replaced 13 verbose options with 5 simple options
-- 8. Mounting Method: Consolidated duplicate mounting variations
-- 9. Merged Control + Additional Features into new "Smart Features" field
-- 10. Removed Step 5 (Style, Grid Pattern, Quantity, Arrangement)
-- 11. Reduced from 5 steps to 4 steps

-- Update the Specialty_Mirrors configuration
UPDATE `customization_field_configs` 
SET `FieldConfig` = '[
  {"type":"tags","label":"Shape","id":"shape","options":["Round","Oval","Square","Rectangle","Arched","Custom"],"stepNumber":1},
  {"type":"tags","label":"Frame Type","id":"frameType","options":["Frameless","Standard Frame","Thin Frame","Grid Frame"],"stepNumber":1},
  {"type":"tags","label":"Frame Color","id":"frameColor","options":["Gold","Silver","Rose Gold","Bronze","Black","White","Wood","Custom Color"],"stepNumber":1},
  {"type":"tags","label":"Edge Finish","id":"edgeFinish","options":["Beveled","Flat Polish","Pencil Edge","Raw"],"stepNumber":2},
  {"type":"tags","label":"Tint","id":"tintFinish","options":["Clear","Bronze","Grey (Smoked)","Black"],"stepNumber":2},
  {"type":"tags","label":"Orientation","id":"orientation","options":["Vertical","Horizontal","Full-body"],"stepNumber":2},
  {"type":"tags","label":"Size","id":"size","options":["Small","Medium","Large","Extra Large","Custom"],"stepNumber":3},
  {"type":"number","label":"Corner Radius (in)","id":"cornerRadius","min":0,"step":0.1,"stepNumber":3},
  {"type":"tags","label":"Mounting Method","id":"mountingMethod","options":["Wall-mounted","Freestanding","Leaning","Adhesive","Hanging"],"stepNumber":3},
  {"type":"tags","label":"Lighting","id":"lighting","options":["None","LED Backlight","LED Front Light"],"stepNumber":4},
  {"type":"tags","label":"LED Color","id":"ledColorTemperature","options":["Warm White","Cool White","Daylight","RGB"],"stepNumber":4},
  {"type":"tags","label":"Smart Features","id":"smartFeatures","options":["Touch Dimmer","Defogger","Motion Sensor","Bluetooth Speaker"],"stepNumber":4}
]',
`Updated_Date` = NOW()
WHERE `FieldKey` = 'Specialty_Mirrors';

-- Verify the update
SELECT ConfigID, Category, Subcategory, FieldKey, Updated_Date 
FROM `customization_field_configs` 
WHERE `FieldKey` = 'Specialty_Mirrors';

-- =====================================================
-- OPTIONAL: If entry doesn't exist, insert it
-- =====================================================
INSERT INTO `customization_field_configs` (`Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`)
SELECT 
  'Mirrors & Specialty Glass',
  'Mirrors',
  'Specialty_Mirrors',
  '[
    {"type":"tags","label":"Shape","id":"shape","options":["Round","Oval","Square","Rectangle","Arched","Custom"],"stepNumber":1},
    {"type":"tags","label":"Frame Type","id":"frameType","options":["Frameless","Standard Frame","Thin Frame","Grid Frame"],"stepNumber":1},
    {"type":"tags","label":"Frame Color","id":"frameColor","options":["Gold","Silver","Rose Gold","Bronze","Black","White","Wood","Custom Color"],"stepNumber":1},
    {"type":"tags","label":"Edge Finish","id":"edgeFinish","options":["Beveled","Flat Polish","Pencil Edge","Raw"],"stepNumber":2},
    {"type":"tags","label":"Tint","id":"tintFinish","options":["Clear","Bronze","Grey (Smoked)","Black"],"stepNumber":2},
    {"type":"tags","label":"Orientation","id":"orientation","options":["Vertical","Horizontal","Full-body"],"stepNumber":2},
    {"type":"tags","label":"Size","id":"size","options":["Small","Medium","Large","Extra Large","Custom"],"stepNumber":3},
    {"type":"number","label":"Corner Radius (in)","id":"cornerRadius","min":0,"step":0.1,"stepNumber":3},
    {"type":"tags","label":"Mounting Method","id":"mountingMethod","options":["Wall-mounted","Freestanding","Leaning","Adhesive","Hanging"],"stepNumber":3},
    {"type":"tags","label":"Lighting","id":"lighting","options":["None","LED Backlight","LED Front Light"],"stepNumber":4},
    {"type":"tags","label":"LED Color","id":"ledColorTemperature","options":["Warm White","Cool White","Daylight","RGB"],"stepNumber":4},
    {"type":"tags","label":"Smart Features","id":"smartFeatures","options":["Touch Dimmer","Defogger","Motion Sensor","Bluetooth Speaker"],"stepNumber":4}
  ]',
  NOW(),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `customization_field_configs` WHERE `FieldKey` = 'Specialty_Mirrors'
);
