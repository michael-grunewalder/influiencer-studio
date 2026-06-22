<?php

namespace App\Livewire;

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Models\Team;
use App\Services\FalAiService;
use App\Services\PromptBuilderService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class InfluencerWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1: Basics
    public string $name = '';

    public string $gender = 'Female';

    public string $age = '';

    public array $niches = [];

    // Step 2: References
    public $face_reference = null;

    public $style_reference = null;

    // Step 3: Story
    public string $backstory = '';

    public int $personality = 50; // 0 = Introvert, 50 = Balanced, 100 = Extrovert

    // Step 4: Look (Physical appearance)
    public string $ethnicity = 'White';

    public string $skin_tone = 'Fair';

    public string $hair_color = 'Blonde';

    public string $hair_length = 'Long';

    public string $hair_texture = 'Straight';

    public string $eye_color = 'Blue';

    public string $build = 'Petite';

    public string $custom_description = '';

    public string $aesthetic_vibe = '';

    // Step 5: Generate
    public bool $is_generating = false;

    public bool $is_done = false;

    public array $generated_variations = [];

    public int $selected_variation_index = 0;

    public ?string $generated_id = null;

    public ?string $generated_avatar = null;

    // Slide images list
    public array $slideshow_images = [];

    // Lists of options for Step 1
    public array $available_niches = [
        'Fashion', 'Beauty', 'Lifestyle', 'Fitness', 'Travel',
        'Food & Dining', 'Tech', 'Gaming', 'Finance',
        'Entertainment', 'Wellness', 'Sports', 'Other',
    ];

    // Lists of options for Step 4
    public array $ethnicities = ['White', 'Black', 'Hispanic', 'East Asian', 'South Asian', 'Middle Eastern', 'Southeast Asian', 'Mixed'];

    public array $skin_tones = ['Fair', 'Light', 'Medium', 'Tan', 'Brown', 'Deep', 'Ebony'];

    public array $hair_colors = ['Blonde', 'Brunette', 'Black', 'Auburn', 'Red', 'Silver', 'Dyed'];

    public array $hair_lengths = ['Short', 'Medium', 'Long', 'Extra long'];

    public array $hair_textures = ['Straight', 'Wavy', 'Curly', 'Coily'];

    public array $eye_colors = ['Blue', 'Green', 'Brown', 'Hazel', 'Dark', 'Grey'];

    public array $builds = ['Petite', 'Slim', 'Athletic', 'Average', 'Curvy', 'Tall', 'Plus'];

    public array $aesthetic_vibes = [
        ['name' => 'Minimalist', 'desc' => 'Clean, simple, less is more', 'icon' => 'o-heart'],
        ['name' => 'Old Money', 'desc' => 'Understated wealth & heritage', 'icon' => 'o-academic-cap'],
        ['name' => 'Clean Girl', 'desc' => 'Effortless, dewy, no-makeup look', 'icon' => 'o-sparkles'],
        ['name' => 'Editorial', 'desc' => 'High fashion, bold & structured', 'icon' => 'o-photo'],
        ['name' => 'Streetwear', 'desc' => 'Urban, casual street style', 'icon' => 'o-cloud'],
        ['name' => 'Bohemian', 'desc' => 'Earthy, flowy, free-spirited', 'icon' => 'o-sun'],
        ['name' => 'Glam', 'desc' => 'Dressy, dramatic & glamorous', 'icon' => 'o-star'],
        ['name' => 'Preppy', 'desc' => 'Classic, collegiate, polished', 'icon' => 'o-bookmark'],
        ['name' => 'Sporty', 'desc' => 'Athletic & activewear vibes', 'icon' => 'o-bolt'],
        ['name' => 'Dark & Moody', 'desc' => 'Alternative, edgy & dramatic', 'icon' => 'o-moon'],
        ['name' => 'Y2K', 'desc' => '2000s nostalgia & pop culture', 'icon' => 'o-musical-note'],
        ['name' => 'Cottagecore', 'desc' => 'Romantic, vintage & nature', 'icon' => 'o-home'],
        ['name' => 'Coastal', 'desc' => 'Linen, nautical, effortlessly sun-worn', 'icon' => 'o-globe-alt'],
    ];

    public function mount(): void
    {
        $this->loadSlideshowImages();
    }

    private function loadSlideshowImages(): void
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

        $this->slideshow_images = $images;
    }

    public function toggleNiche(string $niche): void
    {
        if (in_array($niche, $this->niches)) {
            $this->niches = array_diff($this->niches, [$niche]);
        } else {
            $this->niches[] = $niche;
        }
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->step < 4) {
            $this->step++;
        } elseif ($this->step === 4) {
            $team = $this->getActiveTeam();
            if (! $team) {
                Toaster::error(__('Kein aktives Team gefunden. Bitte melde dich an oder erstelle ein Team.'));

                return;
            }

            if (! $team->hasFalApiKey() && ! $team->hasCreditsFor(3)) {
                $cost = 3 * 0.35;
                Toaster::error(sprintf(
                    __('Ungenügendes Guthaben! Für 3 Variationen werden $%s benötigt. Aktuelles Guthaben: $%s. Hinterlege einen API-Key für das Team oder lade dein Guthaben auf.'),
                    number_format($cost, 2),
                    number_format($team->credits, 2)
                ));

                return;
            }

            $this->step = 5;
            $this->is_generating = true;
            $this->is_done = false;
            $this->generated_variations = [
                ['status' => 'pending', 'url' => null, 'error' => null, 'payload' => null],
                ['status' => 'pending', 'url' => null, 'error' => null, 'payload' => null],
                ['status' => 'pending', 'url' => null, 'error' => null, 'payload' => null],
            ];
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1 && ! $this->is_generating && ! $this->is_done) {
            $this->step--;
        }
    }

    public function randomizeLook(): void
    {
        $this->ethnicity = collect($this->ethnicities)->random();
        $this->skin_tone = collect($this->skin_tones)->random();
        $this->hair_color = collect($this->hair_colors)->random();
        $this->hair_length = collect($this->hair_lengths)->random();
        $this->hair_texture = collect($this->hair_textures)->random();
        $this->eye_color = collect($this->eye_colors)->random();
        $this->build = collect($this->builds)->random();
        $this->aesthetic_vibe = collect($this->aesthetic_vibes)->random()['name'];

        Toaster::info(__('Zufällige Merkmale ausgewählt!'));
    }

    /**
     * Call Fal.ai parallel generation endpoint to build 3 variations.
     */
    /**
     * Call Fal.ai parallel generation endpoint to build 3 variations using Ideogram v4.
     */
    public function generate(FalAiService $falAiService): void
    {
        if (! $this->is_generating) {
            return;
        }

        // If we already have success/failed results, or if prompts are already populated, don't run again
        $hasResults = collect($this->generated_variations)->contains(fn ($v) => in_array($v['status'], ['success', 'failed']));
        if ($hasResults || (isset($this->generated_variations[0]['prompt']) && $this->generated_variations[0]['prompt'] !== null)) {
            return;
        }

        $wizardData = [
            'name' => $this->name,
            'gender' => $this->gender,
            'age' => $this->age,
            'niches' => $this->niches,
            'backstory' => $this->backstory,
            'personality' => $this->personality,
            'ethnicity' => $this->ethnicity,
            'skin_tone' => $this->skin_tone,
            'hair_color' => $this->hair_color,
            'hair_length' => $this->hair_length,
            'hair_texture' => $this->hair_texture,
            'eye_color' => $this->eye_color,
            'build' => $this->build,
            'custom_description' => $this->custom_description,
            'aesthetic_vibe' => $this->aesthetic_vibe,
        ];

        Log::info('InfluencerWizard::generate: Starting image generation process', [
            'wizard_data' => $wizardData,
        ]);

        try {
            $team = $this->getActiveTeam();
            if (! $team) {
                throw new \Exception(__('Kein aktives Team gefunden.'));
            }

            // 1. Resolve Ideogram v4 model config
            $modelKey = 'ideogram';
            $modelConfig = config("image_models.models.{$modelKey}");
            if (! $modelConfig) {
                throw new \Exception(__('Model configuration for Ideogram v4 not found.'));
            }

            // 2. Handle reference image upload to fal.ai CDN if available
            $referenceFile = $this->face_reference ?: $this->style_reference;
            $uploadedUrl = null;
            if ($referenceFile) {
                Log::info('InfluencerWizard::generate: Reference file found, initiating upload to fal.ai CDN', [
                    'original_name' => $referenceFile->getClientOriginalName(),
                    'mime_type' => $referenceFile->getMimeType(),
                    'size' => $referenceFile->getSize(),
                ]);

                $uploadedUrl = $falAiService->uploadFile($team, $referenceFile->getRealPath(), $referenceFile->getMimeType() ?: 'image/png');

                Log::info('InfluencerWizard::generate: Reference file uploaded successfully', [
                    'uploaded_url' => $uploadedUrl,
                ]);
            }

            // Choose endpoint based on reference presence
            $selectedModel = $uploadedUrl ? $modelConfig['model_edit'] : $modelConfig['model'];

            // 3. Build variation prompts
            $physicalDesc = PromptBuilderService::buildPhysicalDescString($this);
            $prompts = PromptBuilderService::buildThreeVariationPrompts(array_merge($wizardData, [
                'physicalDesc' => $physicalDesc,
            ]), '9:16', $selectedModel);

            Log::info('InfluencerWizard::generate: Generated prompts for variations', [
                'selected_model' => $selectedModel,
                'prompts' => $prompts,
            ]);

            // 4. Compose payloads with default size 768x1024
            $payloads = [];
            $width = $modelConfig['default_size']['width'] ?? 768;
            $height = $modelConfig['default_size']['height'] ?? 1024;

            foreach ($prompts as $index => $prompt) {
                $payload = [
                    'prompt' => $prompt,
                    'image_size' => [
                        'width' => $width,
                        'height' => $height,
                    ],
                ];

                if ($uploadedUrl) {
                    $payload['image_url'] = $uploadedUrl;
                }

                $payloads[] = $payload;

                // Store details in components state
                $this->generated_variations[$index]['prompt'] = $prompt;
                $this->generated_variations[$index]['payload'] = $payload;
            }

            Log::info('InfluencerWizard::generate: Prepared payloads for parallel generation', [
                'selected_model' => $selectedModel,
                'payloads' => $payloads,
            ]);

            // 5. Generate parallel variations
            $results = $falAiService->generateParallelPayloads($team, $payloads, $selectedModel);

            foreach ($results as $index => $res) {
                if ($res['status'] === 'success') {
                    $this->generated_variations[$index]['status'] = 'success';
                    $this->generated_variations[$index]['url'] = $res['images'][0]['url'] ?? null;
                    $this->generated_variations[$index]['error'] = null;
                } else {
                    $this->generated_variations[$index]['status'] = 'failed';
                    $this->generated_variations[$index]['error'] = $res['error'] ?? 'Unknown error';
                    $this->generated_variations[$index]['url'] = null;
                }
            }

            // Find first successful variation and set as selected_variation_index
            $firstSuccess = null;
            foreach ($this->generated_variations as $idx => $var) {
                if ($var['status'] === 'success') {
                    $firstSuccess = $idx;
                    break;
                }
            }
            $this->selected_variation_index = $firstSuccess ?? 0;

            $this->is_generating = false;
            $this->dispatch('credits-updated');

            Log::info('InfluencerWizard::generate: Finished generating variations', [
                'generated_variations' => $this->generated_variations,
            ]);

            Toaster::success(__('Variationen erfolgreich generiert! Wähle deinen Favoriten.'));
        } catch (\Throwable $e) {
            Log::error('InfluencerWizard::generate: Image generation failed', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'wizard_data' => $wizardData,
                'uploaded_url' => $uploadedUrl ?? null,
            ]);

            $this->is_generating = false;
            $this->step = 4;
            Toaster::error(__('Fehler bei der Bildgenerierung: ').$e->getMessage());
        }
    }

    /**
     * Retry generation for a single variation that failed.
     */
    public function retryGeneration(int $index, FalAiService $falAiService): void
    {
        if (! isset($this->generated_variations[$index])) {
            return;
        }

        $variation = $this->generated_variations[$index];
        if (! $variation['payload']) {
            Toaster::error(__('Kein Payload für diese Option gefunden.'));

            return;
        }

        $this->generated_variations[$index]['status'] = 'pending';
        $this->generated_variations[$index]['error'] = null;
        $this->generated_variations[$index]['url'] = null;

        try {
            $team = $this->getActiveTeam();
            if (! $team) {
                throw new \Exception(__('Kein aktives Team gefunden.'));
            }

            $modelKey = 'ideogram';
            $modelConfig = config("image_models.models.{$modelKey}");
            if (! $modelConfig) {
                throw new \Exception(__('Model configuration for Ideogram v4 not found.'));
            }

            $uploadedUrl = $variation['payload']['image_url'] ?? null;
            $selectedModel = $uploadedUrl ? $modelConfig['model_edit'] : $modelConfig['model'];

            Log::info('InfluencerWizard::retryGeneration: Retrying single request', [
                'index' => $index,
                'selected_model' => $selectedModel,
                'payload' => $variation['payload'],
            ]);

            // Single payload retry using parallel generation logic to maintain consistency
            $results = $falAiService->generateParallelPayloads($team, [$variation['payload']], $selectedModel);
            $res = $results[0] ?? null;

            if ($res && $res['status'] === 'success') {
                $this->generated_variations[$index]['status'] = 'success';
                $this->generated_variations[$index]['url'] = $res['images'][0]['url'] ?? null;

                // If the selected variation index was on this and it failed, keep/set it selected
                if ($this->selected_variation_index === $index) {
                    $this->selected_variation_index = $index;
                } else {
                    // If the current selection is not a success, select this one
                    $currentSelection = $this->generated_variations[$this->selected_variation_index] ?? null;
                    if (! $currentSelection || ($currentSelection['status'] ?? '') !== 'success') {
                        $this->selected_variation_index = $index;
                    }
                }

                $this->dispatch('credits-updated');
                Toaster::success(__('Variation erfolgreich neu generiert!'));
            } else {
                $this->generated_variations[$index]['status'] = 'failed';
                $this->generated_variations[$index]['error'] = $res['error'] ?? 'Unknown error';
                Toaster::error(__('Fehler bei der Neugenerierung.'));
            }
        } catch (\Throwable $e) {
            Log::error('InfluencerWizard::retryGeneration: Retry failed', [
                'index' => $index,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->generated_variations[$index]['status'] = 'failed';
            $this->generated_variations[$index]['error'] = $e->getMessage();
            Toaster::error(__('Fehler bei der Bildgenerierung: ').$e->getMessage());
        }
    }

    public function finishGeneration(): void
    {
        if ($this->is_done) {
            return;
        }

        $team = $this->getActiveTeam() ?: Team::first() ?: Team::create(['name' => 'Default Team']);
        $teamId = $team->id;
        $influencerId = (string) Str::ulid();

        $facePath = null;
        if ($this->face_reference) {
            $ext = $this->face_reference->getClientOriginalExtension();
            $destPath = "teams/{$teamId}/influencers/{$influencerId}/references/face_reference_".time().".{$ext}";
            $facePath = app(FalAiService::class)->downloadAndRegister($team, $this->face_reference->getRealPath(), 'face_reference', $destPath);
        }

        $stylePath = null;
        if ($this->style_reference) {
            $ext = $this->style_reference->getClientOriginalExtension();
            $destPath = "teams/{$teamId}/influencers/{$influencerId}/references/style_reference_".time().".{$ext}";
            $stylePath = app(FalAiService::class)->downloadAndRegister($team, $this->style_reference->getRealPath(), 'style_reference', $destPath);
        }

        $variation = $this->generated_variations[$this->selected_variation_index] ?? null;
        $selectedAvatar = ($variation && isset($variation['url'])) ? $variation['url'] : 'https://picsum.photos/720/1280';

        $ext = pathinfo(parse_url($selectedAvatar, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
        $destPath = "teams/{$teamId}/influencers/{$influencerId}/avatar.{$ext}";
        $localAvatarUrl = app(FalAiService::class)->downloadAndRegister($team, $selectedAvatar, 'avatar', $destPath);
        $this->generated_avatar = $localAvatarUrl;

        $properties = new InfluencerProperties(
            gender: $this->gender,
            age: (int) $this->age,
            niche: $this->niches,
            backstory: $this->backstory ?: null,
            personality: $this->personality,
            face_reference: $facePath,
            style_reference: $stylePath,
            ethnicity: $this->ethnicity,
            skin_tone: $this->skin_tone,
            hair_color: $this->hair_color,
            hair_length: $this->hair_length,
            hair_texture: $this->hair_texture,
            eye_color: $this->eye_color,
            build: $this->build,
            custom_description: $this->custom_description ?: null,
            aesthetic_vibe: $this->aesthetic_vibe ?: null,
            closeup: $localAvatarUrl,
        );

        $influencer = Influencer::create([
            'id' => $influencerId,
            'team_id' => $teamId,
            'name' => $this->name,
            'stagename' => $this->name,
            'avatar' => $localAvatarUrl,
            'bio' => $this->backstory ?: ($this->name.' is a digital influencer specialized in '.implode(', ', $this->niches).'.'),
            'properties' => $properties,
        ]);

        $this->generated_id = $influencer->id;
        $this->is_done = true;

        Toaster::success(__('Influencer erfolgreich generiert!'));
    }

    private function getActiveTeam(): ?Team
    {
        $teamId = session('active_team_id');
        if ($teamId) {
            return Team::find($teamId);
        }

        if (auth()->check()) {
            $user = auth()->user();
            $team = $user->teams()->first();
            if ($team) {
                session(['active_team_id' => $team->id]);

                return $team;
            }

            $team = Team::first() ?: Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
            if (! $user->teams()->where('teams.id', $team->id)->exists()) {
                $user->teams()->attach($team);
            }
            session(['active_team_id' => $team->id]);

            return $team;
        }

        return null;
    }

    private function validateCurrentStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'name' => 'required|string|min:2|max:100',
                'gender' => 'required|in:Female,Male',
                'age' => 'required|integer|min:18|max:100',
                'niches' => 'required|array|min:1',
            ], [
                'name.required' => 'Bitte gib einen Namen ein.',
                'age.required' => 'Bitte gib ein Alter an.',
                'age.integer' => 'Das Alter muss eine Zahl sein.',
                'niches.required' => 'Wähle mindestens eine Nische aus.',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'face_reference' => 'nullable|image|max:5120',
                'style_reference' => 'nullable|image|max:5120',
            ]);
        } elseif ($this->step === 3) {
            $this->validate([
                'backstory' => 'nullable|string|max:1000',
                'personality' => 'required|integer|min:0|max:100',
            ]);
        } elseif ($this->step === 4) {
            $this->validate([
                'ethnicity' => 'required|string',
                'skin_tone' => 'required|string',
                'hair_color' => 'required|string',
                'hair_length' => 'required|string',
                'hair_texture' => 'required|string',
                'eye_color' => 'required|string',
                'build' => 'required|string',
                'custom_description' => 'nullable|string|max:1000',
                'aesthetic_vibe' => 'nullable|string',
            ], [
                'ethnicity.required' => 'Ethnizität ist erforderlich.',
                'skin_tone.required' => 'Hautton ist erforderlich.',
                'hair_color.required' => 'Haarfarbe ist erforderlich.',
                'hair_length.required' => 'Haarlänge ist erforderlich.',
                'hair_texture.required' => 'Haartextur ist erforderlich.',
                'eye_color.required' => 'Augenfarbe ist erforderlich.',
                'build.required' => 'Körperbau ist erforderlich.',
            ]);
        }
    }

    #[Layout('layouts.blank')]
    public function render()
    {
        return view('livewire.influencer-wizard');
    }
}
