# Database Sharing Guide: phpMyAdmin Export/Import

This guide will help you share your `glassify-test` database with your groupmate using phpMyAdmin.

## Method 1: Export via phpMyAdmin (Easiest - Recommended)

### On Your Side (Exporting):

1. **Open phpMyAdmin**
   - Navigate to `http://localhost/phpmyadmin` in your browser
   - Login with your MySQL credentials

2. **Select Your Database**
   - Click on `glassify-test` in the left sidebar

3. **Export the Database**
   - Click on the **"Export"** tab at the top
   - Choose export method:
     - **Quick**: Recommended for most cases (exports entire database)
     - **Custom**: For more control over what to export

4. **Export Settings (Quick Method)**
   - Format: **SQL** (default)
   - Click **"Go"** button
   - Save the `.sql` file (it will download automatically)

5. **Export Settings (Custom Method - More Control)**
   - Format: **SQL**
   - Select tables: Choose **"Select All"** or pick specific tables
   - Structure: ✓ Check "Add CREATE DATABASE / USE statement"
   - Data: ✓ Check "Add INSERT statements"
   - Click **"Go"** to download

### On Your Groupmate's Side (Importing):

1. **Open phpMyAdmin**
   - Navigate to `http://localhost/phpmyadmin`
   - Login with their MySQL credentials

2. **Create Database (if it doesn't exist)**
   - Click on **"New"** in the left sidebar
   - Database name: `glassify-test`
   - Collation: `utf8_general_ci` (or `utf8mb4_general_ci` for better Unicode support)
   - Click **"Create"**

3. **Select the Database**
   - Click on `glassify-test` in the left sidebar

4. **Import the SQL File**
   - Click on the **"Import"** tab at the top
   - Click **"Choose File"** and select the `.sql` file you sent them
   - Leave other settings as default
   - Click **"Go"** at the bottom

5. **Wait for Import to Complete**
   - You'll see a success message when it's done
   - All tables and data should now be imported

---

## Method 2: Using Command Line (Alternative)

### Export (Your Side):
```bash
# Navigate to your XAMPP MySQL bin directory
cd C:\xampp\mysql\bin

# Export database
mysqldump -u admin_glassify -p glassify-test > C:\xampp\htdocs\Glassify-CI\database_export.sql

# Enter password when prompted: glassifyAdmin
```

### Import (Groupmate's Side):
```bash
# Navigate to their XAMPP MySQL bin directory
cd C:\xampp\mysql\bin

# Import database (create database first if needed)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS glassify-test CHARACTER SET utf8 COLLATE utf8_general_ci;"

# Import the SQL file
mysql -u root -p glassify-test < path\to\database_export.sql
```

---

## Method 3: Share via File Sharing Service

1. **Export your database** using Method 1 or 2
2. **Upload the `.sql` file** to:
   - Google Drive
   - Dropbox
   - OneDrive
   - Or send via email (if file is small)
3. **Share the link** with your groupmate
4. **They download and import** using the Import steps above

---

## Important Notes:

### Database Configuration Update:
After importing, your groupmate may need to update the database credentials in:
- `application/config/database.php`
  - Update `username` and `password` to match their MySQL credentials
  - Keep `database` as `glassify-test`

### Common Issues & Solutions:

**Issue: "Access denied" error**
- Solution: Make sure your groupmate's MySQL user has permission to create/import databases
- They may need to use `root` user or grant permissions

**Issue: "File size too large" error in phpMyAdmin**
- Solution: Increase `upload_max_filesize` and `post_max_size` in `php.ini`
- Or use command line method instead

**Issue: "Unknown collation" error**
- Solution: Update collation to `utf8mb4_general_ci` or `utf8_general_ci` during database creation

**Issue: Character encoding problems**
- Solution: Ensure both databases use the same character set (utf8 or utf8mb4)

---

## Quick Checklist:

### For You (Exporting):
- [ ] Open phpMyAdmin
- [ ] Select `glassify-test` database
- [ ] Click Export tab
- [ ] Choose SQL format
- [ ] Click Go and save the file
- [ ] Share the `.sql` file with your groupmate

### For Your Groupmate (Importing):
- [ ] Create `glassify-test` database in their phpMyAdmin
- [ ] Select the database
- [ ] Click Import tab
- [ ] Choose the `.sql` file you sent
- [ ] Click Go and wait for success
- [ ] Update database credentials in `application/config/database.php`

---

## File Location Reference:

Your database config is located at:
- `application/config/database.php`

Current settings:
- Database: `glassify-test`
- Username: `admin_glassify`
- Password: `glassifyAdmin`
- Host: `localhost`

**⚠️ Security Note**: Consider using environment-specific configs and never commit passwords to version control in production!


