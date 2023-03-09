<?php

return [
    'model' => [
        'users' => config('auth.providers.users.model'),
        'user_key_type' => 'int',    // int, uuid
    ],

    'url' => [
        'document' => env('DOCUMENT_URL', ''),
        'vendor' => env('VENDOR_URL', ''),
    ],

    'fake_mail_domain' => env('FAKE_MAIL_DOMAIN', 'localdomain'),

    'plugins' => [
        'config_path' => 'koffinate.plugins',
        'public_path' => 'plugins', // without /public
    ],
];
