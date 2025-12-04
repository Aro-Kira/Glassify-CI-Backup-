# Sales Representative Pages - Brief Summary

## 1. Dashboard Page
**File:** `application/views/sales_page/sales_dashboard.php`  
**Route:** `/sales-dashboard`

### Description
Main dashboard showing task cards with key metrics (orders today, payments under review, high priority issues) and recent system activities log.

### Database Tables
- `pending_review_orders`, `awaiting_admin_orders`, `ready_to_approve_orders`, `approved_orders` - Count orders
- `payment` - Count under review payments
- `issuereport` - Count high priority issues
- `system_activity_log` - Recent activities
- `inventory_items` - Low stock warnings

### Page Connections
- **To Orders:** "View Orders" button → `/sales-orders`
- **To Payments:** "Review Payments" button → `/sales-payments`
- **To Issues:** "View Issues" button → `/sales-issues`
- **Sidebar:** Accessible from all pages

---

## 2. Orders Page
**File:** `application/views/sales_page/sales_orders.php`  
**Route:** `/sales-orders`

### Description
Manages order approval workflow with 3 tabs: Pending Review, Awaiting Admin, Ready to Approve. Sales rep reviews orders, submits for admin approval, and makes final approval/disapproval decisions.

### Database Tables
- `pending_review_orders` - Initial orders awaiting review
- `awaiting_admin_orders` - Orders sent to admin
- `ready_to_approve_orders` - Admin-approved orders
- `approved_orders` - Final approved orders (after sales rep approval)
- `disapproved_orders` - Rejected orders
- `order_page` - Master order details
- `product` - Product category information

### Page Connections
- **From Dashboard:** "View Orders" button → `/sales-orders`
- **To Payments:** When order approved → Automatically appears in `/sales-payments`
- **Workflow:** Pending Review → Request Approval → Awaiting Admin → Admin Decision → Ready to Approve → Approve/Disapprove → Approved Orders or Disapproved Orders

---

## 3. Payments Page
**File:** `application/views/sales_page/sales_payments.php`  
**Route:** `/sales-payments`

### Description
Manages payment processing for approved orders. Displays orders with payment status, allows viewing receipts, verifying payments, and marking as paid. When marked paid, automatically deducts materials from inventory.

### Database Tables
- `approved_orders` - Source of orders ready for payment
- `payment` - Payment records (status, amount, method)
- `user` - Customer information
- `product` - Product images
- `inventory_items` - Materials deducted when payment marked as paid
- `product_materials` - Maps products to required materials

### Page Connections
- **From Dashboard:** "Review Payments" button → `/sales-payments`
- **From Orders:** When order approved → Automatically appears here
- **To Inventory:** When payment marked as paid → Materials automatically deducted

---

## 4. Inventory Page
**File:** `application/views/sales_page/sales_inventory.php`  
**Route:** `/sales-inventory`

### Description
Displays raw materials inventory with real-time stock levels. Shows statistics (total items, low stock, out of stock, new items), status badges, and allows searching by item name. Sales reps can view but cannot modify inventory.

### Database Tables
- `inventory_items` - Raw materials with stock levels
- `inventory_notifications` - Out of stock alerts

### Page Connections
- **Sidebar:** Accessible from all pages
- **From Payments:** When payment marked as paid → Stock automatically updated here
- **Stock Updates:** Only changes when orders approved (materials deducted) or inventory officer updates

---

## 5. Products Page
**File:** `application/views/sales_page/sales_products.php`  
**Route:** `/sales-products`

### Description
Displays product catalog organized by category. Shows product images, names, prices. Filterable by category with search functionality. View-only for sales reps.

### Database Tables
- `product` - Product catalog (Product_ID, ProductName, Category, Material, Price, ImageUrl, Status)

### Page Connections
- **Sidebar:** Accessible from all pages
- **To Orders:** Product information used when viewing order details in Orders page

---

## 6. End User Page
**File:** `application/views/sales_page/sales_endUser.php`  
**Route:** `/sales-endUser`

### Description
Displays customer (end user) information including name, email, phone, join date, last active, and status. View-only with pagination (10 per page) and search functionality.

### Database Tables
- `enduser` - Customer information table (or `user` table as fallback)

### Page Connections
- **Sidebar:** Accessible from all pages
- **To Orders:** Customer information linked to orders via Customer_ID

---

## 7. Issues/Support Page
**File:** `application/views/sales_page/sales_issues.php`  
**Route:** `/sales-issues`

### Description
Displays customer support issues and tickets. Shows high priority issues that need attention. Sales reps can view issue details and customer contact information.

### Database Tables
- `issuereport` - Customer issue reports (Issue_ID, Customer_ID, Order_ID, Category, Priority, Description, Report_Date)

### Page Connections
- **From Dashboard:** "View Issues" button → `/sales-issues`
- **Sidebar:** Accessible from all pages
- **To Orders:** Issues may reference specific orders via Order_ID

---

## 8. Account Page
**File:** `application/views/sales_page/sales_account.php`  
**Route:** `/sales-account`

### Description
Sales representative profile management. Allows updating personal information (name, email, phone, password) with validation. Title field is read-only. Changes saved only when "Save" button clicked.

