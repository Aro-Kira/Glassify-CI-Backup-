# 📊 Glassify-CI Architecture Summary

> **CodeIgniter MVC Project** - Glass Products E-commerce System  
> Generated: December 7, 2025

---

## 📑 Table of Contents

1. [Database Tables](#-database-tables-used)
2. [MVC Connections Map](#-mvc-connections-map)
3. [JavaScript Files](#-javascript-files--their-purpose)
4. [API Endpoints](#-api-endpoints-inventory)
5. [Model-to-Table Mapping](#-model-to-table-mapping)

---

## 🗄️ Database Tables Used

| Table Name | Purpose | Related Model |
|------------|---------|---------------|
| `user` | Stores all users (Admin, Sales, Inventory, Customer) | `User_model` |
| `user_address` | User shipping/billing addresses | `User_model` |
| `customer` | Links UserID to Customer_ID | `Order_model`, `Cart_model` |
| `product` | Product catalog | `Product_model` |
| `cart` | Shopping cart items | `Cart_model` |
| `wishlist` | Customer wishlists | `Wishlist_model` |
| `order` | Base order records | `Order_model` |
| `order_page` | Formatted order display data | `Order_model` |
| `pending_review_orders` | Orders awaiting sales review | `Order_model` |
| `awaiting_admin_orders` | Orders awaiting admin approval | `AdminCon` |
| `ready_to_approve_orders` | Admin-approved, ready for final | `AdminCon` |
| `approved_orders` | Fully approved orders | `AdminCon`, `SalesCon` |
| `disapproved_orders` | Rejected orders | `AdminCon`, `SalesCon` |
| `appointments` | Installation/service scheduling | `AdminCon` |
| `payment` | Payment records | `Order_model` |
| `customization` | Legacy customization table | `Customization_model`, `Wishlist_model` |
| `mirror_customization` | Mirror-specific customizations | `Cart_model`, `Customization_model` |
| `shower_enclosure_customization` | Shower door customizations | `Cart_model`, `Customization_model` |
| `aluminum_doors_customization` | Aluminum door customizations | `Cart_model`, `Customization_model` |
| `aluminum_bathroom_doors_customization` | Bathroom door customizations | `Cart_model`, `Customization_model` |
| `inventory_items` | Inventory stock items | `Inventory_model` |
| `product_materials` | Product-to-material mapping | `Inventory_model` |
| `inventory_notifications` | Stock alerts | `Inventory_model` |
| `sales_notif` | Sales notifications | `Inventory_model` |
| `stock_transactions` | Stock movement logs | `Inventory_model` |
| `activities` | Activity/audit log | `Inventory_model` |
| `issuereport` | Customer issue reports | `Issue_model` |
| `system_activity_log` | System-wide activity logging | `AdminCon` |

---

## 🔄 MVC Connections Map

### Customer Flow

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/` | `Pages` | `home` | - | `home.php` |
| `/products` | `ShopCon` | `products` | `Product_model` | `shop/products.php` |
| `/2DModeling` | `ShopCon` | `product_2d` | `Product_model` | `shop/2DModeling.php` |
| `/addtocart` | `CartCon` | `cart_page` | `Cart_model` | `shop/addtocart.php` |
| `/payment` | `ShopCon` | `checkout` | `User_model` | `shop/checkout.php` |
| `/paying` | `ShopCon` | `ewallet` | - | `shop/ewallet.php` |
| `/waiting_order` | `ShopCon` | `waiting_order` | `Order_model`, `Cart_model`, `Inventory_model` | `shop/WaitingOrder.php` |
| `/complete` | `ShopCon` | `complete` | `Order_model`, `User_model` | `shop/order_complete.php` |
| `/track_order` | `ShopCon` | `order_tracking` | `Order_model`, `User_model` | `shop/order_tracking.php` |
| `/wishlist` | `ShopCon` | `wishlist` | `Wishlist_model` | `shop/wishlist.php` |
| `/my_purchases` | `ShopCon` | `list_products` | `Order_model` | `shop/list_product.php` |
| `/Profile` | `UserCon` | `profile` | `User_model` | `user/profile.php` |

### Authentication Flow

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/login` | `Auth` | `login` | `User_model` | `auth/login.php` |
| `/register` | `Auth` | `register` | `User_model` | `auth/register.php` |
| `/Adlog` | `Auth` | `admin_login` | `User_model` | `auth/login_admin.php` |
| `/SLslog` | `Auth` | `sales_login` | `User_model` | `auth/login_sales.php` |
| `/InvLog` | `Auth` | `inventory_login` | `User_model` | `auth/login_inventory.php` |
| `/forgot-password` | `Auth` | `forgot_password` | `User_model` | `auth/forgot_password.php` |
| `/reset-password` | `Auth` | `reset_password` | `User_model` | `auth/reset_password.php` |

### Admin Panel

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/admin-dashboard` | `AdminCon` | `admin_dashboard` | - | `admin_page/admin_dashboard.php` |
| `/admin-orders` | `AdminCon` | `admin_orders` | - | `admin_page/admin_orders.php` |
| `/admin-appointment` | `AdminCon` | `admin_appointment` | - | `admin_page/admin_appointment.php` |
| `/admin-employee` | `AdminCon` | `admin_employee` | - | `admin_page/admin_employee.php` |
| `/admin-endUser` | `AdminCon` | `admin_endUser` | - | `admin_page/admin_endUser.php` |
| `/admin-inventory` | `AdminCon` | `admin_inventory` | - | `admin_page/admin_inventory.php` |
| `/admin-product` | `AdminCon` | `admin_product` | `Product_model` | `admin_page/admin_product.php` |
| `/admin-payments` | `AdminCon` | `admin_payments` | - | `admin_page/admin_payments.php` |
| `/admin-reports` | `AdminCon` | `admin_reports` | - | `admin_page/admin_reports.php` |
| `/admin-account` | `AdminCon` | `admin_account` | `User_model` | `admin_page/admin_account.php` |

### Sales Panel

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/sales-dashboard` | `SalesCon` | `sales_dashboard` | - | `sales_page/sales_dashboard.php` |
| `/sales-orders` | `SalesCon` | `sales_orders` | - | `sales_page/sales_orders.php` |
| `/sales-products` | `SalesCon` | `sales_products` | - | `sales_page/sales_products.php` |
| `/sales-inventory` | `SalesCon` | `sales_inventory` | - | `sales_page/sales_inventory.php` |
| `/sales-endUser` | `SalesCon` | `sales_endUser` | - | `sales_page/sales_endUser.php` |
| `/sales-payments` | `SalesCon` | `sales_payments` | - | `sales_page/sales_payments.php` |
| `/sales-issues` | `SalesCon` | `sales_issues` | `Issue_model` | `sales_page/sales_issues.php` |
| `/sales-notif` | `SalesCon` | `sales_notif` | - | `sales_page/sales_notif.php` |
| `/sales-account` | `SalesCon` | `sales_account` | `User_model` | `sales_page/sales_account.php` |

### Inventory Panel

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/inventory-dashboard` | `InventCon` | `inventory_dashboard` | - | `inventory_page/inventory_dashboard.php` |
| `/inventory-products` | `InventCon` | `inventory_products` | - | `inventory_page/inventory_products.php` |
| `/inventory-inventory` | `InventCon` | `inventory_inventory` | `Inventory_model` | `inventory_page/inventory_inventory.php` |
| `/inventory-account` | `InventCon` | `inventory_account` | - | `inventory_page/inventory_account.php` |
| `/inventory-reports` | `InventCon` | `inventory_reports` | - | `inventory_page/inventory_reports.php` |
| `/inventory-notif` | `InventCon` | `inventory_notif` | - | `inventory_page/inventory_notif.php` |

### FAQ & Support

| Route | Controller | Method | Model(s) | View |
|-------|------------|--------|----------|------|
| `/faq` | `FaqCon` | `faq` | - | `faq/faq.php` |
| `/report-issue` | `FaqCon` | `faq_report` | - | `faq/report_issue.php` |
| `/submit-issue` | `FaqCon` | `submit_issue` | `Issue_model` | - |

---

## 📜 JavaScript Files & Their Purpose

### Core Shop Features

| File | Purpose | Related Views |
|------|---------|---------------|
| `cart.js` | Shopping cart CRUD (add/remove/update qty) | `addtocart.php` |
| `2d-functions/2d_customization.js` | Konva.js 2D product customization, price calculation | `2DModeling.php` |
| `2d-functions/2d_functions.js` | Utility functions for 2D modeling | `2DModeling.php` |
| `2d-functions/addtocustomization.js` | Save customization to cart | `2DModeling.php` |
| `2d-functions/buy-now-handler.js` | Buy now / checkout flow | `2DModeling.php` |

### Product Pages

| File | Purpose | Related Views |
|------|---------|---------------|
| `products-page/filters.js` | Product filtering (category, price) | `products.php` |
| `products-page/filter-status.js` | Status filter controls | `products.php` |
| `products-page/testimonial.js` | Testimonial carousel | `products.php` |

### Admin Panel

| File | Purpose | Related Views |
|------|---------|---------------|
| `admin-js/account-edit.js` | Admin account editing | `admin_account.php` |
| `admin-js/employee.js` | Employee management | `admin_employee.php` |
| `admin-js/end-user.js` | End user management | `admin_endUser.php` |
| `admin-js/order.js` | Order management | `admin_orders.php` |
| `admin-js/products.js` | Product CRUD operations | `admin_product.php` |

### Sales Panel

| File | Purpose | Related Views |
|------|---------|---------------|
| `sales-js/sales-orders-main.js` | Sales order management | `sales_orders.php` |
| `sales-js/sales-order-tabs.js` | Tab navigation for orders | `sales_orders.php` |
| `sales-js/sales-order-approve-handler.js` | Order approval workflow | `sales_orders.php` |
| `sales-js/sales-order-approval-btn.js` | Approval button handlers | `sales_orders.php` |
| `sales-js/sales-request-approval-handler.js` | Request admin approval | `sales_orders.php` |
| `sales-js/payments-action.js` | Payment processing | `sales_payments.php` |
| `sales-js/payment-filter.js` | Payment filtering | `sales_payments.php` |
| `sales-js/view-receipt-payments.js` | Receipt viewing modal | `sales_payments.php` |
| `sales-js/inventory-filter.js` | Inventory filtering | `sales_inventory.php` |
| `sales-js/product-filter.js` | Product filtering | `sales_products.php` |
| `sales-js/account-edit.js` | Sales account editing | `sales_account.php` |

### Inventory Panel

| File | Purpose | Related Views |
|------|---------|---------------|
| `inventory-js/inventory-filter.js` | Inventory item filtering | `inventory_inventory.php` |
| `inventory-js/Inventory-new-filter.js` | New inventory filter | `inventory_inventory.php` |
| `inventory-js/sales-notif-filter.js` | Notification filtering | `inventory_notif.php` |

### Common/Shared

| File | Purpose | Related Views |
|------|---------|---------------|
| `includes/header.js` | Header interactions | All customer views |
| `includes/sidebar.js` | Sidebar navigation | Admin/Sales/Inventory panels |
| `auth.js` | Authentication handlers | Login/Register views |
| `calendar.js` | Calendar/date picker | Appointment scheduling |
| `img-upload.js` | Image upload handling | Product, profile pages |
| `order-status.js` | Order status updates | Order tracking |
| `sales-issues-pagination.js` | Issue list pagination | `sales_issues.php` |
| `feature-slideshow.js` | Homepage slideshow | `home.php` |

---

## 🔗 API Endpoints (Inventory)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/inventory/get_items` | GET | Fetch all inventory items |
| `/api/inventory/get_statistics` | GET | Get inventory stats |
| `/api/inventory/add_item` | POST | Add new inventory item |
| `/api/inventory/update_item/{id}` | PUT | Update inventory item |
| `/api/inventory/delete_item/{id}` | DELETE | Delete inventory item |
| `/api/inventory/manage_stock/{id}` | POST | Add/remove stock |
| `/api/inventory/get_activities` | GET | Get activity log |

---

## 📁 Model-to-Table Mapping

### User_model

**Tables:** `user`, `user_address`

| Method | Description |
|--------|-------------|
| `register($data)` | Insert new user |
| `login($email)` | Get user by email for login |
| `get_by_id($user_id)` | Get user by ID |
| `email_exists($email)` | Check if email exists |
| `update_account($user_id, $data)` | Update user info |
| `update_password($user_id, $hashed_password)` | Update password |
| `save_reset_token($user_id, $token, $expiry)` | Save password reset token |
| `get_by_reset_token($token)` | Get user by reset token |
| `clear_reset_token($user_id)` | Clear reset token |
| `get_addresses($userID)` | Get user addresses |
| `update_address($userID, $addressType, $data)` | Update/insert address |
| `add_address($data)` | Add new address |
| `get_user_addresses($userID)` | Get all user addresses |

### Product_model

**Tables:** `product`

| Method | Description |
|--------|-------------|
| `get_products()` | Get all products ordered by DateAdded |
| `get_product($id)` | Get single product by ID |
| `insert_product($data)` | Insert new product |
| `update_product($id, $data)` | Update product |
| `delete_product($id)` | Delete product |

### Cart_model

**Tables:** `cart`, `product`, `*_customization`

| Method | Description |
|--------|-------------|
| `get_customization_table($product_id)` | Get appropriate customization table by category |
| `save_customization($data)` | Save customization to appropriate table |
| `get_cart_items($customer_id)` | Get cart items with product info |
| `add_to_cart($data)` | Add item to cart |
| `remove_item($cart_id)` | Remove item from cart |
| `clear_cart($customer_id)` | Clear entire cart |

### Wishlist_model

**Tables:** `wishlist`, `customization`, `product`, `cart`

| Method | Description |
|--------|-------------|
| `add_to_wishlist($data)` | Add item to wishlist |
| `save_customization($data)` | Save customization for wishlist |
| `get_wishlist_items($customer_id)` | Get all wishlist items |
| `get_wishlist_item($wishlist_id)` | Get single wishlist item |
| `remove_item($wishlist_id)` | Remove from wishlist |
| `clear_wishlist($customer_id)` | Clear entire wishlist |
| `get_wishlist_count($customer_id)` | Get wishlist item count |
| `is_in_wishlist($customer_id, $product_id)` | Check if product in wishlist |
| `move_to_cart($wishlist_id, $customer_id)` | Move item to cart |

### Order_model

**Tables:** `order`, `order_page`, `pending_review_orders`, `payment`, `user`, `*_customization`

| Method | Description |
|--------|-------------|
| `can_create_order($product_id, $quantity)` | Check inventory availability |
| `create_order($order_data)` | Create new order with customization |
| `get_order($order_id)` | Get order by ID |
| `get_order_with_customer($order_id)` | Get order with customer details |
| `get_customer_orders($customer_id)` | Get all orders for customer |
| `update_order_status($order_id, $status)` | Update order status |
| `update_payment_status($order_id, $status)` | Update payment status |
| `get_default_sales_rep()` | Get first available sales rep |
| `get_order_tracking_details($order_id)` | Get full order tracking info |
| `get_order_payment($order_id)` | Get payment info for order |
| `get_order_progress($status)` | Get progress steps based on status |
| `get_customer_order_items($customer_id)` | Get order items for My Purchases |

### Inventory_model

**Tables:** `inventory_items`, `product_materials`, `inventory_notifications`, `sales_notif`, `activities`, `stock_transactions`

| Method | Description |
|--------|-------------|
| `get_all_items()` | Get all inventory items |
| `get_item($inventory_item_id)` | Get single inventory item |
| `get_product_materials($product_id)` | Get materials required for product |
| `can_manufacture_product($product_id, $quantity)` | Check if materials available |
| `deduct_materials_for_order($order_id, $product_id, $quantity)` | Deduct materials from inventory |
| `add_item($data)` | Add new inventory item |
| `update_item($item_id, $data)` | Update inventory item |
| `delete_item($item_id)` | Delete inventory item |
| `manage_stock($item_id, $add_quantity, $remove_quantity)` | Add/remove stock |
| `get_statistics()` | Get inventory statistics |
| `get_activities($limit)` | Get activity log |
| `log_activity(...)` | Log activity to database |
| `create_stock_transaction(...)` | Create stock transaction record |
| `get_unread_notifications()` | Get unread notifications |
| `mark_notification_read($notification_id)` | Mark notification as read |

### Issue_model

**Tables:** `issuereport`, `order`, `customer`

| Method | Description |
|--------|-------------|
| `create_issue($data)` | Create new issue report |
| `get_all_issues($filters)` | Get all issues with filters |
| `get_issue_by_id($issue_id)` | Get single issue |
| `update_issue($issue_id, $data)` | Update issue |
| `mark_as_resolved($issue_id)` | Mark issue resolved |
| `update_priority($issue_id, $priority)` | Update issue priority |
| `get_issue_statistics($salesrep_id)` | Get issue stats |
| `get_issues_count($status, $salesrep_id)` | Get issue count by status |

### Customization_model

**Tables:** `customization`, `mirror_customization`, `shower_enclosure_customization`, `aluminum_doors_customization`, `aluminum_bathroom_doors_customization`

| Method | Description |
|--------|-------------|
| `add_customization($data)` | Add customization via Cart_model |
| `delete_customization($customization_id, $product_id)` | Delete customization |
| `delete_customization_from_any_table($customization_id)` | Delete from any table |
| `delete_multiple($ids, $product_id)` | Delete multiple customizations |
| `get_customization($customization_id, $product_id)` | Get customization by ID |

---

## 🏗️ Project Structure Overview

```
Glassify-CI/
├── application/
│   ├── config/
│   │   ├── autoload.php
│   │   ├── config.php
│   │   ├── database.php
│   │   └── routes.php
│   ├── controllers/
│   │   ├── AddtoCartCon.php
│   │   ├── AdminCon.php
│   │   ├── Auth.php
│   │   ├── CartCon.php
│   │   ├── CustomizationCon.php
│   │   ├── EmpCon.php
│   │   ├── EndUserCon.php
│   │   ├── FaqCon.php
│   │   ├── InventCon.php
│   │   ├── OrderCon.php
│   │   ├── Pages.php
│   │   ├── ProductCon.php
│   │   ├── SalesCon.php
│   │   ├── Shop.php
│   │   ├── ShopCon.php
│   │   ├── UserCon.php
│   │   └── api/
│   │       └── Inventory_api.php
│   ├── models/
│   │   ├── Cart_model.php
│   │   ├── Customization_model.php
│   │   ├── Inventory_model.php
│   │   ├── Issue_model.php
│   │   ├── Order_model.php
│   │   ├── Product_model.php
│   │   ├── User_model.php
│   │   └── Wishlist_model.php
│   └── views/
│       ├── admin_page/
│       ├── auth/
│       ├── faq/
│       ├── includes/
│       ├── inventory_page/
│       ├── pages/
│       ├── sales_page/
│       ├── shop/
│       └── user/
├── assets/
│   ├── css/
│   │   ├── admin_css/
│   │   ├── general-customer/
│   │   ├── include/
│   │   ├── inventory_css/
│   │   └── sales_css/
│   ├── images/
│   └── js/
│       ├── 2d-functions/
│       ├── admin-js/
│       ├── includes/
│       ├── inventory-js/
│       ├── products-page/
│       └── sales-js/
└── system/
```

---

## 📝 Notes

- **User Roles:** Admin, Sales Representative, Inventory Officer, Customer
- **Order Flow:** Customer → Pending Review → Sales Approval → Admin Approval → Approved → In Fabrication → Installed → Completed
- **Customization Tables:** Category-specific tables for Mirrors, Shower Enclosures, Aluminum Doors, and Bathroom Doors
- **Inventory Integration:** Orders check material availability before creation; materials are deducted upon payment

---

*This document was auto-generated based on codebase analysis.*

