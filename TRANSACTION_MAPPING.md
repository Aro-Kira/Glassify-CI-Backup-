# Database Transaction Mapping
## Models and Controllers to Database Transaction Functions

This document maps the order fulfillment sequence to database transaction functions based on `super-latest-optimized.sql`.

---

## Sequence Flow

```
Admin Add Product → Customer Product → Add to Cart → Cart → Checkout → Payment → Complete → Sales Rep (Order Confirmation) → Admin (Order Confirmation)
```

---

## 1. Admin Add Product

**Controller:** `AdminCon->admin_product()`  
**Model:** `Product_model->insert_product()`  
**Database Tables:** `product`, `system_activity_log`

### Transaction Function:
```php
Product_model->insert_product($data)
```

**Transaction Steps:**
1. `START TRANSACTION`
2. `INSERT INTO product` (ProductName, Category, Material, Price, ImageUrl, Description, Status)
3. `INSERT INTO system_activity_log` (Action: 'Product Added')
4. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `Product_ID` (AUTO_INCREMENT)
- `Status` enum('In Stock','Out of Stock','Low Stock')
- `DateAdded` (timestamp)

---

## 2. Customer Product (Browse/Select)

**Controller:** `ShopCon->products()`, `ShopCon->product_2d()`  
**Model:** `Product_model->get_products()`, `Product_model->get_product()`  
**Database Tables:** `product`

### Transaction Function:
```php
Product_model->get_products()  // Read-only, no transaction needed
Product_model->get_product($id)  // Read-only, no transaction needed
```

**No transaction required** - Read operations only.

---

## 3. Add to Cart

**Controller:** `CartCon->add_customized()`, `CartCon->add_customized_ajax()`, `AddtoCartCon->save()`  
**Model:** `Cart_model->save_customization()`, `Cart_model->add_to_cart()`  
**Database Tables:** `customization`, `cart`

### Transaction Functions:
```php
Cart_model->save_customization($data)  // Creates customization record
Cart_model->add_to_cart($data)  // Adds to cart with transaction
```

**Transaction Steps:**
1. `START TRANSACTION`
2. `INSERT INTO customization` (Customer_ID, Product_ID, Dimensions, GlassShape, GlassType, etc.)
3. `INSERT INTO cart` OR `UPDATE cart` (if item already exists, increment Quantity)
4. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `CustomizationID` (AUTO_INCREMENT)
- `Cart_ID` (AUTO_INCREMENT)
- `Customer_ID` (FK to customer)
- `Product_ID` (FK to product)
- `CustomizationID` (FK to customization, nullable)

---

## 4. Cart (View/Manage)

**Controller:** `CartCon->cart_page()`  
**Model:** `Cart_model->get_cart_items()`, `Cart_model->remove_item()`, `Cart_model->update_qty()`  
**Database Tables:** `cart`, `product`, `customization`

### Transaction Functions:
```php
Cart_model->remove_item($cart_id)  // Remove item with transaction
Cart_model->update_qty()  // Update quantity
```

**Transaction Steps (Remove Item):**
1. `START TRANSACTION`
2. `DELETE FROM customization` (if CustomizationID exists)
3. `DELETE FROM cart` (Cart_ID)
4. `COMMIT` or `ROLLBACK` on error

---

## 5. Checkout

**Controller:** `ShopCon->checkout()`, `ShopCon->place_order()`  
**Model:** `Order_model->create_order()`, `Order_model->save_order_customizations()`  
**Database Tables:** `order`, `order_items`, `system_activity_log`

### Transaction Functions:
```php
Order_model->create_order($order_data)  // Creates order with transaction
Order_model->save_order_customizations($order_id, $cart_items)  // Saves order items
```

**Transaction Steps:**
1. `START TRANSACTION`
2. Generate `OrderNumber` (GI001, GI002, etc.)
3. `INSERT INTO order` (OrderNumber, Customer_ID, SalesRep_ID, TotalAmount, Status: 'Pending Review', PaymentStatus: 'Pending', DeliveryAddress, SpecialInstructions)
4. `INSERT INTO order_items` (for each cart item: OrderID, Product_ID, CustomizationID, Quantity, UnitPrice, EstimatePrice, customization details)
5. `INSERT INTO system_activity_log` (Action: 'Order Created')
6. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `OrderID` (AUTO_INCREMENT)
- `OrderNumber` (varchar, unique: GI001, GI002, etc.)
- `Status` enum('Pending Review','Awaiting Admin','Ready to Approve','Approved','Disapproved','In Fabrication','Ready for Installation','Completed','Cancelled','Returned')
- `PaymentStatus` enum('Pending','Paid','Partial','Refunded')
- `PaymentMethod` enum('E-Wallet','Cash on Delivery')

