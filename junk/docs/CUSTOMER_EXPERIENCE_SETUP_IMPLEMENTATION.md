# Set Up Your Experience (Customer Onboarding) - Implementation Documentation

## Overview
This document describes the implementation of the "Set Up Your Experience" feature for customer onboarding in the Glassify-CI system. This feature helps classify customers as Beginners or Professionals and tailors their experience accordingly.

## Implementation Date
February 4, 2026

---

## 1. Database Changes

### Migration File
**Location:** `database/migrations/add_customer_experience_fields.sql`

### Schema Changes
Added to `customer` table:
```sql
- role (ENUM: 'beginner', 'professional')
- setup_status (ENUM: 'pending', 'completed')
- experience_data (JSON - stores all questionnaire answers)
- Date_Updated (TIMESTAMP)
- Indexes on role and setup_status
```

### How to Apply Migration
```bash
# Option 1: Direct SQL execution
mysql -u your_username -p your_database < database/migrations/add_customer_experience_fields.sql

# Option 2: Via phpMyAdmin
# Import the SQL file through phpMyAdmin interface
```

---

## 2. Question Flow Structure

### Step 1: Role Selection (Common for All)
**Question:** What best describes your role?

**Options:**
- Beginner (no technical background in measurements or specifications)
- Professional
- Architect
- Engineer  
- Contractor
- Other (with text input)

**Logic:**
- Selecting "Beginner" → Shows 3 beginner-specific questions
- Selecting Professional/Architect/Engineer/Contractor/Other → Shows 3 professional-specific questions

---

### Beginner Questions (Steps 2-4)

#### Question 2
**Question:** Have you ever ordered a product that required specifications?

**Options:**
- No, this is my first time
- Yes, once or twice
- Yes, several times

#### Question 3
**Question:** Are you familiar with reading or providing product specifications (sizes, profiles, materials)?

**Options:**
- Not at all
- A little
- Yes, but I still need guidance

#### Question 4
**Question:** How would you like your product customization to be handled after the ocular visit?

**Options:**
- I prefer GlassWorth Builders to prepare the customization for me
- I want to review and approve the customization prepared for me

**Note:** Beginner users CANNOT create customization themselves. This affects review/approval flow only.

---

### Professional Questions (Steps 2-4)

#### Question 2
**Question:** Have you previously worked with products that required detailed specifications?

**Options:**
- Yes, regularly
- Yes, occasionally
- No, but I understand technical drawings

#### Question 3
**Question:** How do you usually prepare product specifications?

**Options:**
- I prepare measurements and specifications myself
- I collaborate after a site assessment
- I adjust specifications provided by suppliers

#### Question 4
**Question:** How comfortable are you with customizing products using a 2D configuration tool?

**Options:**
- Very comfortable
- Somewhat comfortable
- I prefer minimal adjustments

---

## 3. File Changes

### New/Modified Files

#### Controllers
1. **Auth.php** - Updated `save_experience_setup()` method
   - Handles new question structure
   - Determines role category (beginner vs professional)
   - Stores professional type for professional roles
   - Location: `application/controllers/Auth.php` (lines 1081-1133)

2. **UserCon.php** - Modified Methods:
   - `profile()` - Loads customer role and experience data
   - `update_experience()` - Handles account settings updates
   - Location: `application/controllers/UserCon.php`

3. **ShopCon.php** - Updated Methods:
   - `product_2d()` - Added setup status and role checks
   - `booking()` - Pass customer role to view
   - Location: `application/controllers/ShopCon.php`

#### Views
1. **setup_experience.php** - Complete overhaul
   - Updated question flow (1 role question + 3 role-specific questions)
   - New JavaScript for step management
   - Session storage for progress preservation
   - Location: `application/views/auth/setup_experience.php`

2. **profile.php** - User Experience Tab
   - Display all answers in account settings
   - Allow editing of responses
   - Professional type shown as read-only
   - Location: `application/views/user/profile.php` (lines 488-611)

3. **2DModeling.php** - Access Control
   - Setup incomplete notice
   - Beginner role blocking notice
   - Redirect to setup or booking
   - Location: `application/views/shop/2DModeling.php`

4. **booking.php** - Role-based behavior
   - Customer role passed to view for conditional rendering
   - Location: `application/views/shop/booking.php`

---

## 4. Navigation Rules

### Setup Experience Page
- **Trigger:** First login after email verification
- **URL:** `/auth/setup_experience`
- **Rules:**
  - One question per step
  - Total 4 steps (1 role + 3 questions)
  - Next button disabled until selection made
  - Back button allowed
  - Complete Setup button on final step only
  - No Cancel button
  - Progress saved in sessionStorage

### Already Completed Setup
- If user already completed setup, they're redirected to home with flash message
- Can edit settings in Account Settings → User Experience tab

---

## 5. Access Control Logic

### 2D Modeling Page Access

#### Not Logged In (Guest)
- **Block:** Yes
- **Message:** "Sign in Required"
- **Action:** Redirect to login

#### Setup Status = Pending
- **Block:** Yes
- **Message:** "Setup Required - Please complete 'Set Up Your Experience'"
- **Action:** Button to `/auth/setup_experience`

#### Role = Beginner
- **Block:** Yes  
- **Message:** "Customization Not Available - As a beginner user, you cannot access 2D customization controls. Customization will be prepared after your ocular visit."
- **Action:** Button to "Book Ocular Visit"

#### Role = Professional
- **Block:** No
- **Access:** Full 2D customization controls enabled

---

## 6. Account Settings - User Experience Tab

