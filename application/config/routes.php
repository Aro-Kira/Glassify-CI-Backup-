<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

/* 
======================================
=============Pages Routes===============
======================================
 */


$route['default_controller'] = 'pages/home';
$route['about'] = 'pages/about';
$route['contact'] = 'pages/contact';
$route['projects'] = 'pages/projects';
$route['home-login'] = 'pages/home_login';
$route['quote-request'] = 'pages/process_quote_request';
$route['test-email'] = 'pages/test_email';


/* 
======================================
=============FAQ Routes===============
======================================
 */

$route['faq'] = 'FaqCon/faq';
$route['faq-ordering'] = 'FaqCon/faq_ordering';
$route['faq-payment'] = 'FaqCon/faq_payment';
$route['faq-pricing'] = 'FaqCon/faq_pricing';
$route['faq-warranty'] = 'FaqCon/faq_warranty';
$route['faq-shipping'] = 'FaqCon/faq_shipping';
$route['faq-account'] = 'FaqCon/faq_account';
$route['report-issue'] = 'FaqCon/faq_report';
$route['submit-issue'] = 'FaqCon/submit_issue';

/* 
======================================
=============Shop Routes===============
======================================
 */

$route['products'] = 'ShopCon/products';
$route['2DModeling'] = 'ShopCon/product_2d';
$route['addtocart'] = 'CartCon/cart_page';    
$route['payment'] = 'ShopCon/checkout';
$route['paying'] = 'ShopCon/ewallet';
$route['complete'] = 'ShopCon/complete';
$route['terms_order'] = 'ShopCon/terms_order';
$route['wishlist'] = 'ShopCon/wishlist';
$route['track_order'] = 'ShopCon/order_tracking';
$route['waiting_order'] = 'ShopCon/waiting_order';
$route['cartsave'] = 'AddtoCartCon/save';
$route['my_purchases'] = 'ShopCon/list_products';

/* 
======================================
=============Auth Routes===============
======================================
 */

$route['login'] = 'auth/login';
$route['register'] = 'auth/register';
$route['logout'] = 'auth/logout';
$route['auth/process_login'] = 'auth/process_login';
$route['auth/process_register'] = 'auth/process_register';

/*==============Custom URL================*/
$route['Adlog'] = 'Auth/admin_login';
$route['SLslog'] = 'Auth/sales_login';
$route['sales-login'] = 'Auth/sales_login';
$route['Invlog'] = 'Auth/inventory_login';
$route['forgot-password/(:any)'] = 'auth/forgot_password/$1';
$route['forgot-password'] = 'auth/forgot_password/Sales';
$route['reset-password/(:any)/(:any)'] = 'auth/reset_password/$1/$2';
$route['auth/process_forgot_password/(:any)'] = 'auth/process_forgot_password/$1';
$route['auth/process_reset_password/(:any)'] = 'auth/process_reset_password/$1';

/* 
======================================
=============User Routes===============
======================================
 */

$route['Profile'] = 'UserCon/profile';


/* 
======================================
=============Admin Routes===============
======================================
 */
$route['admin-dashboard'] = 'AdminCon/admin_dashboard';
$route['admin-orders'] = 'AdminCon/admin_orders';
$route['admin-appointment'] = 'AdminCon/admin_appointment';
$route['admin-employee'] = 'AdminCon/admin_employee';
$route['admin-endUser'] = 'AdminCon/admin_endUser';
$route['admin-inventory'] = 'AdminCon/admin_inventory';
$route['admin-product'] = 'AdminCon/admin_product';
$route['admin-payments'] = 'AdminCon/admin_payments';
$route['admin-reports'] = 'AdminCon/admin_reports';
$route['admin-account'] = 'AdminCon/admin_account';


/* 
======================================
=============Sales Routes===============
======================================
 */

$route['sales-dashboard'] = 'SalesCon/sales_dashboard';
$route['sales-orders'] = 'SalesCon/sales_orders';
$route['sales-products'] = 'SalesCon/sales_products';
$route['sales-inventory'] = 'SalesCon/sales_inventory';
$route['sales-endUser'] = 'SalesCon/sales_endUser';
$route['sales-payments'] = 'SalesCon/sales_payments';
$route['sales-issues'] = 'SalesCon/sales_issues';
$route['sales-account'] = 'SalesCon/sales_account';
$route['sales-get-issues'] = 'SalesCon/get_issues_ajax';
$route['sales-get-issue-details/(:num)'] = 'SalesCon/get_issue_details_ajax/$1';
$route['sales-mark-resolved'] = 'SalesCon/mark_resolved_ajax';
$route['sales-update-priority'] = 'SalesCon/update_priority_ajax';
$route['sales-get-issue-stats'] = 'SalesCon/get_issue_stats_ajax';
$route['sales-notif'] = 'SalesCon/sales_notif';
$route['SalesCon/update_account'] = 'SalesCon/update_account';
$route['AdminCon/update_account'] = 'AdminCon/update_account';
$route['SalesCon/get_order_details'] = 'SalesCon/get_order_details';
$route['SalesCon/filter_orders_by_date'] = 'SalesCon/filter_orders_by_date';
$route['SalesCon/get_payment_details'] = 'SalesCon/get_payment_details';
$route['SalesCon/mark_payment_paid'] = 'SalesCon/mark_payment_paid';
$route['SalesCon/approve_order'] = 'SalesCon/approve_order';
$route['SalesCon/disapprove_order'] = 'SalesCon/disapprove_order';
$route['SalesCon/request_approval'] = 'SalesCon/request_approval';

/* 
======================================
=============Inventory Routes===============
======================================
 */

$route['inventory-dashboard'] = 'InventCon/inventory_dashboard';
$route['inventory-products'] = 'InventCon/inventory_products';
$route['inventory-inventory'] = 'InventCon/inventory_inventory';
$route['inventory-account'] = 'InventCon/inventory_account';
$route['inventory-reports'] = 'InventCon/inventory_reports';
$route['inventory-notif'] = 'InventCon/inventory_notif';

/* 
======================================
=============Inventory API Routes==============
======================================
 */
$route['api/inventory/get_items'] = 'api/Inventory_api/get_items';
$route['api/inventory/get_statistics'] = 'api/Inventory_api/get_statistics';
$route['api/inventory/add_item'] = 'api/Inventory_api/add_item';
$route['api/inventory/update_item/(:num)'] = 'api/Inventory_api/update_item/$1';
$route['api/inventory/delete_item/(:num)'] = 'api/Inventory_api/delete_item/$1';
$route['api/inventory/manage_stock/(:num)'] = 'api/Inventory_api/manage_stock/$1';
$route['api/inventory/get_activities'] = 'api/Inventory_api/get_activities';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

