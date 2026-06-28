<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Livewire\InfluencerWizard;
use App\Models\Influencer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('a user can belong to multiple teams', function () {
    $user = User::factory()->create();
    $team1 = Team::create(['name' => 'Team Alpha']);
    $team2 = Team::create(['name' => 'Team Beta']);

    $user->teams()->attach([$team1->id, $team2->id]);

    expect($user->teams)->toHaveCount(2);
    expect($user->teams->pluck('name'))->toContain('Team Alpha', 'Team Beta');
});

test('influencer belongs to a team and is resolved via model event', function () {
    $team = Team::create(['name' => 'Team Charlie']);
    $influencer = Influencer::create([
        'team_id' => $team->id,
        'name' => 'Carla Lopez',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 24,
            niche: ['Fashion'],
        ),
    ]);

    expect($influencer->team->name)->toBe('Team Charlie');
    expect($team->influencers)->toHaveCount(1);
});

test('creating influencer without team_id auto-assigns it to active team', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Team Delta']);
    $user->teams()->attach($team->id);

    $this->actingAs($user);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'name' => 'Elena Drake',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 28,
            niche: ['Travel'],
        ),
    ]);

    expect($influencer->team_id)->toBe($team->id);
});

test('dashboard filters influencers by active team selection', function () {
    $user = User::factory()->create();
    $team1 = Team::create(['name' => 'Team One']);
    $team2 = Team::create(['name' => 'Team Two']);
    $user->teams()->attach([$team1->id, $team2->id]);

    $inf1 = Influencer::create([
        'team_id' => $team1->id,
        'name' => 'Influencer One',
        'properties' => new InfluencerProperties(gender: 'Female', age: 25, niche: ['Beauty']),
    ]);

    $inf2 = Influencer::create([
        'team_id' => $team2->id,
        'name' => 'Influencer Two',
        'properties' => new InfluencerProperties(gender: 'Male', age: 30, niche: ['Tech']),
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedTeamId', $team1->id)
        ->assertSee('Influencer One')
        ->assertDontSee('Influencer Two');

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedTeamId', $team2->id)
        ->assertSee('Influencer Two')
        ->assertDontSee('Influencer One');
});

test('wizard correctly assigns newly generated influencer to active team', function () {
    Http::fake([
        '*' => Http::response('fake image binary content', 200, ['Content-Type' => 'image/png']),
    ]);

    $user = User::factory()->create();
    $team = Team::create(['name' => 'Wizard Team']);
    $user->teams()->attach($team->id);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(InfluencerWizard::class)
        ->set('name', 'Elena Sterling')
        ->set('gender', 'Female')
        ->set('age', 26)
        ->set('niches', ['Fashion'])
        ->call('nextStep')
        ->call('nextStep')
        ->call('nextStep')
        ->call('nextStep')
        ->call('finishGeneration')
        ->assertHasNoErrors();

    $influencer = Influencer::where('name', 'Elena Sterling')->first();
    expect($influencer)->not->toBeNull();
    expect($influencer->team_id)->toBe($team->id);
});

test('deleting a team cascades and deletes associated influencers', function () {
    $team = Team::create(['name' => 'Cascade Team']);
    $influencer = Influencer::create([
        'team_id' => $team->id,
        'name' => 'Cascade Model',
        'properties' => new InfluencerProperties(gender: 'Female', age: 22, niche: ['Sports']),
    ]);
});

test('active team is initialized to default team if mode is default', function () {
    $user = User::factory()->create(['team_selection_mode' => 'default']);
    $team1 = Team::create(['name' => 'Team One']);
    $team2 = Team::create(['name' => 'Team Two']);
    $user->teams()->attach([$team1->id, $team2->id]);

    $user->update(['default_team_id' => $team2->id]);

    // Send a request to dashboard as $user (without active_team_id in session)
    $this->actingAs($user)
        ->get('/')
        ->assertOk();

    // Verify session active_team_id has been set to the default team
    expect(session('active_team_id'))->toBe($team2->id);
});

test('active team is initialized to last active team if mode is last_used', function () {
    $user = User::factory()->create([
        'team_selection_mode' => 'last_used',
    ]);
    $team1 = Team::create(['name' => 'Team One']);
    $team2 = Team::create(['name' => 'Team Two']);
    $user->teams()->attach([$team1->id, $team2->id]);

    $user->update([
        'default_team_id' => $team1->id,
        'last_active_team_id' => $team2->id,
    ]);

    // Send a request to dashboard as $user (without active_team_id in session)
    $this->actingAs($user)
        ->get('/')
        ->assertOk();

    // Verify session active_team_id has been set to the last active team
    expect(session('active_team_id'))->toBe($team2->id);
});
