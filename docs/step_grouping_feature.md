# Step Grouping Feature for Customization Fields

## Overview

Customization fields are now organized into steps with optional step names, limiting each step to a maximum of 3-4 fields for better customer experience.

## Features

### 1. **Step Grouping**
- Fields are automatically grouped by their step number
- Each step can have a maximum of 4 fields (recommended: 3-4)
- Steps are displayed in order (Step 1, Step 2, etc.)

### 2. **Optional Step Names**
- Admins can name each step (e.g., "Basic Options", "Advanced Settings", "Dimensions")
- Step names are optional - if not provided, steps show as "Step 1", "Step 2", etc.
- Step names appear in both the field manager and customer-facing forms

### 3. **Field Limit Validation**
- System warns when adding more than 4 fields to a step
- Visual indicators show field count per step
- Color-coded badges:
  - **Green**: 1-3 fields (optimal)
  - **Yellow**: 4 fields (at limit)
  - **Red**: Over 4 fields (warning)

### 4. **Smart Step Suggestions**
- When adding a new field, the system suggests the best step
- If the last step is full (4 fields), suggests creating a new step
- Shows current field count for each step in the dropdown

## Usage

### For Admins:

1. **Open Manage Customization Fields**
   - Click "Manage Customization Fields" button
   - Fields are grouped by step with headers

2. **Name a Step (Optional)**
   - In the step header, enter a name in the text input
   - Examples: "Basic Options", "Glass Selection", "Dimensions & Size"
   - Leave empty to use default "Step X" format

3. **Add Fields**
   - Click "Add Field"
   - Select which step the field should belong to
   - System shows current field count per step
   - Warning appears if step is full

4. **Monitor Field Counts**
   - Each step header shows field count badge
   - Red warning if over limit
   - System prevents overload by suggesting new steps

### For Customers:

- Fields are displayed grouped by steps
- Step names (if set) appear as section headers
- Maximum 3-4 options per step for easier decision-making
- Clear visual separation between steps

## Implementation Details

### Data Structure

```javascript
// Fields with step numbers
{
  type: "tags",
  label: "Glass Type",
  id: "glassType",
  options: [...],
  stepNumber: 1  // Step assignment
}

// Step names stored separately
{
  "Windows_Sliding_stepNames": {
    "1": "Basic Options",
    "2": "Advanced Settings"
  }
}
```

### Storage

- Step names are stored in `customizationFields` with key `${fieldKey}_stepNames`
- Saved to both localStorage and database
- Loaded automatically when fields are loaded

### Validation

- Maximum 4 fields per step (configurable via `MAX_FIELDS_PER_STEP`)
- Warning shown when limit is reached
- Admin can override warning if needed
- Visual feedback in field manager

## Benefits

1. **Better UX**: Customers see 3-4 options at a time instead of overwhelming lists
2. **Organized**: Fields logically grouped by step
3. **Flexible**: Admins can name steps to match their workflow
4. **Preventive**: System prevents field overload with warnings
5. **Clear Structure**: Visual step headers make the form easier to navigate

## Example Workflow

**Step 1: Basic Options** (3 fields)
- Glass Type
- Frame Color
- Number of Panels

**Step 2: Advanced Settings** (2 fields)
- Grid Pattern
- Thickness

**Step 3: Additional Features** (1 field)
- Screen (checkbox)

---

**Last Updated**: 2026-01-15  
**Feature Version**: 1.0