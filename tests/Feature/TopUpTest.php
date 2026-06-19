<?php

use App\Livewire\TopUp;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('top up page is only accessible to authenticated users', function () {
    $this->get('/top-up')->assertRedirect('/login');

    $user = User::factory()->create();
    $this->actingAs($user)->get('/top-up')->assertOk();
});

test('user can top up credits and allocate all to active team', function () {
    $user = User::factory()->create(['credits' => 0.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '20.00')
        ->set('allocationMode', 'all_team')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '12/28')
        ->set('card_cvc', '123')
        ->call('processPayment')
        ->assertHasNoErrors();

    $user->refresh();
    $team->refresh();

    expect((float) $user->credits)->toBe(0.00);
    expect((float) $team->credits)->toBe(30.00); // 10.00 + 20.00
});

test('user can top up credits and allocate all to personal wallet', function () {
    $user = User::factory()->create(['credits' => 5.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '15.50')
        ->set('allocationMode', 'all_personal')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '12/28')
        ->set('card_cvc', '123')
        ->call('processPayment')
        ->assertHasNoErrors();

    $user->refresh();
    $team->refresh();

    expect((float) $user->credits)->toBe(20.50); // 5.00 + 15.50
    expect((float) $team->credits)->toBe(10.00); // unchanged
});

test('user can top up credits and split between wallet and active team', function () {
    $user = User::factory()->create(['credits' => 5.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    session(['active_team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '50.00')
        ->set('allocationMode', 'split')
        ->set('teamAmount', '35.00')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '12/28')
        ->set('card_cvc', '123')
        ->call('processPayment')
        ->assertHasNoErrors();

    $user->refresh();
    $team->refresh();

    // Total: 50.00, Team: 35.00, Personal: 15.00
    expect((float) $user->credits)->toBe(20.00); // 5.00 + 15.00
    expect((float) $team->credits)->toBe(45.00); // 10.00 + 35.00
});

test('top up validation fails for split exceeding total amount', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '10.00')
        ->set('allocationMode', 'split')
        ->set('teamAmount', '15.00')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '12/28')
        ->set('card_cvc', '123')
        ->call('processPayment')
        ->assertHasErrors(['teamAmount' => 'max']);
});

test('top up validation fails for incomplete details', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '10.00')
        ->set('allocationMode', 'all_personal')
        ->call('processPayment')
        ->assertHasErrors([
            'cardholder_name' => 'required',
            'card_number' => 'required',
            'card_expiry' => 'required',
            'card_cvc' => 'required',
        ]);
});

test('top up validation fails for invalid card formats', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TopUp::class)
        ->set('amount', '10.00')
        ->set('allocationMode', 'all_personal')
        ->set('cardholder_name', 'Jane Doe')
        ->set('card_number', '4111 2222 3333 4444')
        ->set('card_expiry', '2028-12') // Invalid format
        ->set('card_cvc', 'abc') // Invalid format
        ->call('processPayment')
        ->assertHasErrors([
            'card_expiry' => 'regex',
            'card_cvc' => 'regex',
        ]);
});
