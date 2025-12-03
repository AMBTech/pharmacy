<?php

use Carbon\Carbon;

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = null)
    {
        if ($amount === null) {
            return "0.00";
        }

        // Get currency from system settings if not provided
        if ($currency === null) {
            $settings = \App\Models\SystemSetting::getSettings();
            $currency = $settings->currency ?? 'PKR';
        }

        // Convert currency codes to symbols
        $currencySymbol = match($currency) {
            'PKR' => 'Rs.',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            default => $currency
        };

        return $currencySymbol . ' ' . number_format((float)$amount, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd M, Y')
    {
        if (empty($date)) {
            return null;
        }

        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('format_date_time')) {
    function format_date_time($dateTime, $format = 'd M, Y h:i A')
    {
        if (empty($dateTime)) {
            return null;
        }

        return Carbon::parse($dateTime)->format($format);
    }
}

if (!function_exists('format_number')) {
    function format_number($value, $decimals = 2)
    {
        return number_format((float)$value, $decimals);
    }
}

if (!function_exists('short_id')) {
    function short_id($value, $length = 8)
    {
        return substr(md5($value), 0, $length);
    }
}

if (!function_exists('percent')) {
    function percent($value, $total, $decimals = 2)
    {
        if ($total == 0) return "0%";

        return number_format(($value / $total) * 100, $decimals) . "%";
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currency = null)
    {
        // Get currency from system settings if not provided
        if ($currency === null) {
            $settings = \App\Models\SystemSetting::getSettings();
            $currency = $settings->currency ?? 'PKR';
        }

        // Convert currency codes to symbols
        return match($currency) {
            'PKR' => 'Rs.',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            default => $currency
        };
    }
}

if (!function_exists('is_active_route')) {
    function is_active_route($route, $activeClass = 'active')
    {
        return request()->routeIs($route) ? $activeClass : '';
    }
}

if (!function_exists('system_access')) {
    function system_access($role)
    {
        return match ($role) {
            'admin' => 'Admin Access',
            'manager' => 'Manager Access',
            'cashier' => 'Cashier Access',
            default => '',
        };
    }
}
