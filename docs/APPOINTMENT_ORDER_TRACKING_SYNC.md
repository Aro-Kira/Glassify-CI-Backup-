# Real-Time Appointment to Order Tracking Sync

## Overview

This document describes the implementation of real-time synchronization between the Admin Appointment Management system and the Customer Order Tracking page. When appointments are updated in the admin panel, the changes are immediately reflected in the customer's order tracking view.

---

## Implementation Summary

### 1. **Appointment Update Sync** (`AdminCon->update_appointment_ajax()`)

**Location**: `application/controllers/AdminCon.php`

**Changes**:
- When an appointment is updated, the system now automatically syncs changes to the `order` table
- Updates order dates (`OcularDate`, `FabricationDate`, `InstallationDate`, `EstimatedDelivery`) based on appointment service type
- Automatically updates order status when appointments are marked as "Complete":
  - **Ocular Visit Complete** → Order status changes from `Approved` to `In Fabrication`
  - **In Fabrication Complete** → Order status changes to `Ready for Installation`
  - **Installed Complete** → Order status changes to `Completed`
  - **Completed Service Complete** → Order status set to `Completed`

**Transaction Safety**: All updates are wrapped in database transactions to ensure data consistency.

---

### 2. **Enhanced Order Progress Calculation** (`Order_model->get_order_progress()`)

**Location**: `application/models/Order_model.php`

**Changes**:
- Now checks the `appointments` table in real-time for actual completion status
- Progress steps are determined by checking appointment status, not just order status
- Checks all appointment stages:
  - **Ocular Visit**: Checks for appointment with `Service = 'Ocular Visit'` and `Status = 'Complete'`
  - **In Fabrication**: Checks for appointment with `Service = 'In Fabrication'` and `Status = 'Complete'`
  - **Installed**: Checks for appointment with `Service = 'Installed'` and `Status = 'Complete'`
  - **Completed**: Checks for appointment with `Service = 'Completed'` and `Status = 'Complete'`

**Benefits**:
- Real-time accuracy: Progress reflects actual appointment completion, not just order status
- Granular control: Each stage can be marked complete independently
- Backward compatible: Falls back to status-based logic if appointments not checked

---

### 3. **Order Tracking Details Enhancement** (`Order_model->get_order_tracking_details()`)

**Location**: `application/models/Order_model.php`

**Changes**:
- Now uses actual appointment dates when available
- Overrides calculated dates with real appointment dates from the `appointments` table
- Falls back to calculated dates if appointments don't exist

**Date Priority**:
1. Actual appointment date from `appointments` table
2. Date from `order` table (if set)
3. Calculated date based on `OrderDate` (fallback)

---

### 4. **Real-Time Progress AJAX Endpoint** (`ShopCon->get_order_progress_ajax()`)

**Location**: `application/controllers/ShopCon.php`

**New Endpoint**: `GET /ShopCon/get_order_progress_ajax?order_id={order_id}`

**Returns**:
```json
{
    "success": true,
    "order_status": "Approved",
    "progress": {
        "order_placed": true,
        "ocular_visit": false,
        "in_fabrication": false,
        "installed": false,
        "completed": false
    },
    "progress_percent": 0,
    "dates": {
        "ocular_date": "Dec 10, 2025",
        "fabrication_date": "Dec 14, 2025",
        "installation_date": "Dec 21, 2025",
        "estimated_delivery": "Dec 28, 2025"
    },
    "order_date": "Dec 7, 2025",
    "order_time": "5:55 PM"
}
```

**Purpose**: Provides real-time order progress data for JavaScript polling

---

### 5. **Real-Time Polling JavaScript** (`order_tracking.php`)

**Location**: `application/views/shop/order_tracking.php`

**Features**:
- **Automatic Polling**: Polls the server every 10 seconds for updates
- **Page Visibility API**: Pauses polling when tab is hidden to save resources
- **Dynamic UI Updates**: Updates progress bar, step icons, and dates in real-time
- **Error Handling**: Gracefully handles network errors without disrupting user experience

**How It Works**:
1. On page load, starts polling every 10 seconds
2. Fetches latest progress from `get_order_progress_ajax` endpoint
3. Updates UI elements:
   - Progress bar width (CSS variable `--progress-width`)
   - Step completion status (adds/removes `completed` class)
   - Check icons (shows/hides checkmark)
   - Date displays (updates expected/completed dates)
4. Pauses when tab is hidden, resumes when visible

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    REAL-TIME SYNC FLOW                            │
└─────────────────────────────────────────────────────────────────┘

