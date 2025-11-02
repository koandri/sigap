<?php

use Illuminate\Support\Str;

use App\Models\FormSubmission;

if (!function_exists('areActiveRoutes')) {
    /**
     * Check if the current route matches any of the given route names/patterns.
     *
     * @param array|string $routes
     * @param string $output
     * @return string
     */
    function areActiveRoutes($routes, $output = "active")
    {
        if (request()->routeIs($routes)) {
            return $output;
        }
        return '';
    }
}

if (!function_exists('areOpenRoutes')) {
    /**
     * Check if the current route matches any of the given route names/patterns
     * and return menu-open for dropdown menus.
     *
     * @param array|string $routes
     * @param string $output
     * @return string
     */
    function areOpenRoutes($routes, $output = "show")
    {
        if (request()->routeIs($routes)) {
            return $output;
        }
        return '';
    }
}

if (! function_exists('generateInitials')) {
    function generateInitials($name) {
        $arr = explode(" ", $name);

        $counter = 1;

        foreach ($arr as $key => $value) {
            if ($counter < 3) {
                $arr2[] = Str::take($value, 1);
            }
            
            $counter++;
        }

        $initials = implode(" ", $arr2);

        return $initials;
    }
}

if (! function_exists('formatBoolean')) {
    function formatBoolean($value){
        if ($value == true) {
            return "Yes";
        }
        else {
            return "No";
        }
    }
}

if (! function_exists('validateMobileNumber')) {
    function validateMobileNumber($number) {
        // Remove all non-numeric characters from the string
        $number = preg_replace('/\D+/', '', $number);

        // If the number starts with '620', remove it
        $number = Str::replaceStart('620', '62', $number);

        // If the number starts with '0', remove it, and replace with 62
        $number = Str::replaceStart('0', '62', $number);
        
        // Add '@c.us' at the end of the number
        return $number . '@c.us';
    }
}

if (! function_exists('sanitize')) {
    function sanitize($value) {
        return is_string($value) ? strip_tags(trim($value)) : $value; // Basic sanitasi, tambah regex kalau perlu
    }
}

if (! function_exists('formatDate')) {
    /**
     * Format a date/datetime to Asia/Jakarta timezone
     *
     * @param \Carbon\Carbon|\DateTime|string|null $date
     * @param string $format
     * @return string
     */
    function formatDate($date, string $format = 'Y-m-d H:i'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->timezone('Asia/Jakarta')->format($format);
    }
}