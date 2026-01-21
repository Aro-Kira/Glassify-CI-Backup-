# Breadcrumb Navigation Helper Usage Guide

## Overview
The `breadcrumb_helper.php` provides reusable functions to generate breadcrumb navigation for Products & Services pages.

## Loading the Helper

In your controller, load the helper:
```php
$this->load->helper('breadcrumb');
```

Or autoload it in `application/config/autoload.php`:
```php
$autoload['helper'] = array('url', 'breadcrumb');
```

## Functions

### 1. `render_breadcrumb()`
Simple breadcrumb navigation without links.

**Parameters:**
- `$page_title` (string, optional): Main page title (default: "Products & Services")
- `$breadcrumb_items` (array, optional): Array of breadcrumb items (default: ["Products"])
- `$container_id` (string, optional): ID for the breadcrumbs container (default: "breadcrumbs-container")

**Example:**
```php
<?php
$this->load->helper('breadcrumb');

// Simple usage
echo render_breadcrumb();

// With custom title and items
echo render_breadcrumb(
    "Products & Services",
    ["Products", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]
);

// With custom container ID (for JavaScript targeting)
echo render_breadcrumb(
    "Products & Services",
    ["Products", "Category", "Subcategory"],
    "my-breadcrumbs"
);
?>
```

**Output:**
```html
<div class="breadcrumb-strip">
    <div class="page-title">Products & Services</div>
    <div class="breadcrumbs" id="breadcrumbs-container">
        <span>Products</span>
        <span class="chevron-right"></span>
        <span>Sliding System & Size</span>
        <span class="chevron-right"></span>
        <span>Frame & Glass</span>
        <span class="chevron-right"></span>
        <span class="active">Hardware & Accessories</span>
    </div>
</div>
```

### 2. `render_breadcrumb_with_links()`
Breadcrumb navigation with clickable links (except the last active item).

**Parameters:**
- `$page_title` (string, optional): Main page title (default: "Products & Services")
- `$breadcrumb_items` (array, optional): Array of breadcrumb items with optional 'text' and 'url' keys
- `$container_id` (string, optional): ID for the breadcrumbs container (default: "breadcrumbs-container")

**Item Format Options:**

**Option 1: Array with 'text' and 'url' keys**
```php
$items = [
    ["text" => "Products", "url" => base_url("products")],
    ["text" => "Sliding System & Size", "url" => base_url("products/sliding")],
    ["text" => "Frame & Glass", "url" => null], // null = no link
    ["text" => "Hardware & Accessories", "url" => null] // Last item is always active
];
```

**Option 2: Simple array (no links)**
```php
$items = ["Products", "Category", "Subcategory"];
```

**Example:**
```php
<?php
$this->load->helper('breadcrumb');
$this->load->helper('url');

$breadcrumb_items = [
    ["text" => "Products", "url" => base_url("products")],
    ["text" => "Sliding System & Size", "url" => base_url("products/sliding")],
    ["text" => "Frame & Glass", "url" => base_url("products/frame")],
    ["text" => "Hardware & Accessories", "url" => null] // Active item
];

echo render_breadcrumb_with_links("Products & Services", $breadcrumb_items);
?>
```

## Real-World Example

**In a Controller:**
```php
class ProductsCon extends CI_Controller {
    public function hardware() {
        $this->load->helper('breadcrumb');
        $this->load->helper('url');
        
        $data['breadcrumb'] = render_breadcrumb(
            "Products & Services",
            ["Products", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]
        );
        
        $this->load->view('products/hardware', $data);
    }
}
```

**In a View:**
```php
<?php echo $breadcrumb; ?>
<!-- Or directly: -->
<?php
$this->load->helper('breadcrumb');
echo render_breadcrumb(
    "Products & Services",
    ["Products", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]
);
?>
```

## Styling

The breadcrumb uses the following CSS classes:
- `.breadcrumb-strip` - Main container with dark teal background
- `.page-title` - Main heading
- `.breadcrumbs` - Breadcrumb trail container
- `.breadcrumbs .active` - Active (last) item highlighted in yellow
- `.breadcrumbs a` - Link styling for clickable items
- `.chevron-right` - Separator between items

Styles are defined in `assets/css/general-customer/shop/2DModeling_styles.css`.

## Notes

- The last item in the breadcrumb array is automatically marked as active (highlighted in yellow)
- All user input is automatically escaped for security (XSS protection)
- The helper follows CodeIgniter conventions and can be autoloaded
