<?php
if (! function_exists('format_number')) {
    function format_number($number, $decimals = 2, $decimal_separator = '.', $thousands_separator = '')
    {
        return number_format($number, $decimals, $decimal_separator, $thousands_separator);
    }
}

if (! function_exists('format_date')) {
    function format_date($date, $format = 'm/d/Y')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (! function_exists('convert_date_to_serial_number')) {
    function convert_date_to_serial_number($date)
    {
        return (25569 + (strtotime($date) / 86400));
    }
}
if (!function_exists('generate_random_string')) {
    function generate_random_string($length = 6) 
    {        
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('actual_discount')) {
    function actual_discount($first, $second)
    {
        if ($first > $second) {
            return $second;
        }
        return $first;
    }
}