<?php

use App\Livewire\SidebarBalances;
use App\Livewire\Teams;
use App\Models\Auth\Permission;
use App\Models\Team;
use App\Models\User;
use App\Services\FalAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    cache()->flush();
    Permission::findOrCreate('team.view-all', 'web');
    Permission::findOrCreate('team.view', 'web');
    Permission::findOrCreate('team.manage', 'web');
    Permission::findOrCreate('team.admin', 'web');
});

test('getAccountBalance returns mock data for bearny-codes key', function () {
    Http::preventStrayRequests();

    $service = new FalAiService;
    $balance = $service->getAccountBalance('bearny-codes');

    expect($balance)->not->toBeNull();
    expect($balance['username'])->toBe('bearny-user-mock');
    expect($balance['credits']['current_balance'])->toBe(99.75);
    expect($balance['credits']['currency'])->toBe('USD');
});

test('getAccountBalance executes HTTP call and returns API data', function () {
    Http::fake([
        'https://api.fal.ai/v1/account/billing?expand=credits' => Http::response([
            'username' => 'test-user-real',
            'credits' => [
                'current_balance' => 45.50,
                'currency' => 'USD',
            ],
        ], 200),
    ]);

    $service = new FalAiService;
    $balance = $service->getAccountBalance('some-real-looking-key');

    expect($balance)->not->toBeNull();
    expect($balance['username'])->toBe('test-user-real');
    expect($balance['credits']['current_balance'])->toBe(45.50);
});

test('teams component fetches and exposes FAL.AI balance on selectTeam', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view');

    $team = Team::create([
        'name' => 'Design Team',
        'fal_api_key' => 'bearny-codes',
    ]);
    $user->teams()->attach($team, ['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->call('selectTeam', $team->id)
        ->assertSet('fal_account_balance.username', 'bearny-user-mock')
        ->assertSet('fal_account_balance.credits.current_balance', 99.75);
});

test('teams component fetches and exposes FAL.AI balance on saveTeam', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view');

    $team = Team::create([
        'name' => 'Design Team',
        'fal_api_key' => null,
    ]);
    $user->teams()->attach($team, ['role' => 'admin']);

    config(['fal_api.key' => null, 'services.fal.key' => null]);

    Livewire::actingAs($user)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->call('selectTeam', $team->id)
        ->assertSet('fal_account_balance', null)
        ->set('team_name', 'Updated Team Name')
        ->set('team_fal_api_key', 'bearny-codes')
        ->call('saveTeam')
        ->assertHasNoErrors()
        ->assertSet('fal_account_balance.username', 'bearny-user-mock');
});

test('sidebar balances component fetches and displays FAL.AI balance', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view');

    $team = Team::create([
        'name' => 'Design Team',
        'fal_api_key' => 'bearny-codes',
    ]);
    $user->teams()->attach($team, ['role' => 'admin']);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(SidebarBalances::class)
        ->assertViewHas('falBalance', function ($balance) {
            return $balance !== null &&
                   $balance['username'] === 'bearny-user-mock' &&
                   $balance['credits']['current_balance'] === 99.75;
        })
        ->assertSee('FAL.AI Balance')
        ->assertSee('$99.75');
});

test('sidebar balances component does not display FAL.AI balance if team has no key', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view');

    $team = Team::create([
        'name' => 'Design Team',
        'fal_api_key' => null,
    ]);
    $user->teams()->attach($team, ['role' => 'admin']);

    session(['active_team_id' => $team->id]);

    // Clear configs just in case
    config(['fal_api.key' => null, 'services.fal.key' => null]);

    Livewire::actingAs($user)
        ->test(SidebarBalances::class)
        ->assertViewHas('falBalance', null)
        ->assertDontSee('FAL.AI Balance');
});
