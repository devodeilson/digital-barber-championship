<?php

return [
    'backup' => [
        'name' => env('APP_NAME', 'Laravel') . ' Backup',
        
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('logs'),
                ],
            ],
            'databases' => [
                'mysql',
            ],
        ],

        'destination' => [
            'disks' => [
                'local',
                's3',
            ],
            
            'filename_prefix' => 'backup-',
            'timestamp' => true,
        ],
        
        'temporary_directory' => storage_path('app/backup-temp'),
    ],

    'notifications' => [
        'notifications' => [
            \App\Notifications\BackupWasSuccessful::class => ['mail'],
            \App\Notifications\BackupHasFailed::class => ['mail'],
            \App\Notifications\CleanupWasSuccessful::class => ['mail'],
            \App\Notifications\CleanupHasFailed::class => ['mail'],
            \App\Notifications\HealthyBackupWasFound::class => ['mail'],
            \App\Notifications\UnhealthyBackupWasFound::class => ['mail'],
        ],

        'notifiable' => \App\Models\User::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL'),
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'Laravel') . ' Backup',
            'disks' => ['local', 's3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 30,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
]; 