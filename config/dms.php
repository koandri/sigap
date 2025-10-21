<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Management System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Document Management
    | System (DMS) module.
    |
    */

    'onlyoffice' => [
        'server_url' => env('ONLYOFFICE_SERVER_URL', 'https://nextcloud.suryagroup.app/onlyoffice'),
        'callback_url' => env('ONLYOFFICE_CALLBACK_URL', env('APP_URL') . '/document-versions/onlyoffice-callback'),
        'secret' => env('ONLYOFFICE_SECRET', ''),
        'jwt_enabled' => env('ONLYOFFICE_JWT_ENABLED', true),
    ],

    'storage' => [
        'disk' => env('DMS_STORAGE_DISK', 's3'),
        'path' => env('DMS_STORAGE_PATH', 'documents'),
    ],

    'watermark' => [
        'enabled' => env('DMS_WATERMARK_ENABLED', true),
        'company_name' => env('DMS_WATERMARK_COMPANY', 'PT. Surya Inti Aneka Pangan'),
        'opacity' => env('DMS_WATERMARK_OPACITY', 0.3),
        'font_size' => env('DMS_WATERMARK_FONT_SIZE', 24),
    ],

    'form_requests' => [
        'deadline_hour' => env('DMS_FORM_DEADLINE_HOUR', 16), // 4 PM
        'deadline_day' => env('DMS_FORM_DEADLINE_DAY', 4), // Thursday (0=Sunday, 4=Thursday)
        'distribution_hour' => env('DMS_FORM_DISTRIBUTION_HOUR', 11), // 11 AM
        'distribution_day' => env('DMS_FORM_DISTRIBUTION_DAY', 5), // Friday
        'sla_acknowledgment_hours' => env('DMS_SLA_ACKNOWLEDGMENT_HOURS', 2),
        'sla_scanning_hours' => env('DMS_SLA_SCANNING_HOURS', 2),
    ],

    'access_control' => [
        'default_expiry_days' => env('DMS_ACCESS_DEFAULT_EXPIRY_DAYS', 30),
        'max_expiry_days' => env('DMS_ACCESS_MAX_EXPIRY_DAYS', 90),
    ],

    'notifications' => [
        'enabled' => env('DMS_NOTIFICATIONS_ENABLED', true),
        'channels' => ['database', 'mail'], // Add 'whatsapp', 'pushover' as needed
    ],

    'search' => [
        'enabled' => env('DMS_SEARCH_ENABLED', false),
        'driver' => env('DMS_SEARCH_DRIVER', 'database'), // 'database', 'meilisearch', 'typesense'
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
            'key' => env('MEILISEARCH_KEY', ''),
        ],
        'typesense' => [
            'host' => env('TYPESENSE_HOST', 'http://localhost:8108'),
            'key' => env('TYPESENSE_KEY', ''),
        ],
    ],
];
