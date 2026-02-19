# Admin Order Management System - Detailed View Structure

## Overview
This document outlines the complete hierarchical structure for the Admin Order Management system, including all modules, fields, actions, and integrations.

---

## 1. ORDERS MODULE (Separated Views)

### 1.1 Direct Orders View
**Route:** `/admin-orders?type=direct`  
**Active State:** `direct-orders`

#### List View
**Filters:**
- Status: All / Pending Review / Approved / In Fabrication / Ready for Installation / Completed / Cancelled
- Date Range: Start Date - End Date (calendar picker)
- Client: Search by client name, email, or phone
- Order Number: Search by Order ID or Order Number
- Month/Year: Calendar dropdown filter

**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Order ID | `OrderNumber` | Display as-is |
| Client Name | `Customer.First_Name + Last_Name` | Full name |
| Product Name | `Product.ProductName` (from order_items) | Product name |
| Address | `DeliveryAddress` | Truncated if long |
| Order Date | `OrderDate` | MM/DD/YYYY |
| Total Amount | `TotalAmount` | ₱X,XXX.XX |
| Status | `Status` | Badge with color coding |
| Actions | Action menu | Dropdown menu |

**Status Badge Colors:**
- Pending Review: Yellow/Orange
- Approved: Blue
- In Fabrication: Purple
- Ready for Installation: Green
- Completed: Dark Green
- Cancelled: Red

**Actions Menu:**
- View Details
- Update Status
- Assign Staff
- Link to Calendar
- Export Order
- Cancel Order (if applicable)

#### Order Details Modal/Popup
**Triggered by:** Clicking "View" or Order ID

**Sections:**

**1. Order Information (Read-only)**
- Order ID: `OrderNumber`
- Order Date: `OrderDate`
- Order Type: "Direct Order" (badge)
- Status: `Status` (with status history timeline)

**2. Customer Information (Read-only)**
- Customer Name: `Customer.First_Name + Middle_Name + Last_Name`
- Email: `Customer.Email`
- Phone: `Customer.PhoneNum`
- Address: `DeliveryAddress`

**3. Sales Representative (Read-only)**
- Sales Rep Name: `SalesRep.First_Name + Last_Name`
- Sales Rep Email: `SalesRep.Email`
- Sales Rep Phone: `SalesRep.PhoneNum`

**4. Products/Items (Read-only)**
Table showing:
- Product Name
- Quantity
- Unit Price
- Subtotal
- Specifications (Shape, Dimension, Type, Thickness, Edge Work, Frame Type, Engraving)
- Design File (if attached): Thumbnail + download link

**5. Pricing & Payment (Read-only)**
- Subtotal: Sum of item subtotals
- Tax (if applicable)
- Total Quotation: `TotalAmount`
- Payment Status: Linked to payment records
- Payment Method: From payment table
- Payment Date: From payment table
- Payment Receipt: Link/thumbnail if uploaded

**6. Assigned Staff (Editable)**
- Fabrication Staff: Dropdown select (from employees table, role = 'Fabrication')
- Installation Staff: Dropdown select (from employees table, role = 'Installation')
- Current Assignments: Display current staff names
- Change Assignment: Button to update

**7. Linked Appointments (Read-only, Clickable)**
- Installation Appointment: 
  - Date & Time
  - Status
  - Assigned Staff
  - Link to appointment details
- Note: Direct orders typically only have Installation appointments (no Ocular)

**8. Admin Actions (Editable)**
- Update Status: Dropdown with valid status transitions
- Add Notes: Textarea for admin notes
- Link to Calendar: Button to open calendar view for this order
- Export Order: Generate PDF/Excel
- Barcode: Display order barcode

**Status Transition Rules:**
- Pending Review → Approved / Cancelled
- Approved → In Fabrication / Cancelled
- In Fabrication → Ready for Installation / Cancelled
- Ready for Installation → Completed / Cancelled
- Completed → (Final state)
- Cancelled → (Final state)

---

### 1.2 Site-Assessed Orders View
**Route:** `/admin-orders?type=site-assessed`  
**Active State:** `site-assessed-orders`

