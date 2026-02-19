# Quick Setup Guide - Customer Experience Feature

## Prerequisites
- Access to database (phpMyAdmin or MySQL client)
- Web server running (XAMPP/WAMP)
- Glassify-CI application installed

## Installation Steps

### Step 1: Apply Database Migration
Run the migration to add required fields to the customer table:

```bash
# Navigate to your database directory
cd c:\xampp6\htdocs\Glassify-CI\database\migrations

# Apply migration using MySQL command line
mysql -u root -p glassify_db < add_customer_experience_fields.sql

# OR use phpMyAdmin:
# 1. Open http://localhost/phpmyadmin
# 2. Select your database (glassify_db)
# 3. Click "Import" tab
# 4. Choose file: add_customer_experience_fields.sql
# 5. Click "Go"
```

### Step 2: Verify Database Changes
Check that the following columns were added to the `customer` table:
- `role` (ENUM)
- `setup_status` (ENUM)
- `experience_data` (JSON)
- `Date_Updated` (TIMESTAMP)

### Step 3: Clear Any Cached Sessions
```php
# Clear browser cookies and sessions
# Or restart your browser
```

### Step 4: Test the Feature

#### Test 1: New User Registration
1. Go to: `http://localhost/Glassify-CI/register`
2. Register a new account
3. Verify email confirmation
4. After login, should redirect to: `/auth/setup_experience`
5. Complete the 4-step questionnaire
6. Verify redirect to home page

#### Test 2: Account Settings
1. Login as the test user
2. Go to: Account Settings → User Experience tab
3. Verify all answers are pre-filled
4. Try changing role from Beginner to Professional
5. Save changes
6. Verify page reloads with updated data

#### Test 3: 2D Modeling Access Control
1. **As Beginner:**
   - Go to any product → 2D customization
   - Should see blocking notice
   - Should offer "Book Ocular Visit" button

2. **As Professional:**
   - Go to any product → 2D customization
   - Should have full access to customization tools

#### Test 4: Setup Incomplete
1. Create a new user via database
2. Set `setup_status` = 'pending'
3. Login as that user
4. Try accessing 2D modeling
5. Should see "Setup Required" notice

## Verification Checklist

- [ ] Database migration applied successfully
- [ ] New user registration flow works
- [ ] Setup questionnaire displays correctly
- [ ] All 4 steps navigate properly
- [ ] Data saves to database
- [ ] Account Settings tab shows correctly
- [ ] Beginner access to 2D modeling blocked
- [ ] Professional access to 2D modeling allowed
- [ ] Setup incomplete notice displays
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs

## Rollback (If Needed)

If you need to undo the changes:

```sql
-- Remove added columns
ALTER TABLE `customer`
DROP COLUMN `role`,
DROP COLUMN `setup_status`,
DROP COLUMN `experience_data`,
DROP COLUMN `Date_Updated`;

-- Remove indexes
ALTER TABLE `customer`
DROP INDEX `idx_role`,
DROP INDEX `idx_setup_status`;
```

## Troubleshooting

### Issue: Setup page doesn't load
**Solution:** Clear browser cache, check Auth controller file exists

### Issue: Questions don't advance
**Solution:** Check JavaScript console for errors, verify form field names

### Issue: Data not saving
**Solution:** Check database connection, verify customer table structure

### Issue: Redirect loop
**Solution:** Check session data, verify setup_status value in database

## Support Files

- Full Documentation: `docs/CUSTOMER_EXPERIENCE_SETUP_IMPLEMENTATION.md`
- Migration File: `database/migrations/add_customer_experience_fields.sql`
- Controller: `application/controllers/Auth.php` (line 1081)
- View: `application/views/auth/setup_experience.php`

## Test Account Credentials

You can create test accounts with different configurations:

### Test Beginner User
```sql
-- Update existing user to beginner
UPDATE customer 
SET role = 'beginner', 
    setup_status = 'completed',
    experience_data = '{"role":"beginner","experience":"first_time","specifications_knowledge":"not_at_all","customization_handling":"prepare_for_me"}'
WHERE Customer_ID = 1;
```

### Test Professional User
```sql
-- Update existing user to professional
UPDATE customer 
SET role = 'professional', 
    setup_status = 'completed',
    experience_data = '{"role":"professional","professional_type":"architect","previous_experience":"yes_regularly","specification_preparation":"prepare_myself","2d_tool_comfort":"very_comfortable"}'
WHERE Customer_ID = 2;
```

## Next Steps

After successful setup:
1. Test with real users
2. Monitor for any issues
3. Review analytics (if implemented)
4. Gather user feedback
5. Iterate on question wording if needed

---

**Setup Complete!** 🎉

For detailed information, see: `docs/CUSTOMER_EXPERIENCE_SETUP_IMPLEMENTATION.md`