### Location
Account Settings → Addresses → **User Experience**

### Fields Displayed

#### For All Roles
- Role (Beginner / Professional) - Editable radio buttons
- All questionnaire answers - Editable

#### Beginner Specific
- Experience level answers
- Specifications knowledge
- Customization handling preference
- **Read-only notice:** "Product customization will be prepared after your ocular visit."

#### Professional Specific
- Professional type (Architect/Engineer/Contractor/Other) - **Read-only, preserved from setup**
- Previous experience with specifications
- Specification preparation method
- 2D tool comfort level

### Behavior
- Fields are pre-filled from database
- User can edit and save changes
- Changes immediately affect access rules
- Validation before saving

---

## 7. Data Storage Structure

### Database Field: experience_data (JSON)

#### Beginner Format
```json
{
  "role": "beginner",
  "experience": "first_time|once_twice|several_times",
  "specifications_knowledge": "not_at_all|a_little|yes_need_guidance",
  "customization_handling": "prepare_for_me|review_and_approve"
}
```

#### Professional Format
```json
{
  "role": "professional",
  "professional_type": "architect|engineer|contractor|<custom text>",
  "previous_experience": "yes_regularly|yes_occasionally|no_understand_drawings",
  "specification_preparation": "prepare_myself|collaborate_after_assessment|adjust_supplier_specs",
  "2d_tool_comfort": "very_comfortable|somewhat_comfortable|prefer_minimal"
}
```

---

## 8. Testing Checklist

### Initial Setup Flow
- [ ] New user registration redirects to setup after email verification
- [ ] Step navigation works correctly (Next/Back buttons)
- [ ] Next button disabled until selection
- [ ] Complete Setup only on final step
- [ ] Progress saved in session storage
- [ ] Can resume from saved step on page refresh
- [ ] Session cleared on successful submission

### Role-Specific Questions
- [ ] Selecting Beginner shows beginner questions
- [ ] Selecting Professional/Architect/Engineer/Contractor shows professional questions
- [ ] Selecting Other shows text input field
- [ ] Text input required when Other selected

### Account Settings
- [ ] User Experience tab loads correctly
- [ ] Pre-filled data matches database
- [ ] Can toggle between Beginner/Professional
- [ ] Professional type preserved and shown as read-only
- [ ] Save functionality works
- [ ] Page reloads after successful save

### 2D Modeling Access Control
- [ ] Guest users see sign-in notice
- [ ] Setup incomplete shows setup required notice
- [ ] Beginner role shows blocked notice with booking button
- [ ] Professional role has full access

### Booking Page
- [ ] Customer role data passed correctly
- [ ] Page loads for both beginners and professionals
- [ ] No errors in console

---

## 9. API Endpoints

### POST `/auth/save_experience_setup`
**Purpose:** Save initial setup responses

**Request Body:**
- `role`: beginner|professional|architect|engineer|contractor|other
- `role_other_text`: (if role = other)
- Beginner fields OR Professional fields based on role

**Response:** Redirect to home with success flash message

### POST `/UserCon/update_experience`
**Purpose:** Update experience settings from account page

**Request Body:** Same as save_experience_setup

**Response:**
```json
{
  "status": "success|error",
  "message": "Your experience settings have been updated successfully."
}
```

---

## 10. Future Enhancements

### Potential Features
1. **Analytics Dashboard**
   - Track beginner vs professional distribution
   - Analyze answer patterns
   - Identify common pain points

2. **Smart Recommendations**
   - Product suggestions based on experience level
   - Tutorial content for beginners
   - Advanced features for professionals

3. **Progress Tracking**
   - Beginner skill progression
   - Professional certification levels

4. **Admin Override**
   - Allow admin to manually adjust user roles
   - Bulk role updates

---

## 11. Troubleshooting

### Common Issues

#### Setup doesn't redirect after login
**Check:**
- Verify `setup_status` in customer table
- Check session data for customer_id
- Review Auth controller redirect logic

#### Questions not showing correctly
**Check:**
- JavaScript console for errors
- Verify role value being set
- Check CSS display properties

#### Data not saving
**Check:**
- Database connection
- JSON encoding of experience_data
- Form field names match controller expectations

#### Access control not working
**Check:**
- Customer role value in database
- View receiving correct data from controller
- Conditional logic in view file

---

## 12. Code Maintenance Notes

### Key Functions

#### `save_experience_setup()` (Auth.php)
- Handles initial setup submission
- Maps role selection to category (beginner/professional)
- Stores professional type

#### `update_experience()` (UserCon.php)
- Handles updates from account settings
- Preserves professional_type
- Validates setup completion before allowing updates

#### JavaScript Step Management (setup_experience.php)
- `goToStep()` - Navigates between steps
- `isCurrentStepValid()` - Validates current selection
- `saveProgress()` - Saves to sessionStorage
- `restoreProgress()` - Loads from sessionStorage

### Important Variables
- `$customer_role` - beginner|professional
- `$setup_status` - pending|completed
- `$experience_data` - JSON object with answers

---

## 13. Security Considerations

1. **Session Management**
   - Setup progress stored in sessionStorage (client-side)
   - Final data saved to database (server-side)
   - Session cleared after completion

2. **Input Validation**
   - Role values validated against enum
   - Text inputs sanitized
   - Required fields enforced

3. **Access Control**
   - Login required for setup
   - Customer role verified
   - Setup status checked before allowing updates

---

## 14. Contact & Support

For questions or issues related to this implementation:
- Review this documentation
- Check code comments in modified files
- Test with sample data
- Verify database schema matches migration

---

**End of Documentation**
