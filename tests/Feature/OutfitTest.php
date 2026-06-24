<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Models\Influencer;
use App\Models\Outfit;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('outfits can be generated for an influencer on dashboard', function () {
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

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->set('outfit_top', 'Silk Blouse')
        ->set('outfit_bottom', 'Pleated Skirt')
        ->set('outfit_hairstyle', 'High Bun')
        ->set('outfit_description', 'A classy evening look.')
        ->call('generateOutfit')
        ->assertHasNoErrors();

    $outfit = Outfit::where('influencer_id', $influencer->id)->first();
    expect($outfit->image_path)->toContain('/storage/teams/')->toContain('/wardrobes/outfit_')->toContain('signature=');

    $parsedPath = parse_url($outfit->image_path, PHP_URL_PATH);
    $path = str_replace('/storage/', '', $parsedPath);
    expect(Storage::disk('local')->exists($path))->toBeTrue();

    $this->assertDatabaseHas('team_assets', [
        'local_url' => $parsedPath,
        'purpose' => 'outfit',
    ]);
});

test('outfits can be manually named and saved', function () {
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

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->set('outfit_top', 'Leather Jacket')
        ->set('outfit_bottom', 'Skinny Jeans')
        ->set('outfit_hairstyle', 'Messy Waves')
        ->set('newOutfitName', 'Street Vibe')
        ->call('saveNewOutfit')
        ->assertHasNoErrors();

    $outfit = Outfit::where('name', 'Street Vibe')->first();
    expect($outfit->image_path)->toContain('/storage/teams/')->toContain('/wardrobes/outfit_')->toContain('signature=');

    $parsedPath = parse_url($outfit->image_path, PHP_URL_PATH);
    $path = str_replace('/storage/', '', $parsedPath);
    expect(Storage::disk('local')->exists($path))->toBeTrue();

    $this->assertDatabaseHas('team_assets', [
        'local_url' => $parsedPath,
        'purpose' => 'outfit',
    ]);
});

test('user can select an outfit to load it into form and show overlay', function () {
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
