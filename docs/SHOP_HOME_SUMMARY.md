# 📋 Shop Directory & Home-Login MVC Architecture Summary

> **Last Updated:** December 7, 2025  
> **Project:** Glassify-CI (Customer Portal)

---

## 🗂️ SHOP DIRECTORY

### Controllers

| Controller | Methods | Route | Description |
|------------|---------|-------|-------------|
| `ShopCon.php` | `products()` | `/products` | Lists all products from database |
| | `product_2d()` | `/2DModeling?id=X` | 2D customization page |
| | `checkout()` | `/payment` | Payment/checkout page |
| | `ewallet()` | `/paying` | E-Wallet payment page |
| | `complete()` | `/complete` | Order completion page |
| | `wishlist()` | `/wishlist` | Display wishlist |
| | `order_tracking()` | `/track_order?order=X` | Track order status |
| | `list_products()` | `/my_purchases` | Customer's purchased items |
| | `place_order()` | AJAX | Creates order from cart |
| | `submit_ewallet_payment()` | POST | Process E-Wallet payment |
| `CartCon.php` | `cart_page()` | `/addtocart` | Shopping cart page |
| | `add_customized_ajax()` | AJAX | Add customized item to cart |
| | `update_qty_ajax()` | AJAX | Update cart item quantity |
| | `remove_ajax()` | AJAX | Remove item from cart |
| | `get_selected_cart_ajax()` | AJAX | Get selected items for checkout |
| `WishlistCon.php` | `index()` | `/wishlist` | Display wishlist |
| | `add_ajax()` | AJAX | Add item to wishlist |
| | `remove_ajax()` | AJAX | Remove from wishlist |
| | `move_to_cart_ajax()` | AJAX | Transfer to cart |

---

### Models & Database Tables

| Model | Database Tables | Key Functions |
|-------|-----------------|---------------|
| `Product_model.php` | `product` | `get_products()`, `get_product($id)`, `get_recommended_products()` |
| `Cart_model.php` | `cart`, `customization` | `get_cart_items()`, `add_to_cart()`, `save_customization()`, `remove_item()` |
| `Order_model.php` | `order`, `order_customization`, `payment` | `create_order()`, `save_order_customizations()`, `get_order_tracking_details()`, `calculate_order_summary()` |
| `Wishlist_model.php` | `wishlist`, `customization` | `add_to_wishlist()`, `get_wishlist_items()`, `move_to_cart()` |
| `User_model.php` | `user`, `user_address` | `get_by_id()`, `get_addresses()` |

---

### Views (Shop Directory)

| View File | CSS File | Description |
|-----------|----------|-------------|
| `shop/products.php` | `products_style.css` | Product catalog grid with filters |
| `shop/2DModeling.php` | `2DModeling_styles.css`, `2DModeling_styles2.css` | Interactive 2D glass customizer (Konva.js) |
| `shop/addtocart.php` | `addtocart_style.css` | Shopping cart with quotation modal |
| `shop/checkout.php` | `checkout_style.css` | Checkout with address & payment selection |
| `shop/ewallet.php` | `ewallet_style.css` | E-Wallet payment & receipt upload |
| `shop/order_complete.php` | `order_complete.css` | Order confirmation summary |
| `shop/order_tracking.php` | `order_tracking.css` | Order progress tracker |
| `shop/wishlist.php` | `wishlist_style.css` | Wishlist management |
| `shop/list_product.php` | `list_product.css` | Customer's purchase history |

---

### JavaScript Files (Shop)

| JS File | Location | Purpose |
|---------|----------|---------|
| `cart.js` | `assets/js/` | Cart AJAX operations (add, remove, update qty, checkout selected) |
| `wishlist.js` | `assets/js/` | Wishlist AJAX operations (add, remove, move to cart, clear) |
| `2d_customization.js` | `assets/js/2d-functions/` | Konva.js 2D visualization, pricing calculator, customization state |
| `addtocustomization.js` | `assets/js/2d-functions/` | Add customized item to cart AJAX |
| `addtowishlist.js` | `assets/js/2d-functions/` | Add customized item to wishlist AJAX |
| `filters.js` | `assets/js/products-page/` | Product filtering (category, material, availability) |
| `testimonial.js` | `assets/js/products-page/` | Testimonial carousel |

---

### CSS Files (Shop)

