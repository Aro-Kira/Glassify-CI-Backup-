# Panelist Feedback Implementation Plan
## Glassify CI - Task Distribution for 4 Fullstack Developers

**Project Manager:** [Your Name]  
**Date Created:** [Current Date]  
**Status:** Planning Phase

---

## Overview

This document organizes panelist feedback into clear, non-overlapping tasks distributed across 4 fullstack developers. Each developer owns specific modules/pages to minimize merge conflicts and confusion.

---

## Developer Domain Ownership

### **Developer 1: Customer-Facing Frontend & User Management**
**Primary Domain:** Customer registration, contact forms, UI consistency, user data validation

**Files Owned:**
- `application/controllers/Auth.php` (registration, login)
- `application/controllers/Pages.php` (contact us)
- `application/views/auth/register.php`
- `application/views/pages/contact.php`
- `application/views/includes/` (header, footer - UI consistency)
- `assets/css/general-customer/auth/`
- `assets/css/general-customer/pages/`
- Customer-facing CSS files (UI consistency tasks)

**DO NOT TOUCH:** Order processing, customization, shipping, calendar, admin/sales modules

---

### **Developer 2: Order Management & Workflow**
**Primary Domain:** Order processing, approval/disapproval workflows, admin order management, table relationships

**Files Owned:**
- `application/controllers/AdminCon.php` (order approval/disapproval methods only)
- `application/controllers/SalesCon.php` (order approval methods)
- `application/controllers/ShopCon.php` (checkout, order creation)
- `application/models/Order_model.php`
- `application/views/shop/` (checkout, order_complete)
- `application/views/admin_page/admin_order.php`
- `application/views/sales_page/sales_order.php`
- `assets/js/admin-js/order.js`
- `assets/js/sales-js/sales-order-approve-handler.js`
- `assets/js/shop/` (checkout-related JS)

**DO NOT TOUCH:** Customer registration, customization logic, shipping calculations, calendar/appointments, UI styling (focus on functionality)

---

### **Developer 3: Customization & Fabrication**
**Primary Domain:** Product customization, fabrication specifications, customization visibility throughout process

**Files Owned:**
- `application/controllers/CustomizationCon.php`
- `application/models/Customization_model.php`
- `application/controllers/ShopCon.php` (customization selection during order)
- `application/views/shop/` (customization pages)
- `application/views/product/` (customization forms)
- `assets/js/2d-functions/` (all 2D customization JS)
- `assets/css/` (customization-related CSS only)
- Customization display in order views (visible in process)

**DO NOT TOUCH:** Order approval workflows, shipping, calendar, registration, general UI consistency

---

### **Developer 4: Shipping, Calendar & Installation**
**Primary Domain:** Shipping calculations, calendar/appointments, ocular visits, installation scheduling

**Files Owned:**
- `application/controllers/AdminCon.php` (appointment/calendar methods only)
- Shipping calculation models/controllers (to be created/identified)
- `application/views/admin_page/admin_appointment.php`
- `application/models/Appointment_model.php` (if exists)
- `assets/js/calendar.js`
- Shipping-related views and forms
- Calendar and installation scheduling features

**DO NOT TOUCH:** Order approval logic, customization details, registration, general UI consistency

---

## Task Breakdown by Panelist Comments

### **Renan Bacit's Comments**

#### ✅ Already Completed:
- Contact Us fields and Registration module improve UI (Dev 1 - DONE)
- Add email validation (Dev 1 - DONE)
- Payment confirmation first before request for approval (Dev 2 - DONE)
- Business rules applied in ordering (Dev 2 - DONE)
- Alert boxes in final confirmation pages (Dev 2 - DONE)

#### 📋 Remaining Tasks:

**Task R-1: Replace Test Data with Realistic Data**
- **Assigned to:** All Developers (coordinated effort)
- **Developer 1:** Replace test data in user registration examples, contact form samples
- **Developer 2:** Replace test order data, realistic customer names in orders, realistic order amounts
- **Developer 3:** Replace test customization data, realistic fabrication specs, product examples
- **Developer 4:** Replace test appointment data, realistic installation dates, shipping addresses
- **Priority:** High
- **Timeline:** Week 1-2

**Task R-2: Admin Module - Modify/Update Order Details**
- **Assigned to:** Developer 2 (Primary), Developer 4 (Ocular visit integration)
- **Scope:** 
  - Developer 2: Create admin interface to modify order details (amounts, status, customer info, payment info)
  - Developer 4: Integrate ocular visit module to modify orders (when ocular visit is scheduled)
- **Files to Modify:**
  - `application/controllers/AdminCon.php` (new method: `update_order_details()`)
  - `application/models/Order_model.php` (new method: `update_order()`)
  - `application/views/admin_page/admin_order.php` (edit order form/modal)