#### List View
**Filters:**
- Status: All / Pending Review / Approved / Ocular Pending / In Fabrication / Ready for Installation / Completed / Cancelled
- Ocular Status: All / Completed / Not Completed (special filter)
- Date Range: Start Date - End Date
- Client: Search by client name, email, or phone
- Order Number: Search by Order ID or Order Number
- Month/Year: Calendar dropdown filter

**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Order ID | `OrderNumber` | Display as-is |
| Client Name | `Customer.First_Name + Last_Name` | Full name |
| Product Name | `Product.ProductName` | Product name |
| Address | `DeliveryAddress` | Truncated if long |
| Order Date | `OrderDate` | MM/DD/YYYY |
| Ocular Status | `OcularCompleted` flag | Badge: "Completed" / "Pending" |
| Total Amount | `TotalAmount` | ₱X,XXX.XX |
| Status | `Status` | Badge with color coding |
| Actions | Action menu | Dropdown menu |

**Additional Status Badge:**
- Ocular Pending: Orange (special status for site-assessed orders)

**Actions Menu:**
- View Details
- Update Status
- Add Ocular Notes
- Assign Staff
- Link to Calendar
- Export Order
- Cancel Order

#### Order Details Modal/Popup
**Same structure as Direct Orders, PLUS:**

**Additional Section: Ocular/Site Assessment (Editable)**
- Ocular Status: Badge (Completed / Pending)
- Ocular Date: Date of ocular appointment
- Ocular Notes: Large textarea (editable)
  - Site measurements
  - Special requirements
  - Access considerations
  - Material recommendations
  - Photos: Upload/view site photos
- Ocular Completed By: Staff name (from ocular appointment)
- Ocular Report: Link to full ocular report if generated

**Linked Appointments Section (Enhanced):**
- Ocular/Site Assessment Appointment:
  - Date & Time
  - Status
  - Assigned Staff
  - Ocular Notes (preview)
  - Link to appointment details
- Installation Appointment:
  - Date & Time
  - Status
  - Assigned Staff
  - Link to appointment details

**Admin Actions (Enhanced):**
- Add/Edit Ocular Notes: Button to open ocular notes editor
- Mark Ocular as Complete: Button (only if ocular appointment exists)
- All other actions same as Direct Orders

---

## 2. APPOINTMENTS MODULE

### 2.1 Ocular / Site Assessment Appointments
**Route:** `/admin-appointment?type=ocular`  
**Active State:** `ocular-appointment`

#### List View
**Filters:**
- Status: All / In Progress / Complete / Cancelled
- Date: Date picker
- Client: Search by client name
- Assigned Staff: Filter by staff member
- Ocular Completed: Yes / No / All

**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Client | `Customer.First_Name + Last_Name` | Full name |
| Order ID | `Order.OrderNumber` | Link to order |
| Date & Time | `AppointmentDate + AppointmentTime` | MM/DD/YYYY HH:MM AM/PM |
| Assigned Staff | `AssignedStaff` | Staff name |
| Status | `Status` | Badge |
| Actions | Edit button | Button |

**Progress Steps Indicator:**
Visual timeline showing:
- Order Placed (Blue) ✓
- Ocular Visit (Orange) ← Current focus
- In Fabrication (Purple)
- Installed (Yellow)
- Completed (Green)

#### Calendar View
- Monthly calendar with appointments marked
- Color-coded by status
- Clickable dates show appointment list
- Drag-and-drop to reschedule

#### Appointment Details Modal
**Triggered by:** Clicking "Edit Progress" or appointment row

**Fields:**
- Order ID: `Order.OrderNumber` (read-only, link to order)
- Client Name: `Customer.First_Name + Last_Name` (editable)
- Service Type: "Ocular Visit" (read-only)
- Date: `AppointmentDate` (editable, date picker)
- Time: `AppointmentTime` (editable, time picker)
- Assigned Staff: `AssignedStaff` (editable, dropdown)
- Status: `Status` (editable, dropdown: In Progress / Complete / Cancelled)
- Notes: `Notes` (editable, textarea)
- Ocular Notes: Special large textarea for site assessment details (editable)
- Site Photos: Upload/view photos (if applicable)

**Actions:**
- Save Changes
- Reschedule: Opens date/time picker
- Cancel Appointment
- Link to Order: Button to view full order details
- Mark as Complete: Quick action button

