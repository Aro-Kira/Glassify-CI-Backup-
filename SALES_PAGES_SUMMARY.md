# Sales Representative Pages - Complete Functionality Summary

## Overview
The Sales Representative module is a comprehensive system for managing orders, payments, inventory, customers, and support issues. All pages require authentication as a Sales Representative and are accessible through the sidebar navigation.

---

## 1. Dashboard Page
**File:** `application/views/sales_page/sales_dashboard.php`  
**Route:** `/sales-dashboard` → `SalesCon/sales_dashboard`  
**Controller Method:** `SalesCon::sales_dashboard()`

### Description
The main dashboard displays task cards with key metrics and a recent activities log. It provides a quick overview of the sales rep's workload and system activity.

### Database Tables Used
- `pending_review_orders` - Count orders assigned today and needing approval
- `awaiting_admin_orders` - Count orders assigned today
- `ready_to_approve_orders` - Count orders assigned today
- `approved_orders` - Count orders assigned today
- `payment` - Count payments with "Under Review" or "Pending" status
- `issuereport` - Count high priority issues and get most common category
- `system_activity_log` - Fetch recent system activities
- `inventory_items` - Generate low stock warnings for activities
- `user` - Get customer names for activity log

### Task Cards
1. **Orders Card:**
   - Main Number: Total orders assigned today (from all order status tables)
   - Subtext: Total orders needing approval (from `pending_review_orders`)
   - Button: Links to `/sales-orders`

2. **Payments Card:**
   - Main Number: Total payments with "Under Review" status
   - Subtext: "Awaiting Verification"
   - Button: Links to `/sales-payments`

3. **Issues Card:**
   - Main Number: Total high priority issues (from `issuereport` where `Priority = 'High'`)
   - Subtext: Most common category (Order Issue, Billing Issue, or Delivery Issue)
   - Button: Links to `/sales-issues`

### Recent Activities
- Displays last 10 activities from `system_activity_log`
- If log is empty, generates activities from:
  - Recent orders (Info badge)
  - Low stock warnings (Warning badge)
  - High priority issues (Error badge)
- Shows: Action type, Description, Role, User, and Timestamp

### Page Connections
- **To Orders:** Click "View Orders" button → `/sales-orders`
- **To Payments:** Click "Review Payments" button → `/sales-payments`
- **To Issues:** Click "View Issues" button → `/sales-issues`
- **Sidebar Navigation:** Accessible from all pages via sidebar menu

---

## 2. Orders Page
**File:** `application/views/sales_page/sales_orders.php`  
**Route:** `/sales-orders` → `SalesCon/sales_orders`  
**Controller Method:** `SalesCon::sales_orders()`

### Description
Manages the complete order approval workflow. Displays orders in three status tabs: Pending Review, Awaiting Admin, and Ready to Approve. Sales reps can review order details, submit orders for admin approval, and make final approval/disapproval decisions.

### Database Tables Used
- `pending_review_orders` - Orders awaiting sales rep review
- `awaiting_admin_orders` - Orders submitted to admin for approval
- `ready_to_approve_orders` - Orders approved by admin, awaiting final sales rep approval
- `order_page` - Master order details table (fallback)
- `product` - Get product category for field display logic
- `user` - Get customer information

### Order Status Workflow
1. **Pending Review** (Initial State)
   - Orders appear here when customers place orders
   - Sales rep can: View details, Submit to Admin, or Disapprove
   - Action: "Request Approval" button → Moves to "Awaiting Admin"

2. **Awaiting Admin** (Admin Review)
   - Orders submitted by sales rep for admin approval
   - Sales rep can: View details only (read-only)
   - Admin reviews and makes decision
   - Admin decision moves order to "Ready to Approve"

3. **Ready to Approve** (Final Approval)
   - Orders approved by admin, awaiting final sales rep approval
   - Shows Admin Status: "Approved" or "Disapproved"
   - Sales rep can: Approve Order (final approval) or Disapprove Order
   - **Approved:** Moves to `approved_orders` → Creates payment record → Appears in Payments page
   - **Disapproved:** Moves to `disapproved_orders` → Customer notified → Order cancelled

### Order Details Popup
Displays complete order information:
- Order ID, Product Name, Address, Date
- Shape, Dimensions (Height x Width), Type, Thickness
- Edge Work, Frame Type, Engraving
- File Attached (clickable link)
- Total Quotation (₱)
- Category-specific fields (LED Backlight, Door Operation, Configuration) - shown/hidden based on product category