---

## 6. Payment

**Controller:** `ShopCon->ewallet()`, `ShopCon->submit_ewallet_payment()`  
**Model:** `Order_model->save_payment_receipt()`, `Order_model->update_payment_method()`  
**Database Tables:** `payment`, `order`

### Transaction Functions:
```php
Order_model->save_payment_receipt($order_id, $receipt_path, $amount)  // Saves payment with transaction
Order_model->update_payment_method($order_id, $payment_method)  // Updates payment method
```

**Transaction Steps (E-Wallet):**
1. `START TRANSACTION`
2. `INSERT INTO payment` OR `UPDATE payment` (OrderID, Amount, ReceiptPath, Status: 'Pending', CustomerName, ProductName, PaymentMethod: 'E-Wallet')
3. `UPDATE order` (PaymentMethod: 'E-Wallet')
4. `COMMIT` or `ROLLBACK` on error

**Transaction Steps (Cash on Delivery):**
1. `START TRANSACTION`
2. `UPDATE order` (PaymentMethod: 'Cash on Delivery', PaymentStatus: 'Pending')
3. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `Payment_ID` (AUTO_INCREMENT)
- `OrderID` (FK to order)
- `Status` enum('Pending','Paid','Failed','Refunded')
- `PaymentMethod` enum('E-Wallet','Cash on Delivery')
- `ReceiptPath` (varchar, for E-Wallet screenshot)

---

## 7. Complete

**Controller:** `ShopCon->complete()`  
**Model:** `Order_model->get_order_with_customer()`, `Order_model->get_order_customizations()`  
**Database Tables:** `order`, `order_items`, `payment`

### Transaction Function:
```php
// Read-only operations, no transaction needed
Order_model->get_order_with_customer($order_id)
Order_model->get_order_customizations($order_id)
```

**No transaction required** - Read operations only.

---

## 8. Sales Rep (Order Confirmation)

**Controller:** `SalesCon->approve_order()`, `SalesCon->request_approval()`, `SalesCon->disapprove_order()`  
**Model:** `Order_model->update_order_status()`  
**Database Tables:** `order`, `payment`, `approved_orders`, `system_activity_log`

### Transaction Functions:
```php
Order_model->update_order_status($order_id, $status, $approved_by, $approved_by_id)  // Updates order status with transaction
```

**Transaction Steps (Approve Order):**
1. `START TRANSACTION`
2. `UPDATE order` (Status: 'Approved', ApprovedBy_SalesRep_ID, Approved_Date)
3. `INSERT INTO payment` (if not exists: OrderID, Amount, Status: 'Pending', CustomerName, ProductName)
4. `INSERT INTO approved_orders` (legacy table for backward compatibility)
5. `INSERT INTO system_activity_log` (Action: 'Order Status Updated')
6. `COMMIT` or `ROLLBACK` on error

**Transaction Steps (Request Admin Approval):**
1. `START TRANSACTION`
2. `UPDATE order` (Status: 'Awaiting Admin')
3. `INSERT INTO awaiting_admin_orders` (if legacy table exists)
4. `INSERT INTO system_activity_log` (Action: 'Approval Requested')
5. `COMMIT` or `ROLLBACK` on error

**Transaction Steps (Disapprove Order):**
1. `START TRANSACTION`
2. `UPDATE order` (Status: 'Disapproved', DisapprovedBy: 'Sales Rep', DisapprovedBy_ID, DisapprovalReason, Disapproved_Date)
3. `INSERT INTO disapproved_orders` (if legacy table exists)
4. `INSERT INTO system_activity_log` (Action: 'Order Disapproved')
5. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `ApprovedBy_SalesRep_ID` (FK to user, nullable)
- `Approved_Date` (datetime, nullable)
- `DisapprovedBy` enum('Sales Rep','Admin', nullable)
- `DisapprovedBy_ID` (FK to user, nullable)
- `DisapprovalReason` (text, nullable)

---

## 9. Admin (Order Confirmation)

**Controller:** `AdminCon->approve_order_admin()`, `AdminCon->disapprove_order_admin()`  
**Model:** `Order_model->update_order_status()`  
**Database Tables:** `order`, `ready_to_approve_orders`, `awaiting_admin_orders`, `system_activity_log`

### Transaction Functions:
```php
Order_model->update_order_status($order_id, $status, 'Admin', $admin_id)  // Updates order status with transaction
```

**Transaction Steps (Approve Order):**
1. `START TRANSACTION`
2. `UPDATE order` (Status: 'Ready to Approve', ApprovedBy_Admin_ID, Approved_Date)
3. `INSERT INTO ready_to_approve_orders` (if legacy table exists)
4. `DELETE FROM awaiting_admin_orders` (if legacy table exists)
5. `INSERT INTO system_activity_log` (Action: 'Order Approved by Admin')
6. `COMMIT` or `ROLLBACK` on error

