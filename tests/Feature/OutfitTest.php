<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Models\Influencer;
use App\Models\Outfit;
use App\Models\User;
use Livewire\Livewire;

test('outfits can be generated for an influencer on dashboard', function () {
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
        ->set('outfit_top', 'Silk Blouse')
        ->set('outfit_bottom', 'Pleated Skirt')
        ->set('outfit_hairstyle', 'High Bun')
        ->set('outfit_description', 'A classy evening look.')
        ->call('generateOutfit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('outfits', [
        'influencer_id' => $influencer->id,
        'top' => 'Silk Blouse',
        'bottom' => 'Pleated Skirt',
        'hairstyle' => 'High Bun',
        'full_look_description' => 'A classy evening look.',
    ]);
});

test('outfits can be manually named and saved', function () {
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
        ->set('outfit_top', 'Leather Jacket')
        ->set('outfit_bottom', 'Skinny Jeans')
        ->set('outfit_hairstyle', 'Messy Waves')
        ->set('newOutfitName', 'Street Vibe')
        ->call('saveNewOutfit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('outfits', [
        'influencer_id' => $influencer->id,
        'name' => 'Street Vibe',
        'top' => 'Leather Jacket',
        'bottom' => 'Skinny Jeans',
        'hairstyle' => 'Messy Waves',
    ]);
});

test('user can select an outfit to load it into form and show overlay', function () {
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

    $outfit = Outfit::create([
        'influencer_id' => $influencer->id,
        'name' => 'Summer Casual',
        'top' => 'Crop Top',
        'bottom' => 'Denim Shorts',
        'hairstyle' => 'Beach Waves',
        'full_look_description' => 'Perfect for beach walks.',
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('selectOutfit', $outfit->id)
        ->assertSet('outfit_top', 'Crop Top')
        ->assertSet('outfit_bottom', 'Denim Shorts')
        ->assertSet('outfit_hairstyle', 'Beach Waves')
        ->assertSet('outfit_description', 'Perfect for beach walks.')
        ->assertSet('activeOutfitId', $outfit->id)
        ->assertSet('showOutfitOverlay', true);
});

test('user can delete an outfit', function () {
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

    $outfit = Outfit::create([
        'influencer_id' => $influencer->id,
        'name' => 'To Be Deleted',
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('deleteOutfit', $outfit->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('outfits', [
        'id' => $outfit->id,
    ]);
});
