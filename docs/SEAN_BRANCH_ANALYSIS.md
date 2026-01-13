# Sean-Branch Analysis Report

## Overview
The **sean-branch** is a production-ready version of the Glassify-CI e-commerce system. Based on commit history, it represents a cleaned-up, optimized branch that removes temporary files, setup scripts, and documentation files to prepare for production deployment.

## System Architecture

### Framework & Technology Stack
- **Framework:** CodeIgniter 3
- **PHP Version:** 7.4
- **Database:** MySQL (using MySQLi driver)
- **Database Name:** `glassify-test`
- **Server:** XAMPP (Apache + MySQL)

### Database Connection
- **Host:** localhost
- **Username:** admin_glassify
- **Password:** glassifyAdmin
- **Database:** glassify-test
- **Driver:** mysqli
- **Character Set:** utf8

## Main Features & Functionality

### 1. User Management & Authentication
- **Multi-role system:**
  - Admin
  - Sales Representative
  - Inventory Officer
  - End Users/Customers

- **Authentication Routes:**
  - `/login` - Customer login
  - `/register` - Customer registration
  - `/Adlog` - Admin login
  - `/SLslog` or `/sales-login` - Sales login
  - `/Invlog` - Inventory login
  - `/forgot-password` - Password recovery
  - `/reset-password` - Password reset

### 2. E-Commerce Features

#### Product Management
- Product catalog browsing (`/products`)
- 2D product modeling (`/2DModeling`)
- Product customization
- Product categories supported:
  - Mirrors
  - Shower Enclosures
  - Aluminum Doors
  - Aluminum Bathroom Doors

#### Shopping Cart
- Add to cart functionality (`/addtocart`)
- Cart management (`/cartsave`)
- Wishlist (`/wishlist`)

#### Order Processing
- Checkout system (`/payment`)
- Order placement
- Order tracking (`/track_order`)
- Waiting orders (`/waiting_order`)
- Purchase history (`/my_purchases`)
- Invoice generation (PDF)
- E-wallet payment integration (`/paying`)
- Order terms & conditions (`/terms_order`)

#### Customization Features
- Product customization by category
- Custom design uploads
- Customization tracking linked to orders

### 3. Admin Dashboard Features
- **Admin Routes:**
  - `/admin-dashboard` - Main dashboard
  - `/admin-orders` - Order management
  - `/admin-appointment` - Appointment scheduling
  - `/admin-employee` - Employee management
  - `/admin-endUser` - Customer management
  - `/admin-inventory` - Inventory overview
  - `/admin-product` - Product management
  - `/admin-payments` - Payment management
  - `/admin-reports` - Reports & analytics
  - `/admin-account` - Account settings

### 4. Sales Dashboard Features
- **Sales Routes:**
  - `/sales-dashboard` - Sales overview
  - `/sales-orders` - Order management
  - `/sales-products` - Product viewing
  - `/sales-inventory` - Inventory viewing
  - `/sales-endUser` - Customer management
  - `/sales-payments` - Payment processing
  - `/sales-issues` - Issue/ticket management
  - `/sales-account` - Account settings
  - `/sales-notif` - Notifications

- **Sales Order Management:**
  - Order approval/disapproval
  - Order details viewing
  - Payment marking
  - Approval requests
  - Date-based filtering

- **Issue Management:**
  - Issue tracking
  - Priority management
  - Resolution marking
  - Issue statistics

### 5. Inventory Management

#### Inventory Dashboard
- `/inventory-dashboard` - Main dashboard
- `/inventory-products` - Product inventory
- `/inventory-inventory` - Inventory items
- `/inventory-reports` - Inventory reports
- `/inventory-account` - Account settings
- `/inventory-notif` - Notifications

#### Inventory API Endpoints
All inventory APIs require authentication as "Inventory Officer" role:

- `GET /api/inventory/get_items` - Get all inventory items
- `GET /api/inventory/get_statistics` - Get inventory statistics
- `POST /api/inventory/add_item` - Add new inventory item
- `POST /api/inventory/update_item/{id}` - Update inventory item
- `POST /api/inventory/delete_item/{id}` - Delete inventory item
- `POST /api/inventory/manage_stock/{id}` - Add/remove stock
- `GET /api/inventory/get_activities` - Get activity log

#### Inventory Features
- Stock management (add/remove stock)
- Material tracking
- Product-material relationships
- Stock threshold monitoring
- Inventory activity logging
- Material deduction on order payment
- Stock availability checking for manufacturing

