<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel SSO Settings
     |--------------------------------------------------------------------------
     |
     | Set type of this web page. Possible options are: 'server' and 'broker'.
     |
     | You must specify either 'server' or 'broker'.
     |
     */

    'type' => env('SSO_SERVER_TYPE', 'broker'),
    'usernameColumn' => env('SSO_USERNAME_COLUMN', 'username'),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO server.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO server.
     |
     */

    'usersModel' => \App\Models\User::class,
    'brokersModel' => Zefy\LaravelSSO\Models\Broker::class,
    'userResource' => Zefy\LaravelSSO\Resources\UserResource::class,

    // Table used in Zefy\LaravelSSO\Models\Broker model
    'brokersTable' => 'brokers',

    // Logged in user fields sent to brokers.
    'userFields' => [
        // Return array field name => database column name
        'id' => 'id',
    ],

    // Logout other logged in devices when user successfully log in
    'logoutOtherDevices' => false,
    'ssoCacheTime' => env('SSO_CACHE_TIME', 60),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO broker.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO broker.
     |
     */

    'serverUrl' => env('SSO_SERVER_URL', null),
    'brokerName' => env('SSO_BROKER_NAME', null),
    'brokerSecret' => env('SSO_BROKER_SECRET', null),
];
