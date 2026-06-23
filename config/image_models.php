<?php

return [
    'default' => 'ideogram',

    'models' => [
        'ideogram' => [
            'name' => 'Ideogram v4',
            'model' => 'ideogram/v4',
            'model_edit' => 'ideogram/v4/image-to-image',
            'image_sizes' => [
                'square_hd' => 'imagesizes.square_hd',
                'portrait_4_3' => 'imagesizes.portrait_4_3',
                'portrait_16_9' => 'imagesizes.portrait_16_9',
                'landscape_4_3' => 'imagesizes.landscape_4_3',
                'landscape_16_9' => 'imagesizes.landscape_16_9',
            ],
            'default_size' => 'portrait_4_3',
            'prompt_style' => 'detailed',
        ],
        'seedream' => [
            'name' => 'Seedream 4.5',
            'model' => 'bytedance/seedream/v4.5/text-to-image',
            'model_edit' => 'bytedance/seedream/v4.5/edit',
            'image_sizes' => [
                'square_hd' => 'imagesizes.square_hd',
                'square' => 'imagesizes.square',
                'portrait_4_3' => 'imagesizes.portrait_4_3',
                'portrait_16_9' => 'imagesizes.portrait_16_9',
                'landscape_4_3' => 'imagesizes.landscape_4_3',
                'landscape_16_9' => 'imagesizes.landscape_16_9',
                'auto' => 'imagesizes.auto',
                'auto_2K' => 'imagesizes.auto2K',
                'auto_4K' => 'imagesizes.auto4K',
            ],
            'default_size' => 'portrait_4_3',
            'prompt_style' => 'flux',
        ],
        'gpt1' => [
            'name' => 'gpt 1.5',
            'model' => 'gpt-image-1.5',
            'model_edit' => 'gpt-image-1.5/edit',
            'image_sizes' => [
                'auto' => 'imagesize.auto',
                '1024x1024' => 'imagesiz.square',
                '1536x1024' => 'imagesize.landscape_3_2',
                '1024x1536' => 'imagesize.portrait_3_2',
            ],
            'default_size' => '1024x1536',
            'prompt_style' => 'simple',
        ],
        'gpt2' => [
            'name' => 'gpt 4',
            'model' => 'openai/gpt-image-2',
            'model_edit' => 'openai/gpt-image-2/edit',
            'image_sizes' => [
                'square_hd' => 'imagesizes.square_hd',
                'square' => 'imagesizes.square',
                'portrait_4_3' => 'imagesizes.portrait_4_3',
                'portrait_16_9' => 'imagesizes.portrait_16_9',
                'landscape_4_3' => 'imagesizes.landscape_4_3',
                'landscape_16_9' => 'imagesizes.landscape_16_9',
                'auto' => 'imagesizes.auto',
            ],
            'default_size' => 'portrait_16_9',
            'prompt_style' => 'simple',
        ],
    ],
];
