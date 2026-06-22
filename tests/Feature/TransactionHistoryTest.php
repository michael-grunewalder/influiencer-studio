<?php

use App\Livewire\TopUp;
use App\Models\Auth\Permission;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FalAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('team.view-all', 'web');
    Permission::findOrCreate('team.view', 'web');
    Permission::findOrCreate('team.manage', 'web');
    Permission::findOrCreate('team.admin', 'web');
});

test('processPayment records transactions correctly', function () {
    $user = User::factory()->create(['credits' => 5.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team, ['role' => 'view']);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '30.00')
        ->set('allocationMode', 'split')
        ->set('teamAmount', '10.00')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '12/28')
        ->set('card_cvc', '123')
        ->call('processPayment')
        ->assertHasNoErrors();

    // Verification
    $user->refresh();
    $team->refresh();

    // Check balances
    expect((float) $user->credits)->toBe(25.00); // 5.00 + 20.00
    expect((float) $team->credits)->toBe(20.00); // 10.00 + 10.00

    // Check transactions table
    $topupTx = Transaction::where('type', 'topup')->whereNull('team_id')->first();
    expect($topupTx)->not->toBeNull();
    expect((float) $topupTx->amount)->toBe(20.00);
    expect($topupTx->user_id)->toBe($user->id);

    $teamTx = Transaction::where('type', 'topup')->whereNotNull('team_id')->first();
    expect($teamTx)->not->toBeNull();
    expect((float) $teamTx->amount)->toBe(10.00);
    expect($teamTx->team_id)->toBe($team->id);
    expect($teamTx->user_id)->toBe($user->id);
});

test('transferToTeam transfers wallet credits and records transaction log', function () {
    $user = User::factory()->create(['credits' => 50.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team, ['role' => 'view']);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('transferAmount', '15.00')
        ->call('transferToTeam')
        ->assertHasNoErrors();

    $user->refresh();
    $team->refresh();

    expect((float) $user->credits)->toBe(35.00);
    expect((float) $team->credits)->toBe(25.00);

    $transferTx = Transaction::where('type', 'transfer')->first();
    expect($transferTx)->not->toBeNull();
    expect((float) $transferTx->amount)->toBe(15.00);
    expect($transferTx->user_id)->toBe($user->id);
    expect($transferTx->team_id)->toBe($team->id);
});

test('falai image generation records spending transaction log', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 10.00,
    ]);

    config(['services.fal.key' => 'bearny-codes', 'fal_api.key' => 'bearny-codes']);

    Http::preventStrayRequests();

    $service = new FalAiService;
    $service->generate($team, 'Test prompt');

    // Verification
    $team->refresh();
    expect((float) $team->credits)->toBe(9.65); // 10.00 - 0.35

    $spendingTx = Transaction::where('type', 'spending')->first();
    expect($spendingTx)->not->toBeNull();
    expect((float) $spendingTx->amount)->toBe(-0.35);
    expect($spendingTx->team_id)->toBe($team->id);
});

test('team journal restricts standard member views but allows admins and managers', function () {
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);

    $member = User::factory()->create();
    $member->teams()->attach($team, ['role' => 'view']);

    $manager = User::factory()->create();
    $manager->teams()->attach($team, ['role' => 'manage']);

    $admin = User::factory()->create();
    $admin->teams()->attach($team, ['role' => 'admin']);

    // Log a few transactions from member and manager
    Transaction::create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'type' => 'transfer',
        'amount' => 5.00,
        'description' => 'Member transfer',
    ]);

    Transaction::create([
        'user_id' => $manager->id,
        'team_id' => $team->id,
        'type' => 'transfer',
        'amount' => 10.00,
        'description' => 'Manager transfer',
    ]);

    // 1. Member view: should only see their own transaction (1 total)
    session(['active_team_id' => $team->id]);
    $testMember = Livewire::actingAs($member)
        ->test(TopUp::class)
        ->assertOk();
    expect(count($testMember->instance()->teamTransactions))->toBe(1);
    expect($testMember->instance()->teamTransactions[0]->user_id)->toBe($member->id);

    // 2. Manager view: should see all transactions (2 total)
    $testManager = Livewire::actingAs($manager)
        ->test(TopUp::class)
        ->assertOk();
    expect(count($testManager->instance()->teamTransactions))->toBe(2);

    // 3. Admin view: should see all transactions (2 total)
    $testAdmin = Livewire::actingAs($admin)
        ->test(TopUp::class)
        ->assertOk();
    expect(count($testAdmin->instance()->teamTransactions))->toBe(2);
});