---

### 2.2 Installation Appointments
**Route:** `/admin-appointment?type=installation`  
**Active State:** `installation-appointment`

#### List View
**Same structure as Ocular Appointments, but:**
- Service Type filter shows "Installed" instead of "Ocular Visit"
- Progress Steps Indicator highlights "Installed" step

#### Appointment Details Modal
**Same structure as Ocular Appointments, but:**
- Service Type: "Installed" (read-only)
- No Ocular Notes field
- Installation Notes: Textarea for installation-specific notes
- Installation Checklist: Checkbox list (if applicable)
  - Materials delivered
  - Site prepared
  - Installation completed
  - Quality check passed

---

## 3. CALENDAR / PROJECT TIMELINE MODULE
**Route:** `/admin-calendar`  
**Active State:** `calendar`

### View Options
- **Monthly View** (default): Full month calendar
- **Weekly View**: 7-day view with time slots
- **Daily View**: Single day with hourly breakdown
- **Timeline View**: Gantt-style project timeline

### Monthly View
**Display:**
- Calendar grid (Sun-Sat)
- Each day shows:
  - Day number
  - Order count badge
  - Color-coded order indicators
  - Click to expand day details

**Color Coding:**
- Direct Orders: Blue
- Site-Assessed Orders: Orange
- Ocular Appointments: Light Orange
- Installation Appointments: Green
- Fabrication: Purple
- Completed: Dark Green
- Cancelled: Red

**Day Details Panel (Sidebar):**
When clicking a date:
- List of all orders/appointments for that day
- Order type badge
- Status badge
- Time (for appointments)
- Quick actions: View, Edit, Reschedule

### Weekly View
**Display:**
- 7 columns (one per day)
- Time slots (8 AM - 6 PM)
- Orders/appointments shown as blocks
- Drag-and-drop to reschedule
- Color-coded by type/status

### Daily View
**Display:**
- Single day with hourly breakdown
- Timeline from 8 AM to 6 PM
- Appointments shown as time blocks
- Orders shown as all-day events
- Detailed information on hover/click

### Timeline View (Gantt Chart)
**Display:**
- Horizontal timeline (weeks/months)
- Each order shown as a horizontal bar
- Bar length = project duration
- Color-coded by status
- Progress indicator (% completion)
- Milestones marked:
  - Order Date
  - Ocular Date (if applicable)
  - Fabrication Start
  - Fabrication End
  - Installation Date
  - Completion Date

**Interactive Features:**
- Click order bar → View order details
- Drag bar → Reschedule (updates order dates)
- Resize bar → Update duration
- Hover → Show quick info tooltip

### Project Progress Display
**For each order in timeline:**
- Progress percentage: Calculated from status stages
- Status stages:
  - Order Placed: 0%
  - Ocular Completed (if applicable): 20%
  - Approved: 30%
  - In Fabrication: 50%
  - Ready for Installation: 75%
  - Installed: 90%
  - Completed: 100%

**Progress Calculation:**
```
Direct Orders:
- Order Placed: 0%
- Approved: 25%
- In Fabrication: 50%
- Ready for Installation: 75%
- Installed: 90%
- Completed: 100%

Site-Assessed Orders:
- Order Placed: 0%
- Ocular Completed: 20%
- Approved: 35%
- In Fabrication: 55%
- Ready for Installation: 80%
- Installed: 95%
- Completed: 100%
```

### Calendar Actions
- **Filter by Order Type**: Direct / Site-Assessed / All
- **Filter by Status**: All statuses
- **Filter by Date Range**: Start - End date
- **Search**: By order number, client name
- **Export**: Export calendar view (PDF/Excel)
- **Print**: Print calendar view

### Integration Points
- **From Orders**: Orders automatically appear on calendar
- **From Appointments**: Appointments automatically appear on calendar
- **To Orders**: Rescheduling in calendar updates order dates
- **To Appointments**: Rescheduling in calendar updates appointment dates
- **To Production**: Fabrication dates reflected in calendar

---

## 4. PRODUCTION / FABRICATION QUEUE MODULE
**Route:** `/admin-production`  
**Active State:** `fabrication-queue`

