<?php

return [
    'defaults' => [
        'guard' => 'web',
        'api',
        'passwords' => 'akuns',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'akuns',
        ],
    ],

    'providers' => [
        'akuns' => [
            'driver' => 'eloquent',
            'model' => App\Models\Akun::class, // Pastikan path ini benar
        ],
    ],

    'passwords' => [
        'akuns' => [
            'provider' => 'akuns',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
