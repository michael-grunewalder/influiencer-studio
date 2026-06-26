<?php

use App\Data\InfluencerProperties;
use App\Livewire\PhotoStudio;
use App\Models\Influencer;
use App\Models\Team;
use App\Models\TeamAsset;
use App\Models\User;
use Livewire\Livewire;

test('parameters can be re-used from an existing asset', function () {
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'team_id' => $team->id,
        'name' => 'Elena Drake',
        'avatar' => 'https://picsum.photos/720/1280',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 28,
            niche: ['Travel'],
        ),
    ]);

    $metaData = [
        'module' => 'photo-studio',
        'settings' => [
            'selectedModel' => 'openai/gpt-image-2/edit',
            'location' => 'beach',
            'timeOfDay' => 'golden-hour',
            'pose' => 'candid',
            'vibe' => 'luxury',
            'outfitPreset' => 'current',
            'stance' => 'standing',
            'aspectRatio' => '16:9',
            'resolution' => '4k',
            'outputCount' => 1,
            'expression' => 'smiling',
            'gaze' => 'looking-away',
            'propText' => 'holding sunglasses',
            'wardrobeText' => 'summer dress',
            'hairstyleText' => 'ponytail',
            'locationText' => '',
            'poseText' => '',
            'hairstyleLocked' => true,
            'selectedOutfitId' => null,
        ],
        'basic_prompt' => 'Model: openai/gpt-image-2/edit, Stance: standing, Vibe: luxury, Location: beach, Pose: candid, Custom Outfit: summer dress, Hairstyle: ponytail, Props: holding sunglasses',
        'enhanced_prompt' => 'An enhanced prompt here',
    ];

    $asset = TeamAsset::create([
        'team_id' => $team->id,
        'local_url' => '/storage/teams/test/photo.png',
        'remote_url' => 'https://fal.media/test/photo.png',
        'mime_type' => 'image/png',
        'purpose' => "photo-studio-{$influencer->id}",
        'meta_data' => $metaData,
    ]);

    // Mount Livewire test and execute reUse()
    Livewire::actingAs($user)
        ->test(PhotoStudio::class, ['influencer' => $influencer])
        ->assertSet('reUseAssetId', null)
        ->call('reUse', $asset->id)
        ->assertSet('reUseAssetId', $asset->id)
        ->assertSet('location', 'beach')
        ->assertSet('timeOfDay', 'golden-hour')
        ->assertSet('pose', 'candid')
        ->assertSet('vibe', 'luxury')
        ->assertSet('aspectRatio', '16:9')
        ->assertSet('expression', 'smiling')
        ->assertSet('gaze', 'looking-away')
        ->assertSet('propText', 'holding sunglasses')
        ->assertSet('wardrobeText', 'summer dress')
        ->assertSet('hairstyleText', 'ponytail')
        ->assertSet('hairstyleLocked', true);
});

test('re-use reference is cleared when new pose selections are made', function () {
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);

    $influencer = Influencer::create([
        'team_id' => $team->id,
        'name' => 'Elena Drake',
        'properties' => new InfluencerProperties(gender: 'Female'),
    ]);

    $metaData = [
        'module' => 'photo-studio',
        'settings' => [
            'pose' => 'candid',
        ],
    ];

    $asset = TeamAsset::create([
        'team_id' => $team->id,
        'local_url' => '/storage/teams/test/photo.png',
        'remote_url' => 'https://fal.media/test/photo.png',
        'mime_type' => 'image/png',
        'purpose' => "photo-studio-{$influencer->id}",
        'meta_data' => $metaData,
    ]);

    // Test clearing when selecting a new preset pose
    Livewire::actingAs($user)
        ->test(PhotoStudio::class, ['influencer' => $influencer])
        ->call('reUse', $asset->id)
        ->assertSet('reUseAssetId', $asset->id)
        ->call('selectPose', 'front')
        ->assertSet('reUseAssetId', null);

    // Test clearing when typing custom pose description
    Livewire::actingAs($user)
        ->test(PhotoStudio::class, ['influencer' => $influencer])
        ->call('reUse', $asset->id)
        ->assertSet('reUseAssetId', $asset->id)
        ->set('poseText', 'Standing confidently leaning on a counter')
        ->assertSet('reUseAssetId', null);
});
