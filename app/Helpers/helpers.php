<?php
if (!function_exists('normalizePhone')) {
    /**
     *
     * @param string $phone The phone number to normalize.
     * @param string $prefix The prefix to remove (e.g., '+880').
     * @return string The normalized phone number.
     */
    function normalizePhone(string $phone, string $prefix = '+88'): string
    {
        if (strpos($phone, $prefix) === 0) {
            return substr($phone, strlen($prefix));
        }
        return $phone;
    }
}
