<?php

return [
    'default' => 'ideogram',

    'models' => [
        'ideogram' => [
            'name' => 'Ideogram v4',
            'model' => 'ideogram/v4',
            'model_edit' => 'ideogram/v4/image-to-image',
            'default_size' => [
                'width' => 768,
                'height' => 1024,
            ],
            'prompt_style' => 'detailed',
        ],
        'seedream' => [
            'name' => 'Seedream 4.5',
            'model' => 'bytedance/seedream/v4.5/text-to-image',
            'model_edit' => 'bytedance/seedream/v4.5/edit',
            'default_size' => [
                'width' => 768,
                'height' => 1024,
            ],
            'prompt_style' => 'flux',
        ],
        'gpt1' => [
            'name' => 'gpt 1.5',
            'model' => 'gpt-image-1.5',
            'model_edit' => 'gpt-image-1.5/edit',
            'default_size' => [
                'width' => 768,
                'height' => 1024,
            ],
            'prompt_style' => 'simple',
        ],
        'gpt2' => [
            'name' => 'gpt 4',
            'model' => 'openai/gpt-image-2',
            'model_edit' => 'openai/gpt-image-2/edit',
            'default_size' => [
                'width' => 768,
                'height' => 1024,
            ],
            'prompt_style' => 'simple',
        ],
    ],
];
