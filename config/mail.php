<?php

return [
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'contato@assin.art'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Assinart')),
    ],

    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS') ?: null,
        'name' => env('MAIL_REPLY_TO_NAME') ?: env('APP_NAME', 'Assinart'),
    ],
];
