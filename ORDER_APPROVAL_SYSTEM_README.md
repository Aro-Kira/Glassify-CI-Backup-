# Order Approval System Implementation

## Overview
This document describes the comprehensive order approval system that organizes orders by status into separate database tables and implements a complete approval workflow.

## Database Tables

### Status-Specific Tables
1. **pending_review_orders** - Orders awaiting initial review by Sales Rep
2. **awaiting_admin_orders** - Orders sent to admin for review
3. **ready_to_approve_orders** - Orders ready for final approval by Sales Rep (after admin review)
4. **disapproved_orders** - Orders that have been cancelled/rejected
5. **approved_orders** - Orders that have been fully approved and are ready for payment

## Workflow

### 1. Pending Review → Awaiting Admin
- Sales Rep reviews order and clicks "Request Approval"
- Order moves from `pending_review_orders` to `awaiting_admin_orders`
- Admin reviews the order

### 2. Awaiting Admin → Ready to Approve
- Admin reviews and approves/disapproves
- If approved: Order moves to `ready_to_approve_orders` with `AdminStatus = 'Approved'`
- If disapproved: Order moves to `disapproved_orders`

### 3. Ready to Approve → Approved
- Sales Rep gives final approval
- Order moves from `ready_to_approve_orders` to `approved_orders`
- Customer is notified and can proceed with payment

### 4. Disapproval (Any Stage)
- Sales Rep can disapprove at any stage
- Order immediately moves to `disapproved_orders`
- Customer is notified of cancellation
- Order is automatically cancelled

## Implementation Files

### Database
- `create_order_status_tables.sql` - Creates all status-specific tables
- `migrate_to_status_tables.php` - Migrates existing orders from `order_page` to new tables

### Controller Methods (SalesCon.php)
- `approve_order()` - Final approval by Sales Rep
- `disapprove_order()` - Disapproval at any stage
- `request_approval()` - Request admin review
- `sales_payments()` - Display approved orders ready for payment

### JavaScript Handlers
- `sales-order-approve-handler.js` - Handles approve/disapprove button clicks
- `sales-request-approval-handler.js` - Handles "Request Approval" button

## Setup Instructions

1. **Create Database Tables**
   ```sql
   -- Run create_order_status_tables.sql in your database
   ```

2. **Migrate Existing Data** (if needed)
   ```bash
   php migrate_to_status_tables.php
   ```

3. **Update Routes** (if needed)
   - Routes are automatically handled by CodeIgniter
   - Ensure `SalesCon/approve_order`, `SalesCon/disapprove_order`, and `SalesCon/request_approval` are accessible

## Notification System (TODO)

The notification system structure is in place but needs to be implemented:
- `notify_customer_approved()` - Send notification when order is approved
- `notify_customer_disapproved()` - Send notification when order is disapproved

These methods should:
- Send email to customer
- Update `CustomerNotified` flag in database
- Set `CustomerNotified_Date`

## Payment Processing

Once an order is approved:
- It appears in `sales_payments.php`
- Customer can choose payment method (E-Wallet or Cash on Delivery)
- Payment status is tracked in `approved_orders` table

## Notes

- All order movements are transactional (using database transactions)
- Orders are automatically removed from source tables when moved
- The system maintains data integrity across all status transitions
- Sales Reps can only see and manage orders assigned to them

