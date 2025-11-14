<?php

if (!function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string|null  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    function __($key = null, $replace = [], $locale = null)
    {
        if (is_null($key)) {
            return '';
        }

        $locale = $locale ?: app()->getLocale();

        // Load translation file
        $translationFile = resource_path("lang/{$locale}.json");

        if (!file_exists($translationFile)) {
            return $key;
        }

        $translations = json_decode(file_get_contents($translationFile), true);

        $translation = $translations[$key] ?? $key;

        // Replace placeholders
        foreach ($replace as $search => $value) {
            $translation = str_replace(":{$search}", $value, $translation);
        }

        return $translation;
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format number as currency
     *
     * @param  float  $amount
     * @return string
     */
    function formatCurrency($amount)
    {
        return '$' . number_format($amount, 2);
    }
}

if (!function_exists('statusBadgeClass')) {
    /**
     * Get badge class for status
     *
     * @param  string  $status
     * @return string
     */
    function statusBadgeClass($status)
    {
        return match ($status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'maintenance' => 'bg-warning',
            'on_trip' => 'bg-info',
            'pending' => 'bg-warning',
            'assigned' => 'bg-info',
            'in_transit' => 'bg-primary',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary'
        };
    }
}

if (!function_exists('tripStatusBadge')) {
    /**
     * Get trip status badge HTML
     *
     * @param  string  $status
     * @return string
     */
    function tripStatusBadge($status)
    {
        $class = statusBadgeClass($status);
        $text = __($status);
        return "<span class='badge {$class}'>{$text}</span>";
    }
}