[ADMIN] Updates Appointment
    │
    ├─► AdminCon->update_appointment_ajax()
    │   ├─► Updates appointments table
    │   ├─► Syncs to order table (dates + status)
    │   └─► Transaction commit
    │
    ▼
[SYSTEM] Appointment Updated
    │
    ├─► Order table dates updated
    ├─► Order status updated (if appointment complete)
    └─► Appointments table status updated
    │
    ▼
[CUSTOMER] Views Order Tracking
    │
    ├─► Page loads with initial data
    ├─► JavaScript starts polling (every 10s)
    │   └─► ShopCon->get_order_progress_ajax()
    │       └─► Order_model->get_order_progress()
    │           └─► Checks appointments table (real-time)
    │
    ├─► UI updates automatically
    │   ├─► Progress bar width
    │   ├─► Step completion icons
    │   └─► Date displays
    │
    └─► Customer sees changes within 10 seconds
```

---

## Database Schema

### `appointments` Table
- `AppointmentID` (PK)
- `OrderID` (FK to `order`)
- `Service` (enum: 'Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed')
- `Status` (enum: 'In Progress', 'Complete', 'Cancelled')
- `AppointmentDate` (date)
- `AppointmentTime` (time)
- `AssignedStaff` (varchar)
- `Notes` (text)

### `order` Table (Relevant Fields)
- `OrderID` (PK)
- `Status` (enum)
- `OcularDate` (date) - Synced from appointments
- `FabricationDate` (date) - Synced from appointments
- `InstallationDate` (date) - Synced from appointments
- `EstimatedDelivery` (date) - Synced from appointments

---

## Status Mapping

### Appointment Service → Order Status Updates

| Appointment Service | Status = 'Complete' | Order Status Change |
|---------------------|-------------------|---------------------|
| Ocular Visit | Complete | `Approved` → `In Fabrication` |
| In Fabrication | Complete | → `Ready for Installation` |
| Installed | Complete | → `Completed` |
| Completed | Complete | → `Completed` |

### Appointment Service → Order Date Updates

| Appointment Service | Order Date Field Updated |
|---------------------|-------------------------|
| Ocular Visit | `OcularDate` |
| In Fabrication | `FabricationDate` |
| Installed | `InstallationDate` |
| Completed | `EstimatedDelivery` |

---

## Testing Checklist

### Admin Side
- [ ] Update appointment service type → Order date updates
- [ ] Mark appointment as "Complete" → Order status updates
- [ ] Update appointment date → Order date field syncs
- [ ] Transaction rollback on error → No partial updates

### Customer Side
- [ ] Page loads with correct initial progress
- [ ] Polling starts automatically
- [ ] Progress updates within 10 seconds of admin change
- [ ] Progress bar width updates correctly
- [ ] Step icons update (checkmarks appear)
- [ ] Dates update in real-time
- [ ] Polling pauses when tab hidden
- [ ] Polling resumes when tab visible
- [ ] Network errors handled gracefully

### Integration
- [ ] Admin updates appointment → Customer sees change within 10s
- [ ] Multiple customers viewing same order → All see updates
- [ ] Order status changes → Progress steps update correctly
- [ ] Appointment dates sync to order table correctly

---

## Performance Considerations

1. **Polling Interval**: 10 seconds balances responsiveness with server load
2. **Page Visibility API**: Reduces unnecessary requests when tab is hidden
3. **Database Queries**: Optimized with proper indexes on `OrderID` and `Service` in `appointments` table
4. **Transaction Safety**: All updates use transactions to prevent data inconsistency

---

## Future Enhancements

1. **WebSocket Support**: Replace polling with WebSocket for instant updates
2. **Push Notifications**: Notify customers when appointments are scheduled/completed
3. **Email Notifications**: Send email when order status changes
4. **SMS Notifications**: Send SMS for critical status changes
5. **Activity Logging**: Log all appointment-to-order syncs for audit trail

---

## Files Modified

1. `application/controllers/AdminCon.php` - Enhanced `update_appointment_ajax()`
2. `application/models/Order_model.php` - Enhanced `get_order_progress()` and `get_order_tracking_details()`
3. `application/controllers/ShopCon.php` - Added `get_order_progress_ajax()`
4. `application/views/shop/order_tracking.php` - Added real-time polling JavaScript

---

## Files Created

1. `APPOINTMENT_ORDER_TRACKING_SYNC.md` - This documentation

---

**Implementation Date**: 2025-01-08  
**Status**: ✅ Complete  
**Version**: 1.0
