<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

/**
 * Account Verification Hook
 * Checks if logged-in user's account still exists
 * Logs out user automatically if account was deleted by admin
 */
$hook['post_controller_constructor'] = array(
    'class'    => 'AccountVerification',
    'function' => 'verify_account',
    'filename' => 'AccountVerification.php',
    'filepath' => 'hooks',
    'params'   => array()
);
