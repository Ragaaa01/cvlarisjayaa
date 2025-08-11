<?php

return [
    'defaults' => [
        'guard' => 'web',
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
            'model' => App\Models\Akun::class,
        ],
    ],

    'passwords' => [
        'akuns' => [
            'provider' => 'akuns',
            'table' => 'password_reset_tokens', // Tidak digunakan karena pakai session
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];