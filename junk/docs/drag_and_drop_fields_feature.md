# Drag and Drop Fields Feature

## Overview

The "Manage Customization Fields" modal now supports drag-and-drop reordering of fields for improved user experience. Admins can easily reorder fields by dragging them up or down.

## Features

### 1. **Drag Handle**
- Each field item has a drag handle icon (⋮⋮) on the left
- The handle provides visual indication that the item is draggable
- Cursor changes to "grab" when hovering over the handle
- Cursor changes to "grabbing" when actively dragging

### 2. **Visual Feedback**
- **Hover State**: Field items highlight with a border and shadow when hovered
- **Dragging State**: The item being dragged becomes semi-transparent (50% opacity)
- **Drop Target**: Fields show a blue top border when another item is being dragged over them
- **Smooth Transitions**: All state changes are animated for better UX

### 3. **Drag and Drop Behavior**
- Drag can be initiated from anywhere on the field item
- Buttons (Edit, Remove, Move Up/Down) do not trigger drag
- Items can be dragged to any position in the list
- The order is automatically saved when "Save Changes" is clicked

### 4. **Alternative Controls**
- Up/Down arrow buttons are still available as an alternative to dragging
- These buttons are useful for precise single-step movements
- Buttons are disabled at the top (Up) and bottom (Down) of the list

## Implementation Details

### JavaScript Changes
- Added drag event handlers (`dragstart`, `dragend`, `dragover`, `dragleave`, `drop`)
- Implemented helper function `getDragAfterElement()` to determine drop position
- Fields are reordered in the `workingFields` array based on final DOM position
- The array is updated on drop, then the UI is re-rendered

### CSS Changes
- Added `.field-manager-drag-handle` styles for the drag handle
- Added `.dragging` state styles for the item being dragged
- Added `.drag-over` state styles for drop targets
- Improved hover states for better visual feedback

## Usage

1. **Open Manage Customization Fields**: Click "Manage Customization Fields" button
2. **Drag a Field**: Click and hold on any field item (or the drag handle)
3. **Move to New Position**: Drag the item up or down to the desired position
4. **Drop**: Release the mouse button to drop the item in the new position
5. **Save**: Click "Save Changes" to persist the new order

## Technical Notes

- The drag-and-drop uses HTML5 Drag and Drop API
- Field order is determined by reading the final DOM position after drag
- Fields are matched by their label and type to ensure correct reordering
- The order is saved to both localStorage and database
- All existing functionality (Edit, Remove, Add) remains unchanged

## Browser Compatibility

- Works in all modern browsers that support HTML5 Drag and Drop API
- Tested in Chrome, Firefox, Edge, and Safari

---

**Last Updated**: 2026-01-15  
**Feature Version**: 1.0