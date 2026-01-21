<?php
/**
 * Breadcrumb Navigation Helper
 * 
 * Generates breadcrumb navigation for Products & Services pages
 * 
 * @package     CodeIgniter
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Glassify Development Team
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Generate Breadcrumb Navigation
 * 
 * Creates a breadcrumb navigation strip with page title and breadcrumb trail.
 * The last item in the breadcrumb array will be highlighted as active.
 * 
 * @param   string  $page_title        Main page title (default: "Products & Services")
 * @param   array   $breadcrumb_items  Array of breadcrumb items (e.g., ["Products", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"])
 * @param   string  $container_id      Optional ID for the breadcrumbs container (default: "breadcrumbs-container")
 * @return  string  HTML string for the breadcrumb navigation
 * 
 * @example
 * echo render_breadcrumb("Products & Services", ["Products", "Sliding System & Size", "Frame & Glass", "Hardware & Accessories"]);
 */
if (!function_exists('render_breadcrumb')) {
    function render_breadcrumb($page_title = "Products & Services", $breadcrumb_items = [], $container_id = "breadcrumbs-container") {
        // Ensure we have at least one breadcrumb item
        if (empty($breadcrumb_items)) {
            $breadcrumb_items = ["Products"];
        }
        
        // Start building the HTML
        $html = '<div class="breadcrumb-strip">';
        $html .= '<div class="page-title">' . htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div class="breadcrumbs" id="' . htmlspecialchars($container_id, ENT_QUOTES, 'UTF-8') . '">';
        
        // Build breadcrumb items
        $item_count = count($breadcrumb_items);
        foreach ($breadcrumb_items as $index => $item) {
            $is_last = ($index === $item_count - 1);
            
            // Add breadcrumb item
            $class = $is_last ? 'active' : '';
            $html .= '<span' . (!empty($class) ? ' class="' . $class . '"' : '') . '>';
            $html .= htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            $html .= '</span>';
            
            // Add chevron separator if not the last item
            if (!$is_last) {
                $html .= '<span class="chevron-right"></span>';
            }
        }
        
        $html .= '</div>'; // Close .breadcrumbs
        $html .= '</div>'; // Close .breadcrumb-strip
        
        return $html;
    }
}

/**
 * Generate Breadcrumb Navigation with Links
 * 
 * Creates a breadcrumb navigation strip with clickable links (except the last active item).
 * 
 * @param   string  $page_title        Main page title (default: "Products & Services")
 * @param   array   $breadcrumb_items  Array of breadcrumb items with optional 'text' and 'url' keys
 *                                      Format: [["text" => "Products", "url" => "/products"], ["text" => "Category", "url" => null]]
 *                                      Or simple array: ["Products", "Category"] (will not be links)
 * @param   string  $container_id      Optional ID for the breadcrumbs container (default: "breadcrumbs-container")
 * @return  string  HTML string for the breadcrumb navigation
 * 
 * @example
 * $items = [
 *     ["text" => "Products", "url" => base_url("products")],
 *     ["text" => "Sliding System & Size", "url" => base_url("products/sliding")],
 *     ["text" => "Frame & Glass", "url" => null],
 *     ["text" => "Hardware & Accessories", "url" => null]
 * ];
 * echo render_breadcrumb_with_links("Products & Services", $items);
 */
if (!function_exists('render_breadcrumb_with_links')) {
    function render_breadcrumb_with_links($page_title = "Products & Services", $breadcrumb_items = [], $container_id = "breadcrumbs-container") {
        // Ensure we have at least one breadcrumb item
        if (empty($breadcrumb_items)) {
            $breadcrumb_items = ["Products"];
        }
        
        // Start building the HTML
        $html = '<div class="breadcrumb-strip">';
        $html .= '<div class="page-title">' . htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div class="breadcrumbs" id="' . htmlspecialchars($container_id, ENT_QUOTES, 'UTF-8') . '">';
        
        // Build breadcrumb items
        $item_count = count($breadcrumb_items);
        foreach ($breadcrumb_items as $index => $item) {
            $is_last = ($index === $item_count - 1);
            
            // Handle both array format (with text/url) and simple string format
            if (is_array($item)) {
                $text = isset($item['text']) ? $item['text'] : $item[0];
                $url = isset($item['url']) ? $item['url'] : (isset($item[1]) ? $item[1] : null);
            } else {
                $text = $item;
                $url = null;
            }
            
            // Add breadcrumb item (as link if not last and URL provided)
            if (!$is_last && !empty($url)) {
                $html .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
                $html .= '<span>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
                $html .= '</a>';
            } else {
                $class = $is_last ? 'active' : '';
                $html .= '<span' . (!empty($class) ? ' class="' . $class . '"' : '') . '>';
                $html .= htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                $html .= '</span>';
            }
            
            // Add chevron separator if not the last item
            if (!$is_last) {
                $html .= '<span class="chevron-right"></span>';
            }
        }
        
        $html .= '</div>'; // Close .breadcrumbs
        $html .= '</div>'; // Close .breadcrumb-strip
        
        return $html;
    }
}
