# Glassify CI - Implementation Summary

## Overview
This document summarizes all changes and implementations made to the Glassify CI system, including the Direct Order and Site Assessment Order flow, booking system, and PayMongo payment integration.

---

## Table of Contents
1. [Order Type System](#order-type-system)
2. [Booking System for Site Assessment Orders](#booking-system-for-site-assessment-orders)
3. [PayMongo Payment Integration](#paymongo-payment-integration)
4. [Order Status Management](#order-status-management)
5. [UI/UX Improvements](#uiux-improvements)
6. [Validation Enhancements](#validation-enhancements)
7. [Admin Panel Updates](#admin-panel-updates)
8. [Files Modified/Created](#files-modifiedcreated)

---

## Order Type System

### Overview
The system now supports two distinct order types:
- **Direct Orders**: Standard orders that proceed directly to payment
- **Site Assessment Orders**: Orders that require an ocular visit before final pricing

### Implementation Details

#### Products Page (`application/views/shop/products.php`)
- Added display of `OrderType` for each product
- Shows "Direct" or "Site Assessment" label on product cards
- Customers can see order type before selecting a product

#### 2D Modeling Page (`application/views/shop/2DModeling.php`)
- Conditional button rendering based on product `OrderType`:
  - **Direct Orders**: Shows "Buy Now" button → redirects to Payment Page
  - **Site Assessment Orders**: Shows "Book Now" button → redirects to Booking Page
- Both order types retain "Add to Cart" functionality

---

## Booking System for Site Assessment Orders

### Booking Page (`application/views/shop/booking.php`)
**New File Created**

A dedicated page for Site Assessment order bookings that:
- **Collects**:
  - Shipping address
  - Preferred ocular visit date (with calendar picker)
  - Order summary with static price range (PriceMin - PriceMax)
  - Terms and conditions acceptance
- **Excludes**:
  - Billing address section
  - Payment method selection
  - PayMongo payment logic
  - Card/e-wallet forms

### Booking Flow

1. Customer selects Site Assessment product → Customizes on 2D Modeling page
2. Clicks "Book Now" → Redirected to Booking Page
3. Fills in shipping address and preferred ocular visit date
4. Clicks "Confirm Booking" → Order created with status "Pending Booking Confirmation"
5. Redirected to Track Order page
6. Admin manually confirms booking to prevent spam/fake requests

### Backend Implementation

#### Controller Methods (`application/controllers/ShopCon.php`)

**`booking()` Method**
- Loads user and cart data for booking view
- Filters cart items based on `selected` URL parameter
- Calculates static price range from product `PriceMin` and `PriceMax`
- Prepares shipping address data

**`confirm_booking()` Method**
- Validates terms acceptance
- Creates order with:
  - `OrderType = 'Site-Assessed'`
  - `Status = 'Pending Booking Confirmation'`
  - `PaymentStatus = 'Pending'`
  - `PreferredInstallationDate` (stores preferred ocular visit date)
- Saves order customizations
- Clears cart after successful booking
- Returns JSON response with redirect URL

**`accept_quotation()` Method**
- Allows customers to accept uploaded quotations
- Updates order status from "Quotation Available" to "Awaiting Payment"
- Enables payment flow after quotation acceptance

### Routes Added (`application/config/routes.php`)
```php
$route['booking'] = 'ShopCon/booking';
$route['confirm-booking'] = 'ShopCon/confirm_booking';
$route['accept-quotation'] = 'ShopCon/accept_quotation';
```

---

## PayMongo Payment Integration

### Overview
Implemented PayMongo Sandbox payment processing for Direct Orders only. All payments use test API keys - no real money is involved.

### PayMongo Keys (Sandbox)
- **Public Key**: `pk_test_H9zcEdXmDXCSjKbFkA9VBcBh`
- **Secret Key**: `sk_test_bprPGRxXWFN5RHJTG3hvY2rP`

### Payment Flow

#### Step 0: Payment Page (UI Only)
- Shipping address
- Billing address
- Order summary
- Payment method selection:
  - Credit / Debit Card
  - GCash
  - Maya
- "Place Order" button

#### Step 1: User Clicks "Confirm & Place Order"
- Frontend sends to backend:
  - `order_id`
  - `selected_payment_method` (card, gcash, maya)
  - `order_amount`

#### Step 2: Backend Creates Payment Intent
- Backend calls PayMongo API: `POST /v1/payment_intents`
- Saves `payment_intent_id` to database (stored in `payment.Transaction_ID`)
- Returns `payment_intent_id` and `client_key` to frontend

#### Step 3: Frontend Creates Payment Method
- **For Card**: Collects card details (number, expiry, CVC) via modal
- **For E-Wallet**: Creates e-wallet payment method directly
- Uses PayMongo REST API with public key

#### Step 4: Attach Payment Method (Backend)
- Frontend sends `payment_method_id` and `payment_intent_id`
- Backend calls: `POST /v1/payment_intents/{payment_intent_id}/attach`
- Returns payment status

#### Step 5: Handle PayMongo Response
- **Card Payment**: Usually returns `status = succeeded` immediately
  - Backend verifies payment
  - Marks order as "Paid"
  - Saves payment record
- **E-Wallet Payment**: Returns `status = awaiting_next_action`
  - Backend sends `next_action.redirect.url`
  - Frontend redirects customer to PayMongo

#### Step 6: Return From PayMongo (E-Wallet)
- PayMongo redirects to: `/payment/complete?order_id=XXX`
- Backend retrieves Payment Intent
- Confirms `status === succeeded`
- Updates order to "Paid" status

### Implementation Files

#### PayMongo Library (`application/libraries/Paymongo.php`)
**New File Created**

Methods:
- `create_payment_intent()` - Creates payment intent with amount, currency, payment method types
- `retrieve_payment_intent()` - Retrieves payment intent status
- `attach_payment_method()` - Attaches payment method to payment intent
- `get_public_key()` - Returns public key for frontend use

#### Backend Endpoints (`application/controllers/ShopCon.php`)

**`create_payment_intent()` Method**
- Validates order belongs to customer
- Ensures only Direct Orders can use PayMongo
- Creates payment intent via PayMongo library
- Saves payment intent ID to database
- Returns payment intent ID and client key

**`attach_payment_method()` Method**
- Verifies order ownership
- Attaches payment method to payment intent
- Handles immediate success (card) vs redirect (e-wallet)
- Updates order and payment status accordingly

**`payment_complete()` Method**
- Handles return from PayMongo redirect
- Verifies payment intent status
- Updates order to "Paid" status if verified
- Displays payment complete page

**Updated `place_order()` Method**
- Creates order with status "Pending Payment" (before payment)
- Returns order ID to frontend
- Frontend then initiates PayMongo payment flow
- Cart is cleared after order creation

#### Frontend Implementation (`application/views/shop/checkout.php`)

**Payment Method Selection**
- Updated to show three separate options:
  - Credit / Debit Card
  - GCash
  - Maya
- Removed generic "E-Wallet" option

**JavaScript Functions**

`initiatePayMongoPayment()`:
- Handles complete PayMongo payment flow
- Creates payment intent via backend
- Creates payment method (card or e-wallet)
- Attaches payment method
- Handles success/redirect responses

`collectCardDetails()`:
- Shows modal for card information collection
- Validates card number format
- Collects: cardholder name, card number, expiry month/year, CVC

**Payment Complete Page** (`application/views/shop/payment_complete.php`)
**New File Created**
- Displays payment status
- Shows order details
- Displays payment method and transaction ID
- Provides links to track order or continue shopping

### Routes Added
```php
$route['payment/create-payment-intent'] = 'ShopCon/create_payment_intent';
$route['payment/attach-payment-method'] = 'ShopCon/attach_payment_method';
$route['payment/complete'] = 'ShopCon/payment_complete';
```

### Important Rules
✅ Always verify via PayMongo API
❌ Never trust frontend success
❌ Never mark paid without `status = succeeded`
✅ Use sandbox keys only

---

## Order Status Management

### Direct Order Status Flow

**Customer Side:**
1. **Order Placed** → Order created with "Pending Payment" status
2. **Paid** → After successful PayMongo payment verification
3. **In Fabrication** → Order is being manufactured
4. **Completed** → Order fully processed and delivered

**Progress Bar**: 4-step flow displayed on Track Order page

### Site Assessment Order Status Flow

**Customer Side:**
1. **Booking Submitted** (Status: "Pending Booking Confirmation")
   - Message: "Your site assessment booking has been submitted and is awaiting admin confirmation. Payment is not available yet."

2. **Booking Confirmed – Waiting for Ocular Visit** (Status: "Approved" or "Booking Confirmed")
   - Message: "Your booking has been confirmed. We will schedule an ocular visit soon."

3. **Ocular Visit Completed – Preparing Quotation** (After ocular visit)
   - Message: "The ocular visit has been completed. We are preparing your quotation."
   - No payment option available

4. **Quotation Available** (Status: "Quotation Available")
   - Message: "Your quotation is ready for review. Please accept it to proceed."
   - Button: "Accept Quotation"

5. **Awaiting Payment** (Status: "Awaiting Payment")
   - Message: "Please proceed with payment to continue with your order."
   - Button: "Pay Now"

6. **Payment Received – In Fabrication** (Status: "In Fabrication")
   - Message: "Payment has been received. Your order is now in fabrication."

7. **Installation Completed – Balance Due** (Status: "Ready for Installation" or "Installation Completed" with balance)
   - Message: "Installation has been completed. Please pay the final payment."
   - Button: "Pay Final Payment"

8. **Completed** (Status: "Completed")
   - Message: "Your site assessment order has been fully completed."

**Progress Bar**: 5-step flow displayed on Track Order page

### Status Transition Validation

#### Backend (`application/controllers/AdminCon.php`)

**`get_valid_status_transitions()` Method**
- Defines valid status transitions for both order types
- Handles case-insensitive status matching
- Maps legacy status names to standard names
- Returns empty array for terminal states (Cancelled, Completed)

**`update_order_status()` Method**
- Validates status transitions before updating
- Handles empty/NULL order statuses (defaults to "Pending Review")
- Extensive debug logging for troubleshooting
- Case-insensitive status comparison
- Uses normalized status names from valid transitions

#### Frontend (`assets/js/admin-js/order-management.js`)

**Updated `transitions` Object**
- Includes all Direct Order statuses
- Includes all Site Assessment statuses
- Defines valid transitions for each status
- Terminal states have empty arrays

**Functions Updated**
- `populateStatusDropdown()` - Populates dropdown with valid transitions
- `populateModalStatusDropdown()` - Same for modal dropdown

### Track Order Page (`application/views/shop/order_tracking.php`)

**Status Display Logic**
- Detects order type (Direct vs Site Assessment)
- Maps status to appropriate labels and messages
- Shows action buttons conditionally:
  - "Accept Quotation" (Site Assessment only)
  - "Pay Now" (Site Assessment - Awaiting Payment)
  - "Pay Final Payment" (Site Assessment - Balance Due)

**Progress Bar Calculation**
- Direct Orders: 4 steps (Order Placed → Paid → In Fabrication → Completed)
- Site Assessment: 5 steps (Booking → Ocular → Fabrication → Installation → Completed)

**Real-time Progress Updates**
- JavaScript polls for order progress via AJAX
- Updates progress bar and status cards dynamically
- Differentiates between Direct and Site Assessment flows

---

## UI/UX Improvements

### Product Display

#### Cart and Order Detail Tables
**Files Modified:**
- `application/views/shop/addtocart.php`
- `application/views/shop/order_complete.php`
- `application/views/shop/order_tracking.php`

**Changes:**
- Combined "Image" and "Product" columns into single "Product" column
- Product image displayed first, followed by product name
- Uses product's `ImageUrl` (not 2D customization image) for main display
- 2D customization image (`DesignRef`) remains in "Customization" column

### Validation Warnings

#### Payment Page (`application/views/shop/checkout.php`)
- Added validation notice that appears when "Place Order" is clicked with incomplete fields
- Lists all missing required fields:
  - Shipping address fields
  - Billing Address (generic, not specific fields)
  - Payment Method
  - Terms and Conditions acceptance
- Scrolls to first error field
- Warning only appears on button click (not on field blur/change)

#### Booking Page (`application/views/shop/booking.php`)
- Similar validation notice for incomplete fields
- Lists missing fields:
  - Shipping address fields
  - Preferred Ocular Visit Date
  - Terms and Conditions acceptance
- Prevents duplicate "Preferred Ocular Visit Date" in warning list

### Payment Method Selection
- Updated from generic "E-Wallet" to specific options:
  - Credit / Debit Card
  - GCash
  - Maya
- Each option has its own radio button
- Payment method properly passed to backend

---

## Validation Enhancements

### Form Validation

#### Payment Page
- Validates all shipping address fields
- Validates billing address if different from shipping
- Validates payment method selection
- Validates terms acceptance
- Shows comprehensive warning with all missing fields

#### Booking Page
- Validates shipping address fields
- Validates preferred ocular visit date selection
- Validates terms acceptance
- Prevents booking confirmation with incomplete data

### Backend Validation

#### Order Creation
- Validates customer is logged in
- Validates cart is not empty
- Validates payment method (card, gcash, or maya)
- Validates terms acceptance
- Validates order type restrictions (PayMongo only for Direct Orders)

#### Status Transitions
- Validates status transitions are allowed
- Case-insensitive status matching
- Handles empty/NULL statuses gracefully
- Prevents invalid transitions (e.g., from "Cancelled")

---

## Admin Panel Updates

### Order Management (`application/views/admin/admin_orders.php`)

**Status Dropdown**
- Populated with valid transitions only
- Prevents invalid status changes
- Shows appropriate options based on current status
- Works for both Direct and Site Assessment orders

**Status Update Modal**
- Same validation as main dropdown
- Shows current status
- Lists available transitions
- Prevents invalid updates

### Debug Logging
- Extensive logging added to `update_order_status()` method
- Logs current status, new status, and valid transitions
- Helps troubleshoot "Invalid status transition" errors
- Returns debug info in AJAX response for frontend inspection

---

## Files Modified/Created

### New Files Created

1. **`application/libraries/Paymongo.php`**
   - PayMongo API library
   - Handles all PayMongo API interactions

2. **`application/views/shop/booking.php`**
   - Booking page for Site Assessment orders
   - Collects booking information without payment

3. **`application/views/shop/payment_complete.php`**
   - Payment completion page
   - Shows payment status and order details

### Files Modified

1. **`application/config/routes.php`**
   - Added booking routes
   - Added PayMongo payment routes

2. **`application/controllers/ShopCon.php`**
   - Added `booking()` method
   - Added `confirm_booking()` method
   - Added `accept_quotation()` method
   - Added `create_payment_intent()` method
   - Added `attach_payment_method()` method
   - Added `payment_complete()` method
   - Updated `place_order()` method for PayMongo flow
   - Updated `get_order_progress_ajax()` for status tracking

3. **`application/controllers/AdminCon.php`**
   - Added `get_valid_status_transitions()` method
   - Updated `update_order_status()` with validation
   - Enhanced debug logging

4. **`application/views/shop/products.php`**
   - Added OrderType display

5. **`application/views/shop/2DModeling.php`**
   - Added conditional "Buy Now" vs "Book Now" buttons
   - Passes product OrderType to JavaScript

6. **`application/views/shop/checkout.php`**
   - Updated payment method selection (Card, GCash, Maya)
   - Added PayMongo payment flow JavaScript
   - Added validation warnings
   - Removed Preferred Ocular Visit Date field
   - Added card details collection modal

7. **`application/views/shop/order_tracking.php`**
   - Added Site Assessment status mapping
   - Added Direct Order simplified flow
   - Added action buttons (Accept Quotation, Pay Now, Pay Final Payment)
   - Updated progress bar calculation for both order types

8. **`application/views/shop/addtocart.php`**
   - Combined Image and Product columns

9. **`application/views/shop/order_complete.php`**
   - Combined Image and Product columns

10. **`assets/js/2d-functions/addtocustomization.js`**
    - Added "Book Now" button handler
    - Redirects to booking page for Site Assessment orders

11. **`assets/js/admin-js/order-management.js`**
    - Updated status transitions object
    - Added all Site Assessment statuses
    - Updated dropdown population functions

---

## Database Changes

### Payment Table
- `Transaction_ID` field used to store PayMongo `payment_intent_id`
- Payment records created/updated during payment flow

### Order Table
- `OrderType` field distinguishes Direct vs Site Assessment orders
- `PreferredInstallationDate` stores preferred ocular visit date for bookings
- `Status` field updated with new statuses:
  - "Pending Booking Confirmation"
  - "Quotation Available"
  - "Awaiting Payment"
  - "Booking Confirmed"
  - "Ocular Visit Completed"
  - "Installation Completed"

---

## Testing Checklist

### Direct Orders
- [ ] Select Direct Order product
- [ ] Customize on 2D Modeling page
- [ ] Click "Buy Now" → Redirects to Payment Page
- [ ] Select payment method (Card/GCash/Maya)
- [ ] Complete payment flow
- [ ] Verify order status updates correctly
- [ ] Check Track Order page shows 4-step progress

### Site Assessment Orders
- [ ] Select Site Assessment product
- [ ] Customize on 2D Modeling page
- [ ] Click "Book Now" → Redirects to Booking Page
- [ ] Fill shipping address and preferred date
- [ ] Click "Confirm Booking"
- [ ] Verify order created with "Pending Booking Confirmation" status
- [ ] Admin confirms booking
- [ ] Check status updates through flow
- [ ] Test "Accept Quotation" functionality
- [ ] Test "Pay Now" after quotation acceptance
- [ ] Check Track Order page shows 5-step progress

### PayMongo Integration
- [ ] Test Card payment (immediate success)
- [ ] Test GCash payment (redirect to PayMongo)
- [ ] Test Maya payment (redirect to PayMongo)
- [ ] Verify payment verification on return
- [ ] Check payment records in database
- [ ] Verify order status updates after payment

### Admin Panel
- [ ] Test status transitions for Direct Orders
- [ ] Test status transitions for Site Assessment Orders
- [ ] Verify invalid transitions are blocked
- [ ] Check debug logging works

---

## Important Notes

1. **PayMongo Sandbox Only**: All payment processing uses test keys - no real money involved
2. **Order Type Restrictions**: PayMongo payment only works for Direct Orders
3. **Status Validation**: Status transitions are strictly validated to prevent invalid changes
4. **Payment Verification**: Always verified server-side via PayMongo API - never trust frontend
5. **Booking Confirmation**: Site Assessment bookings require admin confirmation to prevent spam

---

## Future Enhancements (Not Implemented)

1. Email notifications for order status changes
2. SMS notifications for booking confirmations
3. Admin dashboard for managing bookings
4. Calendar integration for ocular visit scheduling
5. Quotation PDF generation and upload
6. Payment receipt generation
7. Refund processing for cancelled orders

---

## Version Information

- **Implementation Date**: 2024
- **CodeIgniter Version**: 3.x
- **PayMongo API Version**: v1
- **PHP Version**: 7.4+

---

## Support and Troubleshooting

### Common Issues

1. **"Invalid status transition" error**
   - Check browser console for debug info
   - Verify current status in database
   - Check valid transitions in `get_valid_status_transitions()`

2. **Payment not processing**
   - Verify PayMongo keys are correct
   - Check network requests in browser console
   - Verify payment intent creation succeeded
   - Check server logs for PayMongo API errors

3. **Booking not creating order**
   - Verify cart items are selected
   - Check terms acceptance
   - Verify shipping address is complete
   - Check browser console for errors

---

## Conclusion

This implementation adds comprehensive support for two distinct order types with appropriate payment and booking flows. The PayMongo integration provides secure payment processing for Direct Orders, while the booking system enables proper workflow management for Site Assessment Orders requiring ocular visits and quotations.

All changes maintain backward compatibility with existing Direct Orders and add new functionality without breaking existing features.
