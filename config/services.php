<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
     */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'googlekey' => [
        'ApiKey' => env('GMAPS_API_KEY'),
    ],

    'var' => [
        'name' => env('APP_NAME', 'Laravel'),
        'org' => env('APP_ORG', 'Organization'),
        'url' => env('APP_URL', 'http://localhost'),
    ],

    'mysql' => [
        'db_connection' => env('DB_CONNECTION', 'mysql'),
        'db_host' => env('DB_HOST', '127.0.0.1'),
        'db_port' => env('DB_PORT', '3306'),
        'db_database' => env('DB_DATABASE'),
        'db_username' => env('DB_USERNAME', 'root'),
        'db_password' => env('DB_PASSWORD'),
        'db_mysql_path' => env('DB_MYSQL_PATH', 'local'),
    ],

    'gmail' => [
        'from' => env('MAIL_USERNAME'),
        'username' => env('MAIL_USERNAME', 'duroflex.reports@gmail.com'),
        'password' => env('MAIL_PASSWORD', 'pioazjeszkpumwuf'),
    ],

];
