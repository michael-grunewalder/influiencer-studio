<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\File;
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

        if ($apiKey === 'bearny-codes') {
            $url = $this->generateDemoImage();

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

        if ($apiKey === 'bearny-codes') {
            $results = [];
            foreach ($prompts as $index => $prompt) {
                $results[$index] = [
                    'images' => [
                        ['url' => $this->generateDemoImage()],
                    ],
                ];
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

        $apiKey = config('services.fal.key');
        if (! $apiKey) {
            throw new \Exception('No API key configured for the team or globally.');
        }

        if ($apiKey === 'bearny-codes') {
            return [
                'apiKey' => $apiKey,
                'shouldCharge' => false,
            ];
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
}
