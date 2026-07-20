<?php

return [
    'provider' => env('AI_PROVIDER', 'yandex'),

    'providers' => [
        'yandex' => [
            'api_key' => env('YANDEX_AI_API_KEY'),
            'folder_id' => env('YANDEX_AI_FOLDER_ID'),
            'model' => env('YANDEX_AI_MODEL', 'yandexgpt-lite/latest'),
        ],

        'proxyapi' => [
            'api_key' => env('PROXYAPI_AI_API_KEY'),
            'model' => env('PROXYAPI_AI_MODEL', 'gpt-4o-mini'),
            'audio_model' => env('PROXYAPI_AUDIO_MODEL', 'gemini-3.5-flash'),
        ],
    ],
];
