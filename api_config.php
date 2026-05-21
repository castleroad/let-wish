<?php
return [
    'google' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'api_url' => env('GOOGLE_API_URL'),
    ],
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL'),
    ],
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'api_url' => env('ANTHROPIC_API_URL'),
    ],
];
