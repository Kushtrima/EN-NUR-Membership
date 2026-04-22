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

];
