<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PayMongo API Library
 * Handles all PayMongo API interactions for payment processing
 */
class Paymongo
{
    private $secret_key;
    private $public_key;
    private $api_base = 'https://api.paymongo.com/v1';
    
    public function __construct()
    {
        // Load PayMongo config
        $CI =& get_instance();
        $CI->config->load('paymongo', true);
        
        // Get keys from config (with fallback to environment variables)
        $this->secret_key = $CI->config->item('paymongo_secret_key', 'paymongo');
        $this->public_key = $CI->config->item('paymongo_public_key', 'paymongo');
        $api_base = $CI->config->item('paymongo_api_base', 'paymongo');
        
        if ($api_base) {
            $this->api_base = $api_base;
        }
        
        // Validate that keys are set (not placeholders)
        if (strpos($this->secret_key, 'YOUR_SECRET_KEY_HERE') !== false) {
            log_message('error', 'PayMongo: Secret key is not configured! Please set it in application/config/paymongo.php');
        }
    }
    
    /**
     * Make API request to PayMongo
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $data Request data
     * @param bool $use_secret_key Use secret key (true) or public key (false)
     * @return array Response data
     */
    private function make_request($endpoint, $method = 'GET', $data = null, $use_secret_key = true)
    {
        // Validate API key before making request
        $key = $use_secret_key ? $this->secret_key : $this->public_key;
        if (empty($key) || strpos($key, 'YOUR_SECRET_KEY_HERE') !== false || strpos($key, 'YOUR_PUBLIC_KEY_HERE') !== false) {
            $key_type = $use_secret_key ? 'Secret' : 'Public';
            $error_msg = "PayMongo {$key_type} Key is not configured. Please set it in application/config/paymongo.php";
            log_message('error', $error_msg);
            return ['success' => false, 'error' => $error_msg];
        }
        
        $url = $this->api_base . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($key . ':')
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            log_message('error', 'PayMongo API Error: ' . $error);
            return ['success' => false, 'error' => $error];
        }
        
