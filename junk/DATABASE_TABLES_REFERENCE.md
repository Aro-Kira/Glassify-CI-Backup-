# Database Tables Reference
## Glassify-CI System

**Scanned Date:** February 19, 2026

This document lists all database tables used across the Glassify-CI application controllers and models, identified through code scanning.

---

## Summary Statistics
- **Total Tables Identified:** 21
- **Models Scanned:** 12
- **Controllers Scanned:** 21

---

## Complete Table List

### Core User Management Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `user` | User_model, UserCon, Auth | Stores user accounts and authentication data |
| `user_address` | User_model, UserCon | Stores user shipping/billing addresses |
| `customer` | User_model, UserCon, Customer_model | Stores customer records linked to users |

### Product & Inventory Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `product` | Product_model, ProductCon, CartCon, ShopCon | Main product catalog |
| `inventory_items` | Inventory_model | Individual inventory/material items |
| `product_tag_prices` | ShopCon | Product tag pricing variations |
| `product_series` | ShopCon | Product series/categories |
| `product_standard_sizes` | ShopCon | Standard measurement sizes for products |

### Order & Payment Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `order` | Order_model, OrderCon, ShopCon, TestOrderFlow, AdminCon | Main orders table |
| `order_items` | Order_model, CartCon, AdminCon, ShopCon | Individual items within orders |
| `payment` | Order_model, ShopCon, AdminCon | Payment records and transactions |
| `appointments` | ShopCon, AdminCon | Installation/ocular appointments |
| `appointment_payments` | ShopCon | Payment records tied to appointments |

### Customization & Cart Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `customization` | Customization_model, Cart_model, CartCon, ShopCon | Glass customization specifications |
| `customization_field_configs` | CartCon | Configuration for customization fields |
| `cart` | Cart_model, CartCon, Wishlist_model | Shopping cart items |

### Wishlist Table

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `wishlist` | Wishlist_model, WishlistCon | Saved items for later |

### Issue & Support Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `issuereport` | Issue_model | User issue/bug reports |

### System & Activity Tables

| Table Name | Used By | Purpose |
|-----------|---------|---------|
| `system_activity_log` | AdminCon | System activity/audit logs |
| `role_request` | Role_requests | User role change requests |

---

## Tables by Model

### User_model
- `user`
- `user_address`
- `customer`

### Product_model
- `product`

### Order_model
- `order`
- `order_items`
- `payment`
- `appointments`

### Cart_model
- `customization`
- `cart`
- `product`
- `customization_field_configs`

### Customer_model
- `customer`

### Customization_model
- `customization`

### Inventory_model
- `inventory_items`

### Wishlist_model
- `wishlist`
- `product`
- `cart`
- `customization`

### Issue_model
- `issuereport`

### Download_model
- *(No direct table usage identified)*

### Role_request_model
- `role_request`

---

## Tables by Controller

### AdminCon
- `system_activity_log`
- `order`
- `order_items`
- `appointments`

### Auth
- `user`

### CartCon
- `cart`
- `product`
- `customization_field_configs`

### CustomizationAjaxCon / CustomizationCon / CustomizationFieldsCon
- `customization`

### EmpCon
- `user`

### EndUserCon
- `user`
- `customer`

### FaqCon
- *(No direct table usage identified)*

### OrderCon
- `order`

### Pages
- *(No direct table usage identified)*

### ProductCon
- `product`

### Role_requests
- `role_request`

### SalesCon
- `order`
- `user`

### Shop
- *(No direct table usage identified)*

### ShopCon
- `product_tag_prices`
- `product_series`
- `product_standard_sizes`
- `order_items`
- `payment`
- `appointments`
- `appointment_payments`
- `customization`
- `order`

### TestOrderFlow
- `order`

### UserCon
- `user_address`
- `customer`
- `user`

### WishlistCon
- `wishlist`

---

## Relationships & Dependencies

```
user ──┬─→ user_address
       └─→ customer ──────┐
                           │
cart ───────┐             │
            └→ customization
                           │
product ───┬─→ order_items ─┐
           │                 ├→ order
           └─→ product_tag_prices │
               product_series     │
               product_standard_sizes
                           
order ──────┬─→ payment ──────────┐
            └─→ appointments ──────┤
                appointment_payments│
                                   │
wishlist ───→ product
             customization
             cart

issuereport (standalone)
role_request (standalone)
system_activity_log (standalone)
inventory_items (standalone)
```

---

## Key Observations

1. **Order Processing Flow:**
   - Orders are created with items from `order_items`
   - Payments tracked in `payment` table
   - Appointments linked for installation
   - Multiple payment stages: downpayment, fabrication, installation

2. **Customization System:**
   - Uses unified `customization` table (optimized schema)
   - Products can have custom field configurations
   - Customization linked to orders via `order_items`

3. **User Management:**
   - Customers are separate from users via `customer` table
   - User addresses stored separately in `user_address`
   - Role-based access control implemented

4. **Product Configuration:**
   - Multiple tables support product variations (tags, series, sizes)
   - Price and size variations managed separately

---

## JavaScript & View Files - AJAX Endpoints

The following frontend files interact with database tables through AJAX calls:

### JavaScript Files Using Tables

| JS File | Tables Used | AJAX Endpoint | Purpose |
|---------|------------|--------------|---------|
| `wishlist.js` | wishlist, cart, product, customization | WishlistCon/* | Wishlist management |
| `cart.js` | cart, product, customization, customization_field_configs | CartCon/* | Shopping cart operations |
| `order-status.js` | order, payment, appointments, order_items | ShopCon/*, OrderCon/* | Order tracking |
| `sales-issues-pagination.js` | issuereport | SalesCon/*, API endpoints | Issue report viewing/updating |
| `sales-orders-main.js` | order, order_items, payment | SalesCon/filter_orders_by_date | Sales order filtering |
| `sales-request-approval-handler.js` | order | SalesCon/request_approval | Approval workflow |
| `view-receipt-payments.js` | payment, order | SalesCon/get_payment_details, mark_payment_paid | Payment receipt viewing |
| `inventory-js/*.js` | inventory_items | InventCon/* | Inventory management |
| `admin-js/*.js` | order, appointments, payment, user, system_activity_log | AdminCon/* | Admin operations |

### View Files Displaying Data

| View File | Tables Used | Purpose |
|-----------|------------|---------|
| `user/profile.php` | order, order_items, user, user_address, cart, customization | User profile and orders |
| `shop/cart.php` | cart, product, customization | Shopping cart display |
| `shop/checkout.php` | order, payment, user_address, appointments | Checkout process |
| `order/order-tracking.php` | order, payment, appointments, order_items | Order status tracking |
| `admin_page/admin_orders.php` | order, order_items, payment, appointments | Admin order management |
| `admin_page/admin_products.php` | product, inventory_items | Admin product management |
| `sales_page/sales_orders.php` | order, payment, order_items | Sales order view |
| `sales_page/sales_payments.php` | payment, order | Sales payment receipts |
| `faq/report_issue.php` | issuereport, order | Issue report submission |

## Notes

- All table references identified through grep/semantic search of PHP source files and JavaScript
- Model property declarations (`$this->table`) used as primary source
- Direct database queries (`$this->db->from()`, `$this->db->get()`, etc.) used as secondary source
- Frontend AJAX calls identified through fetch() and $.ajax() patterns
- Some tables like `appointment_payments` use conditional existence checks
- Table names are case-sensitive in some database drivers (backticks used for reserved words like `order`)
- JavaScript files primarily interact with tables through controller AJAX endpoints
- Views display data loaded from controllers which query the database models