| CSS File | Location |
|----------|----------|
| `products_style.css` | `assets/css/general-customer/shop/` |
| `2DModeling_styles.css` | `assets/css/general-customer/shop/` |
| `2DModeling_styles2.css` | `assets/css/general-customer/shop/` |
| `addtocart_style.css` | `assets/css/general-customer/shop/` |
| `checkout_style.css` | `assets/css/general-customer/shop/` |
| `ewallet_style.css` | `assets/css/general-customer/shop/` |
| `order_complete.css` | `assets/css/general-customer/shop/` |
| `order_tracking.css` | `assets/css/general-customer/shop/` |
| `wishlist_style.css` | `assets/css/general-customer/shop/` |
| `list_product.css` | `assets/css/general-customer/shop/` |

---

### Table Relationships (Shop Flow)

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│   product   │──┬──│  customization   │──┬──│    cart     │
│  Product_ID │  │  │ CustomizationID  │  │  │   Cart_ID   │
│ ProductName │  │  │   Product_ID     │  │  │ Customer_ID │
│    Price    │  │  │   Dimensions     │  │  │  Product_ID │
│  ImageUrl   │  │  │    GlassType     │  │  │CustomizationID│
│  Category   │  │  │  GlassThickness  │  │  │  Quantity   │
│  Material   │  │  │    EdgeWork      │  │  └─────────────┘
└─────────────┘  │  │   FrameType      │  │
                 │  │  EstimatePrice   │  │  ┌─────────────┐
                 │  │    DesignRef     │  ├──│  wishlist   │
                 │  └──────────────────┘  │  │ Wishlist_ID │
                 │                        │  │ Customer_ID │
                 │                        │  │  Product_ID │
                 │                        │  │CustomizationID│
                 │                        │  └─────────────┘
                 │
                 │  ┌──────────────────┐     ┌─────────────────────┐
                 └──│      order       │──┬──│ order_customization │
                    │    OrderID       │  │  │OrderCustomizationID │
                    │  Customer_ID     │  │  │      OrderID        │
                    │  SalesRep_ID     │  │  │    Product_ID       │
                    │  TotalAmount     │  │  │    Dimensions       │
                    │    Status        │  │  │    GlassType        │
                    │ PaymentStatus    │  │  │  EstimatePrice      │
                    │DeliveryAddress   │  │  │    Quantity         │
                    └──────────────────┘  │  └─────────────────────┘
                                          │
                                          │  ┌─────────────┐
                                          └──│   payment   │
                                             │ PaymentID   │
                                             │  OrderID    │
                                             │   Amount    │
                                             │ReceiptPath │
                                             │   Status    │
                                             └─────────────┘
```

---

## 🏠 HOME-LOGIN PAGE

### Controller

| Controller | Method | Route | Description |
|------------|--------|-------|-------------|
| `Pages.php` | `home_login()` | `/home-login` | Customer dashboard after login |

---

### Models Used

| Model | Purpose |
|-------|---------|
| `Order_model` | Get orders, activity feed, count by status |
| `User_model` | Get user data |
| `Product_model` | Get recommended products |

---

### View & CSS

| View | CSS | Description |
|------|-----|-------------|
| `pages/home-login.php` | `home_style.css` | Dashboard with order progress, activity feed, appointments, recommendations |

---

### Data Flow (Home-Login)

```php
// Pages.php -> home_login()
$data['user'] = User_model->get_by_id($user_id);
$data['orders_in_progress'] = Order_model->count_orders_by_status($user_id, ['Pending', 'Approved', 'In Fabrication', 'Ready for Installation']);
$data['recent_activity'] = Order_model->get_recent_order_activity($user_id);
$data['orders'] = Order_model->get_customer_orders_with_products($user_id, 10);
$data['activity_feed'] = Order_model->get_activity_feed($user_id, 20);
$data['recommendations'] = Product_model->get_recommended_products(4);
$data['next_appointment'] = // Calculated from order dates
```

---

### JavaScript Functions (Home-Login - Inline)

| Function | Purpose |
|----------|---------|
| `toggleDropdown()` | Filter dropdown menus |
| `filterOrders()` | Filter order table by status |
| `filterAppointments()` | Filter appointment table by status |
| `toggleActivityFeed()` | Expand/collapse activity feed |

---

## 🔐 AUTH PAGES

### Controller

| Controller | Method | Route | Description |
|------------|--------|-------|-------------|
| `Auth.php` | `login()` | `/login` | Customer login page |
| | `register()` | `/register` | Customer registration |
| | `process_role_login()` | POST | Process login & redirect based on role |
| | `logout()` | `/logout` | Destroy session |

### Views & CSS

| View | CSS |
|------|-----|
| `auth/login.php` | `login_style.css` |
| `auth/register.php` | `register_style.css` |

### Model

- `User_model.php` → `email_exists()`, `get_by_email()`, `register()`

---

## 📊 Complete Customer Flow

```
┌──────────────┐   ┌───────────────┐   ┌──────────────────┐
│   Customer   │──▶│  /login       │──▶│   /home-login    │
│   (Guest)    │   │  Auth.php     │   │   Pages.php      │
└──────────────┘   └───────────────┘   └────────┬─────────┘
                                                │
        ┌───────────────────────────────────────┘
        ▼
