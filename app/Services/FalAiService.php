<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class FalAiService
{
    /**
     * Generate a single image using fal.ai.
     */
    public function generate(Team $team, string $prompt, string $model = 'fal-ai/flux/schnell', array $options = []): array
    {
        $resolved = $this->resolveApiKeyAndCharge($team, 1);
        $apiKey = $resolved['apiKey'];
        $shouldCharge = $resolved['shouldCharge'];

        $payload = array_merge([
            'prompt' => $prompt,
            'image_size' => 'portrait_16_9',
        ], $options);

        if (isset($payload['aspect_ratio'])) {
            $ar = $payload['aspect_ratio'];
            unset($payload['aspect_ratio']);
            if ($ar === '9:16') {
                $payload['image_size'] = 'portrait_16_9';
            } elseif ($ar === '16:9') {
                $payload['image_size'] = 'landscape_16_9';
            } elseif ($ar === '1:1') {
                $payload['image_size'] = 'square';
            }
        }

        $response = Http::withHeaders([
            'Authorization' => 'Key '.$apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("https://fal.run/{$model}", $payload);

        if ($response->failed()) {
            throw new \Exception('Fal.ai image generation failed: '.$response->body());
        }

        $data = $response->json();

        if ($shouldCharge) {
            $team->chargeForImages(1);
        }

        return $data;
    }

    /**
     * Generate multiple images in parallel using fal.ai.
     */
    public function generateParallel(Team $team, array $prompts, string $model = 'fal-ai/flux/schnell', array $options = []): array
    {
        $imageCount = count($prompts);
        if ($imageCount === 0) {
            return [];
        }

        $resolved = $this->resolveApiKeyAndCharge($team, $imageCount);
        $apiKey = $resolved['apiKey'];
        $shouldCharge = $resolved['shouldCharge'];

        $payloads = [];
        foreach ($prompts as $prompt) {
            $payload = array_merge([
                'prompt' => $prompt,
                'image_size' => 'portrait_16_9',
            ], $options);

            if (isset($payload['aspect_ratio'])) {
                $ar = $payload['aspect_ratio'];
                unset($payload['aspect_ratio']);
                if ($ar === '9:16') {
                    $payload['image_size'] = 'portrait_16_9';
                } elseif ($ar === '16:9') {
                    $payload['image_size'] = 'landscape_16_9';
                } elseif ($ar === '1:1') {
                    $payload['image_size'] = 'square';
                }
            }
            $payloads[] = $payload;
        }

        $responses = Http::pool(function (Pool $pool) use ($apiKey, $model, $payloads) {
            $poolCalls = [];
            foreach ($payloads as $index => $payload) {
                $poolCalls[] = $pool->as((string) $index)
                    ->withHeaders([
                        'Authorization' => 'Key '.$apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(60)
                    ->post("https://fal.run/{$model}", $payload);
            }

            return $poolCalls;
        });

        $results = [];
        $failedCount = 0;
        $errors = [];

        foreach ($prompts as $index => $prompt) {
            $response = $responses[(string) $index] ?? null;
            if ($response && $response->successful()) {
                $results[$index] = $response->json();
            } else {
                $failedCount++;
                $errors[] = $response ? $response->body() : 'No response';
            }
        }

        if ($failedCount > 0) {
            throw new \Exception('Fal.ai image generation failed for '.$failedCount.' requests. Errors: '.implode(' | ', $errors));
        }

        if ($shouldCharge) {
            $team->chargeForImages($imageCount);
        }

        return $results;
    }

    /**
     * Resolve the API Key and verify if team credits are sufficient.
     */
    protected function resolveApiKeyAndCharge(Team $team, int $imageCount): array
    {
        if ($team->fal_api_key) {
            return [
                'apiKey' => $team->fal_api_key,
                'shouldCharge' => false,
            ];
        }

        $apiKey = config('services.fal.key') ?: env('FAL_API_KEY');
        if (! $apiKey) {
            throw new \Exception('No API key configured for the team or globally.');
        }

        $cost = $imageCount * 0.35;
        if (! $team->hasCreditsFor($imageCount)) {
            throw new \Exception("Insufficient team credits. Required: \${$cost}. Current balance: \${$team->credits}.");
        }

        return [
            'apiKey' => $apiKey,
            'shouldCharge' => true,
        ];
    }
}