- **Priority:** High
- **Timeline:** Week 2-3

---

### **Melvin S. Ferrer's Comments**

#### ✅ Already Completed:
- Customer name is missing (Dev 1 - DONE)

#### 📋 Remaining Tasks:

**Task M-1: Expand Customization with Detailed Fabrication Specifications**
- **Assigned to:** Developer 3
- **Scope:** 
  - Add detailed fabrication options beyond basic customization
  - Include material specifications, glass treatments, edge details, frame specifications
  - Create fabrication data model/table if needed
- **Files to Modify:**
  - `application/models/Customization_model.php`
  - `application/views/product/` (customization forms)
  - `assets/js/2d-functions/2d_customization.js`
  - Database: `customization` table (may need new fields)
- **Priority:** High
- **Timeline:** Week 2-3

**Task M-2: Customization of Fabrication Data Selection Based on Product Order**
- **Assigned to:** Developer 3
- **Scope:** 
  - Make fabrication options dynamic based on product type
  - Different products show different customization options
  - Link fabrication specs to specific product categories
- **Files to Modify:**
  - `application/controllers/CustomizationCon.php`
  - `application/models/Customization_model.php`
  - Product customization views
- **Priority:** High
- **Timeline:** Week 3-4

**Task M-3: Shipping Fee Based on Distance and Weight Handling Fee**
- **Assigned to:** Developer 4
- **Scope:** 
  - Implement shipping fee calculation algorithm
  - Factor in distance (from warehouse to delivery address)
  - Factor in weight/package size
  - Include handling fee in calculation
  - Store calculated shipping fee in order
- **Files to Create/Modify:**
  - New: `application/models/Shipping_model.php`
  - `application/controllers/ShopCon.php` (checkout - calculate shipping)
  - `application/views/shop/checkout.php` (display shipping fee)
  - Database: May need `shipping_zones` or `shipping_rates` table
- **Priority:** High
- **Timeline:** Week 2-3

**Task M-4: Module for Ocular Visit to Modify the Order**
- **Assigned to:** Developer 4
- **Scope:** 
  - Create ocular visit appointment scheduling
  - Allow modification of order details during/after ocular visit
  - Link ocular visit to order modification workflow
  - Integration with Task R-2 (admin order modification)
- **Files to Create/Modify:**
  - `application/controllers/AdminCon.php` (new: `schedule_ocular_visit()`, `update_order_from_ocular_visit()`)
  - `application/views/admin_page/` (ocular visit form/scheduling)
  - `application/models/Appointment_model.php` (if exists) or create new
  - Integration with `Order_model.php` for order updates
- **Priority:** Medium-High
- **Timeline:** Week 3-4

---

### **Mikee Gonzaga's Comments**

#### 📋 Tasks:

**Task MG-1: Clarify the Process**
- **Assigned to:** Developer 3 (Document customization process), Developer 2 (Document order workflow)
- **Scope:** 
  - Developer 3: Create visual flow showing customization → cart → checkout process
  - Developer 2: Create visual flow showing order → approval → fabrication → installation
  - Add process indicators/steppers in UI
- **Deliverable:** Process documentation + UI indicators
- **Priority:** Medium
- **Timeline:** Week 2-3

**Task MG-2: Customization Not Seen on the Process**
- **Assigned to:** Developer 3
- **Scope:** 
  - Display customization details in order confirmation page
  - Show customization in order tracking/status pages
  - Display customization in admin/sales order views
  - Ensure customization is visible throughout entire order process
- **Files to Modify:**
  - `application/views/shop/order_complete.php`
  - `application/views/admin_page/admin_order.php`
  - `application/views/sales_page/sales_order.php`
  - Order tracking views
- **Priority:** High
- **Timeline:** Week 2-3

**Task MG-3: Real Data Needed**
- **Assigned to:** All Developers (Same as Task R-1)
- **Priority:** High
- **Timeline:** Week 1-2

**Task MG-4: UI (Gradient) Not a Big Fan**
- **Assigned to:** Developer 1 (Primary), All Developers (their respective pages)
- **Scope:** 
  - Remove gradients from UI design
  - Implement minimal, consistent design system
  - Update color scheme to flat design
  - Create style guide for team
- **Files to Modify:**
  - All CSS files (coordinated by Dev 1)
  - `assets/css/` (all subdirectories)
- **Priority:** Medium
- **Timeline:** Week 3-4

**Task MG-5: Minimal Process**
- **Assigned to:** All Developers (UX simplification)
- **Scope:** 
  - Simplify workflows where possible
  - Reduce unnecessary steps
  - Streamline user interactions
- **Priority:** Low-Medium
- **Timeline:** Ongoing

---

### **Hermininio Lagunzad's Comments**

