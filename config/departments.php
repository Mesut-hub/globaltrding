<?php

return [
    'admin' => [
        'inbox' => env('MAIL_ADMIN_INBOX', 'info@globaltrding.com'),
        'name'  => env('MAIL_ADMIN_INBOX_NAME', 'Global Trading'),
    ],

    'inquiry' => [
        'from'      => env('MAIL_FROM_INQUIRY', 'inquiry@globaltrding.com'),
        'from_name' => env('MAIL_FROM_INQUIRY_NAME', 'Global Trading Inquiry'),
        'reply_to'  => env('MAIL_REPLY_TO_DEFAULT', env('MAIL_FROM_ADDRESS', 'info@globaltrding.com')),
    ],

    'collaboration' => [
        'from'      => env('MAIL_FROM_COLLABORATION', 'collaboration@globaltrding.com'),
        'from_name' => env('MAIL_FROM_COLLABORATION_NAME', 'Global Trading Collaboration'),
        'reply_to'  => env('MAIL_REPLY_TO_DEFAULT', env('MAIL_FROM_ADDRESS', 'info@globaltrding.com')),
    ],

    'products_url' => env('PUBLIC_PRODUCTS_URL', env('APP_URL', 'https://globaltrding.com') . '/en/products'),
];