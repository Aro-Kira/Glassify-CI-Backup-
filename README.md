# Glassify-CI

A comprehensive e-commerce management system built with **CodeIgniter 3** framework for selling custom glass products with advanced customization features, employee management, and integrated payment processing.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [Database Setup](#database-setup)
- [Payment Integration (PayMongo)](#payment-integration-paymongo)
- [Troubleshooting](#troubleshooting)

---

## Features

### 🛍️ E-Commerce Management
- Product catalog with custom glass product listings
- Shopping cart functionality
- Order management with multi-stage payments
- Wishlist feature
- Customer order tracking

### 🎨 Advanced Customization
- Real-time 2D product customization using Konva.js
- Customizable product fields (text, colors, dimensions, materials)
- Design preview and rendering
- Customization templates and field management

### 💳 Payment Processing
- **PayMongo Integration** for processing payments
- Multi-stage payment support (downpayment, fabrication, installation)
- Transaction tracking and history
- Payment status management

### 👥 User Management
- Employee role-based access control
- Role and permission system
- Admin dashboard
- Account verification
- User profile management

### 📊 Business Management
- Inventory tracking
- Sales analytics
- FAQ management
- Promotion and discount system

---

## Requirements

- **PHP**: >= 5.3.7 (tested with PHP 7.4)
- **Web Server**: Apache with mod_rewrite enabled
- **Database**: MySQL/MariaDB
- **Composer**: For dependency management
- **Node.js** (optional): For front-end asset management

### PHP Extensions Required
- MySQLi
- JSON
- Sessions
- cURL (for PayMongo integration)

---

## Installation

### 1. Prerequisites
Ensure you have:
- XAMPP/WAMP/LAMP server running
- MySQL service running
- Composer installed

### 2. Clone/Setup the Project
```bash
# Navigate to your web root
cd /path/to/htdocs

# Clone or extract the Glassify-CI project
git clone <repository-url> Glassify-CI
# or
unzip Glassify-CI.zip

cd Glassify-CI
```

### 3. Install Dependencies
```bash
composer install
```

This will install the CodeIgniter framework and required dependencies.

### 4. Set File Permissions
Ensure writable directories have proper permissions:
```bash
chmod 777 application/cache
chmod 777 application/logs
chmod 777 uploads
chmod 777 writable
```

### 5. Configure Your Web Server
#### Apache (.htaccess)
Ensure `.htaccess` is enabled and mod_rewrite is active:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /Glassify-CI/
    RewriteCond %{REQUEST_URI} ^system.*
    RewriteRule ^(.*)$ /index.php/$1 [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

---

## Configuration

### 1. Database Configuration
Edit `application/config/database.php`:
```php
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => '127.0.0.1',
	'port' => 3306,
	// Original credentials (backup): username => 'admin_glassify', password => 'glassifyAdmin'
	// Changed to XAMPP local defaults for testing on localhost
	'username' => 'root',
	'password' => '',
	'database' => 'glassify_db',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
```

### 2. Application Configuration
Edit `application/config/config.php`:
```php
// Base URL - auto-configured for localhost and network access
$config['base_url'] = 'http://localhost/Glassify-CI/';

// Index file (leave empty if using mod_rewrite)
$config['index_page'] = '';

// Session settings
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;  // 2 hours
```

### 3. PayMongo Configuration
Edit `application/config/paymongo.php`:
```php
$config['paymongo_secret_key'] = 'your_secret_key_here';
$config['paymongo_public_key'] = 'your_public_key_here';
$config['paymongo_api_url'] = 'https://api.paymongo.com/v1';
$config['environment'] = 'sandbox';  // or 'production'
```

Get your PayMongo keys from: https://paymongo.com/developers

### 4. Email Configuration (Optional)
Edit `application/config/email.php`:
```php
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'your_smtp_host';
$config['smtp_user'] = 'your_email@domain.com';
$config['smtp_pass'] = 'your_password';
$config['smtp_port'] = 587;
```

---

## Project Structure

```
Glassify-CI/
├── application/
│   ├── config/           # Configuration files
│   ├── controllers/      # MVC Controllers
│   │   ├── AdminCon.php          # Admin dashboard
│   │   ├── ProductCon.php        # Product management
│   │   ├── CartCon.php           # Shopping cart
│   │   ├── OrderCon.php          # Order processing
│   │   ├── CustomizationCon.php  # Customization engine
│   │   └── Auth.php              # Authentication
│   ├── models/           # Database models
│   ├── views/            # HTML/PHP templates
│   ├── helpers/          # Helper functions
│   ├── hooks/            # CodeIgniter hooks
│   ├── libraries/        # Custom libraries (Paymongo.php)
│   └── logs/             # Application logs
├── system/               # CodeIgniter framework (do not modify)
├── assets/               # Static files
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── images/           # Images
│   └── data/             # JSON data files
├── uploads/              # User uploads
│   ├── products/         # Product images
│   ├── designs/          # Custom designs
│   ├── profile/          # User profile images
│   └── receipts/         # Payment receipts
├── writable/             # Writable directory
├── index.php             # Application entry point
└── composer.json         # PHP dependencies

```

---

## Usage

### 1. Access the Application
Open your browser and navigate to:
```
http://localhost/Glassify-CI/
```

### 2. User Registration & Login
- Navigate to the login page
- Create a new account as an end-user or employee
- Verify your email (if email is configured)
- Login with your credentials

### 3. Shopping Flow (End-Users)
1. Browse products in the shop
2. Select a product → Click "Customize"
3. Use the 2D customization panel to design your product
4. Add to cart
5. Proceed to checkout
6. Complete payment via PayMongo
7. Track your order in "My Orders"

### 4. Admin Panel
- Login with admin credentials
- Access `/admin` dashboard
- Manage products, inventory, orders, and customers
- View sales analytics

### 5. Employee Management
- Admin can create employee accounts with specific roles
- Employees have access to relevant sections (e.g., inventory, orders)
- Role-based permissions control feature access

---

## Database Setup

### 1. Create Database User
Create a dedicated database user with full privileges:

```sql
-- Create database user
CREATE USER 'admin_glassify'@'%' IDENTIFIED BY 'your_password_here';

-- Grant all privileges to the user
GRANT ALL PRIVILEGES ON glassify_db.* TO 'admin_glassify'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;
```

**User Details**:
- **Username**: `admin_glassify`
- **Host**: `%` (accessible from any host)
- **Privileges**: ALL PRIVILEGES
- **Default Password**: Change to a secure password

### 2. Create Database
```sql
CREATE DATABASE glassify_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import Database Schema
Use the provided SQL dump file:
```bash
# Import the complete database schema
mysql -u admin_glassify -p glassify_db < latest_glassifydb.sql

# Or via phpMyAdmin:
# 1. Open phpMyAdmin
# 2. Create database "glassify_db"
# 3. Go to Import tab
# 4. Select latest_glassifydb.sql
# 5. Click Import
```

### Database Tables Overview

The system includes the following key tables:

| Table | Purpose |
|-------|---------|
| `customer` | End-user/customer accounts |
| `order` | Customer orders |
| `order_items` | Individual items in orders |
| `product` | Product catalog |
| `customization` | Product customization configurations |
| `customization_field_configs` | Customization field definitions |
| `payment` | Payment transactions |
| `appointments` | Service appointments (ocular, installation) |
| `activities` | Activity/audit logs |
| `inventory_items` | Stock management |
| `cart` | Shopping cart data |
| `customer_customizations` | Saved customer designs |
| `customer_notifications` | Order and status notifications |
| `wishlist` | Customer wishlist items |
| `approved_orders` | Orders approved by admin |
| `pending_review_orders` | Orders awaiting admin review |
| `disapproved_orders` | Rejected orders |
| `awaiting_admin_orders` | New orders for review |
| `employee_archive` | Archived employee records |
| `enduser_archive` | Archived customer records |
| `issuereport` | Bug/issue reports |

### Important Tables Explained

#### Customer Table
Stores customer account information, preferences, and contact details.

#### Order Table
Tracks orders with status (pending, processing, fabrication, installation, completed) and payment stages.

#### Payment Table
Records all payment transactions linked to orders via PayMongo integration.

#### Appointments Table
Manages appointments for:
- Ocular visits (site assessment)
- Fabrication tracking
- Installation scheduling
- Payment collection

#### Customization Tables
- `customization`: Product customization rules and settings
- `customization_field_configs`: Field types (text, color, dimension, etc.)
- `customer_customizations`: Individual customer's saved designs

---

## Payment Integration (PayMongo)

### Setup Steps

1. **Register with PayMongo**
   - Go to https://paymongo.com
   - Create a business account
   - Complete verification

2. **Get API Keys**
   - Navigate to Settings → API Keys
   - Copy Secret Key and Public Key

3. **Configure in Application**
   - Update `application/config/paymongo.php` with your keys
   - Set environment to 'sandbox' for testing

4. **Payment Flow**
   ```
   User Places Order → Create PayMongo Source/Payment
   → Redirect to PayMongo Checkout → Payment Processing
   → Callback to Application → Order Status Updated
   → Receipt Generated
   ```

5. **Transaction Tracking**
   - All PayMongo transaction IDs are stored in the database
   - Link PayMongo source ID to orders for tracking
   - View payment status in admin and user dashboards

### Testing PayMongo in Sandbox
- Use test credit card: `4343 4343 4343 4343`
- Any future expiry date
- Any 3-digit CVC

---

## Troubleshooting

### Common Issues

#### 1. "Page Not Found" Error
**Solution**: 
- Ensure `.htaccess` is present and mod_rewrite is enabled
- Check `$config['base_url']` in `config.php`
- Verify your URL structure

#### 2. Database Connection Error
**Solution**:
- Check MySQL is running
- Verify credentials in `database.php`
- Ensure database exists
- Check user permissions

#### 3. "No Input file specified" Error
**Solution**:
- Verify `index.php` is in the root
- Check file permissions
- Clear application cache

#### 4. Session Not Working
**Solution**:
- Verify `application/cache` directory is writable
- Check session configuration in `config.php`
- Clear browser cookies

#### 5. Customization Not Displaying
**Solution**:
- Check browser console for JavaScript errors
- Verify Konva.js library is loaded
- Check `customization-fields.json` is valid JSON
- Clear browser cache

#### 6. Payment Processing Fails
**Solution**:
- Verify PayMongo API keys are correct
- Check cURL is enabled in PHP
- Verify webhook URL is correct
- Check logs in `application/logs/`

### Viewing Logs
```bash
# Check latest log file
tail -f application/logs/log-*.php
```

---

## Support & Maintenance

### Regular Tasks
- Monitor application logs weekly
- Backup database regularly
- Clear old logs monthly
- Update dependencies quarterly

### Performance Tips
- Enable query caching in production
- Use database indexes on frequently queried columns
- Minify CSS and JavaScript assets
- Implement lazy loading for images
- Use CDN for static assets

---

## Security Recommendations

1. **Keep CodeIgniter Updated**
   - Regularly check for security patches

2. **Environment Variables**
   - Store sensitive config in `.env` files (use environment override)
   - Never commit `.env` to version control

3. **SQL Injection Prevention**
   - Use CodeIgniter's Query Builder (ORM)
   - Avoid raw SQL queries

4. **CSRF Protection**
   - Ensure CSRF tokens are included in forms
   - Configure in `config.php`

5. **XSS Prevention**
   - Use `$this->input->get()` and `$this->input->post()` to sanitize input
   - Escape output with `htmlspecialchars()`

---

## Contact & Documentation

For issues or questions:
- Check application logs: `application/logs/`
- Review inline code comments
- Reference CodeIgniter documentation: https://codeigniter.com/userguide3/

---

**Last Updated**: February 2026  
**Version**: 1.0  
**License**: MIT
