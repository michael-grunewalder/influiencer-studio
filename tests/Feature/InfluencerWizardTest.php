<?php

use App\Livewire\InfluencerWizard;
use App\Models\Influencer;
use App\Models\User;
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

test('influencer wizard can progress and create an influencer', function () {
    $user = User::factory()->create();

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
        // Step 2 (skip optional files)
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

    // Call finishGeneration to simulate final step completion
    $test->call('finishGeneration')
        ->assertSet('is_done', true)
        ->assertSet('is_generating', false);

    // Assert database has the influencer
    $this->assertDatabaseHas('influencers', [
        'name' => 'Elena Sterling',
    ]);

    $influencer = Influencer::where('name', 'Elena Sterling')->first();
    expect($influencer->properties)->not->toBeNull();
    expect($influencer->properties->gender)->toBe('Female');
    expect($influencer->properties->age)->toBe(26);
    expect($influencer->properties->niche)->toBe(['Fashion', 'Beauty']);
    expect($influencer->properties->hair_color)->toBe('Brunette');
});
