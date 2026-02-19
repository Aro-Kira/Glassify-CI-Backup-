# Glassify Database Setup Guide

This guide will help you set up a fresh database for the Glassify system with sample data.

## Quick Setup

### Option 1: Using phpMyAdmin (Recommended for Windows/XAMPP)

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Open phpMyAdmin**
   - Go to `http://localhost/phpmyadmin` in your browser
   - Login with your MySQL credentials (default: username `root`, password empty)

3. **Import the SQL file**
   - Click on "Import" tab in phpMyAdmin
   - Click "Choose File" and select `database/setup_database_with_data.sql`
   - Click "Go" to execute

4. **Verify the database**
   - You should see a new database named `latest_glassifydb`
   - Check that all tables are created and sample data is inserted

### Option 2: Using MySQL Command Line

1. **Open Command Prompt**
   - Press `Win + R`, type `cmd`, press Enter

2. **Navigate to MySQL bin directory**
   ```bash
   cd C:\xampp\mysql\bin
   ```

3. **Run the SQL file**
   ```bash
   mysql.exe -u root -p < "C:\xampp 7.4\htdocs\Glassify-CI\database\setup_database_with_data.sql"
   ```
   - Enter your MySQL password when prompted (default is empty, just press Enter)

### Option 3: Using MySQL Workbench

1. **Open MySQL Workbench**
2. **Connect to your MySQL server**
3. **Open the SQL file**
   - File → Open SQL Script
   - Navigate to `database/setup_database_with_data.sql`
4. **Execute the script**
   - Click the "Execute" button (lightning bolt icon) or press `Ctrl+Shift+Enter`

## Database Configuration

After setting up the database, verify your database configuration in:
- `application/config/database.php`

The default configuration expects:
- **Database Name**: `latest_glassifydb`
- **Username**: `admin_glassify` (or `root` for local development)
- **Password**: `glassifyAdmin` (or empty for local development)
- **Host**: `localhost`

If you used different credentials, update the `database.php` file accordingly.

## Default Login Credentials

After setup, you can login with these accounts:

### Admin Account
- **Email**: `admin@glassify.com`
- **Password**: `password123`

### Sales Representative
- **Email**: `queen@gmail.com`
- **Password**: `password123`

### Customer Account
- **Email**: `john.doe@example.com`
- **Password**: `password123`

**⚠️ IMPORTANT**: Change all passwords after first login for security!

## Sample Data Included

The setup script includes:

### Users (8 total)
- 2 Admin users
- 2 Sales Representatives
- 1 Inventory Officer
- 3 Customer accounts

### Products (18 total)
- 12 Direct order products (Windows, Mirrors, Glass Partitions, Doors)
- 6 Site assessment products

### Inventory Items (12 total)
- Glass materials (various types and thicknesses)
- Aluminum frames and components
- Accessories (adhesives, sealants, mounting hardware)

### Orders (3 sample orders)
- 1 Pending Review order
- 1 Approved and Paid order
- 1 Site-Assessed order (pending ocular)

### Other Data
- User addresses
- Appointments (ocular and installation)
- Quotations
- Payments
- System activity logs
- Status history

## Troubleshooting

### Error: "Database already exists"
If the database already exists, you have two options:

1. **Drop and recreate** (⚠️ This will delete all existing data):
   ```sql
   DROP DATABASE IF EXISTS latest_glassifydb;
   ```
   Then run the setup script again.

2. **Use a different database name**:
   - Edit `setup_database_with_data.sql` and change `latest_glassifydb` to your preferred name
   - Update `application/config/database.php` to match

### Error: "Access denied for user"
- Make sure MySQL is running in XAMPP
- Check your MySQL username and password
- For local development, try using `root` with an empty password

### Error: "Table already exists"
- The script uses `CREATE TABLE IF NOT EXISTS`, so this shouldn't happen
- If it does, you may need to drop existing tables first or use a fresh database

### Foreign Key Constraint Errors
- Make sure you're running the entire script in one go
- The script creates tables in the correct order to avoid foreign key issues

## Resetting the Database

To reset the database and start fresh:

1. **Drop the database**:
   ```sql
   DROP DATABASE IF EXISTS latest_glassifydb;
   ```

2. **Run the setup script again** using one of the methods above

## Next Steps

After setting up the database:

1. ✅ Verify database connection in your application
2. ✅ Test login with default credentials
3. ✅ Change all default passwords
4. ✅ Review and customize sample data as needed
5. ✅ Set up proper file permissions for uploads directory

## Additional Resources

- Main schema file: `database/glassify_complete_schema.sql`
- Migration scripts: `database/migrations/`
- Database scripts: `database/scripts/`

## Support

If you encounter any issues:
1. Check the error message carefully
2. Verify MySQL is running
3. Check database credentials in `application/config/database.php`
4. Ensure you have proper permissions to create databases