### Database Tables
- `user` - Sales rep account information

### Page Connections
- **Sidebar:** Accessible from all pages
- **To Login:** Logout button → `/sales-login`

---

## 9. Login Page
**File:** `application/views/auth/login_sales.php`  
**Route:** `/sales-login`

### Description
Sales representative authentication. Handles login with email/password, includes "Remember Me" functionality and "Forgot Password" link. Only Sales Representatives can access.

### Database Tables
- `user` - User credentials and role verification

### Page Connections
- **To Dashboard:** Successful login → `/sales-dashboard`
- **To Forgot Password:** "Forgot Password?" link → `/forgot-password/Sales`
- **From Account:** Logout → Redirects here

---

## 10. Forgot Password Page
**File:** `application/views/auth/forgot_password.php`  
**Route:** `/forgot-password/Sales`

### Description
Password reset request page. Sales reps enter email to receive password reset link. Only accepts emails belonging to existing Sales Representatives.

### Database Tables
- `user` - Verify email exists and belongs to Sales Representative, store reset token

### Page Connections
- **From Login:** "Forgot Password?" link → `/forgot-password/Sales`
- **To Reset Password:** Email link → `/reset-password/Sales/{token}`

---

## 11. Reset Password Page
**File:** `application/views/auth/reset_password.php`  
**Route:** `/reset-password/Sales/{token}`

### Description
Password reset confirmation. Sales reps set new password using reset token from email. Validates token and updates password in database.

### Database Tables
- `user` - Verify reset token and update password hash

### Page Connections
- **From Forgot Password:** Email link → `/reset-password/Sales/{token}`
- **To Login:** After successful reset → `/sales-login`

---

## Complete Workflow Connections

### Order Lifecycle Flow
```
Customer Places Order
    ↓
Orders Page → "Pending Review" Tab
    ↓
Sales Rep Reviews → "Request Approval"
    ↓
Orders Page → "Awaiting Admin" Tab
    ↓
Admin Reviews & Decides
    ↓
Orders Page → "Ready to Approve" Tab
    ↓
Sales Rep Final Approval → "Approve Order"
    ↓
Order moves to approved_orders
Payment record created
    ↓
Payments Page → Order appears here
    ↓
Customer Pays → Sales Rep Marks as "Paid"
    ↓
Inventory Page → Materials automatically deducted
```

### Disapproval Flow
```
Any Order Status
    ↓
Sales Rep Disapproves
    ↓
Order moves to disapproved_orders
Customer notified
Order cancelled
```

### Payment Flow
```
Orders Page → Order Approved
    ↓
Payments Page → Order appears
    ↓
Customer Submits Payment
    ↓
Sales Rep Views Receipt
    ↓
Sales Rep Marks as "Paid"
    ↓
Payment Status Updated
Inventory Materials Deducted
```

### Dashboard Integration
- **Orders Card** → Click → Orders Page
- **Payments Card** → Click → Payments Page  
- **Issues Card** → Click → Issues Page
- **Recent Activities** → Shows system-wide log

### Sidebar Navigation
All pages accessible via sidebar menu:
- Dashboard
- Orders
- End User
- Issue/Support
- Inventory
- Products
- Payments
- Account (via header/profile)

---

## Key Database Tables by Function

### Order Management
- `pending_review_orders` - Initial orders
- `awaiting_admin_orders` - Admin review
- `ready_to_approve_orders` - Final approval
- `approved_orders` - Completed approvals
- `disapproved_orders` - Rejected orders
- `order_page` - Master order details

### Payment Processing
- `payment` - Payment records

### Inventory Management
- `inventory_items` - Raw materials stock
- `inventory_notifications` - Stock alerts
- `product_materials` - Product-to-materials mapping

### Customization (Category-Specific)
- `mirror_customization`
- `shower_enclosure_customization`
- `aluminum_doors_customization`
- `aluminum_bathroom_doors_customization`

### User Management
- `user` - All user accounts
- `enduser` - Customer information

### Support
- `issuereport` - Customer issues

### System
- `system_activity_log` - Activity tracking
- `product` - Product catalog

---

## Authentication Flow

```
Login Page
    ↓
Authenticate (Sales Representative only)
    ↓
Dashboard (default landing)
    ↓
Access all sales pages via sidebar
    ↓
Account Page → Logout
    ↓
Redirect to Login Page
```

---

## Summary

The Sales Representative module consists of 11 interconnected pages that manage the complete order-to-payment workflow:

1. **Dashboard** - Central hub with metrics and activities
2. **Orders** - Multi-stage order approval workflow
3. **Payments** - Payment processing with inventory integration
4. **Inventory** - Raw materials tracking (view-only)
5. **Products** - Product catalog viewing
6. **End User** - Customer information display
7. **Issues** - Support ticket management
8. **Account** - Profile management
9. **Login** - Authentication entry point
10. **Forgot Password** - Password reset request
11. **Reset Password** - Password reset confirmation

All pages are connected through:
- Sidebar navigation (persistent access)
- Dashboard task cards (quick navigation)
- Workflow transitions (order lifecycle)
- Automatic data flow (approval → payment → inventory deduction)

