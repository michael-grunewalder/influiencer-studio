<?php

namespace App\Livewire;

use App\Models\Influencer;
use App\Models\Team;
use App\Models\TeamAsset;
use App\Services\FalAiService;
use App\Services\PromptBuilderService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PhotoStudio extends Component
{
    public Influencer $influencer;

    // Model options
    public string $selectedModel = 'openai/gpt-image-2/edit';

    public array $availableModels = [
        'openai/gpt-image-2/edit' => 'GPT 2 Photo Edit (Recommended)',
        'fal-ai/bytedance/seedream/v4.5/edit' => 'SeeDream 4.5 Edit',
        'fal-ai/gpt-image-1.5/edit' => 'GPT 1.5 Edit',
    ];

    // Params
    public ?string $location = 'coffee-shop';

    public string $timeOfDay = 'afternoon';

    public string $pose = 'front';

    public string $vibe = 'candid';

    public string $outfitPreset = 'current';

    public string $stance = 'standing';

    public string $aspectRatio = '9:16';

    public string $resolution = '4k';

    public int $outputCount = 1;

    public string $expression = 'natural';

    public string $gaze = 'at-camera';

    // Text overrides
    public string $propText = '';

    public string $wardrobeText = '';

    public string $hairstyleText = '';

    public bool $hairstyleLocked = false;

    // Outfit mapping
    public ?string $selectedOutfitId = null;

    // State
    public bool $generating = false;

    public array $currentImgs = [];

    public ?string $error = null;

    // View options
    public string $rightMode = 'location'; // location, pose

    public ?string $expandedImg = null;

    protected $listeners = ['photoStudioReset' => 'resetParams'];

    public function mount(Influencer $influencer): void
    {
        $this->influencer = $influencer;
        $this->resetParams();
    }

    public function resetParams(): void
    {
        $this->location = 'coffee-shop';
        $this->timeOfDay = 'afternoon';
        $this->pose = 'front';
        $this->vibe = 'candid';
        $this->outfitPreset = 'current';
        $this->selectedOutfitId = null;
        $this->stance = 'standing';
        $this->aspectRatio = '9:16';
        $this->resolution = '4k';
        $this->outputCount = 1;
        $this->expression = 'natural';
        $this->gaze = 'at-camera';
        $this->propText = '';
        $this->wardrobeText = '';
        $this->hairstyleText = '';
        $this->hairstyleLocked = false;
        $this->currentImgs = [];
        $this->error = null;
    }

    public function randomize(): void
    {
        $locations = ['coffee-shop', 'city-street', 'beach', 'rooftop', 'bedroom', 'bathroom', 'mall', 'gym', 'park', 'restaurant', 'hotel', 'studio'];
        $times = ['morning', 'afternoon', 'golden-hour', 'night'];
        $vibes = ['candid', 'editorial', 'luxury', 'street', 'cozy'];
        $expressions = ['natural', 'smiling', 'laughing', 'serious'];

        $this->location = collect($locations)->random();
        $this->timeOfDay = collect($times)->random();
        $this->vibe = collect($vibes)->random();
        $this->expression = collect($expressions)->random();
        $this->stance = collect(['standing', 'sitting'])->random();

        // Poses depend on stance and gender
        $poses = $this->getAvailablePoses();
        $this->pose = count($poses) > 0 ? collect($poses)->random()['id'] : 'front';

        $this->hairstyleText = '';
        $this->propText = '';
        $this->wardrobeText = '';
        $this->selectedOutfitId = null;
    }

    public function getAvailablePoses(): array
    {
        $gender = $this->influencer->properties->gender ?? 'Female';
        $isMale = strtolower($gender) === 'male';

        if ($this->stance === 'sitting') {
            return [
                ['id' => 'front', 'label' => 'Sitting Front'],
                ['id' => 'plandid', 'label' => 'Sitting Plandid'],
                ['id' => 'candid', 'label' => 'Sitting Candid'],
                ['id' => 'cute-posed', 'label' => 'Sitting Cute Posed'],
                ['id' => 'hip-pop', 'label' => 'Sitting Hip Pop'],
                ['id' => 'triangle', 'label' => 'Sitting Triangle'],
                ['id' => 'over-shoulder', 'label' => 'Sitting Over Shoulder'],
                ['id' => 'mid-turn', 'label' => 'Sitting Mid Turn'],
                ['id' => 'long-line', 'label' => 'Sitting Long Line'],
                ['id' => 'hands-pockets', 'label' => 'Sitting Hands in Pockets'],
                ['id' => 'crossed-arms', 'label' => 'Sitting Crossed Arms'],
                ['id' => 'lean', 'label' => 'Sitting Lean'],
                ['id' => 'handheld', 'label' => 'Sitting Handheld'],
            ];
        }

        if ($isMale) {
            return [
                ['id' => 'front', 'label' => 'Confident Front'],
                ['id' => 'handheld', 'label' => 'Handheld'],
                ['id' => 'hands-pockets', 'label' => 'Hands In Pockets'],
                ['id' => 'crossed-arms', 'label' => 'Crossed Arms'],
                ['id' => 'lean', 'label' => 'Environment Lean'],
                ['id' => 'walking', 'label' => 'Walking'],
                ['id' => 'over-shoulder', 'label' => 'Over Shoulder'],
                ['id' => 'facing-away', 'label' => 'Facing Away'],
            ];
        }

        return [
            ['id' => 'front', 'label' => 'Front-Facing'],
            ['id' => 'handheld', 'label' => 'Handheld'],
            ['id' => 'candid', 'label' => 'Candid'],
            ['id' => 'cute-posed', 'label' => 'Hair Touch'],
            ['id' => 'hip-pop', 'label' => 'Hip Pop'],
            ['id' => 'over-shoulder', 'label' => 'Over Shoulder'],
            ['id' => 'facing-away', 'label' => 'Facing Away'],
            ['id' => 'walking', 'label' => 'Walking'],
            ['id' => 'mid-turn', 'label' => 'Mid Turn'],
            ['id' => 'long-line', 'label' => 'Step Out'],
            ['id' => 'lean', 'label' => 'Wall Lean'],
        ];
    }

    public function getPosePreviewUrl(string $pose): string
    {
        $stance = $this->stance;
        $filename = "pose_{$stance}_{$pose}";

        $fallbacks = [
            'standing_candid' => 'pose_candid',
            'standing_cute-posed' => 'pose_cute-posed',
            'standing_facing-away' => 'pose_facing-away',
            'standing_front' => 'pose_front',
            'standing_handheld' => 'pose_handheld',
            'standing_mid-turn' => 'pose_mid-turn',
            'standing_plandid' => 'pose_plandid',
            'standing_walking' => 'pose_walking',
            'facing-away' => 'pose_facing-away',
            'handheld' => 'pose_handheld',
        ];

        $key = "{$stance}_{$pose}";
        if (isset($fallbacks[$key])) {
            $filename = $fallbacks[$key];
        }

        if ($filename === 'pose_sitting_hands-pockets') {
            return '/storage/assets/'.$filename.'.png';
        }

        return '/storage/assets/'.$filename.'.webp';
    }

    public function getLocationPreviewUrl(string $loc, string $tod): string
    {
        return "/storage/assets/loc_{$loc}-{$tod}.webp";
    }

    protected function getPublicUrlForReference(Team $team, ?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (str_starts_with($url, 'http') && ! str_contains($url, '/storage/teams/')) {
            return $url;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);
        $cleanPath = str_replace('/storage/', '', $parsedPath);

        // 1. Try public disk (storage/app/public/)
        $fullPath = Storage::disk('public')->path($cleanPath);
        if (! file_exists($fullPath)) {
            // 2. Try local disk (storage/app/private/)
            $fullPath = Storage::disk('local')->path($cleanPath);
        }
        if (! file_exists($fullPath)) {
            // 3. Try direct storage_path fallback
            $fullPath = storage_path('app/'.$cleanPath);
        }

        if (file_exists($fullPath)) {
            $mimeType = mime_content_type($fullPath) ?: 'image/png';

            return app(FalAiService::class)->uploadFile($team, $fullPath, $mimeType);
        }

        return $url;
    }

    public function generate(FalAiService $falAiService): void
    {
        $this->generating = true;
        $this->error = null;
        $this->currentImgs = [];

        try {
            $team = $this->influencer->team ?: Team::first();
            if (! $team) {
                throw new \Exception('No team context found.');
            }

            // 1. Resolve reference images
            $imageUrls = [];

            // Reference 1: Pose Preview (the canvas/base image to edit)
            $poseUrl = $this->getPosePreviewUrl($this->pose);
            $publicPoseUrl = $this->getPublicUrlForReference($team, $poseUrl);
            if (! $publicPoseUrl) {
                throw new \Exception('Failed to upload pose preview reference.');
            }
            $imageUrls[] = $publicPoseUrl;
            $poseTag = '@image1'; // @image1 is always the pose template!

            // Reference 2: Face/Identity (closeup or avatar)
            $faceRef = $this->influencer->properties->closeup ?? $this->influencer->avatar;
            $faceTag = null;
            if ($faceRef) {
                $publicFaceUrl = $this->getPublicUrlForReference($team, $faceRef);
                if ($publicFaceUrl) {
                    $imageUrls[] = $publicFaceUrl;
                    $faceTag = '@image'.count($imageUrls);
                }
            }

            // Reference 3: Wardrobe Outfit (selected look or character sheet)
            $wardrobeRef = null;
            $trimmedWardrobeText = trim($this->wardrobeText ?? '');
            if (empty($trimmedWardrobeText)) {
                if ($this->outfitPreset === 'wardrobe' && $this->selectedOutfitId) {
                    $outfit = $this->influencer->outfits()->find($this->selectedOutfitId);
                    if ($outfit && $outfit->image_path) {
                        $wardrobeRef = $outfit->image_path;
                    }
                } elseif ($this->outfitPreset === 'current') {
                    $wardrobeRef = $this->influencer->properties->character_sheet;
                }
            }

            $wardrobeTag = null;
            if ($wardrobeRef) {
                $publicWardrobeUrl = $this->getPublicUrlForReference($team, $wardrobeRef);
                if ($publicWardrobeUrl) {
                    $imageUrls[] = $publicWardrobeUrl;
                    $wardrobeTag = '@image'.count($imageUrls);
                }
            }

            // Reference 4: Closeup sheet / detail sheet
            $detailSheetRef = $this->influencer->properties->detail_sheet;
            $closeUp1Tag = null;
            if ($detailSheetRef) {
                $publicDetailUrl = $this->getPublicUrlForReference($team, $detailSheetRef);
                if ($publicDetailUrl) {
                    $imageUrls[] = $publicDetailUrl;
                    $closeUp1Tag = '@image'.count($imageUrls);
                }
            }

            // Reference 5: Character sheet (if not already used as wardrobe reference)
            $charSheetRef = $this->influencer->properties->character_sheet;
            $closeUp2Tag = null;
            if ($charSheetRef && $charSheetRef !== $wardrobeRef) {
                $publicCharUrl = $this->getPublicUrlForReference($team, $charSheetRef);
                if ($publicCharUrl) {
                    $imageUrls[] = $publicCharUrl;
                    $closeUp2Tag = '@image'.count($imageUrls);
                }
            }

            // 2. Build prompts
            $promptArgs = [
                'influencer' => $this->influencer,
                'location' => $this->location,
                'timeOfDay' => $this->timeOfDay,
                'pose' => $this->pose,
                'vibe' => $this->vibe,
                'wardrobeText' => $this->wardrobeText,
                'hairstyleText' => $this->hairstyleText,
                'outfitPreset' => $this->outfitPreset,
                'stance' => $this->stance,
                'aspectRatio' => $this->aspectRatio,
                'expression' => $this->expression,
                'gaze' => $this->gaze,
                'propText' => $this->propText,
                'propRefs' => [],
                'poseTag' => $poseTag,
                'faceTag' => $faceTag,
                'wardrobeTag' => $wardrobeTag,
                'closeUp1Tag' => $closeUp1Tag,
                'closeUp2Tag' => $closeUp2Tag,
            ];

            $prompts = [];
            for ($i = 0; $i < $this->outputCount; $i++) {
                $promptArgs['variationIdx'] = $i;
                $prompts[] = PromptBuilderService::buildPhotoStudioPrompt($promptArgs);
            }

            // Log prompts
            foreach ($prompts as $idx => $p) {
                PromptBuilderService::logPrompt($p, null, "PhotoStudio Variation #{$idx}");
            }

            // 3. Check credits balance if no custom API key is configured
            if (! $team->hasFalApiKey() && ! $team->hasCreditsFor($this->outputCount)) {
                $cost = $this->outputCount * 0.35;
                throw new \Exception("Insufficient team credits. Required: \${$cost}. Current balance: \${$team->credits}.");
            }

            // 4. Construct model payloads
            $payloads = [];
            foreach ($prompts as $p) {
                $payloads[] = $this->buildModelPayload($this->selectedModel, $p, $imageUrls, $this->aspectRatio);
            }

            // 5. Call generator
            $results = $falAiService->generateParallelPayloads($team, $payloads, $this->selectedModel);

            // 6. Handle results
            $successCount = 0;
            $tempImgs = [];
            foreach ($results as $index => $res) {
                if ($res['status'] === 'success') {
                    $remoteUrl = $res['images'][0]['url'] ?? null;
                    if ($remoteUrl) {
                        $ext = pathinfo(parse_url($remoteUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                        $destPath = "teams/{$team->id}/influencers/{$this->influencer->id}/photo-studio/photo_".time()."_{$index}.{$ext}";

                        // Download and register
                        $localUrl = $falAiService->downloadAndRegister(
                            $team,
                            $remoteUrl,
                            "photo-studio-{$this->influencer->id}",
                            $destPath
                        );

                        $tempImgs[] = $localUrl;
                        $successCount++;
                    }
                } else {
                    $this->error = $res['error'] ?? 'Generation failed.';
                }
            }

            $this->currentImgs = $tempImgs;

            if ($successCount > 0) {
                Toaster::success(__("{$successCount} Foto(s) erfolgreich generiert!"));
                $this->dispatch('credits-updated');
            } else {
                if (empty($this->error)) {
                    $this->error = 'No images were generated successfully.';
                }
            }

        } catch (\Throwable $e) {
            Log::error('PhotoStudio::generate failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = $e->getMessage();
            Toaster::error(__('Generierung fehlgeschlagen: ').$e->getMessage());
        } finally {
            $this->generating = false;
        }
    }

    protected function buildModelPayload(string $model, string $prompt, array $imageUrls, string $aspectRatio): array
    {
        $payload = [
            'prompt' => $prompt,
        ];

        // Format image references based on model
        if (str_contains($model, 'gpt-image-2')) {
            $payload['image_urls'] = $imageUrls;
        } else {
            // Fallback for models expecting a single image or other formats
            $payload['image_url'] = $imageUrls[0] ?? null;
            if (count($imageUrls) > 1) {
                $payload['image_urls'] = $imageUrls;
            }
        }

        // Handle aspect ratio
        if ($aspectRatio === '9:16') {
            $payload['image_size'] = 'portrait_16_9';
        } elseif ($aspectRatio === '16:9') {
            $payload['image_size'] = 'landscape_16_9';
        } elseif ($aspectRatio === '1:1') {
            $payload['image_size'] = 'square';
        }

        return $payload;
    }

    public function selectOutfit(string $id): void
    {
        $this->selectedOutfitId = $id;
        $this->outfitPreset = 'wardrobe';
    }

    public function selectPose(string $id): void
    {
        $this->pose = $id;
    }

    public function selectLocation(string $id): void
    {
        $this->location = $id;
    }

    public function resolvePhotoUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // If it's already a full URL or temporary URL (starts with http)
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $cleanPath = $url;
        if (str_starts_with($cleanPath, '/storage/')) {
            $cleanPath = substr($cleanPath, strlen('/storage/'));
        } elseif (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, strlen('storage/'));
        }

        // Check if the file actually exists in local disk
        if (Storage::disk('local')->exists($cleanPath)) {
            try {
                return Storage::disk('local')->temporaryUrl($cleanPath, now()->addDay());
            } catch (\Throwable $e) {
                return $url;
            }
        }

        return $url;
    }

    public function render()
    {
        $team = $this->influencer->team ?: Team::first();
        $galleryPhotos = $team ? TeamAsset::where('team_id', $team->id)
            ->where('purpose', "photo-studio-{$this->influencer->id}")
            ->latest()
            ->get() : collect();

        return view('livewire.photo-studio', [
            'galleryPhotos' => $galleryPhotos,
        ]);
    }
}