#### ✅ Already Completed:
- Check approve/disapprove area for validation and checking (Dev 2 - DONE)

#### 📋 Remaining Tasks:

**Task H-1: Validate Shipping Fee for 25 Pesos Only on NCR**
- **Assigned to:** Developer 4
- **Scope:** 
  - Implement region-based shipping fee validation
  - NCR (National Capital Region) should have base fee of ₱25
  - Other regions have different rates (based on distance/weight)
  - Validate shipping fee calculation against business rules
- **Files to Modify:**
  - `application/models/Shipping_model.php` (create)
  - Shipping calculation logic
  - Validation in checkout process
- **Priority:** High
- **Timeline:** Week 2-3

**Task H-2: Check Table Connection - Order ID and Customer Name**
- **Assigned to:** Developer 2
- **Scope:** 
  - Verify foreign key relationships between `order`, `customer`, and `appointments` tables
  - Ensure OrderID is properly linked to customer
  - Ensure Customer Name is correctly displayed from customer table
  - Fix any broken relationships or queries
  - Validate data integrity
- **Files to Review/Modify:**
  - `application/models/Order_model.php`
  - `application/models/Appointment_model.php` (if exists)
  - Database schema verification
  - Queries that join order and customer tables
- **Priority:** High
- **Timeline:** Week 1-2

**Task H-3: Validate All Processes and Incorporate to System**
- **Assigned to:** All Developers (coordination required)
- **Scope:** 
  - Comprehensive testing of all workflows
  - End-to-end validation of order process
  - Validation of customization → order → approval → fabrication → installation flow
  - Document and fix any gaps
- **Priority:** High
- **Timeline:** Week 4-5 (final validation phase)

**Task H-4: Selection Date Installation and Time Frame Visible in Calendar**
- **Assigned to:** Developer 4
- **Scope:** 
  - Add installation date selection in checkout/order process
  - Add time frame selection (morning, afternoon, evening) or specific time slots
  - Display installation dates and time frames in admin calendar
  - Show installation appointments with time frames
- **Files to Modify:**
  - `application/views/shop/checkout.php` (installation date/time selection)
  - `application/views/admin_page/admin_appointment.php` (calendar display)
  - `assets/js/calendar.js` (time frame display)
  - `application/models/Appointment_model.php` (store time frames)
  - Database: `appointments` table (may need `TimeFrame` field)
- **Priority:** High
- **Timeline:** Week 3-4

---

## Task Summary by Developer

### **Developer 1: Customer-Facing Frontend & User Management**
1. ✅ Contact Us fields and Registration module improve UI (DONE)
2. ✅ Add email validation (DONE)
3. 📋 Task R-1: Replace test data in registration/contact (Week 1-2)
4. 📋 Task MG-4: Remove gradients, implement minimal UI design (Week 3-4)

**Total Estimated Work:** 1-2 weeks

---

### **Developer 2: Order Management & Workflow**
1. ✅ Payment confirmation before approval request (DONE)
2. ✅ Business rules in ordering (DONE)
3. ✅ Alert boxes in final confirmation (DONE)
4. ✅ Approve/disapprove validation (DONE)
5. 📋 Task R-1: Replace test order data (Week 1-2)
6. 📋 Task R-2: Admin module to modify/update order details (Week 2-3)
7. 📋 Task H-2: Check table connections (Order ID, Customer Name) (Week 1-2)
8. 📋 Task H-3: Validate all processes (Week 4-5)
9. 📋 Task MG-5: Simplify order process (Ongoing)

**Total Estimated Work:** 3-4 weeks

---

### **Developer 3: Customization & Fabrication**
1. 📋 Task M-1: Expand customization with detailed fabrication specs (Week 2-3)
2. 📋 Task M-2: Fabrication data selection based on product (Week 3-4)
3. 📋 Task MG-1: Clarify customization process (Week 2-3)
4. 📋 Task MG-2: Make customization visible throughout process (Week 2-3)
5. 📋 Task R-1: Replace test customization data (Week 1-2)
6. 📋 Task H-3: Validate customization → order flow (Week 4-5)

**Total Estimated Work:** 3-4 weeks

---

### **Developer 4: Shipping, Calendar & Installation**
1. 📋 Task M-3: Shipping fee based on distance and weight (Week 2-3)
2. 📋 Task M-4: Ocular visit module to modify order (Week 3-4)
3. 📋 Task H-1: Validate NCR shipping fee (₱25) and region-based rates (Week 2-3)
4. 📋 Task H-4: Installation date and time frame selection in calendar (Week 3-4)
5. 📋 Task R-1: Replace test appointment/shipping data (Week 1-2)
6. 📋 Task R-2: Integrate ocular visit with order modification (Week 3-4)
7. 📋 Task H-3: Validate installation workflow (Week 4-5)

