<?php

return [
    'provider' => env('AI_PROVIDER', 'yandex'),

    'providers' => [
        'yandex' => [
            'api_key' => env('YANDEX_AI_API_KEY'),
            'folder_id' => env('YANDEX_AI_FOLDER_ID'),
            'model' => env('YANDEX_AI_MODEL', 'yandexgpt-lite/latest'),
        ],
    ],
];
