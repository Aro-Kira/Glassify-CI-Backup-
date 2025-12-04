# Testing Guide: Sales Account Page Database Updates

## Prerequisites
1. Make sure XAMPP is running (Apache and MySQL)
2. Database `glassify-test` should be accessible
3. Browser with Developer Tools enabled (F12)

---

## Step 1: Login as Sales Representative

### Test Account Credentials:
- **URL**: `http://localhost/Glassify-CI/sales-login`
- **Email**: `sarah.johnson.sales@glassify.com`
- **Password**: `SalesRep123!`

### Steps:
1. Open your browser and navigate to: `http://localhost/Glassify-CI/sales-login`
2. Enter the email and password above
3. Click "Login as Sale"
4. You should be redirected to the Sales Dashboard

---

## Step 2: Navigate to Account Page

1. Click on the "Account" link in the navigation (or go directly to: `http://localhost/Glassify-CI/sales-account`)
2. You should see your account information displayed

---

## Step 3: Open Browser Console (For Debugging)

**Before making any changes:**
1. Press `F12` to open Developer Tools
2. Click on the **Console** tab
3. Keep this open while testing - you'll see all AJAX requests and responses here

---

## Step 4: Test Updating a Field

### Test Case 1: Update First Name

1. **Before Update**: Note the current First Name value (e.g., "Sarah")
2. **Click the edit icon** (pencil icon) next to "First Name"
3. **A popup will appear** with the current value
4. **Change the value** to something different (e.g., "Sarah" → "SarahTest")
5. **Click "Save"**
6. **Watch the console** - you should see:
   ```
   Base URL: http://localhost/Glassify-CI/
   Full API URL: http://localhost/Glassify-CI/SalesCon/update_account
   Field: First_Name Value: SarahTest
   Response status: 200
   ```
7. **Expected Result**: 
   - Alert message: "Account updated successfully!"
   - The First Name field should update to "SarahTest"
   - Page may reload if First Name or Last Name was changed

### Test Case 2: Update Phone Number

1. Click edit icon next to "Phone Number"
2. Change to a new 10-13 digit number (e.g., "09123456789")
3. Click "Save"
4. Check console for success message
5. Verify the phone number updated on the page

### Test Case 3: Update Password

1. Click edit icon next to "Password"
2. **Two fields will appear**: "Password" and "Confirm Password"
3. Enter new password (minimum 6 characters, e.g., "NewPass123!")
4. Enter the same password in "Confirm Password"
5. Click "Save"
6. **Expected**: Success message and password field shows "************"

### Test Case 4: Update Middle Name (Optional Field)

1. Click edit icon next to "Middle Name"
2. You can:
   - Enter a new value (e.g., "M.")
   - Or clear it completely (leave empty)
3. Click "Save"
4. Verify the change

---

## Step 5: Verify in Database

### Method 1: Using phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `glassify-test`
3. Click on table: `user`
4. Find the user with email: `sarah.johnson.sales@glassify.com`
5. Check the updated fields:
   - `First_Name` should show your new value
   - `PhoneNum` should show your new phone number
   - `Password` should show a hashed value (starts with `$2y$`)
   - `Date_Updated` should show the current timestamp

### Method 2: Using MySQL Command Line

```sql
-- Connect to MySQL
mysql -u root -p

-- Select database
USE glassify-test;

-- View the user's current data
SELECT UserID, First_Name, Middle_Name, Last_Name, PhoneNum, Date_Updated 
FROM user 
WHERE Email = 'sarah.johnson.sales@glassify.com';

-- Check if Date_Updated changed after your update
SELECT UserID, First_Name, Date_Updated 
FROM user 
WHERE Email = 'sarah.johnson.sales@glassify.com'
ORDER BY Date_Updated DESC;
```

---

## Step 6: Test Error Cases

### Test Case A: No Changes (Same Value)

1. Click edit on "First Name"
2. Enter the **same value** that's already there
3. Click "Save"
4. **Expected**: Error message "No changes detected. The value is the same as the current value."

### Test Case B: Invalid Phone Number

1. Click edit on "Phone Number"
2. Enter invalid format (e.g., "123" or "abc123")
3. Click "Save"
4. **Expected**: Error message "Phone number must be 10-13 digits only."

### Test Case C: Password Mismatch

1. Click edit on "Password"
2. Enter password: "NewPass123!"
3. Enter different confirm password: "DifferentPass"
4. Click "Save"
5. **Expected**: Error message "Passwords do not match. Please try again."

### Test Case D: Invalid Name Format

1. Click edit on "First Name"
2. Enter invalid characters (e.g., "John123" or "John@Doe")
3. Click "Save"
4. **Expected**: Error message "Name can only contain letters, spaces, hyphens, and apostrophes."

---

## Step 7: Check PHP Logs (If Issues Occur)

### Location of Logs:
- **Windows**: `C:\xampp2\htdocs\Glassify-CI\application\logs\log-[date].php`
- Look for entries with:
  - `User_model->update_account`
  - `Controller update_account`
  - `Sales Rep account updated successfully`

### What to Look For:
```
DEBUG - User_model->update_account: Updating UserID=2 with data: {"First_Name":"SarahTest"}
INFO - User_model->update_account: Successfully updated 1 row(s) for UserID=2
INFO - Sales Rep account updated successfully: UserID=2, Field=First_Name, Affected rows=1
```

---

## Step 8: Verify Session Update

If you updated First Name or Last Name:
1. Check the top navigation/header
2. Your name should be updated there as well
3. This confirms the session was updated

---

## Troubleshooting

### Issue: "Unauthorized - Please log in again"
- **Solution**: Make sure you're logged in as a Sales Representative
- Log out and log back in

### Issue: "No changes were made"
- **Solution**: Make sure you're entering a **different** value than what's currently displayed
- Check the database directly to see the current value

### Issue: Console shows "Failed to fetch" or Network Error
- **Solution**: 
  - Check if Apache is running
  - Verify the URL in console: should be `http://localhost/Glassify-CI/SalesCon/update_account`
  - Check browser console for CORS errors

### Issue: Database not updating
- **Solution**:
  1. Check PHP logs for errors
  2. Verify database connection in `application/config/database.php`
  3. Check if `Date_Updated` column exists in `user` table
  4. Verify user has proper permissions to update the database

---

## Success Criteria

✅ **Test is successful if:**
1. Changes are saved when "Save" button is clicked
2. Success message appears
3. UI updates to show new value
4. Database shows updated value
5. `Date_Updated` timestamp changes in database
6. No errors in browser console
7. No errors in PHP logs

---

## Quick Test Checklist

- [ ] Login as Sales Representative
- [ ] Navigate to Account page
- [ ] Open browser console (F12)
- [ ] Update First Name → Verify in database
- [ ] Update Phone Number → Verify in database
- [ ] Update Password → Verify hash in database
- [ ] Test error cases (same value, invalid format)
- [ ] Check PHP logs for any errors
- [ ] Verify Date_Updated changes after each update

---

## Need Help?

If something doesn't work:
1. Check browser console (F12 → Console tab)
2. Check PHP error logs
3. Verify database connection
4. Check that all files were saved correctly
5. Try clearing browser cache and cookies

