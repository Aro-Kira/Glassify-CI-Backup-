<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| PayMongo API Configuration
|--------------------------------------------------------------------------
|
| Configuration for PayMongo payment gateway integration.
|
| IMPORTANT: Replace these with your actual PayMongo API keys!
| 
| Get your keys from: https://dashboard.paymongo.com/
|
| For testing/development:
| - Use PayMongo Sandbox keys (starts with sk_test_ and pk_test_)
| - These keys do NOT process real payments
|
| For production:
| - Use PayMongo Live keys (starts with sk_live_ and pk_live_)
| - These keys process REAL payments
|
*/

// PayMongo Secret Key (Server-side operations)
// Set via environment variable PAYMONGO_SECRET_KEY, or use value below
$config['paymongo_secret_key'] = getenv('PAYMONGO_SECRET_KEY') ?: '';

// PayMongo Public Key (Client-side operations)
// Set via environment variable PAYMONGO_PUBLIC_KEY, or use value below
$config['paymongo_public_key'] = getenv('PAYMONGO_PUBLIC_KEY') ?: '';

/*
|--------------------------------------------------------------------------
| PayMongo API Base URL
|--------------------------------------------------------------------------
*/
$config['paymongo_api_base'] = 'https://api.paymongo.com/v1';

/* End of file paymongo.php */
/* Location: ./application/config/paymongo.php */