### Queue View
**Display Format:** Kanban board or List view (toggleable)

#### Kanban Board View
**Columns:**
1. **Queued** (Status: Approved, not yet started)
2. **In Progress** (Status: In Fabrication)
3. **Quality Check** (Status: Fabrication complete, pending QC)
4. **Ready** (Status: Ready for Installation)
5. **Completed** (Status: Installed/Completed)

**Card Display (per order):**
- Order Number (link to order)
- Client Name
- Product Name + Quantity
- Order Type Badge (Direct / Site-Assessed)
- Start Date
- Expected End Date
- Assigned Fabrication Staff
- Progress Bar (% completion)
- Priority Badge (if applicable)

#### List View
**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Order ID | `OrderNumber` | Link to order |
| Client | `Customer.First_Name + Last_Name` | Full name |
| Product | `Product.ProductName` | Product name |
| Quantity | `OrderItems.Quantity` | Number |
| Order Type | `OrderType` | Badge |
| Start Date | `FabricationStartDate` | MM/DD/YYYY |
| End Date | `FabricationEndDate` | MM/DD/YYYY |
| Assigned Staff | `FabricationStaff` | Staff name |
| Progress | `FabricationProgress` | Progress bar + % |
| Status | `FabricationStatus` | Badge |
| Actions | Action menu | Dropdown |

**Filters:**
- Status: All / Queued / In Progress / Quality Check / Ready / Completed
- Order Type: All / Direct / Site-Assessed
- Date Range: Start Date - End Date
- Assigned Staff: Filter by staff member
- Search: By order number, client name

### Order Details in Queue
**Clicking an order card/row opens details:**

**1. Order Information**
- Order Number (link to full order)
- Order Type
- Client Information
- Product Details

**2. Fabrication Details (Editable)**
- Start Date: `FabricationStartDate` (editable)
- Expected End Date: `FabricationEndDate` (editable)
- Actual Start Date: `ActualFabricationStartDate` (auto-set)
- Actual End Date: `ActualFabricationEndDate` (editable)
- Assigned Staff: `FabricationStaff` (editable, dropdown)
- Progress: `FabricationProgress` (editable, 0-100%)
- Status: `FabricationStatus` (editable)

**3. Production Notes (Editable)**
- Fabrication Notes: Textarea
- Quality Check Notes: Textarea
- Issues/Problems: Textarea
- Materials Used: List/table
- Photos: Upload fabrication progress photos

**4. Linked Information (Read-only)**
- Linked Order: Link to full order details
- Linked Calendar: Link to calendar view
- Installation Appointment: Link to installation appointment (if scheduled)

### Queue Actions
- **Update Progress**: Slider or input (0-100%)
- **Update Status**: Dropdown
- **Assign Staff**: Dropdown
- **Reschedule**: Update start/end dates
- **Add Notes**: Textarea for production notes
- **Mark Complete**: Button to mark fabrication complete
- **Move to Quality Check**: Button
- **Move to Ready**: Button

### Integration Points
- **From Orders**: Approved orders automatically enter queue
- **To Calendar**: Fabrication dates appear on calendar
- **To Orders**: Progress updates reflected in order status
- **To Appointments**: Completion triggers installation appointment creation

---

## 5. QUOTATIONS MODULE
**Route:** `/admin-quotations`  
**Active State:** `quotations`

### List View
**Filters:**
- Status: All / Pending / Approved / Rejected / Converted to Order
- Date Range: Start Date - End Date
- Client: Search by client name
- Sales Rep: Filter by sales representative
- Amount Range: Min - Max amount

**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Quotation ID | `QuotationID` or `QuotationNumber` | Display |
| Client | `Customer.First_Name + Last_Name` | Full name |
| Sales Rep | `SalesRep.First_Name + Last_Name` | Full name |
| Product | `Product.ProductName` | Product name |
| Amount | `TotalAmount` | ₱X,XXX.XX |
| Date Created | `CreatedDate` | MM/DD/YYYY |
| Status | `Status` | Badge |
| Actions | Action menu | Dropdown |

**Status Badges:**
- Pending: Yellow
- Approved: Green
- Rejected: Red
- Converted to Order: Blue (with order link)

### Quotation Details Modal
**Sections:**

