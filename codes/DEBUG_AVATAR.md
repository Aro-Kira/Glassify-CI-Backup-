# Avatar Upload Debugging Guide

This guide will help you debug why the avatar disappears after page refresh.

## Step 1: Check Browser Console

1. Open your browser's Developer Tools (Press `F12` or `Ctrl+Shift+I`)
2. Go to the **Console** tab
3. Refresh the page
4. Look for the message: `Profile loaded:` followed by user data
5. Check if `avatar` field has a value or is `null`

**What to look for:**
- If `avatar: null` → The database doesn't have the avatar path
- If `avatar: "uploads/avatars/..."` → The path exists in database, check if file exists

## Step 2: Check Network Requests

1. In Developer Tools, go to the **Network** tab
2. Refresh the page
3. Look for `get_user_profile.php` request
4. Click on it and check the **Response** tab
5. Verify the JSON response contains the avatar path

**What to check:**
```json
{
  "success": true,
  "data": {
    "avatar": "uploads/avatars/avatar_1234567890_abc123.jpg"
  }
}
```

## Step 3: Verify Database

### Using phpMyAdmin:

1. Open phpMyAdmin
2. Select `glassworth_builders` database
3. Click on `users` table
4. Click **Browse** tab
5. Check if the `avatar` column exists
6. Check if the first user has a value in the `avatar` column

**SQL Query to check:**
```sql
SELECT user_id, name, email, avatar FROM users ORDER BY user_id ASC LIMIT 1;
```

### Using MySQL Command Line:

```sql
USE glassworth_builders;
SELECT user_id, name, email, avatar FROM users ORDER BY user_id ASC LIMIT 1;
```

## Step 4: Check File System

1. Navigate to your project folder: `C:\xampp\htdocs\codes\`
2. Check if `uploads/avatars/` folder exists
3. Check if the uploaded image file exists in that folder
4. Verify the filename matches what's in the database

**Path should be:**
```
C:\xampp\htdocs\codes\uploads\avatars\avatar_[timestamp]_[uniqueid].jpg
```

## Step 5: Test Avatar Upload API Directly

1. Open Developer Tools → **Network** tab
2. Upload an avatar image
3. Look for `upload_avatar.php` request
4. Check the **Response** tab for success/error message
5. Check the **Status Code** (should be 200)

**Expected Response:**
```json
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "data": {
    "avatar_url": "uploads/avatars/avatar_1234567890_abc123.jpg"
  }
}
```

## Step 6: Enable PHP Error Logging

Add this to the top of `api/get_user_profile.php` and `api/upload_avatar.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
```

Then check `error.log` file in your project root for any errors.

## Step 7: Manual Database Check Script

Create a file `test_avatar.php` in your project root:

```php
<?php
require_once 'config.php';

$conn = getDBConnection();

// Check if avatar column exists
$columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    echo "✓ Avatar column exists<br>";
} else {
    echo "✗ Avatar column does NOT exist<br>";
    echo "Adding avatar column...<br>";
    $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    echo "✓ Avatar column added<br>";
}

// Get user data
$result = $conn->query("SELECT user_id, name, email, avatar FROM users ORDER BY user_id ASC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<br>User Data:<br>";
    echo "User ID: " . $user['user_id'] . "<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Avatar: " . ($user['avatar'] ? $user['avatar'] : 'NULL') . "<br>";
    
    if ($user['avatar']) {
        $avatarPath = __DIR__ . '/' . $user['avatar'];
        if (file_exists($avatarPath)) {
            echo "✓ Avatar file exists at: " . $avatarPath . "<br>";
            echo "<img src='" . $user['avatar'] . "' alt='Avatar' style='max-width: 200px; margin-top: 10px;'><br>";
        } else {
            echo "✗ Avatar file does NOT exist at: " . $avatarPath . "<br>";
        }
    }
} else {
    echo "✗ No user found<br>";
}

$conn->close();
?>
```

Visit `http://localhost/codes/test_avatar.php` in your browser to see the current state.

## Step 8: Common Issues and Solutions

### Issue 1: Avatar column doesn't exist
**Solution:** The script should auto-create it, but you can manually run:
```sql
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;
```

### Issue 2: File uploaded but not saved to database
**Solution:** Check PHP error logs and verify database connection

### Issue 3: Database has path but file doesn't exist
**Solution:** Check file permissions and uploads directory path

### Issue 4: Path mismatch
**Solution:** Verify the path in database matches actual file location

## Step 9: Check File Permissions

Make sure the `uploads/avatars/` directory has write permissions:
- Windows: Right-click folder → Properties → Security → Ensure write permissions
- The PHP script should create this automatically, but verify it exists

## Quick Debug Checklist

- [ ] Browser console shows avatar path in profile data
- [ ] Network tab shows successful API responses
- [ ] Database has avatar column
- [ ] Database has avatar path value
- [ ] Uploads folder exists
- [ ] Image file exists in uploads folder
- [ ] File path in database matches actual file location
- [ ] No PHP errors in error log
- [ ] Browser can access the image URL directly

## Still Having Issues?

If the avatar still disappears, check:
1. Browser cache (try Ctrl+F5 to hard refresh)
2. Check if multiple users exist (the script updates the first user)
3. Verify the avatar path is accessible via browser: `http://localhost/codes/uploads/avatars/[filename]`