### AJAX Endpoints
- `SalesCon/get_order_details` - Fetches order details for popup
- `SalesCon/request_approval` - Moves order from Pending Review to Awaiting Admin
- `SalesCon/approve_order` - Final approval, moves to approved_orders, creates payment
- `SalesCon/disapprove_order` - Disapproves order, moves to disapproved_orders

### Page Connections
- **From Dashboard:** "View Orders" button → `/sales-orders`
- **To Payments:** When order is approved → Automatically appears in `/sales-payments`
- **Sidebar Navigation:** Accessible from all pages

---

## 3. Payments Page
**File:** `application/views/sales_page/sales_payments.php`  
**Route:** `/sales-payments` → `SalesCon/sales_payments`  
**Controller Method:** `SalesCon::sales_payments()`

### Description
Manages payment processing for approved orders. Displays orders ready for payment with filtering by status (All, Paid, Pending, Under Review, Overdue). Sales reps can view payment receipts, verify payments, and mark payments as paid.

### Database Tables Used
- `approved_orders` - Source of orders ready for payment
- `payment` - Payment records with status, amount, method
- `user` - Customer information (First_Name, Last_Name, Email)
- `product` - Product images for receipt display

### Statistics Cards
- **Weekly Sales:** Total sales from approved orders in last 7 days
- **Pending Payments:** Count of payments with "Pending" status
- **Overdue Payments:** Payments pending for more than 7 days

### Payment Statuses
- **Paid:** Payment completed
- **Pending:** Payment awaiting completion
- **Under Review:** Payment receipt being verified
- **Overdue:** Payment pending for more than 7 days

### Features
- Filter by payment status (All, Paid, Pending, Under Review, Overdue)
- Search functionality
- Pagination
- View Receipt popup showing:
  - Product image
  - Total price
  - Payment method (Gcash or Cash)
  - Customer name
- Mark as Paid button - Updates payment status to "Paid"

### AJAX Endpoints
- `SalesCon/get_payment_details` - Fetches payment details for receipt popup
- `SalesCon/mark_payment_paid` - Updates payment status to "Paid" and deducts inventory materials

### Inventory Integration
When payment is marked as "Paid":
- Materials are automatically deducted from `inventory_items` table
- Stock levels updated
- Low stock/Out of stock notifications created if needed

### Page Connections
- **From Dashboard:** "Review Payments" button → `/sales-payments`
- **From Orders:** When order approved → Automatically appears here
- **Sidebar Navigation:** Accessible from all pages

---

## 4. Inventory Page
**File:** `application/views/sales_page/sales_inventory.php`  
**Route:** `/sales-inventory` → `SalesCon/sales_inventory`  
**Controller Method:** `SalesCon::sales_inventory()`

### Description
Displays raw materials inventory with real-time stock levels. Shows statistics, stock warnings, and allows filtering/searching. Sales reps can view inventory but cannot add/modify items (inventory officer only).

### Database Tables Used
- `inventory_items` - All raw material items with stock levels
- `inventory_notifications` - Out of stock alerts for sales reps

### Statistics Cards
- **Total Items:** Count of all inventory items
- **Low Stock Alerts:** Items with stock 1-10
- **Out of Stock Alerts:** Items with stock 0
- **New Items:** Items added within last 2 days

### Features
- Real-time stock numbers from database
- Status badges:
  - **New:** Items added within last 2 days (blue badge next to name)
  - **Low Stock:** Stock 1-10 (orange badge next to stock number)
  - **Out of Stock:** Stock 0 (red badge next to stock number)
- Search by item name (real-time filtering)
- Pagination (5, 10, or 25 rows per page, default 5)
- Out of Stock notifications displayed at top of page

### Stock Updates
Stock numbers only change when:
- Sales rep approves an order → Materials deducted via `Inventory_model->deduct_materials_for_order()`
- Inventory officer manually updates items

### Page Connections
- **Sidebar Navigation:** Accessible from all pages
- **From Payments:** When payment marked as paid → Inventory automatically updated

---

## 5. Products Page
**File:** `application/views/sales_page/sales_products.php`  
**Route:** `/sales-products` → `SalesCon/sales_products`  
**Controller Method:** `SalesCon::sales_products()`

### Description
Displays all available products organized by category. Sales reps can view product details, images, and prices. Products are filtered by category and searchable.

### Database Tables Used
- `product` - All product information (Product_ID, ProductName, Category, Material, Price, ImageUrl, Status, DateAdded)

### Features
- Category filter dropdown (dynamically populated from product table)
- Product grid display with:
  - Product image
  - Product name
  - Price
- Search functionality
- Pagination

### Product Categories
- Mirrors
- Shower Enclosure / Partition
- Aluminum Doors
- Aluminum and Bathroom Doors

