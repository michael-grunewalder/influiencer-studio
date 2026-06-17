<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Models\Influencer;
use App\Models\User;
use Livewire\Livewire;

test('dashboard is accessible by authenticated users and displays influencers', function () {
    $user = User::factory()->create();
    $influencer = Influencer::create([
        'name' => 'Carla Lopez',
        'avatar' => 'https://picsum.photos/720/1280',
        'bio' => 'Fashion influencer.',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 24,
            niche: ['Fashion'],
        ),
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Carla Lopez');
});

test('authenticated user can select an influencer and edit metadata', function () {
    $user = User::factory()->create();
    $influencer = Influencer::create([
        'name' => 'Marcus Aurelius',
        'avatar' => 'https://picsum.photos/720/1280',
        'properties' => new InfluencerProperties(
            gender: 'Male',
            age: 30,
            niche: ['Tech'],
        ),
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('loadInfluencerData')
        ->set('edit_name', 'Marcus Decimus')
        ->set('edit_age', 32)
        ->set('edit_location', 'Rome')
        ->set('edit_backstory', 'A Roman general.')
        ->call('saveOverview')
        ->assertHasNoErrors();

    $influencer->refresh();
    expect($influencer->name)->toBe('Marcus Decimus');
    expect($influencer->properties->age)->toBe(32);
    expect($influencer->properties->location)->toBe('Rome');
    expect($influencer->properties->backstory)->toBe('A Roman general.');
});

test('user can generate and download sheet images', function () {
    $user = User::factory()->create();
    $influencer = Influencer::create([
        'name' => 'Elena Drake',
        'avatar' => 'https://picsum.photos/720/1280',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 28,
            niche: ['Travel'],
        ),
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('generateImage', 'character_sheet')
        ->assertHasNoErrors();

    $influencer->refresh();
    expect($influencer->properties->character_sheet)->not->toBeNull();
});

test('user can delete an influencer on dashboard', function () {
    $user = User::factory()->create();
    $influencer = Influencer::create([
        'name' => 'Kayla Croft',
        'avatar' => 'https://picsum.photos/720/1280',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 26,
            niche: ['Sports'],
        ),
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('deleteInfluencer', $influencer->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('influencers', [
        'id' => $influencer->id,
    ]);
});
