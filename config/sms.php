<?php

return [
    'driver' => env('SMS_DRIVER', 'sms.ru'),

    'drivers' => [
        'sms.ru' => [
            'api_id' => env('SMS_RU_APP_ID')
        ],
    ],
];