**Total Estimated Work:** 3-4 weeks

---

## Timeline Overview

### **Week 1-2: Foundation & Data Cleanup**
- All: Replace test data with realistic data
- Dev 2: Fix table connections (Order ID, Customer Name)
- Dev 4: Start shipping fee calculation structure

### **Week 2-3: Core Feature Development**
- Dev 2: Admin order modification module
- Dev 3: Detailed fabrication specifications
- Dev 3: Customization visibility in process
- Dev 4: Shipping fee calculation (distance + weight)
- Dev 4: NCR validation (₱25 base fee)
- Dev 1: UI consistency (remove gradients)

### **Week 3-4: Advanced Features**
- Dev 3: Product-based fabrication selection
- Dev 3: Process clarification (documentation + UI)
- Dev 4: Ocular visit module
- Dev 4: Installation date/time frame in calendar
- Dev 4: Calendar enhancement (show time frames)

### **Week 4-5: Validation & Testing**
- All: End-to-end process validation
- All: Integration testing
- Bug fixes and refinements

---

## Conflict Prevention Guidelines

### **File Ownership Rules:**
1. **DO NOT** modify files outside your domain without consulting the owner developer
2. **DO** communicate changes that might affect other domains (e.g., adding fields to shared tables)
3. **DO** use feature branches for all work: `dev1/ui-consistency`, `dev2/order-modification`, etc.
4. **DO** create pull requests and tag relevant developers for review

### **Shared Files/Areas (Require Coordination):**
- Database schema changes (all developers must coordinate)
- `application/views/includes/header.php` and `footer.php` (Dev 1 owns, but others may need to add content)
- `application/config/` files (coordinate changes)
- `.gitignore`, `composer.json`, etc. (coordinate changes)

### **Integration Points:**
- **Dev 2 ↔ Dev 4:** Order modification and ocular visit integration (Week 3-4)
- **Dev 3 ↔ Dev 2:** Customization display in order views (Week 2-3)
- **Dev 4 ↔ Dev 2:** Appointment/order synchronization (ongoing)

---

## Database Changes Coordination

### **Potential Schema Changes:**
1. **`customization` table** (Dev 3): May need additional fields for detailed fabrication specs
2. **`order` table** (Dev 2): May need fields for admin modifications audit
3. **`appointments` table** (Dev 4): May need `TimeFrame` field for installation time slots
4. **New `shipping_zones` or `shipping_rates` table** (Dev 4): For shipping calculations
5. **New `ocular_visit` table or fields in `appointments`** (Dev 4): For ocular visit tracking

**Action Required:** Before making database changes, create a shared document listing proposed changes for team review.

---

## Testing Checklist (Week 4-5)

### **Developer 2:**
- [ ] Order creation → approval → disapproval workflow
- [ ] Admin order modification works correctly
- [ ] Table connections (Order ID ↔ Customer Name) verified
- [ ] Payment confirmation before approval enforced

### **Developer 3:**
- [ ] Customization visible in order confirmation
- [ ] Customization visible in order tracking
- [ ] Customization visible in admin/sales views
- [ ] Product-based fabrication selection works
- [ ] Detailed fabrication specs saved and displayed

### **Developer 4:**
- [ ] Shipping fee calculates correctly for NCR (₱25)
- [ ] Shipping fee calculates for other regions (distance + weight)
- [ ] Ocular visit scheduling works
- [ ] Ocular visit can modify orders
- [ ] Installation dates appear in calendar
- [ ] Time frames display in calendar

### **All Developers:**
- [ ] No test data remains in system
- [ ] UI is consistent (no gradients)
- [ ] End-to-end process works (customization → order → approval → installation)

---

## Notes for Project Manager

1. **Daily Standups:** Focus on blockers and integration points
2. **Code Reviews:** Ensure developers review each other's PRs, especially for integration points
3. **Database Migrations:** Use version control for SQL changes (create migration files)
4. **Feature Flags:** Consider feature flags for new features to enable gradual rollout
5. **Testing:** Schedule integration testing sessions in Week 4-5
6. **Documentation:** Ensure each developer documents their changes

---

## Questions & Clarifications Needed

1. **Shipping Zones:** What are the exact shipping zones/regions and their base rates?
2. **Fabrication Specs:** What are the specific fabrication details needed for each product category?
3. **Time Frames:** What are the available time frame options for installation? (e.g., 9-12 AM, 1-4 PM, 5-8 PM)
4. **Ocular Visit Workflow:** Can orders be modified multiple times, or only once after ocular visit?
5. **Admin Permissions:** What order fields can admin modify? (All fields? Specific fields only?)

---

**Document Version:** 1.0  
**Last Updated:** [Current Date]  
**Next Review:** End of Week 1