**1. Quotation Information**
- Quotation ID
- Date Created
- Valid Until: `ExpiryDate`
- Status
- Sales Representative

**2. Customer Information**
- Customer Name
- Email
- Phone
- Address

**3. Items/Products**
- Product list with specifications
- Quantities
- Unit prices
- Subtotals
- Total amount

**4. Admin Actions**
- Approve Quotation: Button
- Reject Quotation: Button (with reason field)
- Convert to Order: Button (creates order from quotation)
- View/Print: Generate PDF
- Send to Customer: Email quotation
- Add Notes: Admin notes textarea

### Integration Points
- **To Orders**: "Convert to Order" creates new order
- **From Sales**: Quotations created by sales reps
- **Order Link**: If converted, shows linked order number

---

## 6. RETURN ORDERS MODULE
**Route:** `/admin-return-orders`  
**Active State:** `return-orders`

### List View
**Filters:**
- Status: All / Pending / Approved / Rejected / Processing / Completed
- Date Range: Start Date - End Date
- Client: Search by client name
- Original Order: Search by order number
- Return Type: All / Defect / Wrong Item / Customer Request / Other

**Table Columns:**
| Column | Field | Display Format |
|--------|-------|----------------|
| # | Row number | Sequential |
| Return ID | `ReturnID` or `ReturnNumber` | Display |
| Original Order | `Order.OrderNumber` | Link to order |
| Client | `Customer.First_Name + Last_Name` | Full name |
| Product | `Product.ProductName` | Product name |
| Return Date | `ReturnDate` | MM/DD/YYYY |
| Reason | `ReturnReason` | Truncated text |
| Status | `ReturnStatus` | Badge |
| Actions | Action menu | Dropdown |

**Status Badges:**
- Pending: Yellow
- Approved: Green
- Rejected: Red
- Processing: Blue
- Completed: Dark Green

### Return Order Details Modal
**Sections:**

**1. Return Information**
- Return ID
- Return Date
- Status
- Return Type

**2. Original Order Information (Read-only, Linked)**
- Order Number (link to order)
- Order Date
- Original Product Details
- Original Amount

**3. Return Details**
- Product Returned: Product name + specifications
- Quantity Returned
- Return Reason: `ReturnReason` (read-only)
- Return Description: Detailed description (read-only)
- Photos: Returned item photos (if uploaded)

**4. Replacement Information (Editable)**
- Replacement Required: Yes/No checkbox
- Replacement Product: Product selection (if applicable)
- Replacement Order: Link to replacement order (if created)
- Replacement Appointment: Link to installation appointment (if scheduled)

**5. Refund Information (Editable)**
- Refund Amount: `RefundAmount`
- Refund Method: Dropdown (Original Payment / Store Credit / Other)
- Refund Status: `RefundStatus`
- Refund Date: `RefundDate` (if processed)

**6. Admin Actions**
- Approve Return: Button
- Reject Return: Button (with reason field)
- Process Refund: Button
- Create Replacement Order: Button
- Schedule Replacement Installation: Button (creates appointment)
- Add Notes: Admin notes textarea
- Update Status: Status dropdown

### Integration Points
- **From Orders**: Returns linked to original orders
- **To Orders**: Replacement orders created from returns
- **To Appointments**: Replacement installation appointments
- **To Payments**: Refund processing

---

## 7. CROSS-MODULE INTEGRATIONS

### 7.1 Orders ↔ Appointments
- **Orders → Appointments**: 
  - Direct Orders: Auto-create Installation appointment on approval
  - Site-Assessed Orders: Auto-create Ocular appointment, then Installation after ocular completion
- **Appointments → Orders**: 
  - Completing Ocular appointment updates order status
  - Completing Installation appointment updates order to "Completed"

### 7.2 Orders ↔ Calendar
- **Orders → Calendar**: All orders appear on calendar with color coding
- **Calendar → Orders**: Rescheduling in calendar updates order dates

### 7.3 Orders ↔ Production
- **Orders → Production**: Approved orders enter fabrication queue
- **Production → Orders**: Fabrication completion updates order status to "Ready for Installation"

### 7.4 Appointments ↔ Calendar
- **Appointments → Calendar**: All appointments appear on calendar
- **Calendar → Appointments**: Rescheduling in calendar updates appointment dates