### Page Connections
- **Sidebar Navigation:** Accessible from all pages
- **To Orders:** Product information used when viewing order details

---

## 6. End User Page
**File:** `application/views/sales_page/sales_endUser.php`  
**Route:** `/sales-endUser` → `SalesCon/sales_endUser`  
**Controller Method:** `SalesCon::sales_endUser()`

### Description
Displays customer information (end users). Shows customer details including name, email, phone, join date, last active, and status. Sales reps can view customer information but cannot edit.

### Database Tables Used
- `enduser` - Dedicated table for customer information (or `user` table as fallback)
- `user` - User account information

### Features
- Customer list with pagination (10 customers per page)
- Displays: Full Name, Email, Phone Number, Joined Date, Last Active, Status
- Search functionality
- Pagination with sliding page numbers (shows 3 pages with ellipsis)
- No action buttons (view-only)

### Page Connections
- **Sidebar Navigation:** Accessible from all pages
- **To Orders:** Customer information linked to orders via Customer_ID

---

## 7. Issues/Support Page
**File:** `application/views/sales_page/sales_issues.php`  
**Route:** `/sales-issues` → `SalesCon/sales_issues`  
**Controller Method:** `SalesCon::sales_issues()`

### Description
Displays customer support issues and tickets. Shows high priority issues that need attention. Sales reps can view issue details and contact customer information.

### Database Tables Used
- `issuereport` - Customer issue reports with:
  - Issue_ID, Customer_ID, Order_ID
  - First_Name, Last_Name, Email, PhoneNum
  - Category (Order Issue, Billing Issue, Delivery Issue)
  - Priority (Low, Medium, High)
  - Description, Report_Date

### Features
- View issue tickets
- Filter by category and priority
- View customer contact information
- Ticket details popup

### Page Connections
- **From Dashboard:** "View Issues" button → `/sales-issues`
- **Sidebar Navigation:** Accessible from all pages
- **To Orders:** Issues may be related to specific orders via Order_ID

---

## 8. Account Page
**File:** `application/views/sales_page/sales_account.php`  
**Route:** `/sales-account` → `SalesCon/sales_account`  
**Controller Method:** `SalesCon::sales_account()`

### Description
Sales representative profile management page. Allows sales reps to view and update their personal information, including name, email, phone, and password.

### Database Tables Used
- `user` - Sales rep account information

### Editable Fields
- First Name (capitalized automatically)
- Middle Name (capitalized automatically)
- Last Name (capitalized automatically)
- Email (with duplicate check)
- Phone Number
- Password (with confirm password field)
- Title (read-only, not editable)

### Features
- Edit individual fields via popup overlay
- Client-side and server-side validation
- Email format validation
- Password requirements validation
- Duplicate email prevention
- Name capitalization (first letter uppercase)
- Save button in popup - changes saved to database only when clicked
- Logout link redirects to sales login page

### AJAX Endpoints
- `SalesCon/update_account` - Updates sales rep account information

### Page Connections
- **Sidebar Navigation:** Accessible from all pages
- **To Login:** Logout button → `/sales-login`

---

## 9. Login Page
**File:** `application/views/auth/login_sales.php`  
**Route:** `/sales-login` → `Auth/sales_login`  
**Controller Method:** `Auth::sales_login()`

### Description
Sales representative authentication page. Handles login with email and password, includes "Remember Me" functionality and "Forgot Password" link.

### Database Tables Used
- `user` - User credentials and role verification

