<?php

use Laravel\Telescope\Http\Middleware\Authorize;
use Laravel\Telescope\Watchers;

return [

    /*
    |--------------------------------------------------------------------------
    | Telescope Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Telescope watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Telescope data storage.
    |
    */

    'enabled' => env('TELESCOPE_ENABLED', env('APP_ENV') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Telescope will be accessible from. If the
    | setting is null, Telescope will reside under the same domain as the
    | application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('TELESCOPE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Telescope will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('TELESCOPE_PATH', 'admin/telescope'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Telescope's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Queue
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the queue connection and queue
    | which will be used to process ProcessPendingUpdate jobs. This can
    | be changed if you would prefer to use a non-default connection.
    |
    */

    'queue' => [
        'connection' => env('TELESCOPE_QUEUE_CONNECTION', null),
        'queue' => env('TELESCOPE_QUEUE', null),
        'delay' => env('TELESCOPE_QUEUE_DELAY', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Telescope route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web',
        'auth', // Ensure user is authenticated
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed / Ignored Paths & Commands
    |--------------------------------------------------------------------------
    |
    | The following array lists the URI paths and Artisan commands that will
    | not be watched by Telescope. In addition to this list, some Laravel
    | commands, like migrations and queue commands, are always ignored.
    |
    */

    'only_paths' => [
        // Focus on important areas for membership system
        'admin/*',
        'payments/*',
        'api/*',
        'webhook/*',
        'dashboard',
        'login',
        'register',
    ],

    'ignore_paths' => [
        'livewire*',
        'nova-api*',
        'pulse*',
        'telescope*', // Don't watch Telescope itself
        '_debugbar*',
        'assets/*',
        'images/*',
        'css/*',
        'js/*',
    ],

    'ignore_commands' => [
        // Ignore routine commands that create noise
        'schedule:run',
        'queue:work',
        'queue:restart',
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | Telescope. The watchers gather the application's profile data when
    | a request or task is executed. Feel free to customize this list.
    |
    */

    'watchers' => [
        // Essential for debugging async operations
        Watchers\BatchWatcher::class => env('TELESCOPE_BATCH_WATCHER', true),

        // Cache monitoring for performance
        Watchers\CacheWatcher::class => [
            'enabled' => env('TELESCOPE_CACHE_WATCHER', true),
            'hidden' => [],
            'ignore' => ['telescope:*'],
        ],

        // Monitor external API calls (Stripe, PayPal)
        Watchers\ClientRequestWatcher::class => env('TELESCOPE_CLIENT_REQUEST_WATCHER', true),

        // Monitor artisan commands
        Watchers\CommandWatcher::class => [
            'enabled' => env('TELESCOPE_COMMAND_WATCHER', true),
            'ignore' => [
                'schedule:run',
                'queue:work',
            ],
        ],

        // Debug dumps - useful during development
        Watchers\DumpWatcher::class => [
            'enabled' => env('TELESCOPE_DUMP_WATCHER', env('APP_ENV') !== 'production'),
            'always' => env('TELESCOPE_DUMP_WATCHER_ALWAYS', false),
        ],

        // Event monitoring for membership events
        Watchers\EventWatcher::class => [
            'enabled' => env('TELESCOPE_EVENT_WATCHER', true),
            'ignore' => [
                'Illuminate\Auth\Events\Login',
                'Illuminate\Auth\Events\Logout',
            ],
        ],

        // Critical for payment system error tracking
        Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),

        // Monitor authorization gates
        Watchers\GateWatcher::class => [
            'enabled' => env('TELESCOPE_GATE_WATCHER', true),
            'ignore_abilities' => [],
            'ignore_packages' => true,
            'ignore_paths' => [],
        ],

        // Monitor background jobs (payment processing)
        Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),

        // Log monitoring - focus on errors and warnings
        Watchers\LogWatcher::class => [
            'enabled' => env('TELESCOPE_LOG_WATCHER', true),
            'level' => env('TELESCOPE_LOG_LEVEL', 'warning'),
        ],

        // Email monitoring (receipts, notifications)
        Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),

        // Model monitoring for User, Payment, MembershipRenewal
        Watchers\ModelWatcher::class => [
            'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
            'events' => ['eloquent.*'],
            'hydrations' => true,
        ],

        // Notification monitoring
        Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),

        // Critical for database performance monitoring
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'ignore_packages' => true,
            'ignore_paths' => [
                'telescope*',
            ],
            'slow' => env('TELESCOPE_SLOW_QUERY_THRESHOLD', 50), // 50ms threshold
        ],

        // Redis monitoring if used
        Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', false),

        // HTTP request monitoring
        Watchers\RequestWatcher::class => [
            'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
            'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
            'ignore_http_methods' => [],
            'ignore_status_codes' => [
                404, // Don't log 404s
            ],
        ],

        // Schedule monitoring for membership renewals
        Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
        
        // View monitoring for performance
        Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', env('APP_ENV') === 'local'),
    ],
];
