<?php

namespace App\Livewire;

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Models\Outfit;
use App\Models\Team;
use App\Services\ClaudeService;
use App\Services\FalAiService;
use App\Services\PromptBuilderService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Dashboard extends Component
{
    use WithFileUploads;

    public ?string $selectedId = null;

    public ?string $selectedTeamId = null;

    public array $generationStates = [];

    public bool $use_prompt_enhancer = false;

    public string $currentTab = 'profile'; // profile, photos, videos

    public string $detailTab = 'overview'; // overview, scripts, wardrobe, home, brand_deals, history

    // Edit properties
    public string $edit_name = '';

    public string $edit_gender = 'Female';

    public string $edit_age = '';

    public array $edit_niches = [];

    public string $edit_backstory = '';

    public int $edit_personality = 50;

    public string $edit_location = '';

    public string $edit_target_audience = '';

    public string $edit_physical_description = '';

    // File uploads
    public $uploaded_avatar;

    public $uploaded_character_sheet;

    public $uploaded_closeup;

    public $uploaded_detail_sheet;

    // Outfit properties
    public string $outfit_top = '';

    public string $outfit_bottom = '';

    public string $outfit_hairstyle = '';

    public string $outfit_description = '';

    public bool $showAddOutfitModal = false;

    public string $newOutfitName = '';

    public ?string $activeOutfitId = null;

    public bool $showOutfitOverlay = false;

    public array $available_niches = [
        'Fashion', 'Beauty', 'Lifestyle', 'Fitness', 'Travel',
        'Food & Dining', 'Tech', 'Gaming', 'Finance',
        'Entertainment', 'Wellness', 'Sports', 'Other',
    ];

    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            $team = $user->teams()->first();
            if (! $team) {
                $team = Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
                $user->teams()->attach($team);
            }

            if (! session()->has('active_team_id')) {
                session(['active_team_id' => $team->id]);
            }

            $this->selectedTeamId = session('active_team_id');
        }

        $first = $this->influencers->first();
        if ($first) {
            $this->selectInfluencer($first->id);
        }
    }

    public function updatedSelectedTeamId(string $value): void
    {
        session(['active_team_id' => $value]);
        if (auth()->check()) {
            auth()->user()->update(['last_active_team_id' => $value]);
        }
        $this->dispatch('credits-updated');
        $first = $this->influencers->first();
        if ($first) {
            $this->selectInfluencer($first->id);
        } else {
            $this->selectedId = null;
            $this->resetEditFields();
        }
    }

    #[Computed]
    public function teams()
    {
        if (auth()->check()) {
            return auth()->user()->teams()->get();
        }

        return collect();
    }

    #[Computed]
    public function influencers()
    {
        if (! $this->selectedTeamId) {
            return collect();
        }

        return Influencer::where('team_id', $this->selectedTeamId)->latest()->get();
    }

    #[Computed]
    public function selectedInfluencer()
    {
        if (! $this->selectedId || ! $this->selectedTeamId) {
            return null;
        }

        return Influencer::where('id', $this->selectedId)
            ->where('team_id', $this->selectedTeamId)
            ->first();
    }

    public function selectInfluencer(string $id): void
    {
        $this->selectedId = $id;
        $this->loadInfluencerData();
    }

    public function loadInfluencerData(): void
    {
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $this->edit_name = $influencer->name ?? '';
            $this->edit_gender = $influencer->properties->gender ?? 'Female';
            $this->edit_age = (string) ($influencer->properties->age ?? '');
            $this->edit_niches = $influencer->properties->niche ?? [];
            $this->edit_backstory = $influencer->properties->backstory ?? '';
            $this->edit_personality = $influencer->properties->personality ?? 50;
            $this->edit_location = $influencer->properties->location ?? '';
            $this->edit_target_audience = $influencer->properties->target_audience ?? '';
            $this->edit_physical_description = $influencer->properties->physical_description ?? '';
            $this->outfit_top = '';
            $this->outfit_bottom = '';
            $this->outfit_hairstyle = '';
            $this->outfit_description = '';
            $this->activeOutfitId = null;
            $this->showOutfitOverlay = false;
        }
    }

    public function deleteInfluencer(string $id): void
    {
        $influencer = Influencer::where('id', $id)
            ->where('team_id', $this->selectedTeamId)
            ->first();

        if ($influencer) {
            $influencer->delete();
            Toaster::success(__('Influencer gelöscht.'));
        }

        if ($this->selectedId === $id) {
            $next = Influencer::where('team_id', $this->selectedTeamId)->latest()->first();
            if ($next) {
                $this->selectInfluencer($next->id);
            } else {
                $this->selectedId = null;
                $this->resetEditFields();
            }
        }
    }

    private function resetEditFields(): void
    {
        $this->edit_name = '';
        $this->edit_gender = 'Female';
        $this->edit_age = '';
        $this->edit_niches = [];
        $this->edit_backstory = '';
        $this->edit_personality = 50;
        $this->edit_location = '';
        $this->edit_target_audience = '';
        $this->edit_physical_description = '';
        $this->outfit_top = '';
        $this->outfit_bottom = '';
        $this->outfit_hairstyle = '';
        $this->outfit_description = '';
        $this->activeOutfitId = null;
        $this->showOutfitOverlay = false;
    }

    public function toggleNiche(string $niche): void
    {
        if (in_array($niche, $this->edit_niches)) {
            $this->edit_niches = array_diff($this->edit_niches, [$niche]);
        } else {
            $this->edit_niches[] = $niche;
        }
    }

    public function saveOverview(): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $this->validate([
            'edit_name' => 'required|string|min:2|max:100',
            'edit_gender' => 'required|in:Female,Male',
            'edit_age' => 'required|integer|min:18|max:100',
            'edit_niches' => 'required|array|min:1',
            'edit_backstory' => 'nullable|string|max:1000',
            'edit_personality' => 'required|integer|min:0|max:100',
            'edit_location' => 'nullable|string|max:100',
            'edit_target_audience' => 'nullable|string|max:200',
            'edit_physical_description' => 'nullable|string|max:500',
        ]);

        $props = $influencer->properties ?? new InfluencerProperties;

        // Update DTO attributes
        $props->gender = $this->edit_gender;
        $props->age = (int) $this->edit_age;
        $props->niche = $this->edit_niches;
        $props->backstory = $this->edit_backstory ?: null;
        $props->personality = $this->edit_personality;
        $props->location = $this->edit_location ?: null;
        $props->target_audience = $this->edit_target_audience ?: null;
        $props->physical_description = $this->edit_physical_description ?: null;

        $influencer->update([
            'name' => $this->edit_name,
            'stagename' => $this->edit_name,
            'bio' => $this->edit_backstory ?: $influencer->bio,
            'properties' => $props,
        ]);

        Toaster::success(__('Änderungen erfolgreich gespeichert!'));
    }

    public function generateImage(string $field): void
    {
        $useGPT2 = ['character_sheet', 'detail_sheet'];
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);

        // Set generation state to generating
        $this->generationStates[$field] = ['status' => 'generating', 'error' => null];

        try {
            // Build prompt
            if ($field === 'character_sheet') {
                $prompt = PromptBuilderService::buildInfluencerSheetPrompt($influencer);
            } elseif ($field === 'closeup') {
                $prompt = PromptBuilderService::buildCloseUpPrompt($influencer);
            } elseif ($field === 'detail_sheet') {
                $prompt = PromptBuilderService::buildFeatureSheetPrompt($influencer);
            } else {
                $prompt = 'Professional turnaround sheet of the character.';
            }

            PromptBuilderService::logPrompt($prompt, null, 'Dashboard: '.ucfirst($field));

            // Resolve avatar as reference image
            $uploadedUrl = null;
            if ($influencer->avatar) {
                $avatarUrl = $influencer->avatar;
                if (str_starts_with($avatarUrl, 'http') && ! str_contains($avatarUrl, '/storage/teams/')) {
                    $uploadedUrl = $avatarUrl;
                } else {
                    $parsedPath = parse_url($avatarUrl, PHP_URL_PATH);
                    $cleanPath = str_replace('/storage/', '', $parsedPath);
                    $fullPath = Storage::disk('local')->path($cleanPath);
                    if (file_exists($fullPath)) {
                        $uploadedUrl = app(FalAiService::class)->uploadFile($team, $fullPath, mime_content_type($fullPath) ?: 'image/png');
                    }
                }
            }

            // Resolve Ideogram model config
            // $modelKey = in_array($field, $useGPT2) ? 'gpt2' : 'ideogram';
            $modelKey = ($field !== 'avatar') ? 'gpt2' : 'ideogram';
            $modelConfig = config("image_models.models.{$modelKey}");
            if (! $modelConfig) {
                throw new \Exception('Model configuration for Ideogram not found.');
            }

            $selectedModel = $uploadedUrl ? $modelConfig['model_edit'] : $modelConfig['model'];

            // Call Claude prompt enhancer if enabled
            $enhancedPrompt = $prompt;
            $negativePrompt = null;
            if ($this->use_prompt_enhancer && $team->hasClaudeApiKey()) {
                try {
                    $claudeService = app(ClaudeService::class);
                    $enhanced = $claudeService->enhancePrompt($prompt, $selectedModel, $team->claude_api_key);
                    $enhancedPrompt = $enhanced['enhanced_prompt'] ?? $prompt;
                    $negativePrompt = $enhanced['negative_prompt'] ?? null;
                } catch (\Throwable $e) {
                    Log::error('Dashboard prompt enhancement failed: '.$e->getMessage());
                }
            }

            $defaultSize = $modelConfig['default_size'] ?? 'portrait_4_3';
            $imageSize = $defaultSize;

            if ($field === 'character_sheet') {
                $sizesKeys = array_keys($modelConfig['image_sizes'] ?? []);
                if (in_array('landscape_16_9', $sizesKeys, true)) {
                    $imageSize = 'landscape_16_9';
                } elseif (in_array('landscape_4_3', $sizesKeys, true)) {
                    $imageSize = 'landscape_4_3';
                } elseif (in_array('1536x1024', $sizesKeys, true)) {
                    $imageSize = '1536x1024';
                } else {
                    $found = null;
                    foreach ($sizesKeys as $key) {
                        if (str_contains($key, 'landscape') || str_contains($key, '1536')) {
                            $found = $key;
                            break;
                        }
                    }
                    $imageSize = $found ?? $defaultSize;
                }
            } elseif ($field === 'detail_sheet') {
                $sizesKeys = array_keys($modelConfig['image_sizes'] ?? []);
                // 2:3 ratio is closest to portrait_16_9, 1024x1536, or custom portrait. Let's look for a strong portrait ratio.
                if (in_array('portrait_16_9', $sizesKeys, true)) {
                    $imageSize = 'portrait_16_9';
                } elseif (in_array('1024x1536', $sizesKeys, true)) {
                    $imageSize = '1024x1536';
                } elseif (in_array('portrait_4_3', $sizesKeys, true)) {
                    $imageSize = 'portrait_4_3';
                }
            }

            $payload = [
                'prompt' => $enhancedPrompt,
                'image_size' => $imageSize,
                'enable_safety_checker' => false,
            ];

            if ($negativePrompt) {
                $payload['negative_prompt'] = $negativePrompt;
            }

            if ($uploadedUrl) {
                if (str_contains($selectedModel, 'gpt-image-2') || str_contains($selectedModel, 'gpt2')) {
                    $payload['image_urls'] = [$uploadedUrl];
                } else {
                    $payload['image_url'] = $uploadedUrl;
                }
            }

            // Call FAL.AI Queue
            $res = app(FalAiService::class)->queue($team, $selectedModel, $payload);
            $requestId = $res['request_id'] ?? null;

            if (! $requestId) {
                throw new \Exception('No request ID returned from FAL.AI Queue.');
            }

            $this->generationStates[$field] = [
                'status' => 'generating',
                'request_id' => $requestId,
                'model' => $selectedModel,
                'queue_status' => 'IN_QUEUE',
                'basic_prompt' => $prompt,
                'enhanced_prompt' => $this->use_prompt_enhancer && $team->hasClaudeApiKey() ? $enhancedPrompt : null,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error("Dashboard::generateImage error: {$e->getMessage()}", [
                'field' => $field,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->generationStates[$field] = ['status' => 'failed', 'error' => $e->getMessage()];
            Toaster::error(__('Ausnahme aufgetreten: :error', ['error' => $e->getMessage()]));
        }
    }

    public function checkGenerationProgress(): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $team = Team::find($influencer->team_id) ?: Team::first();
        if (! $team) {
            return;
        }

        $service = app(FalAiService::class);

        foreach ($this->generationStates as $field => $state) {
            if (($state['status'] ?? '') !== 'generating') {
                continue;
            }

            $requestId = $state['request_id'] ?? null;
            $model = $state['model'] ?? null;

            if (! $requestId || ! $model) {
                continue;
            }

            try {
                $statusRes = $service->checkQueueStatus($team, $model, $requestId);
                $status = $statusRes['status'] ?? 'IN_QUEUE';
                $this->generationStates[$field]['queue_status'] = $status;

                if ($status === 'COMPLETED') {
                    $response = $service->getQueueResponse($team, $model, $requestId);
                    $remoteUrl = $response['images'][0]['url'] ?? null;

                    if (! $remoteUrl) {
                        throw new \Exception('No image URL returned from FAL.AI Queue response.');
                    }

                    $metaData = [
                        'module' => 'dashboard',
                        'basic_prompt' => $state['basic_prompt'] ?? null,
                        'enhanced_prompt' => $state['enhanced_prompt'] ?? null,
                    ];

                    $ext = pathinfo(parse_url($remoteUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                    if ($field === 'avatar') {
                        $destPath = "teams/{$team->id}/influencers/{$influencer->id}/avatar.{$ext}";
                        $localUrl = $service->downloadAndRegister($team, $remoteUrl, 'avatar', $destPath, $metaData);
                        $influencer->update(['avatar' => $localUrl]);
                    } else {
                        $destPath = "teams/{$team->id}/influencers/{$influencer->id}/references/{$field}_".time().".{$ext}";
                        $localUrl = $service->downloadAndRegister($team, $remoteUrl, $field, $destPath, $metaData);
                        $props = $influencer->properties ?? new InfluencerProperties;
                        $props->{$field} = $localUrl;
                        $influencer->update(['properties' => $props]);
                    }

                    $influencer->refresh();
                    unset($this->generationStates[$field]);
                    Toaster::success(__(':field erfolgreich generiert!', ['field' => ucfirst(str_replace('_', ' ', $field))]));
                } elseif ($status === 'FAILED') {
                    $this->generationStates[$field] = [
                        'status' => 'failed',
                        'error' => 'Generation failed on FAL.AI side.',
                    ];
                    Toaster::error(__('Fehler bei der Generierung: :error', ['error' => 'FAL.AI queue task failed.']));
                }
            } catch (\Throwable $e) {
                Log::error('Dashboard::checkGenerationProgress: Failed for field '.$field, [
                    'error' => $e->getMessage(),
                ]);
                $this->generationStates[$field] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
                Toaster::error(__('Ausnahme aufgetreten: :error', ['error' => $e->getMessage()]));
            }
        }
    }

    // Handles files livewire uploads
    public function updatedUploadedAvatar(): void
    {
        $this->validate(['uploaded_avatar' => 'image|max:5120']);
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);
            $ext = $this->uploaded_avatar->getClientOriginalExtension();
            $destPath = "teams/{$team->id}/influencers/{$influencer->id}/avatar.{$ext}";
            $localUrl = app(FalAiService::class)->downloadAndRegister($team, $this->uploaded_avatar->getRealPath(), 'avatar', $destPath);
            $influencer->update(['avatar' => $localUrl]);
            Toaster::success(__('Avatar erfolgreich ersetzt!'));
        }
    }

    public function updatedUploadedCharacterSheet(): void
    {
        $this->validate(['uploaded_character_sheet' => 'image|max:5120']);
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);
            $ext = $this->uploaded_character_sheet->getClientOriginalExtension();
            $destPath = "teams/{$team->id}/influencers/{$influencer->id}/references/character_sheet_".time().".{$ext}";
            $localUrl = app(FalAiService::class)->downloadAndRegister($team, $this->uploaded_character_sheet->getRealPath(), 'character_sheet', $destPath);
            $this->updatePropertyImage('character_sheet', $localUrl);
        }
    }

    public function updatedUploadedCloseup(): void
    {
        $this->validate(['uploaded_closeup' => 'image|max:5120']);
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);
            $ext = $this->uploaded_closeup->getClientOriginalExtension();
            $destPath = "teams/{$team->id}/influencers/{$influencer->id}/references/closeup_".time().".{$ext}";
            $localUrl = app(FalAiService::class)->downloadAndRegister($team, $this->uploaded_closeup->getRealPath(), 'closeup', $destPath);
            $this->updatePropertyImage('closeup', $localUrl);
        }
    }

    public function updatedUploadedDetailSheet(): void
    {
        $this->validate(['uploaded_detail_sheet' => 'image|max:5120']);
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);
            $ext = $this->uploaded_detail_sheet->getClientOriginalExtension();
            $destPath = "teams/{$team->id}/influencers/{$influencer->id}/references/detail_sheet_".time().".{$ext}";
            $localUrl = app(FalAiService::class)->downloadAndRegister($team, $this->uploaded_detail_sheet->getRealPath(), 'detail_sheet', $destPath);
            $this->updatePropertyImage('detail_sheet', $localUrl);
        }
    }

    private function updatePropertyImage(string $field, string $url): void
    {
        $influencer = $this->selectedInfluencer;
        if ($influencer) {
            $props = $influencer->properties ?? new InfluencerProperties;
            $props->{$field} = $url;
            $influencer->update(['properties' => $props]);
            $influencer->refresh();
            Toaster::success(__(ucfirst(str_replace('_', ' ', $field)).' erfolgreich ersetzt!'));
        }
    }

    public function downloadImage(string $field)
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $url = ($field === 'avatar')
            ? $influencer->avatar
            : ($influencer->properties->{$field} ?? null);

        if (! $url) {
            Toaster::error(__('Keine Bilddatei vorhanden.'));

            return;
        }

        // Check if the URL belongs to private local disk
        if (str_starts_with($url, '/storage/')) {
            $pathAfterStorage = substr($url, strlen('/storage/'));
            if (Storage::disk('local')->exists($pathAfterStorage)) {
                return response()->download(Storage::disk('local')->path($pathAfterStorage));
            }
        }

        // Map URL back to path
        $relativePath = str_replace('/storage/', 'app/public/', $url);
        $absolutePath = storage_path($relativePath);
        if (! file_exists($absolutePath)) {
            $absolutePath = public_path(str_replace('/storage/', 'storage/', $url));
        }

        if (file_exists($absolutePath)) {
            return response()->download($absolutePath);
        }

        Toaster::error(__('Datei konnte nicht auf der Festplatte gefunden werden.'));
    }

    #[Computed]
    public function profileCompleteness(): int
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return 0;
        }

        $score = 0;
        if (! empty($influencer->name)) {
            $score += 10;
        }
        if ($influencer->properties->age) {
            $score += 10;
        }
        if ($influencer->properties->gender) {
            $score += 10;
        }
        if (! empty($influencer->properties->niche)) {
            $score += 10;
        }
        if (! empty($influencer->properties->backstory)) {
            $score += 15;
        }
        if ($influencer->properties->character_sheet) {
            $score += 10;
        }
        if ($influencer->properties->closeup) {
            $score += 10;
        }
        if ($influencer->properties->detail_sheet) {
            $score += 10;
        }
        if ($influencer->properties->location) {
            $score += 5;
        }
        if ($influencer->properties->physical_description) {
            $score += 10;
        }

        return min(100, $score);
    }

    #[Computed]
    public function generatedPrompt(): string
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return '';
        }

        $props = $influencer->properties;
        $name = $influencer->name;
        $gender = $props->gender ?? 'Female';
        $age = $props->age ?? '22';
        $niche = implode(', ', $props->niche ?? ['Fashion']);
        $ethnicity = $props->ethnicity ?? 'White';
        $hair = trim(($props->hair_length ?? 'Long').' '.($props->hair_texture ?? 'Straight').' '.($props->hair_color ?? 'Blonde'));
        $build = $props->build ?? 'Petite';
        $vibe = $props->aesthetic_vibe ?? 'Minimalist';

        return "Candid iPhone photo of {$name}, a {$age}-year-old {$gender} {$niche} influencer. ".
               "Physical features: {$ethnicity} ethnicity, {$hair} hair, {$build} build, ".($props->skin_tone ?? 'Fair').' skin tone. '.
               "Wearing a complete outfit reflecting the '{$vibe}' aesthetic. ".
               'Reproducing all clothing, headwear, and accessories exactly. '.
               'Mid-action — mid-laugh, mid-sip, mid-step, or mid-reach — body fully committed to the action, expression caught at the apex. '.
               'Eyes can be on lens (late-arrival) or completely off-axis. Hands engaged with the action, not posed. '.
               'Expression: direct and serious — neutral mouth at rest, steady gaze into the lens, no smile. Composed and self-assured. '.
               'Soft morning window light from one side, cool and directional. Eye level, 24mm, handheld, f/1.8, close-up framing. '.
               'Deep focus, no bokeh, photorealistic. No other people in frame.';
    }

    private function getRandomOutfitImage(): string
    {
        $images = [];
        $dir = public_path('storage/influencer');
        if (File::isDirectory($dir)) {
            $files = File::files($dir);
            foreach ($files as $file) {
                $images[] = '/storage/influencer/'.$file->getFilename();
            }
        }

        if (empty($images)) {
            for ($i = 1; $i <= 46; $i++) {
                $ext = in_array($i, [3, 4, 6]) ? 'jpg' : 'png';
                $images[] = "/storage/influencer/i{$i}.{$ext}";
            }
        }

        return collect($images)->random();
    }

    public function generateOutfit(): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            Toaster::error(__('Kein Influencer ausgewählt.'));

            return;
        }

        $this->validate([
            'outfit_top' => 'nullable|string|max:255',
            'outfit_bottom' => 'nullable|string|max:255',
            'outfit_hairstyle' => 'nullable|string|max:255',
            'outfit_description' => 'nullable|string|max:1000',
        ]);

        $image = $this->getRandomOutfitImage();
        $localUrl = $this->storeOutfitImage($influencer, $image);

        $influencer->outfits()->create([
            'name' => 'Outfit #'.($influencer->outfits()->count() + 1),
            'top' => $this->outfit_top,
            'bottom' => $this->outfit_bottom,
            'hairstyle' => $this->outfit_hairstyle,
            'full_look_description' => $this->outfit_description,
            'image_path' => $localUrl,
        ]);

        Toaster::success(__('Outfit erfolgreich generiert und zur Garderobe hinzugefügt!'));
    }

    public function addOutfitPrompt(): void
    {
        if (! $this->selectedId) {
            Toaster::error(__('Kein Influencer ausgewählt.'));

            return;
        }
        $this->newOutfitName = '';
        $this->showAddOutfitModal = true;
    }

    public function saveNewOutfit(): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $this->validate([
            'newOutfitName' => 'required|string|min:1|max:100',
            'outfit_top' => 'nullable|string|max:255',
            'outfit_bottom' => 'nullable|string|max:255',
            'outfit_hairstyle' => 'nullable|string|max:255',
            'outfit_description' => 'nullable|string|max:1000',
        ]);

        $image = $this->getRandomOutfitImage();
        $localUrl = $this->storeOutfitImage($influencer, $image);

        $influencer->outfits()->create([
            'name' => $this->newOutfitName,
            'top' => $this->outfit_top,
            'bottom' => $this->outfit_bottom,
            'hairstyle' => $this->outfit_hairstyle,
            'full_look_description' => $this->outfit_description,
            'image_path' => $localUrl,
        ]);

        $this->showAddOutfitModal = false;
        $this->newOutfitName = '';

        Toaster::success(__('Outfit erfolgreich gespeichert!'));
    }

    private function storeOutfitImage(Influencer $influencer, string $sourceImage): string
    {
        $team = Team::find($influencer->team_id) ?: Team::first() ?: Team::create(['name' => 'Default Team']);
        $ext = pathinfo($sourceImage, PATHINFO_EXTENSION) ?: 'png';
        $destPath = "teams/{$team->id}/influencers/{$influencer->id}/wardrobes/outfit_".time().'_'.rand(1000, 9999).".{$ext}";

        return app(FalAiService::class)->downloadAndRegister($team, $sourceImage, 'outfit', $destPath);
    }

    public function selectOutfit(string $id): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $outfit = $influencer->outfits()->find($id);
        if ($outfit) {
            $this->outfit_top = $outfit->top ?? '';
            $this->outfit_bottom = $outfit->bottom ?? '';
            $this->outfit_hairstyle = $outfit->hairstyle ?? '';
            $this->outfit_description = $outfit->full_look_description ?? '';
            $this->activeOutfitId = $id;
            $this->showOutfitOverlay = true;
        }
    }

    public function deleteOutfit(string $id): void
    {
        $influencer = $this->selectedInfluencer;
        if (! $influencer) {
            return;
        }

        $outfit = $influencer->outfits()->find($id);
        if ($outfit) {
            $outfit->delete();
            Toaster::success(__('Outfit gelöscht.'));
        }
        if ($this->activeOutfitId === $id) {
            $this->activeOutfitId = null;
            $this->showOutfitOverlay = false;
        }
    }

    #[Layout('layouts.blank')]
    public function render()
    {
        return view('livewire.dashboard');
    }
}
