<?php

use App\Services\ClaudeService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('system prompt builds correctly for seedream model', function () {
    $systemPrompt = ClaudeService::buildSystemPrompt('fal-ai/bytedance/seedream/v4.5/text-to-image');

    expect($systemPrompt)->toContain('Seedream 4.5')
        ->toContain('Subject + Style + Composition + Lighting + Technical Parameters')
        ->toContain('Always generate a negative prompt')
        ->toContain('enhanced_prompt');
});

test('system prompt builds correctly for gpt2 model', function () {
    $systemPrompt = ClaudeService::buildSystemPrompt('openai/gpt-image-2');

    expect($systemPrompt)->toContain('GPT Image 2')
        ->toContain('Scene + Subject + Important Details + Use Case + Constraints')
        ->not->toContain('Always generate a negative prompt');
});

test('system prompt fallback works', function () {
    $systemPrompt = ClaudeService::buildSystemPrompt('unknown-model-key');

    expect($systemPrompt)->toContain('GPT Image 2');
});

test('prompt enhancement handles successful response from Claude', function () {
    $apiKey = 'claude-test-api-key';
    $basePrompt = 'A young east asian woman sitting on grass';
    $modelKey = 'openai/gpt-image-2';

    $expectedResponse = [
        'enhanced_prompt' => 'Scene: Sunny green park. Subject: East Asian woman, 22, wearing a linen summer dress, sitting cross-legged on lush green grass with a gentle smile. Details: 35mm lens, shallow depth of field, warm sunlight, organic texture. Use Case: Instagram lifestyle post. Constraints: no watermark, no extra text.',
        'negative_prompt' => null,
        'platform' => 'Instagram',
        'style_tags' => ['lifestyle', 'candid'],
        'mood' => 'peaceful',
    ];

    Http::fake([
        'https://api.anthropic.com/v1/messages' => Http::response([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($expectedResponse),
                ],
            ],
        ], 200),
    ]);

    $service = new ClaudeService;
    $result = $service->enhancePrompt($basePrompt, $modelKey, $apiKey);

    expect($result)->toBeArray()
        ->toHaveKey('enhanced_prompt', $expectedResponse['enhanced_prompt'])
        ->toHaveKey('negative_prompt', null)
        ->toHaveKey('platform', 'Instagram');

    Http::assertSent(function (Request $request) use ($apiKey) {
        return $request->hasHeader('x-api-key', $apiKey) &&
            $request->hasHeader('anthropic-version', '2023-06-01') &&
            $request->url() === 'https://api.anthropic.com/v1/messages' &&
            $request['model'] === 'claude-3-5-sonnet-20241022';
    });
});

test('prompt enhancement strips markdown wrappers from JSON', function () {
    Http::fake([
        'https://api.anthropic.com/v1/messages' => Http::response([
            'content' => [
                [
                    'type' => 'text',
                    'text' => "```json\n{\n  \"enhanced_prompt\": \"Enhanced markdown prompt\",\n  \"negative_prompt\": null,\n  \"platform\": \"Pinterest\",\n  \"style_tags\": [],\n  \"mood\": \"vibrant\"\n}\n```",
                ],
            ],
        ], 200),
    ]);

    $service = new ClaudeService;
    $result = $service->enhancePrompt('test', 'gpt2', 'key');

    expect($result)->toBeArray()
        ->toHaveKey('enhanced_prompt', 'Enhanced markdown prompt')
        ->toHaveKey('platform', 'Pinterest');
});

test('prompt enhancement throws exception on request failure', function () {
    Http::fake([
        'https://api.anthropic.com/v1/messages' => Http::response([
            'error' => [
                'message' => 'Invalid API key provided.',
            ],
        ], 401),
    ]);

    $service = new ClaudeService;

    expect(fn () => $service->enhancePrompt('test', 'gpt2', 'invalid-key'))
        ->toThrow(Exception::class, 'Claude API Request failed: Invalid API key provided.');
});