### Features
- Email and password authentication
- Role-based access (only Sales Representatives can login)
- "Remember Me" checkbox (stores email in cookie)
- "Forgot Password" link → `/forgot-password/Sales`
- Error handling:
  - Account Not Found (email doesn't exist)
  - Inactive Account
  - Incorrect Credentials

### Page Connections
- **To Dashboard:** Successful login → `/sales-dashboard`
- **To Forgot Password:** "Forgot Password?" link → `/forgot-password/Sales`
- **From Account:** Logout → Redirects here

---

## 10. Forgot Password Page
**File:** `application/views/auth/forgot_password.php`  
**Route:** `/forgot-password/Sales` → `Auth/forgot_password('Sales')`  
**Controller Method:** `Auth::forgot_password($role)`

### Description
Password reset request page. Allows sales reps to request a password reset link via email.

### Database Tables Used
- `user` - Verify email exists and belongs to Sales Representative
- Password reset token stored in `user` table (`reset_token`, `reset_token_expiry`)

### Features
- Email validation (must belong to existing Sales Representative)
- Generates reset token
- Sends reset link (email implementation placeholder)

### Page Connections
- **From Login:** "Forgot Password?" link → `/forgot-password/Sales`
- **To Reset Password:** Email link → `/reset-password/Sales/{token}`

---

## 11. Reset Password Page
**File:** `application/views/auth/reset_password.php`  
**Route:** `/reset-password/Sales/{token}` → `Auth/reset_password('Sales', $token)`  
**Controller Method:** `Auth::reset_password($role, $token)`

### Description
Password reset confirmation page. Allows sales reps to set a new password using the reset token from email.

### Database Tables Used
- `user` - Verify reset token and update password

### Features
- Token validation
- New password and confirm password fields
- Password requirements validation
- Updates password hash in database

### Page Connections
- **From Forgot Password:** Email link → `/reset-password/Sales/{token}`
- **To Login:** After successful reset → `/sales-login`

---

## Page Workflow Connections

### Order Lifecycle Flow
1. **Customer places order** → Stored in category-specific customization tables
2. **Order created** → Appears in **Orders Page** → "Pending Review" tab
3. **Sales rep reviews** → Clicks "Request Approval" → Moves to "Awaiting Admin" tab
4. **Admin reviews** → Makes decision → Moves to "Ready to Approve" tab
5. **Sales rep final approval** → Clicks "Approve Order" → 
   - Order moves to `approved_orders` table
   - Payment record created in `payment` table
   - Order appears in **Payments Page**
6. **Customer pays** → Sales rep marks as "Paid" → 
   - Payment status updated
   - Materials deducted from **Inventory Page**

### Disapproval Flow
1. **Sales rep disapproves** (at any stage) → 
   - Order moves to `disapproved_orders` table
   - Customer notified
   - Order cancelled

### Payment Flow
1. **Order approved** → Appears in **Payments Page**
2. **Customer submits payment** → Sales rep views receipt
3. **Sales rep verifies** → Marks as "Paid" → 
   - Inventory materials deducted
   - Payment status updated

### Dashboard Integration
- **Orders Card** → Links to **Orders Page**
- **Payments Card** → Links to **Payments Page**
- **Issues Card** → Links to **Issues Page**
- **Recent Activities** → Shows system-wide activity log

---

## Database Tables Summary

### Order Management Tables
- `pending_review_orders` - Initial orders awaiting sales rep review
- `awaiting_admin_orders` - Orders submitted to admin
- `ready_to_approve_orders` - Orders approved by admin, awaiting final approval
- `approved_orders` - Fully approved orders ready for payment
- `disapproved_orders` - Cancelled/rejected orders
- `order_page` - Master order details table
- `order` - Base order table

### Payment Tables
- `payment` - Payment records with status, amount, method

### Inventory Tables
- `inventory_items` - Raw materials with stock levels
- `inventory_notifications` - Out of stock alerts
- `product_materials` - Maps products to required raw materials

### Customization Tables (Category-Specific)
- `mirror_customization` - Mirror product customizations
- `shower_enclosure_customization` - Shower enclosure customizations
- `aluminum_doors_customization` - Aluminum door customizations
- `aluminum_bathroom_doors_customization` - Bathroom door customizations

### User & Customer Tables
- `user` - User accounts (sales reps, customers, admins)
- `enduser` - Customer information table
- `customer` - Customer reference table

### Support Tables
- `issuereport` - Customer issue reports

### System Tables
- `system_activity_log` - System-wide activity tracking
- `product` - Product catalog

---

## Authentication & Security

### Access Control
- All sales pages require authentication as "Sales Representative"
- Unauthorized access redirects to `/sales-login`
- Session-based authentication
- Role-based access control (RBAC)

### Password Security
- Bcrypt password hashing
- Password reset tokens with expiry
- Secure password requirements validation

---

## Key Features Summary

1. **Order Approval Workflow:** Multi-stage approval process with admin and sales rep involvement
2. **Payment Processing:** Integrated payment management with inventory deduction
3. **Inventory Management:** Real-time stock tracking with automatic deduction on payment
4. **Customer Management:** View customer information and support issues
5. **Dashboard Analytics:** Real-time metrics and activity tracking
6. **Product Catalog:** View and filter products by category
7. **Profile Management:** Update personal information with validation
8. **Activity Logging:** System-wide activity tracking for audit trail

---

## Technical Implementation

### Framework
- CodeIgniter 3.x PHP framework
- MVC architecture
- AJAX for dynamic interactions
- Session management
- Database transactions for data integrity

### Frontend
- JavaScript for dynamic UI interactions
- CSS for styling
- Font Awesome icons
- Google Fonts (Montserrat, DM Sans)

### Backend
- PHP controllers and models
- MySQL database
- RESTful AJAX endpoints
- Form validation
- Error handling and logging

