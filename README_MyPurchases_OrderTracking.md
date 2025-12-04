# My Purchases & Order Tracking Feature

## Overview
This update adds dynamic database-driven functionality to the **My Purchases** page and **Order Tracking** page, allowing customers to view their purchase history and track order status in real-time.

---

## New Features

### 1. My Purchases Page (`list_product.php`)
- **Route**: `/my_purchases` or `/list_products`
- **Description**: Displays all orders placed by the logged-in customer
- **Features**:
  - Product image, name, price, quantity, and subtotal
  - "Delivered on [Date]" button linking to order tracking
  - Empty state with "Start Shopping" button when no orders exist

### 2. Order Tracking Page (`order_tracking.php`)
- **Route**: `/track_order?order={OrderID}`
- **Description**: Shows detailed order status and progress
- **Features**:
  - Order info: Order ID, Payment Method, Transaction ID, Estimated Delivery
  - **Dynamic Progress Bar**: Visual timeline showing order stages
    - Order Placed → Ocular Visit → In Fabrication → Installed → Completed
  - Products table with all order items
  - Order summary (subtotal, shipping, handling, total)
  - Shipping & Billing addresses

---

## Files Modified

### Controllers
| File | Changes |
|------|---------|
| `ShopCon.php` | Updated `list_products()` to fetch customer orders; Updated `order_tracking()` to fetch order details dynamically |

### Models
| File | Changes |
|------|---------|
| `Order_model.php` | Added methods: `get_customer_order_items()`, `get_order_tracking_details()`, `get_order_payment()`, `get_order_progress()` |
| `User_model.php` | Added address functions: `get_addresses()`, `update_address()`, `add_address()`, `get_user_addresses()` |

### Views
| File | Changes |
|------|---------|
| `list_product.php` | Complete rewrite - now displays dynamic order data with delivery button |
| `order_tracking.php` | Complete rewrite - now displays dynamic order status, progress bar, products, and addresses |
| `order_complete.php` | Fixed null `ImageUrl` handling with null coalescing |
| `profile.php` | Fixed address iteration bug for users with no saved addresses |
| `header.php` | Changed tracking icon link from `/track_order` to `/my_purchases` |

### CSS
| File | Changes |
|------|---------|
| `list_product.css` | Added status badges, delivery button styles, empty state styling |
| `order_tracking.css` | Added dynamic progress bar using CSS variable `--progress-width` |

### Database
| File | Changes |
|------|---------|
| `glassify-test-lastest.sql` | Added `user_address` table definition |
| `database_migrations/add_user_address_table.sql` | Standalone migration for existing databases |

### Routes
| Route | Controller Method |
|-------|------------------|
| `my_purchases` | `ShopCon/list_products` |
| `track_order` | `ShopCon/order_tracking` |

---

## Bug Fixes

### 1. Switch Fall-through in `get_order_progress()`
- **Issue**: Missing break statements caused unintended fall-through
- **Fix**: Replaced with explicit assignments for each case

### 2. Missing `user_address` Table
- **Issue**: `User_model` referenced non-existent table
- **Fix**: Added table definition to SQL dump and created migration file

### 3. Null `ImageUrl` Handling
- **Issue**: `order_complete.php` didn't handle null images
- **Fix**: Added null coalescing: `$item->ImageUrl ?? 'default.jpg'`

### 4. Profile Address Iteration
- **Issue**: `profile.php` crashed when user had no addresses
- **Fix**: Added null check before displaying addresses

---

## Database Schema

### New Table: `user_address`
```sql
CREATE TABLE `user_address` (
  `AddressID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `AddressType` enum('Shipping','Billing') DEFAULT 'Shipping',
  `AddressLine` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT 'Philippines',
  `ZipCode` varchar(20) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `IsDefault` tinyint(1) DEFAULT 0,
  `Created_Date` timestamp DEFAULT current_timestamp(),
  `Updated_Date` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`AddressID`),
  FOREIGN KEY (`UserID`) REFERENCES `user`(`UserID`) ON DELETE CASCADE
);
```

### Tables Used
- `order` - Main order information
- `order_customization` - Order items with customization details
- `product` - Product information
- `payment` - Payment details
- `user` - Customer information
- `user_address` - Customer addresses

---

## Order Progress Logic

| Order Status | Progress % | Steps Completed |
|--------------|-----------|-----------------|
| Pending | 10% | Order Placed |
| Approved | 30% | Order Placed, Ocular Visit |
| In Fabrication | 50% | Order Placed, Ocular Visit, In Fabrication |
| Ready for Installation | 70% | Order Placed, Ocular Visit, In Fabrication, Installed |
| Completed | 100% | All steps |

---

## How to Apply Changes

### For New Installations
1. Import `glassify-test-lastest.sql` to your database

### For Existing Databases
1. Run the migration script:
```sql
-- Run this in phpMyAdmin or MySQL CLI
SOURCE database_migrations/add_user_address_table.sql;
```

---

## Testing

1. **Login** as a customer
2. **Place an order** through the checkout flow
3. **Navigate to My Purchases** via the tracking icon in header
4. **Click "Delivered on [Date]"** button to view order tracking
5. **Verify** order details, progress bar, and addresses display correctly

---

## Author
Generated by AI Assistant - December 4, 2025

