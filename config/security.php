<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Override Key
    |--------------------------------------------------------------------------
    |
    | Secret required by the admin "create user without email" form
    | (AdminController::createUserWithoutEmail). Set via the
    | ADMIN_OVERRIDE_KEY environment variable. If unset, all submissions
    | are rejected — fail-closed.
    |
    */

    'admin_override_key' => env('ADMIN_OVERRIDE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Seed / Setup Passwords
    |--------------------------------------------------------------------------
    |
    | Used by console commands and admin "setup expired memberships" tool
    | to initialise known accounts on a fresh install. Must be set via env
    | on any real environment; 'change-me' defaults are present only so
    | local dev without env configured still works (the created accounts
    | are immediately unusable because everyone knows the default).
    |
    | Access pattern: config('security.super_admin_password') etc.
    | Do NOT call env() for these outside config files — env() returns
    | null once php artisan config:cache has run in production.
    |
    */

    'super_admin_password'   => env('SUPER_ADMIN_PASSWORD', 'change-me'),
    'test_user_password'     => env('TEST_USER_PASSWORD', 'change-me'),
    'user_correct_password'  => env('USER_CORRECT_PASSWORD', 'change-me'),

    /*
    |--------------------------------------------------------------------------
    | Operational Addresses
    |--------------------------------------------------------------------------
    */

    'admin_email'            => env('ADMIN_EMAIL'),
    'mail_reply_to_address'  => env('MAIL_REPLY_TO_ADDRESS'),

];