### 7.5 Production ↔ Calendar
- **Production → Calendar**: Fabrication dates appear on calendar
- **Calendar → Production**: Rescheduling fabrication in calendar updates production dates

### 7.6 Quotations → Orders
- **Quotations → Orders**: Converting quotation creates new order

### 7.7 Return Orders → Orders & Appointments
- **Return Orders → Orders**: Creates replacement order
- **Return Orders → Appointments**: Creates replacement installation appointment

---

## 8. COMMON FEATURES ACROSS ALL MODULES

### 8.1 Search & Filter
- Global search bar (searches across all modules)
- Module-specific filters
- Date range pickers
- Status filters
- Client/Staff filters

### 8.2 Export & Print
- Export to PDF
- Export to Excel/CSV
- Print view
- Custom date range export

### 8.3 Notifications
- Status change notifications
- Appointment reminders
- Overdue items alerts
- New order notifications

### 8.4 Staff Assignment
- Dropdown selection from employees table
- Filter by role (Fabrication, Installation, Sales, etc.)
- Multiple staff assignment (if applicable)
- Assignment history

### 8.5 Status Management
- Status transition validation
- Status history log
- Status change notifications
- Bulk status update (if applicable)

### 8.6 Notes & Comments
- Admin notes (internal)
- Customer notes (visible to customer)
- Staff notes (visible to assigned staff)
- Timestamped notes
- Note attachments (files, photos)

---

## 9. DATA MODEL RELATIONSHIPS

### Key Tables
- `order`: Main orders table
- `order_items`: Order line items
- `appointments`: Appointments table
- `product`: Products table
- `customer`: Customers table
- `employee`: Staff/employees table
- `payment`: Payment records
- `quotation`: Quotations table (if separate)
- `return_order`: Return orders table (if separate)
- `fabrication_queue`: Production queue table (if separate)

### Key Relationships
- Order → Customer (Many-to-One)
- Order → SalesRep (Many-to-One)
- Order → OrderItems (One-to-Many)
- Order → Appointments (One-to-Many)
- Order → Payment (One-to-Many)
- Appointment → Order (Many-to-One)
- Appointment → Employee (Many-to-One)
- Quotation → Order (One-to-One, if converted)
- ReturnOrder → Order (Many-to-One)

---

## 10. USER PERMISSIONS & ACCESS CONTROL

### Admin Permissions
- Full access to all modules
- Can view, edit, delete all records
- Can assign staff
- Can approve/reject orders/quotations
- Can update statuses
- Can access reports

### Role-Based Views
- Different views for different admin roles (if applicable)
- Audit log for all admin actions
- Permission checks on all actions

---

## 11. UI/UX CONSIDERATIONS

### Responsive Design
- Mobile-friendly views
- Tablet optimization
- Desktop full-featured views

### Loading States
- Skeleton loaders for tables
- Progress indicators for actions
- Success/error notifications

### Accessibility
- Keyboard navigation
- Screen reader support
- High contrast mode
- Font size options

### Performance
- Pagination for large datasets
- Lazy loading for images
- Cached data where appropriate
- Optimized database queries

---

## 12. IMPLEMENTATION NOTES

### Separation of Direct vs Site-Assessed Orders
- **Database Field**: Add `OrderType` field to `order` table ('Direct' or 'Site-Assessed')
- **Filtering**: Use `OrderType` field to separate views
- **UI Indicators**: Clear badges/colors to distinguish types
- **Workflow Differences**: Site-Assessed orders require ocular completion before fabrication

### Calendar Integration
- Use existing calendar library (FullCalendar, etc.)
- Real-time updates via AJAX
- Drag-and-drop functionality
- Color coding by order type and status

### Production Queue
- Consider Kanban board library (Trello-style)
- Real-time progress updates
- Staff assignment interface
- Progress tracking with photos

### Status Synchronization
- Ensure status changes in one module reflect in others
- Use database triggers or application-level sync
- Maintain status history log

---

## END OF DOCUMENT

This structure provides a comprehensive foundation for implementing the Admin Order Management system with clear separation between Direct and Site-Assessed orders, and full integration across all modules.
