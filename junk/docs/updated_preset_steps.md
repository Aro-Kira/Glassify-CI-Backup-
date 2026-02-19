# Updated Preset Steps - Summary

## Overview

All preset customization fields have been reorganized into logical steps with 3-4 fields per step and meaningful step names.

## Step Organization by Category

### Windows

#### **Windows_Sliding**
- **Step 1: Basic Options** (4 fields)
  - Glass Type
  - Frame Color/Material
  - Number of Panels
  - Operation
- **Step 2: Design & Details** (4 fields)
  - Grid Pattern
  - Grid Position
  - Thickness (mm)
  - Screen (checkbox)

#### **Windows_Awning**
- **Step 1: Basic Options** (3 fields)
  - Glass Type
  - Frame Color/Material
  - Operation
- **Step 2: Configuration & Details** (4 fields)
  - Size Configuration
  - Opening Direction
  - Thickness (mm)
  - Screen (checkbox)

#### **Windows_Casement**
- **Step 1: Basic Options** (3 fields)
  - Glass Type
  - Frame Color/Material
  - Operation
- **Step 2: Panel Configuration** (3 fields)
  - Number of Panels
  - Hinge Side
  - Configuration
- **Step 3: Advanced Options** (3 fields)
  - Transom Options
  - Thickness (mm)
  - Screen (checkbox)

#### **Windows_Fixed Glass**
- **Step 1: Basic Options** (3 fields)
  - Glass Type
  - Frame Color/Material
  - Configuration
- **Step 2: Installation & Details** (4 fields)
  - Usage
  - Installation Method
  - Thickness (mm)
  - Screen (checkbox)

### Doors

#### **Doors_Sliding**
- **Step 1: Basic Options** (3 fields)
  - Glass Type
  - Frame Material/Color
  - Panel Count
- **Step 2: Operation & Configuration** (2 fields)
  - Operation
  - Panel Configuration
- **Step 3: Hardware & Features** (3 fields)
  - Handle Type
  - Hardware Finish
  - Soft-close (checkbox)

#### **Doors_Frameless**
- **Step 1: Basic Options** (3 fields)
  - Glass Type
  - Door Type
  - Door Swing
- **Step 2: Panel Configuration** (2 fields)
  - Fixed Panels
  - Configuration
- **Step 3: Design & Hardware** (3 fields)
  - Handle Style
  - Hardware Finish
  - Grid Pattern
- **Step 4: Glass Treatment & Installation** (4 fields)
  - Glass Treatment
  - Installation
  - Hardware
  - Soft-close (checkbox)

### Glass Partitions & Enclosures

#### **Partitions_Frameless Glass**
- **Step 1: Basic Options** (3 fields)
  - Layout
  - Glass Type
  - Finish
- **Step 2: Configuration & Hardware** (4 fields)
  - Configuration
  - Hardware Color
  - Mounting Hardware
  - Glass Thickness (mm)

#### **Partitions_Shower Enclosure**
- **Step 1: Basic Options** (3 fields)
  - Layout
  - Configuration
  - Glass Type
- **Step 2: Glass Treatment** (2 fields)
  - Glass Treatment
  - Glass Thickness (mm)
- **Step 3: Hardware & Installation** (4 fields)
  - Hardware Finish
  - Handle Style
  - Door Swing
  - Mounting

#### **Partitions_Fixed Glass**
- **Step 1: Basic Options** (3 fields)
  - Layout
  - Glass Type
  - Finish
- **Step 2: Configuration & Hardware** (4 fields)
  - Configuration
  - Mounting Hardware
  - Hardware Finish
  - Glass Thickness (mm)

### Mirrors & Specialty Glass

#### **Specialty_Mirrors**
- **Step 1: Basic Shape & Frame** (3 fields)
  - Shape
  - Frame Type
  - Frame Material/Color
- **Step 2: Finish & Details** (3 fields)
  - Edge Finish
  - Tint/Finish
  - Orientation
