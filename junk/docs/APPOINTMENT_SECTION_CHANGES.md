# Appointment Section Changes Summary

## Overview
Updated the Appointment section on the customer home dashboard to display accurate, dynamic appointment data from the database instead of hardcoded values.

---

## Changes Made

### 1. **View File Changes** (`home-login.php`)

#### Change 1.1: Updated Appointment Data Source
**Location:** Lines 399-447 (Appointment table body)

**What Changed:**
- Switched from using `$orders` array to `$appointments` array
- Removed hardcoded staff name cycling and service type derivation
- Now pulls actual appointment data from database

**Original Code:**
```php
<?php if (isset($orders) && !empty($orders)): ?>
    <?php 
    $appointment_count = 0;
    $total_appointments = count($orders);
    // Staff names for display
    $staff_names = ['Joaquin Santos', 'Engr. Cruz', 'M. Lopez', 'R. Garcia', 'J. Reyes'];
    
    foreach ($orders as $order): 
        $appointment_count++;
        // Determine service type based on status
        $service = 'Consultation';
        $appt_status = 'Pending';
        if ($order->Status == 'Ready for Installation') {
            $service = 'Installation';
            $appt_status = 'Confirmed';
        } elseif ($order->Status == 'In Fabrication') {
            $service = 'Ocular Visit';
            $appt_status = 'Confirmed';
        } // ... more hardcoded logic
        
        // Calculate estimated appointment date
        $base_date = strtotime($order->OrderDate);
        $appointment_date = date('m/d/Y - g:i A', strtotime('+14 days', $base_date));
        
        // Get staff name (cycle through for demo)
        $staff_name = $staff_names[($appointment_count - 1) % count($staff_names)];
```

**New Code:**
```php
<?php if (isset($appointments) && !empty($appointments)): ?>
    <?php 
    $appointment_count = 0;
    
    foreach ($appointments as $appointment): 
        $appointment_count++;
        
        // Get actual appointment data from database
        $order_id = htmlspecialchars($appointment->OrderNumber ?? 'GI' . str_pad($appointment->OrderID, 3, '0', STR_PAD_LEFT));
        $service = htmlspecialchars($appointment->ServiceType ?? 'Consultation');
        $appt_status = htmlspecialchars($appointment->AppointmentStatus ?? 'Pending');
        $staff_name = htmlspecialchars($appointment->AssignedStaff ?? 'TBD');
        
        // Format appointment date and time
        $appt_date = isset($appointment->AppointmentDate) ? $appointment->AppointmentDate : 'TBD';
        $appt_time = isset($appointment->AppointmentTime) ? $appointment->AppointmentTime : '09:00';
        
        if ($appt_date !== 'TBD') {
            $appointment_date = date('m/d/Y - g:i A', strtotime($appt_date . ' ' . $appt_time));
        } else {
            $appointment_date = 'TBD';
        }
```

**Benefits:**
- ✅ Displays real appointment data from database
- ✅ Actual service types instead of assumed values
- ✅ Real appointment dates/times instead of +14 day estimates
- ✅ Actual assigned staff instead of cycling through names

---

#### Change 1.2: Enhanced Status Mapping
**Location:** Lines 415-437 (Status class determination)

**What Changed:**
- Maps database status enum values to CSS classes
- Handles all appointment status values: `In Progress`, `Complete`, `Cancelled`
- Displays user-friendly status text

**Original Code:**
```php
// Determine status class
$status_class = strtolower($appt_status);
if ($appt_status == 'Confirmed') $status_class = 'confirmed';
elseif ($appt_status == 'Pending') $status_class = 'pending';
elseif ($appt_status == 'Cancelled') $status_class = 'cancelled';
elseif ($appt_status == 'Completed') $status_class = 'completed';
```

**New Code:**
```php
// Determine status class - map database values to CSS classes
$status_class = strtolower(trim($appt_status));
if ($appt_status == 'In Progress') {
    $status_class = 'in-progress';
    $display_status = 'In Progress';
} elseif ($appt_status == 'Complete') {
    $status_class = 'completed';
    $display_status = 'Completed';
} elseif ($appt_status == 'Confirmed') {
    $status_class = 'confirmed';
    $display_status = 'Confirmed';
} elseif ($appt_status == 'Pending') {
    $status_class = 'pending';
    $display_status = 'Pending';
} elseif ($appt_status == 'Cancelled') {
    $status_class = 'cancelled';
    $display_status = 'Cancelled';
} else {
    $status_class = 'pending';
    $display_status = $appt_status;
}
```

**Benefits:**
- ✅ Proper CSS class mapping for styling
- ✅ Colored status badges display correctly
- ✅ Human-readable status text in UI
- ✅ Fallback handling for unknown statuses

---

#### Change 1.3: Updated "See More" Button Condition
**Location:** Line 450

