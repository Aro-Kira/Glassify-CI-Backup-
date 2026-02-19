<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Address helper for consistent formatting across views
 */
if (!function_exists('format_address_lines')) {
    function format_address_lines($addr)
    {
        // Accept object or array
        if (is_object($addr)) {
            $a = (array) $addr;
        } elseif (is_array($addr)) {
            $a = $addr;
        } else {
            return ['', ''];
        }

        // Normalize keys (support different naming used in DB)
        $unit = trim($a['UnitHouseNumber'] ?? $a['Unit'] ?? $a['unit_house_number'] ?? '');
        $street = trim($a['Street'] ?? $a['street'] ?? '');
        $sub = trim($a['Subdivision'] ?? $a['subdivision'] ?? '');
        $barangay = trim($a['Barangay'] ?? $a['barangay'] ?? '');
        $city = trim($a['City'] ?? $a['city'] ?? '');
        $province = trim($a['Province'] ?? $a['province'] ?? '');
        $region = trim($a['Region'] ?? $a['region'] ?? '');
        $zip = trim($a['ZipCode'] ?? $a['zipcode'] ?? $a['postal_code'] ?? '');
        $country = trim($a['Country'] ?? $a['country'] ?? 'Philippines');

        $line1Parts = array_filter([$unit, $street, $sub]);
        $line2Parts = array_filter([$barangay, $city, $province, $region, $zip, $country]);

        $line1 = $line1Parts ? implode(', ', $line1Parts) : '';
        $line2 = $line2Parts ? implode(', ', $line2Parts) : '';

        return [$line1, $line2];
    }
}

if (!function_exists('format_address_html')) {
    function format_address_html($addr)
    {
        list($l1, $l2) = format_address_lines($addr);
        $escape = function($s) {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };
        if ($l1 && $l2) {
            return $escape($l1) . '<br>' . $escape($l2);
        }
        if ($l1) return $escape($l1);
        if ($l2) return $escape($l2);
        return '';
    }

    if (!function_exists('format_address_three_lines')) {
        function format_address_three_lines($addr)
        {
            if (is_object($addr)) $a = (array)$addr;
            elseif (is_array($addr)) $a = $addr;
            else return ['', '', ''];

            $unit = trim($a['UnitHouseNumber'] ?? $a['Unit'] ?? $a['unit_house_number'] ?? '');
            $street = trim($a['Street'] ?? $a['street'] ?? $a['AddressLine'] ?? '');
            $sub = trim($a['Subdivision'] ?? $a['subdivision'] ?? '');
            $barangay = trim($a['Barangay'] ?? $a['barangay'] ?? '');
            $city = trim($a['City'] ?? $a['city'] ?? '');
            $province = trim($a['Province'] ?? $a['province'] ?? '');
            $region = trim($a['Region'] ?? $a['region'] ?? '');
            $zip = trim($a['ZipCode'] ?? $a['zipcode'] ?? $a['postal_code'] ?? '');
            $country = trim($a['Country'] ?? $a['country'] ?? '');

            // Line 1: Unit/House Number, Street, Subdivision
            $line1 = implode(', ', array_filter([$unit, $street, $sub]));
            // Line 2: Barangay, City/Municipality, State/Province, Region
            $line2 = implode(', ', array_filter([$barangay, $city, $province, $region]));
            // Line 3: Postal Code
            // Line 4: Country
            $line3 = $zip ?: '';
            $line4 = $country ?: '';

            return [$line1, $line2, $line3, $line4];
        }
    }

    if (!function_exists('format_address_three_html')) {
        function format_address_three_html($addr)
        {
            list($l1, $l2, $l3, $l4) = format_address_three_lines($addr);
            $escape = function($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
            $out = [];
            if ($l1) $out[] = $escape($l1);
            if ($l2) $out[] = $escape($l2);
            if ($l3) $out[] = $escape($l3);
            if ($l4) $out[] = $escape($l4);
            return implode('<br>', $out);
        }
    }

    if (!function_exists('format_display_phone')) {
        function format_display_phone($phone)
        {
            $p = trim((string)$phone);
            if ($p === '') return '';
            // remove spaces and common separators
            $normalized = preg_replace('/[\s\-\(\)]/', '', $p);
            // If starts with +63, remove a following 0 if present
            if (strpos($normalized, '+63') === 0) {
                $after = substr($normalized, 3);
                if (strpos($after, '0') === 0) $after = substr($after, 1);
                return '(+63) ' . $after;
            }
            // If starts with 63 (no plus)
            if (strpos($normalized, '63') === 0) {
                $after = substr($normalized, 2);
                if (strpos($after, '0') === 0) $after = substr($after, 1);
                return '(+63) ' . $after;
            }
            // If starts with 0 and looks like a Philippine number, convert to (+63) and drop leading 0
            if (strpos($normalized, '0') === 0 && preg_match('/^0\d{9,10}$/', $normalized)) {
                return '(+63) ' . substr($normalized, 1);
            }
            // Fallback: return original trimmed phone
            return htmlspecialchars($p, ENT_QUOTES, 'UTF-8');
        }
    }
}
