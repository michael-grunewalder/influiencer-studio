<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Models\Influencer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('dashboard is accessible by authenticated users and displays influencers', function () {
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'team_id' => $team->id,
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
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'team_id' => $team->id,
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
    $team = Team::create([
        'name' => 'Test Team',
        'credits' => 10.00,
    ]);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    // Mock API key and fake HTTP responses for Fal.ai
    config(['services.fal.key' => 'mock-global-key', 'fal_api.key' => 'mock-global-key']);
    Http::fake([
        'https://fal.run/*' => Http::response([
            'images' => [
                ['url' => 'https://v3.fal.media/files/mock-image.png'],
            ],
        ], 200),
        'https://v3.fal.media/*' => Http::response('fake binary content', 200, ['Content-Type' => 'image/png']),
    ]);

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
        ->call('generateImage', 'character_sheet')
        ->assertHasNoErrors();

    Http::assertSent(function (Request $request) {
        if ($request->url() !== 'https://fal.run/openai/gpt-image-2/edit') {
            return false;
        }
        $payload = $request->data();

        return isset($payload['image_urls']) &&
               is_array($payload['image_urls']) &&
               count($payload['image_urls']) === 1 &&
               ! isset($payload['image_url']);
    });

    $influencer->refresh();
    $sheetUrl = $influencer->properties->character_sheet;
    expect($sheetUrl)->toContain('/storage/teams/')->toContain('/references/character_sheet_')->toContain('signature=');

    $parsedPath = parse_url($sheetUrl, PHP_URL_PATH);
    $path = str_replace('/storage/', '', $parsedPath);
    expect(Storage::disk('local')->exists($path))->toBeTrue();

    $this->assertDatabaseHas('team_assets', [
        'team_id' => $team->id,
        'local_url' => $parsedPath,
        'purpose' => 'character_sheet',
    ]);
});

test('user can delete an influencer on dashboard', function () {
    $team = Team::create(['name' => 'Test Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'team_id' => $team->id,
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
