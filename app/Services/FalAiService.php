<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamAsset;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FalAiService
{
    /**
     * Generate a single image using fal.ai.
     */
    public function generate(Team $team, string $prompt, string $model = 'flux/schnell', array $options = []): array
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
            'enable_safety_checker' => false,
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

        $maskedKey = strlen($apiKey) <= 8 ? $apiKey : substr($apiKey, 0, 8).str_repeat('*', strlen($apiKey) - 8);
        Log::info('FalAiService::generate: Sending image generation request', [
            'model' => $model,
            'api_key' => $maskedKey,
            'payload' => $payload,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(180)->post("https://fal.run/{$model}", $payload);

            if ($response->failed()) {
                Log::error('FalAiService::generate: Generation request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Fal.ai image generation failed: '.$response->body());
            }

            $data = $response->json();
            Log::info('FalAiService::generate: Generation request succeeded', [
                'response' => $data,
            ]);

            if ($shouldCharge) {
                $team->chargeForImages(1);
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('FalAiService::generate: Generation request threw exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate multiple images in parallel using fal.ai.
     */
    public function generateParallel(Team $team, array $prompts, string $model = 'flux/schnell', array $options = []): array
    {
        $payloads = [];
        foreach ($prompts as $prompt) {
            $payload = array_merge([
                'prompt' => $prompt,
                'image_size' => 'portrait_16_9',
                'enable_safety_checker' => false,
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

        return $this->generateParallelPayloads($team, $payloads, $model);
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

    /**
     * Upload a local file to fal.ai CDN and return its public URL.
     */
    public function uploadFile(Team $team, string $filePath, string $contentType = 'image/png'): string
    {
        try {
            $resolved = $this->resolveApiKeyAndCharge($team, 0);
            $apiKey = $resolved['apiKey'];

            if ($apiKey === 'bearny-codes' && app()->environment('local', 'testing')) {
                Log::info('FalAiService::uploadFile: Bypass mock upload', [
                    'file_path' => $filePath,
                ]);

                return 'https://v3.fal.media/files/mock-image.png';
            }

            // 1. Initiate upload
            $initiateUrl = 'https://rest.fal.ai/storage/upload/initiate';
            $fileName = basename($filePath);

            Log::info('FalAiService::uploadFile: Initiating storage upload on fal.ai', [
                'file_name' => $fileName,
                'content_type' => $contentType,
            ]);

            $initiateResponse = Http::withHeaders([
                'Authorization' => 'Key '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post($initiateUrl, [
                'file_name' => $fileName,
                'content_type' => $contentType,
            ]);

            if ($initiateResponse->failed()) {
                Log::error('FalAiService::uploadFile: Failed to initiate upload', [
                    'status' => $initiateResponse->status(),
                    'body' => $initiateResponse->body(),
                ]);
                throw new \Exception('Failed to initiate upload to fal.ai: '.$initiateResponse->body());
            }

            $initiateData = $initiateResponse->json();
            $uploadUrl = $initiateData['upload_url'] ?? null;
            $fileUrl = $initiateData['file_url'] ?? null;

            if (! $uploadUrl || ! $fileUrl) {
                throw new \Exception('Invalid upload initiate response from fal.ai.');
            }

            // 2. PUT file contents
            Log::info('FalAiService::uploadFile: Uploading file binary content', [
                'file_url' => $fileUrl,
            ]);

            $fileContents = file_get_contents($filePath);
            $putResponse = Http::withBody($fileContents, $contentType)->put($uploadUrl);

            if ($putResponse->failed()) {
                Log::error('FalAiService::uploadFile: PUT content failed', [
                    'status' => $putResponse->status(),
                    'body' => $putResponse->body(),
                ]);
                throw new \Exception('Failed to upload file content to fal.ai: '.$putResponse->body());
            }

            Log::info('FalAiService::uploadFile: File upload completed successfully', [
                'file_url' => $fileUrl,
            ]);

            return $fileUrl;
        } catch (\Throwable $e) {
            Log::error('FalAiService::uploadFile: Exception occurred during upload', [
                'file_path' => $filePath,
                'content_type' => $contentType,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate multiple images in parallel using fal.ai using custom payloads.
     */
    public function generateParallelPayloads(Team $team, array $payloads, string $model): array
    {
        $count = count($payloads);
        if ($count === 0) {
            return [];
        }

        $resolved = $this->resolveApiKeyAndCharge($team, $count);
        $apiKey = $resolved['apiKey'];
        $shouldCharge = $resolved['shouldCharge'];

        // Mask key for log security
        $maskedKey = strlen($apiKey) <= 8 ? $apiKey : substr($apiKey, 0, 8).str_repeat('*', strlen($apiKey) - 8);

        if ($apiKey === 'bearny-codes' && app()->environment('local', 'testing')) {
            Log::info('FalAiService::generateParallelPayloads: Bypass mock parallel generation', [
                'api_key' => $maskedKey,
                'count' => $count,
                'model' => $model,
            ]);

            $results = [];
            foreach ($payloads as $index => $payload) {
                $results[$index] = [
                    'status' => 'success',
                    'images' => [
                        ['url' => $this->generateDemoImage()],
                    ],
                ];
            }

            if ($shouldCharge) {
                $team->chargeForImages($count);
            }

            return $results;
        }

        Log::info('FalAiService::generateParallelPayloads: Sending parallel requests to FAL.AI API', [
            'model' => $model,
            'api_key' => $maskedKey,
            'count' => $count,
            'payloads' => $payloads,
        ]);

        foreach ($payloads as $index => $payload) {
            Log::info("FalAiService::generateParallelPayloads: Request {$index} details", [
                'prompt' => $payload['prompt'] ?? null,
                'image_size' => $payload['image_size'] ?? null,
                'image_url' => $payload['image_url'] ?? null,
            ]);
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($apiKey, $model, $payloads) {
                $poolCalls = [];
                foreach ($payloads as $index => $payload) {
                    $poolCalls[] = $pool->as((string) $index)
                        ->withHeaders([
                            'Authorization' => 'Key '.$apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->timeout(180)
                        ->post("https://fal.run/{$model}", $payload);
                }

                return $poolCalls;
            });
        } catch (\Throwable $e) {
            Log::error('FalAiService::generateParallelPayloads: Pool request threw exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $results = [];
        $failedCount = 0;
        $successCount = 0;
        $errors = [];

        foreach ($payloads as $index => $payload) {
            $response = $responses[(string) $index] ?? null;
            if ($response instanceof Response && $response->successful()) {
                $successCount++;
                $results[$index] = [
                    'status' => 'success',
                    'images' => $response->json()['images'] ?? [],
                ];
                Log::info("FalAiService::generateParallelPayloads: Request {$index} succeeded", [
                    'response' => $results[$index],
                ]);
            } else {
                $failedCount++;
                if ($response instanceof Response) {
                    $errorBody = $response->body();
                    $status = $response->status();
                } elseif ($response instanceof \Throwable) {
                    $errorBody = $response->getMessage();
                    $status = null;
                } else {
                    $errorBody = 'No response';
                    $status = null;
                }
                $errors[] = $errorBody;

                $results[$index] = [
                    'status' => 'failed',
                    'error' => $errorBody,
                ];

                Log::error("FalAiService::generateParallelPayloads: Request {$index} failed", [
                    'status' => $status,
                    'body' => $errorBody,
                    'payload' => $payload,
                ]);
            }
        }

        Log::info('FalAiService::generateParallelPayloads: Parallel requests execution finished', [
            'total_count' => $count,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);

        if ($shouldCharge && $successCount > 0) {
            $team->chargeForImages($successCount);
        }

        return $results;
    }

    /**
     * Download a remote asset (e.g. from fal.ai) and register it in the team_assets registry.
     */
    public function downloadAndRegister(Team $team, string $remoteUrl, string $purpose, string $destPath): string
    {
        try {
            // If it is a local URL already (e.g. mock), copy it
            if (! str_starts_with($remoteUrl, 'http')) {
                $relativePath = str_replace('/storage/', 'storage/', $remoteUrl);
                $srcPath = public_path($relativePath);
                if (! file_exists($srcPath)) {
                    $srcPath = storage_path(str_replace('/storage/', 'app/public/', $remoteUrl));
                }

                if (file_exists($srcPath)) {
                    Storage::disk('local')->put($destPath, file_get_contents($srcPath));
                    $localUrl = Storage::disk('local')->url($destPath);

                    TeamAsset::create([
                        'team_id' => $team->id,
                        'local_url' => $localUrl,
                        'remote_url' => $remoteUrl,
                        'mime_type' => mime_content_type($srcPath) ?: 'image/png',
                        'purpose' => $purpose,
                    ]);

                    return $localUrl;
                }

                return $remoteUrl;
            }

            // Remote URL: download
            $response = Http::get($remoteUrl);
            if ($response->successful()) {
                $content = $response->body();
                Storage::disk('local')->put($destPath, $content);
                $localUrl = Storage::disk('local')->url($destPath);

                $mimeType = $response->header('Content-Type') ?: 'image/png';

                TeamAsset::create([
                    'team_id' => $team->id,
                    'local_url' => $localUrl,
                    'remote_url' => $remoteUrl,
                    'mime_type' => $mimeType,
                    'purpose' => $purpose,
                ]);

                return $localUrl;
            }

            Log::error('FalAiService::downloadAndRegister: Failed downloading remote file', [
                'url' => $remoteUrl,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('FalAiService::downloadAndRegister: Exception occurred while downloading and registering asset', [
                'url' => $remoteUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $remoteUrl;
    }
}