- **Step 3: Mounting & Installation** (3 fields)
  - Mounting Method
  - Size
  - Corner Radius (in)
- **Step 4: Lighting & Features** (4 fields)
  - Lighting
  - LED Color/Temperature
  - Control
  - Additional Features
- **Step 5: Special Options** (4 fields)
  - Style
  - Grid Pattern
  - Quantity
  - Arrangement

#### **Specialty_Top Glass**
- **Step 1: Basic Options** (2 fields)
  - Shape
  - Edge Finish
- **Step 2: Details & Installation** (2 fields)
  - Corner Radius (in)
  - Mounting Method

#### **Specialty_Glass Board**
- **Step 1: Basic Options** (2 fields)
  - Shape
  - Edge Finish
- **Step 2: Details & Installation** (2 fields)
  - Corner Radius (in)
  - Mounting Method

### Cabinets & Furniture

#### **Cabinets_Kitchen Cabinet**
- **Step 1: Basic Material & Frame** (4 fields)
  - Material
  - Frame Type
  - Cabinet Color/Finish
  - Finish Type
- **Step 2: Door Style & Design** (3 fields)
  - Cabinet Door Style
  - Door Design
  - Glass Type
- **Step 3: Hardware & Features** (4 fields)
  - Hinges
  - Handles/Pulls
  - Frame Material/Finish
  - Frame Profile
- **Step 4: Layout & Configuration** (3 fields)
  - Layout Configuration
  - Open Shelving
  - Lighting
- **Step 5: Surfaces & Accessories** (3 fields)
  - Countertop Material
  - Backsplash Material
  - Accessories
  - Organizer Materials

#### **Cabinets_Wardrobe Cabinet**
- **Step 1: Basic Options** (4 fields)
  - Material
  - Finish
  - Door Type
  - Accessories

### Commercial & Exterior

#### **Commercial_Storefront**
- **Step 1: Basic Options** (2 fields)
  - Glass Type
  - Safety Glass Type
- **Step 2: Hardware & Installation** (3 fields)
  - Handrail Type
  - Mounting System
  - Hardware Finish

#### **Commercial_Glass Balcony**
- **Step 1: Basic Options** (3 fields)
  - Safety Glass Type
  - Handrail Type
  - Mounting System

#### **Commercial_Stair Railings**
- **Step 1: Basic Options** (3 fields)
  - Safety Glass Type
  - Handrail Type
  - Mounting System

## Step Names

All categories now have preset step names that will appear in the field manager and customer forms:

- **Windows**: "Basic Options", "Design & Details", "Configuration & Details", "Panel Configuration", "Advanced Options", "Installation & Details"
- **Doors**: "Basic Options", "Operation & Configuration", "Hardware & Features", "Panel Configuration", "Design & Hardware", "Glass Treatment & Installation"
- **Partitions**: "Basic Options", "Configuration & Hardware", "Glass Treatment", "Hardware & Installation"
- **Mirrors**: "Basic Shape & Frame", "Finish & Details", "Mounting & Installation", "Lighting & Features", "Special Options"
- **Cabinets**: "Basic Material & Frame", "Door Style & Design", "Hardware & Features", "Layout & Configuration", "Surfaces & Accessories", "Basic Options"
- **Commercial**: "Basic Options", "Hardware & Installation"

## Benefits

1. **Optimal Field Distribution**: Each step has 2-4 fields (mostly 3-4)
2. **Logical Grouping**: Related fields are grouped together
3. **Meaningful Names**: Step names describe what customers will configure
4. **Better UX**: Customers see manageable options per step
5. **Easy to Customize**: Admins can still modify step names and field distribution

## Notes

- Step names are stored with the key pattern: `${fieldKey}_stepNames`
- Step names are optional - if not set, defaults to "Step 1", "Step 2", etc.
- Admins can edit step names in the "Manage Customization Fields" modal
- Field distribution can be adjusted by dragging fields between steps

---

**Last Updated**: 2026-01-15  
**Version**: 2.0 (Reorganized with Step Names)