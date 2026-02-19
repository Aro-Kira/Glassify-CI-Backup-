# Installation Payment Workflow Documentation
**Date:** February 10, 2026  
**Purpose:** Handle 5-day payment grace period after installation completion

---

## Business Rules

### Payment Timeline
1. **Physical Installation Complete** → Status: "Installed"
   - 5-day grace period starts automatically
   - Customer can pay via:
     - Cash on-site (logged by admin)
     - Online: GCash, Maya, Credit/Debit Card

2. **Payment Received (within 5 days)** → Status: "Complete"
   - Order fully closed ✓
   - Customer happy, Glassworth happy

3. **No Payment After 5 Days** → Status: "Payment Overdue"
   - Warning state
   - Admin must take action
   - Contact customer for payment

4. **Product Removed** → Status: "Returned"
   - Glassworth exercised right to remove installation
   - Order marked as returned due to non-payment

---

## Status Definitions

| Status | Meaning | Payment Status | Action Required |
|--------|---------|----------------|-----------------|
| **In Progress** | Installation team is actively working | N/A | Continue installation work |
| **Installed** | Physical installation complete, awaiting 10% payment | Pending (within 5-day grace period) | Monitor payment deadline |
| **Complete** | Installation AND payment both received | Paid ✓ | None - order closed successfully |
| **Payment Overdue** | Installation done but payment past 5-day deadline | Overdue (past deadline) | Contact customer or remove product |
| **Cancelled** | Installation appointment cancelled | N/A | Reschedule or close order |
| **Returned** | Product removed due to non-payment | Non-payment | Update order status to "Returned" |

---

## Database Fields

### New Fields in `appointments` Table

```sql
InstallationCompletedDate DATETIME
  - When admin marks status as "Installed"
  - Automatically set to current date/time
  - Used to calculate due date

PaymentDueDate DATETIME
  - Automatically calculated: InstallationCompletedDate + 5 days
  - Shows deadline to customer and admin
  - Cleared when payment received

PaymentGracePeriodDays INT (default: 5)
  - Configurable number of days for payment
  - Default is 5 days
  - Can be adjusted per order if needed
```

---

## UI/UX Flow

### Installation Appointment Page

#### When Status = "Installed"
```
┌─────────────────────────────────────────────┐
│ ⚠️  Installation Complete - Payment Pending │
├─────────────────────────────────────────────┤
│                                             │
│ Installation completed on: Feb 10, 2026     │
│ Payment due by: Feb 15, 2026 (5 days)      │
│                                             │
│ Countdown: ⏰ 4 days, 23 hours remaining    │
│                                             │
│ [Mark as Paid] [Extend Deadline]            │
└─────────────────────────────────────────────┘
```

#### When Status = "Payment Overdue"
```
┌─────────────────────────────────────────────┐
│ 🚨 PAYMENT OVERDUE                          │
├─────────────────────────────────────────────┤
│                                             │
│ Installation completed: Feb 10, 2026        │
│ Payment was due: Feb 15, 2026               │
│ Days overdue: 3 days                        │
│                                             │
│ Actions:                                    │
│ [Mark as Paid]                              │
│ [Contact Customer]                          │
│ [Mark as Returned] (remove installation)    │
└─────────────────────────────────────────────┘
```

---

## Implementation Checklist

### Phase 1: Database (Do this first)
- [ ] Run migration SQL: `add_installation_payment_statuses.sql`
- [ ] Verify trigger is created
- [ ] Test with sample appointment

### Phase 2: Backend
- [ ] Update `AdminCon.php` - appointment save logic
- [ ] Add validation for status transitions
- [ ] Add payment date calculation logic
- [ ] Add overdue payment detection

### Phase 3: Frontend (Admin)
- [ ] Update status dropdown in `admin_appointment.php`
- [ ] Add payment deadline display
- [ ] Add countdown timer for "Installed" status
- [ ] Add warning banners for "Payment Overdue"
- [ ] Add quick action buttons

### Phase 4: Frontend (Customer)
- [ ] Update order tracking page
- [ ] Show payment deadline countdown
- [ ] Add "Pay Now" button when status = "Installed"
- [ ] Show payment urgent warning when < 24 hours left

### Phase 5: Automation (Optional but recommended)
- [ ] Create cron job or scheduled task
- [ ] Auto-check appointments daily
- [ ] Auto-change "Installed" → "Payment Overdue" when deadline passes
- [ ] Send email notifications at:
  - 2 days before deadline
  - 1 day before deadline  
  - Day of deadline
  - 1 day after overdue

---

## Status Transition Rules