**What Changed:**
- Changed from checking `$orders` count to `$appointments` count
- Shows "See more" button only when there are more than 5 appointments

**Original Code:**
```php
<?php if (isset($orders) && count($orders) > 5): ?>
    <div class="see-more-container">
        <a href="#" class="see-more-link" id="appointmentSeeMore">See more <span class="arrow">▼</span></a>
    </div>
<?php endif; ?>
```

**New Code:**
```php
<?php if (isset($appointments) && count($appointments) > 5): ?>
    <div class="see-more-container">
        <a href="#" class="see-more-link" id="appointmentSeeMore">See more <span class="arrow">▼</span></a>
    </div>
<?php endif; ?>
```

---

### 2. **Controller Changes** (`Pages.php`)

#### Change 2.1: Added Appointments Data Retrieval
**Location:** Line 100 (in `home_login()` method)

**What Changed:**
- Added new line to fetch appointments from database
- Passes appointments array to view

**Original Code:**
```php
// Get next appointment (placeholder - using order dates for now)
$data['next_appointment'] = $this->get_next_appointment($customer_id);

$data['title'] = "Glassify - Home";
```

**New Code:**
```php
// Get next appointment (placeholder - using order dates for now)
$data['next_appointment'] = $this->get_next_appointment($customer_id);

// Get appointments with staff names
$data['appointments'] = $this->get_customer_appointments($customer_id);

$data['title'] = "Glassify - Home";
```

---

#### Change 2.2: New Helper Method to Fetch Appointments
**Location:** Lines 368-388 (New method at end of class)

**What Changed:**
- Added new `get_customer_appointments()` private method
- Joins appointments table with orders and user tables
- Returns clean appointment data with staff names

**Code Added:**
```php
/**
 * Helper function to get customer appointments
 * Fetches all appointments for the customer with staff names
 * @param int $customer_id Customer_ID
 */
private function get_customer_appointments($customer_id) {
    $this->load->database();
    
    // Get appointments with joined staff names
    $this->db->select('
        a.AppointmentID,
        a.OrderID,
        o.OrderNumber,
        a.Service as ServiceType,
        a.AppointmentDate,
        a.AppointmentTime,
        a.Status as AppointmentStatus,
        COALESCE(u.First_Name, a.AssignedStaff) as AssignedStaff
    ');
    $this->db->from('appointments a');
    $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
    $this->db->join('user u', 'a.AssignedStaff_ID = u.UserID', 'left');
    $this->db->where('a.Customer_ID', $customer_id);
    $this->db->order_by('a.AppointmentDate', 'DESC');
    $this->db->limit(50);
    
    $result = $this->db->get()->result();
    return !empty($result) ? $result : [];
}
```

**Benefits:**
- ✅ Fetches actual appointments from database
- ✅ Joins with orders table for order numbers
- ✅ Joins with user table for real staff names
- ✅ Falls back to stored staff name if user not found
- ✅ Orders by date descending (most recent first)
- ✅ Returns empty array if no appointments found

---

## Database Schema Used

### Appointments Table Fields:
- `AppointmentID` - Primary key
- `OrderID` - Foreign key to orders
- `Customer_ID` - Foreign key to customer
- `Service` - enum field: 'Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed'
- `AppointmentDate` - Date of appointment
- `AppointmentTime` - Time of appointment
- `AssignedStaff` - Staff name (deprecated)
- `AssignedStaff_ID` - Foreign key to user for actual staff member
- `Status` - enum: 'In Progress', 'Complete', 'Cancelled'

---

## Data Flow

```
Database (appointments table)
    ↓
Controller: get_customer_appointments()
    ↓ [Joins with orders and user tables]
    ↓
View: home-login.php
    ↓ [Maps status values and formats dates]
    ↓
HTML Table with styled badges
```

---

## Visual Changes

### Before:
- Service types: Hardcoded derivations from order status
- Dates: Calculated as OrderDate + 14 days
- Staff: Cycled through predefined list
- Status: Always showed "Pending" or "Confirmed" based on logic

### After:
- Service types: Actual service types from appointments table
- Dates: Real appointment dates and times
- Staff: Actual assigned staff member names (with "TBD" fallback)
- Status: Real appointment status with proper color badges
- Display: Only shows appointments that exist in database

---

## Files Modified

1. **`/application/views/pages/home-login.php`**
   - Lines 399-447: Updated appointment data source and logic
   - Line 450: Updated "See more" button condition

2. **`/application/controllers/Pages.php`**
   - Line 100: Added appointments data retrieval
   - Lines 368-388: Added new `get_customer_appointments()` method

---

## Testing Recommendations

1. Verify appointments display when data exists in database
2. Test "See more" button with >5 appointments
3. Check status badge colors match CSS styles
4. Verify staff names display correctly
5. Test with missing/NULL appointment dates (should show "TBD")
6. Confirm filtering by status works correctly
7. Test with no appointments (should show "No appointments scheduled")

