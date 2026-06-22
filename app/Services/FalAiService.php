<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        if ($apiKey === 'bearny-codes' && app()->environment('local', 'testing')) {
            $url = $this->generateDemoImage();

            if ($shouldCharge) {
                $team->chargeForImages(1);
            }

            return [
                'images' => [
                    ['url' => $url],
                ],
            ];
        }

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

        if ($apiKey === 'bearny-codes' && app()->environment('local', 'testing')) {
            $results = [];
            foreach ($prompts as $index => $prompt) {
                $results[$index] = [
                    'images' => [
                        ['url' => $this->generateDemoImage()],
                    ],
                ];
            }

            if ($shouldCharge) {
                $team->chargeForImages($imageCount);
            }

            return $results;
        }

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

        $apiKey = config('fal_api.key');
        if ($apiKey === 'NO_FAL_API_KEY_SET' || ! $apiKey) {
            $apiKey = config('services.fal.key');
        }
        if ($apiKey === 'NO_FAL_API_KEY_SET' || ! $apiKey) {
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

    /**
     * Generate a demo watermarked image using a random available image.
     */
    protected function generateDemoImage(): string
    {
        $dir = storage_path('app/public/influencer');
        if (! File::isDirectory($dir)) {
            $dir = public_path('storage/influencer');
        }

        $files = [];
        if (File::isDirectory($dir)) {
            $files = File::files($dir);
        }

        $files = array_filter($files, function ($file) {
            return in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg'], true);
        });

        if (empty($files)) {
            $image = imagecreatetruecolor(720, 1280);
            $bgColor = imagecolorallocate($image, 18, 18, 18);
            imagefill($image, 0, 0, $bgColor);
            $width = 720;
            $height = 1280;
        } else {
            $randomFile = $files[array_rand($files)];
            $sourcePath = $randomFile->getRealPath();
            $ext = strtolower($randomFile->getExtension());

            if ($ext === 'jpg' || $ext === 'jpeg') {
                $image = @imagecreatefromjpeg($sourcePath);
            } elseif ($ext === 'png') {
                $image = @imagecreatefrompng($sourcePath);
            } else {
                $image = @imagecreatefromstring(file_get_contents($sourcePath));
            }

            if (! $image) {
                $image = imagecreatetruecolor(720, 1280);
                $bgColor = imagecolorallocate($image, 18, 18, 18);
                imagefill($image, 0, 0, $bgColor);
                $width = 720;
                $height = 1280;
            } else {
                $width = imagesx($image);
                $height = imagesy($image);
            }
        }

        $textColor = imagecolorallocate($image, 239, 68, 68);
        $bannerColor = imagecolorallocatealpha($image, 255, 255, 255, 20);

        $bannerHeight = (int) ($height * 0.08);
        $bannerY = (int) (($height - $bannerHeight) / 2);

        imagefilledrectangle($image, 0, $bannerY, $width, $bannerY + $bannerHeight, $bannerColor);

        $text = 'DEMO ONLY - DEMO ONLY - DEMO ONLY';
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        $x = (int) (($width - $textWidth) / 2);
        $y = (int) ($bannerY + ($bannerHeight - $textHeight) / 2);

        $shadowColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, $font, $x + 1, $y + 1, $text, $shadowColor);
        imagestring($image, $font, $x - 1, $y - 1, $text, $shadowColor);
        imagestring($image, $font, $x + 1, $y - 1, $text, $shadowColor);
        imagestring($image, $font, $x - 1, $y + 1, $text, $shadowColor);

        imagestring($image, $font, $x, $y, $text, $textColor);

        $targetDir = storage_path('app/public/references');
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $filename = 'demo_'.uniqid().'.png';
        $targetPath = $targetDir.'/'.$filename;

        imagepng($image, $targetPath);
        imagedestroy($image);

        return '/storage/references/'.$filename;
    }

    /**
     * Fetch FAL.AI account billing balance.
     */
    public function getAccountBalance(?string $apiKey = null): ?array
    {
        // Resolve key
        $key = $apiKey;
        if (! $key) {
            $key = config('fal_api.key');
            if ($key === 'NO_FAL_API_KEY_SET' || ! $key) {
                $key = config('services.fal.key');
            }
        }

        if ($key === 'NO_FAL_API_KEY_SET' || ! $key) {
            Log::warning('FalAiService::getAccountBalance: No API key found.');

            return null;
        }

        // Mask key for log file security
        $maskedKey = strlen($key) <= 8 ? $key : substr($key, 0, 8).str_repeat('*', strlen($key) - 8);

        // If local/testing bypass key
        if ($key === 'bearny-codes' && app()->environment('local', 'testing')) {
            Log::info('FalAiService::getAccountBalance: Bypass mock billing balance check', [
                'api_key' => $maskedKey,
            ]);

            return [
                'username' => 'bearny-user-mock',
                'credits' => [
                    'current_balance' => 99.75,
                    'currency' => 'USD',
                ],
            ];
        }

        // Build URL
        $baseUrl = rtrim(config('fal_api.base_url', 'https://api.fal.ai/v1'), '/');
        $endpoint = ltrim(config('fal_api.account.balance', 'account/billing?expand=credits'), '/');
        $url = $baseUrl.'/'.$endpoint;

        Log::info('FalAiService::getAccountBalance: Sending request to FAL.AI API', [
            'url' => $url,
            'api_key' => $maskedKey,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key '.$key,
            ])->timeout(10)->get($url);

            if ($response->failed()) {
                Log::error('FalAiService::getAccountBalance: FAL.AI billing request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();
            Log::info('FalAiService::getAccountBalance: FAL.AI billing request succeeded', [
                'response' => $data,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('FalAiService::getAccountBalance: FAL.AI billing request threw exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