### 6. FAQ & Support
- `/faq` - Main FAQ page
- `/faq-ordering` - Ordering FAQs
- `/faq-payment` - Payment FAQs
- `/faq-pricing` - Pricing FAQs
- `/faq-warranty` - Warranty FAQs
- `/faq-shipping` - Shipping FAQs
- `/faq-account` - Account FAQs
- `/report-issue` - Issue reporting
- `/submit-issue` - Submit issue form

### 7. User Profile
- `/Profile` - User profile management
- Address management (Shipping & Billing)
- Profile image upload
- Account information updates

### 8. Public Pages
- `/` (home) - Landing page
- `/about` - About page
- `/contact` - Contact page
- `/projects` - Projects showcase
- `/home-login` - Home with login options
- `/quote-request` - Quote request processing

## Database Connections & Models

### Key Database Tables
Based on the models and codebase:

1. **User Management:**
   - `users` - User accounts
   - `user_addresses` - User shipping/billing addresses

2. **Product & Inventory:**
   - `products` - Product catalog
   - `inventory_items` - Inventory stock items
   - `product_materials` - Product-material relationships

3. **Order Management:**
   - `order` - Main orders table
   - Customization tables:
     - `mirror_customization`
     - `shower_enclosure_customization`
     - `aluminum_doors_customization`
     - `aluminum_bathroom_doors_customization`
   - `cart` - Shopping cart

4. **Administrative:**
   - `employees` - Employee records
   - `appointments` - Appointment scheduling
   - `activities` - Activity logs
   - `issues` - Support tickets/issues
   - `payments` - Payment records

### Models in Use
- `User_model` - User and address management
- `Product_model` - Product operations
- `Inventory_model` - Inventory management
- `Order_model` - Order processing
- `Cart_model` - Cart operations
- `Customization_model` - Product customization
- `Issue_model` - Issue tracking

## Key Differences from main-aro Branch

### Removed Files (Cleanup)
The sean-branch has removed many documentation and temporary files:
- Various `.md` documentation files (ARCHITECTURE_SUMMARY.md, DATABASE_*.md, ORDER_FLOW_*.md, etc.)
- SQL migration/setup scripts (add_order_date_columns.sql, etc.)
- Test files (TestOrderFlow.php)
- Temporary session files

### Code Optimizations
- Simplified controllers (ShopCon.php, SalesCon.php, AdminCon.php)
- Optimized models (Order_model.php, Cart_model.php)
- Removed unnecessary controllers (WishlistCon.php, FixCustomers.php)
- Removed unused models (Wishlist_model.php, Customer_model.php)
- Streamlined views with reduced code complexity

### Database Configuration
- Updated database connection settings
- Cleaner route configuration

## API Connections

### Internal APIs
1. **Inventory API** (`application/controllers/api/Inventory_api.php`)
   - RESTful endpoints for inventory management
   - JSON responses
   - Role-based authentication

### External Integrations (Potential)
- E-wallet payment system (`/paying` route)
- Email system (password reset, notifications)
- PDF generation (invoice creation)

## Session Management
- Uses CodeIgniter's session library
- Session data stored in `application/writable/session/`
- Session-based authentication across all controllers
- Role-based access control

## File Uploads
Upload directories:
- `uploads/designs/` - Custom design uploads
- `uploads/payments/` - Payment receipts/proofs
- `uploads/products/` - Product images
- `uploads/profile/` - User profile images
- `uploads/receipts/` - Order receipts

## Security Features
- Session-based authentication
- Role-based access control
- Password reset functionality
- Direct script access prevention (BASEPATH checks)
- Input validation and sanitization

## Production Readiness
Based on the latest commit: **"Project cleanup: Remove temporary files, setup scripts, and documentation - Ready for production"**

The branch is:
- ✅ Cleaned of temporary files
- ✅ Documentation removed (production-ready)
- ✅ Optimized codebase
- ✅ Database connections configured
- ✅ All routes defined
- ✅ Models and controllers streamlined

## Summary
The **sean-branch** is a production-ready version of the Glassify-CI system with:
- Complete e-commerce functionality
- Multi-role user management (Admin, Sales, Inventory, Customer)
- Inventory management with API endpoints
- Order processing with customization
- Sales and admin dashboards
- Support/FAQ system
- Clean, optimized codebase ready for deployment


