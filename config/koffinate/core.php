<?php

return [
    'model' => [
        'users' => config('auth.providers.users.model'),

        /**
         * The user key type.
         * possible values: int, uuid.
         */
        'user_key_type' => 'int',

        /**
         * The model to be used performer.
         * affected field are created_by, updated_by, deleted_by
         * possible values: true, false.
         */
        'use_perform_by' => true,   // true, false; affected field are created_by, updated_by, deleted_by
    ],

    'url' => [
        'document' => env('DOCUMENT_URL', ''),
        'vendor' => env('VENDOR_URL', ''),
    ],

    'breeze' => [
        /**
         * The view to be used for serve on breeze.
         * possible values: blade, inertia.
         */
        'type' => 'blade',
    ],

    'fake_mail_domain' => env('FAKE_MAIL_DOMAIN', 'localdomain'),

    'plugins' => [
        'config_path' => 'koffinate.plugins',
        'public_path' => 'plugins', // without /public
    ],
];
