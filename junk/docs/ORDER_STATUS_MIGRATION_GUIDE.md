# Order Status Migration Guide

## Overview
This document describes the migration from the old order status system to the new order status system.

## Old Status Values
- Pending Review
- Awaiting Admin
- Ready to Approve
- Approved
- Disapproved
- In Fabrication
- Ready for Installation
- Completed
- Cancelled
- Returned

## New Status Values
- **Pending Payment** - Order is created and awaiting payment
- **Paid** - Payment has been received
- **Payment Verified** - Payment has been verified by staff
- **Approved** - Order is approved and ready for production
- **In Fabrication** - Order is being manufactured
- **Scheduling** - Order is being scheduled for installation/shipping
- **For Installation / Shipping** - Order is ready for installation or shipping
- **Completed** - Order is completed and delivered
- **Cancelled** - Order has been cancelled (replaces Disapproved)
- **Returned** - Order has been returned

## Status Flow

### New Workflow
```
Pending Payment → Paid → Payment Verified → Approved → In Fabrication → Scheduling → For Installation / Shipping → Completed
```

### Status Transitions

1. **Pending Payment**
   - Can transition to: `Paid`, `Cancelled`
   - Description: Initial status when order is created

2. **Paid**
   - Can transition to: `Payment Verified`, `Cancelled`
   - Description: Customer has submitted payment

3. **Payment Verified**
   - Can transition to: `Approved`, `Cancelled`
   - Description: Staff has verified the payment

4. **Approved**
   - Can transition to: `In Fabrication`, `Cancelled`
   - Description: Order is approved and ready for production

5. **In Fabrication**
   - Can transition to: `Scheduling`, `Cancelled`
   - Description: Order is being manufactured

6. **Scheduling**
   - Can transition to: `For Installation / Shipping`, `Cancelled`
   - Description: Order is being scheduled

7. **For Installation / Shipping**
   - Can transition to: `Completed`, `Cancelled`
   - Description: Order is ready for delivery/installation

8. **Completed**
   - Terminal state
   - Description: Order is finished

9. **Cancelled**
   - Terminal state
   - Description: Order was cancelled (replaces old "Disapproved")

10. **Returned**
    - Terminal state
    - Description: Order was returned

## Migration Mapping

| Old Status | New Status | Notes |
|------------|------------|-------|
| Pending Review | Pending Payment | Initial order status |
| Awaiting Admin | Pending Payment | Orders awaiting admin review need payment |
| Ready to Approve | Pending Payment | Orders ready to approve need payment |
| Approved | Approved | Keep as is (but requires payment verification first) |
| Disapproved | Cancelled | Disapproved orders are now cancelled |
| In Fabrication | In Fabrication | Keep as is |
| Ready for Installation | For Installation / Shipping | Updated name |
| Completed | Completed | Keep as is |
| Cancelled | Cancelled | Keep as is |
| Returned | Returned | Keep as is |

## Role Permissions

### Sales Representative
- Can transition: `Pending Payment` → `Paid`
- Can transition: `Paid` → `Payment Verified`

### Admin
- Can transition: `Pending Payment` → `Paid`, `Cancelled`
- Can transition: `Paid` → `Payment Verified`, `Cancelled`
- Can transition: `Payment Verified` → `Approved`, `Cancelled`
- Can transition: `Approved` → `In Fabrication`, `Cancelled`
- Can transition: `In Fabrication` → `Scheduling`, `Cancelled`
- Can transition: `Scheduling` → `For Installation / Shipping`, `Cancelled`
- Can transition: `For Installation / Shipping` → `Completed`, `Cancelled`

## Migration Steps

1. **Run Database Migration**
   ```sql
   -- Execute: database/scripts/update_order_status_enum.sql
   ```

2. **Update Application Code**
   - Order_model.php has been updated with new validation logic
   - Controllers need to be updated to use new status values
   - Views need to be updated to display new status values

3. **Test Status Transitions**
   - Verify all status transitions work correctly
   - Test role-based permissions
   - Test backward compatibility with old status values

4. **Update Views and UI**
   - Update status badges/colors
   - Update status filters
   - Update status display text

## Backward Compatibility

The `Order_model::map_old_status_to_new()` function provides backward compatibility by mapping old status values to new ones. This allows the system to handle both old and new status values during the migration period.

## Notes

- Payment verification is now a separate step in the workflow
- "Disapproved" status is replaced with "Cancelled"
- "Ready for Installation" is renamed to "For Installation / Shipping" to be more descriptive
- The new workflow emphasizes payment processing before order approval
