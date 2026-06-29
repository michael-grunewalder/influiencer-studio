<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public const MODEL_GUIDES = [
        'openai/gpt-image-2' => [
            'name' => 'GPT Image 2',
            'format' => 'Scene + Subject + Important Details + Use Case + Constraints',
            'alwaysInclude' => [
                'Scene: where the image exists (location, time of day, background)',
                'Subject: who is the main focus (identity, clothing, expression, pose)',
                'Important Details: materials, skin texture, camera angle, lens feel, mood',
                'Use Case: editorial photo / product mockup / social content / poster',
                'Constraints: no watermark, preserve face, no extra text',
            ],
            'supportsNegativePrompt' => false,
        ],
        'fal-ai/bytedance/seedream/v4-5/text-to-image' => [
            'name' => 'Seedream 4.5',
            'format' => 'Subject + Style + Composition + Lighting + Technical Parameters',
            'alwaysInclude' => [
                'Subject: front-loaded appearance, outfit, expression (MUST come first)',
                'Style: photographic approach (\'editorial photography\', \'cinematic still\')',
                'Composition: shot type and framing (\'medium close-up, centered\')',
                'Lighting: specific cue (\'golden hour\', \'dramatic side lighting\', \'studio diffusion\')',
                'Technical Parameters: lens (85mm, f/1.8), resolution (4K), camera brand',
            ],
            'supportsNegativePrompt' => true,
            'defaultNegativePrompt' => 'blurry, low resolution, watermark, logo, extra fingers, distorted hands, plastic skin, overexposed',
        ],
        'fal-ai/gpt-image-1.5' => [
            'name' => 'GPT Image 1.5',
            'format' => 'Subject + Location + Action + Lighting + Style + Constraints',
            'alwaysInclude' => [
                'Subject: physical appearance, outfit, expression',
                'Location: specific environment, time of day, atmosphere',
                'Action: what the subject is doing or how they are positioned',
                'Lighting: type, source direction, intensity, color temperature',
                'Style: editorial, lifestyle, candid, luxury brand aesthetic',
                'Constraints: no watermark, no logos, no extra people',
            ],
            'supportsNegativePrompt' => false,
        ],
        'fal-ai/ideogram/v3' => [
            'name' => 'Ideogram 4',
            'format' => 'Subject + Style + Composition + Lighting + Typography + Color Palette',
            'alwaysInclude' => [
                'Subject: influencer appearance, outfit, pose',
                'Style: visual aesthetic reference (editorial, luxury, streetwear)',
                'Composition: layout, framing, spatial relationships',
                'Lighting: source, direction, quality (\'single soft key light from upper left\')',
                'Typography: any text in quotes with font style specified',
                'Color Palette: hex values or named colors',
            ],
            'supportsNegativePrompt' => false,
        ],
        'fal-ai/nano-banana-pro' => [
            'name' => 'Nano Banana Pro',
            'format' => 'Subject + Action + Location + Look/Style + Framing + Lighting + On-Image Text',
            'alwaysInclude' => [
                'Subject: who is in frame — physical details, outfit, expression',
                'Action: what they are doing (posing, walking, looking over shoulder)',
                'Location: specific environment with concrete detail (not \'a street\' — \'rain-slicked Shibuya at midnight\')',
                'Look/Style: \'35mm documentary\', \'Vogue editorial\', \'authentic UGC lifestyle\'',
                'Framing: camera position and lens (\'eye-level at 85mm\', \'wide-angle at 24mm\')',
                'Lighting: concrete setup (\'soft overcast light\', \'rim-lit by a neon sign behind\')',
                'On-Image Text: quoted exactly with font style if needed',
            ],
            'supportsNegativePrompt' => false,
        ],
    ];

    public static function resolveModelGuideKey(string $model): string
    {
        $model = strtolower($model);
        if (str_contains($model, 'gpt-image-2') || str_contains($model, 'gpt2')) {
            return 'openai/gpt-image-2';
        }
        if (str_contains($model, 'seedream')) {
            return 'fal-ai/bytedance/seedream/v4-5/text-to-image';
        }
        if (str_contains($model, 'gpt-image-1.5') || str_contains($model, 'gpt1')) {
            return 'fal-ai/gpt-image-1.5';
        }
        if (str_contains($model, 'ideogram')) {
            return 'fal-ai/ideogram/v3';
        }
        if (str_contains($model, 'banana')) {
            return 'fal-ai/nano-banana-pro';
        }

        return 'openai/gpt-image-2';
    }

    public static function buildSystemPrompt(string $modelKey): string
    {
        $guideKey = self::resolveModelGuideKey($modelKey);
        $guide = self::MODEL_GUIDES[$guideKey];

        $includeList = '';
        foreach ($guide['alwaysInclude'] as $i => $item) {
            $includeList .= ($i + 1).'. '.$item."\n";
        }

        $negativeSection = '';
        if ($guide['supportsNegativePrompt']) {
            $negativeSection = "\n## Negative Prompt\nAlways generate a negative prompt. Default base:\n\"".$guide['defaultNegativePrompt']."\"\n";
        }

        return 'You are an elite AI image prompt engineer for AI influencer content on FAL.AI.
You are generating prompts specifically for the **'.$guide['name'].'** model.

## Model Format Rule — Always Follow This Structure
**'.$guide['format'].'**

## Always Include (in the correct format order above)
'.trim($includeList).'
'.$negativeSection.'
## Anti-Slop Rules
- Never use: "stunning", "beautiful", "masterpiece", "breathtaking", "8K ultra HD"
- Replace ALL adjectives with concrete visual facts
- Wrong: "beautiful lighting" → Right: "soft diffused light from a north-facing window"
- Wrong: "stunning outfit" → Right: "oversized camel trench coat with gold buttons, collar up"

## Output Format
Return ONLY this JSON — no markdown, no explanation:
{
  "enhanced_prompt": "prompt built in '.$guide['name'].' format",
  "negative_prompt": "'.($guide['supportsNegativePrompt'] ? 'negative prompt string' : 'null').'",
  "platform": "Instagram | TikTok | Pinterest | LinkedIn",
  "style_tags": ["tag1", "tag2", "tag3"],
  "mood": "one-word mood descriptor"
}';
    }

    private function cleanJson(string $text): string
    {
        $text = preg_replace('/^```(?:json)?/m', '', $text);
        $text = preg_replace('/```$/m', '', $text);

        return trim($text);
    }

    public function enhancePrompt(string $prompt, string $modelKey, string $apiKey): array
    {
        $systemPrompt = self::buildSystemPrompt($modelKey);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 1024,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Enhance this base prompt: '.$prompt,
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception('Claude API Request failed: '.($response->json('error.message') ?? $response->body()));
        }

        $text = $response->json('content.0.text');
        if (empty($text)) {
            throw new \Exception('Claude API returned an empty response.');
        }

        $cleaned = $this->cleanJson($text);
        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Claude response JSON decode failed: '.json_last_error_msg(), ['response_text' => $text]);
            throw new \Exception('Failed to parse Claude prompt enhancer response as JSON.');
        }

        return $data;
    }
}
