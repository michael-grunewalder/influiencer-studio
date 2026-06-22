<?php

return [
    'key' => env('FAL_API_KEY', 'NO_FAL_API_KEY_SET'),
    'base_url' => env('FAL_API_URL', 'https://api.fal.ai/v1'),
    'queue_url' => env('FAL_API_QUEUE', 'https://queue.fal.run/fal-ai'),
    'account' => [
        'balance' => env('FAL_API_ACCOUNT_Balance', 'account/billing?expand=credits'),
        /*
        {
            "username": "a-cool-user",
            "credits": {
                "current_balance": 99.75,
                "currency": "USD"
            }
        }
        */
    ],
    'image' => [
        'seedream' => 'bytedance/seedream/v4.5/text-to-image',
        'seedream_edit' => 'bytedance/seedream/v4.5/text-to-image',
        'gpt1' => 'gpt-image-1.5',
        'gpt1_edit' => 'gpt-image-1.5/edit',
        'gpt2' => 'openai/gpt-image-2',
        'gpt2_edit' => 'openai/gpt-image-2/edit',
        'ideogram' => '',
    ],
];
