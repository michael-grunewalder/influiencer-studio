<?php

use App\Livewire\InfluencerWizard;
use App\Models\Influencer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('influencer wizard page can be accessed by authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('influencers.create'))
        ->assertOk();
});

test('influencer wizard page redirects guests to login', function () {
    $this->get(route('influencers.create'))
        ->assertRedirect(route('login'));
});

test('influencer wizard validates basic information on step 1', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(InfluencerWizard::class)
        ->set('step', 1)
        ->set('name', '')
        ->set('age', '')
        ->set('niches', [])
        ->call('nextStep')
        ->assertHasErrors(['name', 'age', 'niches'])
        ->assertSet('step', 1);
});

test('influencer wizard can progress and create an influencer with credit system', function () {
    $user = User::factory()->create();
    $team = Team::create([
        'name' => 'Test Team',
        'credits' => 10.00,
    ]);
    $user->teams()->attach($team);

    // Mock global API key in config
    config(['services.fal.key' => 'mock-global-key']);

    // Mock Fal.ai image generation API
    Http::fake([
        'https://fal.run/*' => Http::response([
            'images' => [
                ['url' => 'https://v3.fal.media/files/mock-image.png'],
            ],
        ], 200),
    ]);

    $test = Livewire::actingAs($user)
        ->test(InfluencerWizard::class)
        // Step 1
        ->set('name', 'Elena Sterling')
        ->set('gender', 'Female')
        ->set('age', 26)
        ->set('niches', ['Fashion', 'Beauty'])
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 2)
        // Step 2
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 3)
        // Step 3
        ->set('backstory', 'A digital model from Milan.')
        ->set('personality', 60)
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 4)
        // Step 4
        ->set('ethnicity', 'Mixed')
        ->set('skin_tone', 'Light')
        ->set('hair_color', 'Brunette')
        ->set('hair_length', 'Long')
        ->set('hair_texture', 'Wavy')
        ->set('eye_color', 'Green')
        ->set('build', 'Slim')
        ->set('aesthetic_vibe', 'Minimalist')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 5)
        ->assertSet('is_generating', true);

    // Call generate to call Fal.ai API
    $test->call('generate')
        ->assertSet('is_generating', false)
        ->assertSet('generated_variations', [
            'https://v3.fal.media/files/mock-image.png',
            'https://v3.fal.media/files/mock-image.png',
            'https://v3.fal.media/files/mock-image.png',
        ]);

    // Check that credits were charged (3 images * $0.35 = $1.05 deducted from $10.00 = $8.95)
    $team->refresh();
    expect((float) $team->credits)->toBe(8.95);

    // Set selection index to 1
    $test->set('selected_variation_index', 1)
        ->call('finishGeneration')
        ->assertSet('is_done', true);

    // Assert database has the influencer with mock avatar
    $this->assertDatabaseHas('influencers', [
        'name' => 'Elena Sterling',
        'avatar' => 'https://v3.fal.media/files/mock-image.png',
    ]);

    $influencer = Influencer::where('name', 'Elena Sterling')->first();
    expect($influencer->properties)->not->toBeNull();
    expect($influencer->properties->gender)->toBe('Female');
    expect($influencer->properties->age)->toBe(26);
    expect($influencer->properties->niche)->toBe(['Fashion', 'Beauty']);
    expect($influencer->properties->hair_color)->toBe('Brunette');
});