┌───────────────┐   ┌───────────────┐   ┌──────────────────┐
│  /products    │──▶│  /2DModeling  │──▶│   /addtocart     │
│  ShopCon.php  │   │  ShopCon.php  │   │   CartCon.php    │
│  (Browse)     │   │  (Customize)  │   │   (Review)       │
└───────────────┘   └───────────────┘   └────────┬─────────┘
                                                 │
        ┌────────────────────────────────────────┘
        ▼
┌───────────────┐   ┌───────────────┐   ┌──────────────────┐
│   /payment    │──▶│   /paying     │──▶│   /complete      │
│  ShopCon.php  │   │  (E-Wallet)   │   │  ShopCon.php     │
│  (Checkout)   │   │  ShopCon.php  │   │  (Confirmation)  │
└───────────────┘   └───────────────┘   └────────┬─────────┘
                                                 │
        ┌────────────────────────────────────────┘
        ▼
┌───────────────────────────────────────────────────────────┐
│   /track_order?order=X  │  /my_purchases  │  /wishlist    │
│   (Order Tracking)      │  (History)      │  (Saved)      │
└───────────────────────────────────────────────────────────┘
```

---

## 📁 File Structure Reference

```
application/
├── controllers/
│   ├── Auth.php              # Login, Register, Logout
│   ├── CartCon.php           # Cart operations
│   ├── Pages.php             # Home, Home-Login, About, Projects
│   ├── ShopCon.php           # Shop pages (products, checkout, orders)
│   └── WishlistCon.php       # Wishlist operations
├── models/
│   ├── Cart_model.php        # Cart & customization DB operations
│   ├── Order_model.php       # Order & payment DB operations
│   ├── Product_model.php     # Product DB operations
│   ├── User_model.php        # User & address DB operations
│   └── Wishlist_model.php    # Wishlist DB operations
└── views/
    ├── auth/
    │   ├── login.php
    │   └── register.php
    ├── pages/
    │   └── home-login.php
    ├── shop/
    │   ├── 2DModeling.php
    │   ├── addtocart.php
    │   ├── checkout.php
    │   ├── ewallet.php
    │   ├── list_product.php
    │   ├── order_complete.php
    │   ├── order_tracking.php
    │   ├── products.php
    │   └── wishlist.php
    └── includes/
        ├── header.php
        └── footer.php

assets/
├── css/
│   └── general-customer/
│       ├── auth/
│       │   ├── login_style.css
│       │   └── register_style.css
│       ├── pages/
│       │   └── home_style.css
│       └── shop/
│           ├── 2DModeling_styles.css
│           ├── 2DModeling_styles2.css
│           ├── addtocart_style.css
│           ├── checkout_style.css
│           ├── ewallet_style.css
│           ├── list_product.css
│           ├── order_complete.css
│           ├── order_tracking.css
│           ├── products_style.css
│           └── wishlist_style.css
└── js/
    ├── 2d-functions/
    │   ├── 2d_customization.js
    │   ├── addtocustomization.js
    │   └── addtowishlist.js
    ├── products-page/
    │   ├── filters.js
    │   └── testimonial.js
    ├── cart.js
    └── wishlist.js
```

---

## 🗄️ Database Tables Summary

| Table | Primary Key | Foreign Keys | Purpose |
|-------|-------------|--------------|---------|
| `user` | UserID | - | User accounts (customers, staff) |
| `user_address` | AddressID | UserID | Shipping/Billing addresses |
| `product` | Product_ID | - | Glass products catalog |
| `customization` | CustomizationID | Customer_ID, Product_ID | Custom glass specifications |
| `cart` | Cart_ID | Customer_ID, Product_ID, CustomizationID | Shopping cart items |
| `wishlist` | Wishlist_ID | Customer_ID, Product_ID, CustomizationID | Saved items |
| `order` | OrderID | Customer_ID, SalesRep_ID | Customer orders |
| `order_customization` | OrderCustomizationID | OrderID, Product_ID | Order line items with specs |
| `payment` | PaymentID | OrderID | Payment records & receipts |

---

> **Note:** This document provides an overview of the MVC architecture for the Shop and Home-Login features. For detailed implementation, refer to the individual source files.
