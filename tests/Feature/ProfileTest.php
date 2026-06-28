<?php

use App\Livewire\Profile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk();
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'old-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('current password must be correct to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});

test('avatar can be uploaded', function () {
    $user = User::factory()->create();

    Storage::fake('public');

    $file = UploadedFile::fake()->image('avatar.jpg');

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('uploaded_avatar', $file)
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->avatar)->not->toBeNull();

    $path = str_replace('/storage/', '', $user->avatar);
    Storage::disk('public')->assertExists($path);
});

test('password validation is bypassed when current password is empty', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', '')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});

test('user can update default team and selection mode', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Team Delta']);
    $user->teams()->attach($team->id);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('default_team_id', $team->id)
        ->set('team_selection_mode', 'last_used')
        ->call('updateTeamSettings')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->default_team_id)->toBe($team->id);
    expect($user->team_selection_mode)->toBe('last_used');
});

test('validation fails if user selects a team they do not belong to', function () {
    $user = User::factory()->create();
    $otherTeam = Team::create(['name' => 'Other Team']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('default_team_id', $otherTeam->id)
        ->set('team_selection_mode', 'default')
        ->call('updateTeamSettings')
        ->assertHasErrors(['default_team_id']);

    $user->refresh();
    expect($user->default_team_id)->toBeNull();
});
