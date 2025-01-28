<?php

return [
    // ... configuração existente ...

    'channels' => [
        // ... outros canais ...

        'activity' => [
            'driver' => 'daily',
            'path' => storage_path('logs/activity.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'warning',
            'days' => 90,
        ],

        'payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payments.log'),
            'level' => 'info',
            'days' => 365,
        ],

        'errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => 'error',
            'days' => 30,
        ],

        'access' => [
            'driver' => 'daily',
            'path' => storage_path('logs/access.log'),
            'level' => 'info',
            'days' => 14,
        ],
    ],
]; 