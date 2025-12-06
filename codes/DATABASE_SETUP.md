# GlassWorth BUILDERS - Database Setup Guide

This guide will help you set up the MySQL database for the GlassWorth BUILDERS inventory management system.

## Prerequisites

- XAMPP (or any MySQL server) installed and running
- phpMyAdmin access (usually at http://localhost/phpmyadmin)
- Basic knowledge of MySQL

## Installation Steps

### Method 1: Using phpMyAdmin (Recommended)

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Open phpMyAdmin**
   - Navigate to http://localhost/phpmyadmin in your browser

3. **Import the SQL file**
   - Click on "Import" tab in phpMyAdmin
   - Click "Choose File" and select `glassworth_database.sql`
   - Click "Go" to import the database
   - The database will be created automatically with all tables and sample data

### Method 2: Using MySQL Command Line

1. **Open MySQL Command Line** or Terminal
   ```bash
   mysql -u root -p
   ```

2. **Import the SQL file**
   ```bash
   source C:/xampp/htdocs/codes/glassworth_database.sql
   ```
   Or:
   ```bash
   mysql -u root -p < C:/xampp/htdocs/codes/glassworth_database.sql
   ```

### Method 3: Manual Setup

1. **Create the database**
   ```sql
   CREATE DATABASE glassworth_builders;
   ```

2. **Select the database**
   ```sql
   USE glassworth_builders;
   ```

3. **Run the SQL file**
   - Copy and paste the contents of `glassworth_database.sql` into MySQL

## Database Configuration

After importing the database, update the `config.php` file with your MySQL credentials:

```php
define('DB_HOST', 'localhost');    // Usually 'localhost'
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password (empty for XAMPP default)
define('DB_NAME', 'glassworth_builders');
```

## Database Structure

### Tables

1. **users** - User accounts and authentication
   - Stores admin and user login information
   - Default admin: admin@glassworth.com / admin123

2. **items** - Inventory items
   - Item code, name, category, stock quantity, unit, threshold
   - Tracks new items and stock levels

3. **products** - Product catalog
   - Product information with prices and categories
   - Used for product display and sales

4. **reports** - System reports
   - Sales, inventory, purchases, and product reports
   - Tracks report status and amounts

5. **activities** - Activity log
   - Records all system activities and changes
   - Tracks stock movements, item updates, etc.

6. **stock_transactions** - Stock movement history
   - Detailed history of all stock additions and removals
   - Links to users and items

### Views

- **v_low_stock_items** - Items below threshold
- **v_out_of_stock_items** - Items with zero stock
- **v_new_items** - Recently added items
- **v_recent_activities** - Latest system activities
- **v_stock_status_summary** - Category-wise stock summary

### Stored Procedures

- **sp_add_stock** - Add stock to an item
- **sp_remove_stock** - Remove stock from an item
- **sp_update_threshold** - Update minimum stock threshold

## Sample Data

The database comes pre-loaded with:
- 1 admin user
- 10 inventory items
- 20 products
- 20 reports
- 8 activity logs
- 4 stock transactions

## Usage Examples

### Connect to Database

```php
<?php
require_once 'config.php';

// Using mysqli
$conn = getDBConnection();

// Using PDO
$pdo = getPDOConnection();
?>
```

### Fetch All Items

```php
<?php
require_once 'config.php';

$items = fetchAll("SELECT * FROM items ORDER BY name");
foreach ($items as $item) {
    echo $item['name'] . " - " . $item['stock_quantity'] . "<br>";
}
?>
```

### Add Stock Using Stored Procedure

```php
<?php
require_once 'config.php';

$conn = getDBConnection();
$stmt = $conn->prepare("CALL sp_add_stock(?, ?, ?, ?)");
$stmt->bind_param("iisi", $item_id, $quantity, $reason, $user_id);

$item_id = 1;
$quantity = 50;
$reason = "New delivery from supplier";
$user_id = 1;

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo "New stock: " . $row['new_stock'];

$stmt->close();
$conn->close();
?>
```

### Get Low Stock Items

```php
<?php
require_once 'config.php';

$lowStockItems = fetchAll("SELECT * FROM v_low_stock_items");
foreach ($lowStockItems as $item) {
    echo $item['name'] . " - Only " . $item['stock_quantity'] . " left!<br>";
}
?>
```

## Security Notes

1. **Change Default Password**
   - The default admin password is `admin123`
   - Change it immediately in production

2. **Database Credentials**
   - Never commit `config.php` with real credentials to version control
   - Use environment variables in production

3. **SQL Injection Prevention**
   - Always use prepared statements (as shown in examples)
   - Never concatenate user input directly into SQL queries

## Troubleshooting

### Connection Error
- Ensure MySQL is running in XAMPP
- Check username and password in `config.php`
- Verify database name is correct

### Import Error
- Make sure MySQL version is 5.7 or higher
- Check file encoding (should be UTF-8)
- Increase `max_allowed_packet` in MySQL if file is large

### Permission Error
- Ensure MySQL user has CREATE, INSERT, UPDATE, DELETE privileges
- For XAMPP, root user should have all privileges by default

## Support

For issues or questions:
1. Check MySQL error logs
2. Verify all tables were created successfully
3. Test connection using `config.php` functions

## Next Steps

1. Update your PHP files to use the database connection
2. Replace hardcoded data with database queries
3. Implement authentication using the users table
4. Connect forms to database operations

---

**Note:** This database schema is designed for the GlassWorth BUILDERS inventory management system. Adjust table structures as needed for your specific requirements.