```
IN PROGRESS
    ↓
INSTALLED (5-day timer starts)
    ↓
    ├→ COMPLETE (payment received ✓)
    │
    └→ PAYMENT OVERDUE (5 days passed, no payment)
           ↓
           ├→ COMPLETE (late payment received)
           │
           └→ RETURNED (product removed)
```

### Validation Rules
- ✗ Cannot go from "In Progress" directly to "Complete" (must be "Installed" first)
- ✓ Can go from "Installed" to "Complete" (immediate payment)
- ✓ Can go from "Payment Overdue" to "Complete" (late payment accepted)
- ✓ Can go from "Payment Overdue" to "Returned" (remove product)
- ✗ Cannot go from "Complete" to any other status (final state)
- ✗ Cannot go from "Returned" to "Complete" (product already removed)

---

## Order Status Synchronization

When appointment status changes, update main order status:

| Appointment Status | Main Order Status | Logic |
|--------------------|-------------------|-------|
| In Progress | Installed | Installation in progress |
| Installed | Installed | Physically installed, payment pending |
| Complete | Completed | Everything done ✓ |
| Payment Overdue | Installed | Still installed but payment overdue |
| Returned | Returned | Product removed |

---

## Payment Recording

### When Customer Pays 10%

**Online Payment (GCash/Maya/Card):**
- Automatically captured by payment gateway
- Webhook updates `InstallationPaymentStatus = 'Paid'`
- Auto-change appointment status to "Complete"

**Cash Payment (On-site):**
- Admin manually records in appointment page
- Update payment method = "Cash"
- Upload receipt photo if available
- Manually mark status as "Complete"

---

## Example Scenarios

### Scenario 1: Happy Path
1. Feb 10: Installation done → Mark as "Installed"
2. Feb 12: Customer pays online → Auto "Complete"
3. ✓ Order successfully closed

### Scenario 2: Last-Minute Payment
1. Feb 10: Installation done → Mark as "Installed"  
2. Feb 15: Customer pays on deadline day → "Complete"
3. ✓ Order successfully closed

### Scenario 3: Overdue but Recovered
1. Feb 10: Installation done → Mark as "Installed"
2. Feb 16: No payment, system shows "Payment Overdue"
3. Feb 17: Customer calls, makes payment → Mark as "Complete"
4. ✓ Order closed (late but recovered)

### Scenario 4: Non-Payment
1. Feb 10: Installation done → Mark as "Installed"
2. Feb 16: No payment, mark as "Payment Overdue"
3. Feb 17: Admin contacts customer, no response
4. Feb 18: Team removes installation → Mark as "Returned"
5. Main order status → "Returned"
6. ✗ Order closed as returned

---

## Configuration

### Adjusting Grace Period
Default is 5 days, but can be adjusted:

```sql
-- For specific appointment
UPDATE appointments 
SET PaymentGracePeriodDays = 7  -- extend to 7 days
WHERE AppointmentID = 123;

-- This will recalculate PaymentDueDate
```

### Manual Due Date Override
```sql
-- Extend deadline manually
UPDATE appointments 
SET PaymentDueDate = '2026-02-20 23:59:59'
WHERE AppointmentID = 123;
```

---

## Reporting Queries

### Find Overdue Payments
```sql
SELECT 
    a.AppointmentID,
    a.OrderID,
    a.ClientName,
    a.InstallationCompletedDate,
    a.PaymentDueDate,
    DATEDIFF(NOW(), a.PaymentDueDate) AS DaysOverdue,
    o.OrderNumber
FROM appointments a
JOIN `order` o ON a.OrderID = o.OrderID
WHERE a.Status = 'Installed'
  AND a.PaymentDueDate < NOW()
  AND a.AppointmentType = 'Installation'
ORDER BY DaysOverdue DESC;
```

### Upcoming Payment Deadlines (Next 48 hours)
```sql
SELECT 
    a.AppointmentID,
    a.OrderID,
    a.ClientName,
    a.PaymentDueDate,
    TIMESTAMPDIFF(HOUR, NOW(), a.PaymentDueDate) AS HoursRemaining
FROM appointments a
WHERE a.Status = 'Installed'
  AND a.PaymentDueDate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)
  AND a.AppointmentType = 'Installation'
ORDER BY PaymentDueDate ASC;
```

---

## Next Steps

1. **Review this workflow** - Make sure it matches your business process
2. **Run database migration** - Execute the SQL file
3. **Test with sample data** - Create test appointments
4. **Update UI** - I'll help you modify the admin appointment page
5. **Train staff** - Explain the new status meanings
6. **Monitor** - Watch first few installations carefully

Let me know if you want me to proceed with updating the UI code!