**Transaction Steps (Disapprove Order):**
1. `START TRANSACTION`
2. `UPDATE order` (Status: 'Disapproved', DisapprovedBy: 'Admin', DisapprovedBy_ID, DisapprovalReason, Disapproved_Date)
3. `INSERT INTO disapproved_orders` (if legacy table exists)
4. `DELETE FROM awaiting_admin_orders` (if legacy table exists)
5. `INSERT INTO system_activity_log` (Action: 'Order Disapproved by Admin')
6. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `ApprovedBy_Admin_ID` (FK to user, nullable)
- `DisapprovedBy_ID` (FK to user, nullable)

---

## 10. Payment Processing (Sales Rep)

**Controller:** `SalesCon->mark_payment_paid()`  
**Model:** `Order_model->update_payment_status()`  
**Database Tables:** `payment`, `order`, `inventory_items`, `stock_transactions`

### Transaction Functions:
```php
Order_model->update_payment_status($order_id, 'Paid')  // Updates payment status with transaction
Inventory_model->deduct_materials_for_order($order_id, $product_id, $quantity)  // Deducts inventory
```

**Transaction Steps:**
1. `START TRANSACTION`
2. `UPDATE payment` (Status: 'Paid', Payment_Date)
3. `UPDATE order` (PaymentStatus: 'Paid')
4. `UPDATE inventory_items` (deduct materials for each product in order)
5. `INSERT INTO stock_transactions` (log each material deduction)
6. `INSERT INTO system_activity_log` (Action: 'Payment Received')
7. `COMMIT` or `ROLLBACK` on error

**Key Fields:**
- `PaymentStatus` in `order` table
- `Status` in `payment` table
- `InStock` in `inventory_items` table

---

## Transaction Best Practices

1. **Always use transactions for multi-table operations**
2. **Start transaction before any database writes**
3. **Rollback on any error**
4. **Commit only after all operations succeed**
5. **Log activities in `system_activity_log` for audit trail**
6. **Use foreign key constraints for data integrity**
7. **Handle inventory checks before order creation**
8. **Validate data before starting transactions**

---

## Error Handling

All transaction functions should:
- Return `false` on failure
- Log errors using `log_message('error', ...)`
- Rollback on any database error
- Provide meaningful error messages

---

## Database Schema Reference

See `super-latest-optimized.sql` for complete table definitions:
- `user` - User accounts
- `customer` - Customer records (linked to user)
- `product` - Product catalog
- `customization` - Unified customization table
- `cart` - Shopping cart
- `order` - Main order table (unified)
- `order_items` - Order line items
- `payment` - Payment records
- `appointments` - Installation scheduling
- `system_activity_log` - Activity audit trail

---

## Sequence Summary

| Step | Controller | Model Method | Tables | Transaction |
|------|-----------|--------------|--------|-------------|
| 1. Admin Add Product | `AdminCon` | `Product_model->insert_product()` | `product`, `system_activity_log` | ✅ |
| 2. Customer Product | `ShopCon` | `Product_model->get_products()` | `product` | ❌ (Read-only) |
| 3. Add to Cart | `CartCon`, `AddtoCartCon` | `Cart_model->save_customization()`, `Cart_model->add_to_cart()` | `customization`, `cart` | ✅ |
| 4. Cart | `CartCon` | `Cart_model->get_cart_items()`, `Cart_model->remove_item()` | `cart`, `customization` | ✅ (Remove only) |
| 5. Checkout | `ShopCon` | `Order_model->create_order()`, `Order_model->save_order_customizations()` | `order`, `order_items`, `system_activity_log` | ✅ |
| 6. Payment | `ShopCon` | `Order_model->save_payment_receipt()`, `Order_model->update_payment_method()` | `payment`, `order` | ✅ |
| 7. Complete | `ShopCon` | `Order_model->get_order_with_customer()` | `order`, `order_items`, `payment` | ❌ (Read-only) |
| 8. Sales Rep Confirmation | `SalesCon` | `Order_model->update_order_status()` | `order`, `payment`, `system_activity_log` | ✅ |
| 9. Admin Confirmation | `AdminCon` | `Order_model->update_order_status()` | `order`, `system_activity_log` | ✅ |
| 10. Payment Processing | `SalesCon` | `Order_model->update_payment_status()`, `Inventory_model->deduct_materials_for_order()` | `payment`, `order`, `inventory_items`, `stock_transactions` | ✅ |

---

**Last Updated:** Based on `super-latest-optimized.sql` schema