        $response_data = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return ['success' => true, 'data' => $response_data];
        } else {
            $error_message = isset($response_data['errors'][0]['detail']) 
                ? $response_data['errors'][0]['detail'] 
                : 'Unknown PayMongo API error';
            log_message('error', 'PayMongo API Error (HTTP ' . $http_code . '): ' . $error_message);
            return ['success' => false, 'error' => $error_message, 'http_code' => $http_code];
        }
    }
    
    /**
     * Create a payment intent
     * 
     * @param float $amount Amount in PHP (will be converted to centavos)
     * @param string $payment_method_allowed Payment method type (card, paymaya, gcash)
     * @param string $description Order description
     * @param array $metadata Additional metadata
     * @return array Payment intent data
     */
    public function create_payment_intent($amount, $payment_method_allowed = 'card', $description = '', $metadata = [])
    {
        // Convert PHP amount to centavos
        $amount_in_centavos = (int)round($amount * 100);
        
        // Map payment method to PayMongo format
        $payment_method_types = [];
        if ($payment_method_allowed === 'card') {
            $payment_method_types = ['card'];
        } elseif ($payment_method_allowed === 'gcash') {
            $payment_method_types = ['gcash'];
        } elseif ($payment_method_allowed === 'maya') {
            $payment_method_types = ['paymaya'];
        } elseif ($payment_method_allowed === 'ewallet') {
            // For e-wallet, allow both GCash and Maya
            $payment_method_types = ['gcash', 'paymaya'];
        } else {
            // Default: allow both card and e-wallets
            $payment_method_types = ['card', 'gcash', 'paymaya'];
        }
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $amount_in_centavos,
                    'currency' => 'PHP',
                    'payment_method_allowed' => $payment_method_types,
                    'description' => $description
                ]
            ]
        ];
        
        if (!empty($metadata) && is_array($metadata)) {
            // PayMongo requires metadata to be flat key => string. Sanitize values.
            $flat = [];
            foreach ($metadata as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    // convert nested structures to JSON string
                    $flat[$k] = json_encode($v);
                } elseif (is_null($v)) {
                    // skip nulls to avoid sending nested/empty values
                    continue;
                } else {
                    $flat[$k] = (string)$v;
                }
            }
            if (!empty($flat)) {
                $data['data']['attributes']['metadata'] = $flat;
            }
        }
        
        $response = $this->make_request('/payment_intents', 'POST', $data, true);
        
        if ($response['success'] && isset($response['data']['data'])) {
            return [
                'success' => true,
                'payment_intent_id' => $response['data']['data']['id'],
                'client_key' => $response['data']['data']['attributes']['client_key'],
                'status' => $response['data']['data']['attributes']['status']
            ];
        }
        
        return $response;
    }
    
    /**
     * Retrieve a payment intent
     * 
     * @param string $payment_intent_id Payment intent ID
     * @return array Payment intent data
     */
    public function retrieve_payment_intent($payment_intent_id)
    {
        $response = $this->make_request('/payment_intents/' . $payment_intent_id, 'GET', null, true);
        
        if ($response['success'] && isset($response['data']['data'])) {
            $pi = $response['data']['data'];
            $payment_id = null;

            // Try common locations for the associated payment id (PayMongo responses vary)
            if (isset($pi['attributes']['payments']['data'][0]['id'])) {
                $payment_id = $pi['attributes']['payments']['data'][0]['id'];
            } elseif (isset($pi['relationships']['payments']['data'][0]['id'])) {
                $payment_id = $pi['relationships']['payments']['data'][0]['id'];
            } elseif (isset($pi['attributes']['charges']['data'][0]['id'])) {
                $payment_id = $pi['attributes']['charges']['data'][0]['id'];
            }

            return [
                'success' => true,
                'payment_intent' => $pi,
                'status' => $pi['attributes']['status'],
                'payment_id' => $payment_id
            ];
        }
        
        return $response;
    }
    
    /**
     * Attach payment method to payment intent
     * 
     * @param string $payment_intent_id Payment intent ID
     * @param string $payment_method_id Payment method ID
     * @param string $return_url Return URL for e-wallet redirects
     * @return array Attach response data
     */
    public function attach_payment_method($payment_intent_id, $payment_method_id, $return_url = '')
    {
        $data = [
            'data' => [
                'attributes' => [
                    'payment_method' => $payment_method_id
                ]
            ]
        ];
        
        if (!empty($return_url)) {
            $data['data']['attributes']['return_url'] = $return_url;
        }
        
        $response = $this->make_request('/payment_intents/' . $payment_intent_id . '/attach', 'POST', $data, true);
        
        if ($response['success'] && isset($response['data']['data'])) {
            $pi = $response['data']['data'];
            $payment_id = null;

            if (isset($pi['attributes']['payments']['data'][0]['id'])) {
                $payment_id = $pi['attributes']['payments']['data'][0]['id'];
            } elseif (isset($pi['relationships']['payments']['data'][0]['id'])) {
                $payment_id = $pi['relationships']['payments']['data'][0]['id'];
            } elseif (isset($pi['attributes']['charges']['data'][0]['id'])) {
                $payment_id = $pi['attributes']['charges']['data'][0]['id'];
            }

            return [
                'success' => true,
                'payment_intent' => $pi,
                'status' => $pi['attributes']['status'],
                'next_action' => isset($pi['attributes']['next_action']) ? $pi['attributes']['next_action'] : null,
                'payment_id' => $payment_id
            ];
        }
        
        return $response;
    }
    
    /**
     * Get public key for frontend use
     * 
     * @return string Public key
     */
    public function get_public_key()
    {
        return $this->public_key;
    }

    /**
     * Retrieve a PayMongo Payment resource
     * @param string $payment_id
     * @return array
     */
    public function retrieve_payment($payment_id)
    {
        $response = $this->make_request('/payments/' . $payment_id, 'GET', null, true);

        if ($response['success'] && isset($response['data']['data'])) {
            return ['success' => true, 'payment' => $response['data']['data']];
        }

        return $response;
    }
}
