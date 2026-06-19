<?php

use App\Livewire\AcceptInvitation;
use App\Livewire\Auth\Register;
use App\Livewire\Teams;
use App\Models\Auth\Permission;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('team.view-all', 'web');
    Permission::findOrCreate('team.view', 'web');
    Permission::findOrCreate('team.manage', 'web');
    Permission::findOrCreate('team.admin', 'web');
});

test('unauthorized users cannot access teams page', function () {
    $user = User::factory()->create(); // No permissions
    $this->actingAs($user)
        ->get(route('teams'))
        ->assertForbidden();
});

test('users with team.view can access teams page and see only their teams', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view');

    $team1 = Team::create(['name' => 'My Team 1']);
    $team2 = Team::create(['name' => 'Other Team']);

    $user->teams()->attach($team1);

    Livewire::actingAs($user)
        ->test(Teams::class)
        ->assertSee('My Team 1')
        ->assertDontSee('Other Team');
});

test('users with team.view-all can see all teams', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('team.view-all');

    Team::create(['name' => 'Team A']);
    Team::create(['name' => 'Team B']);

    Livewire::actingAs($user)
        ->test(Teams::class)
        ->assertSee('Team A')
        ->assertSee('Team B');
});

test('only users with manage role on the team can send invitations', function () {
    $userView = User::factory()->create();
    $userView->givePermissionTo('team.view');

    $userManage = User::factory()->create();
    $userManage->givePermissionTo('team.view');

    $team = Team::create(['name' => 'Inc']);
    $userView->teams()->attach($team, ['role' => 'view']);
    $userManage->teams()->attach($team, ['role' => 'manage']);

    // View-only user fails to invite
    Livewire::actingAs($userView)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->call('openInviteModal')
        ->assertForbidden();

    // Manage user successfully invites
    Mail::fake();

    Livewire::actingAs($userManage)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->call('openInviteModal')
        ->set('invite_name', 'Invitee')
        ->set('invite_email', 'invitee@example.com')
        ->call('sendInvitation')
        ->assertHasNoErrors();

    expect(TeamInvitation::where('email', 'invitee@example.com')->exists())->toBeTrue();
});

test('only users with admin role on the team can edit team details', function () {
    $userManage = User::factory()->create();
    $userManage->givePermissionTo('team.view');

    $userAdmin = User::factory()->create();
    $userAdmin->givePermissionTo('team.view');

    $team = Team::create(['name' => 'Original Name']);
    $userManage->teams()->attach($team, ['role' => 'manage']);
    $userAdmin->teams()->attach($team, ['role' => 'admin']);

    // Manage user fails to save
    Livewire::actingAs($userManage)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->set('team_name', 'New Name')
        ->call('saveTeam')
        ->assertForbidden();

    // Admin user successfully saves
    Livewire::actingAs($userAdmin)
        ->test(Teams::class)
        ->set('selectedTeamId', $team->id)
        ->set('team_name', 'Admin New Name')
        ->set('team_description', 'Updated Description')
        ->call('saveTeam')
        ->assertHasNoErrors();

    expect($team->refresh()->name)->toBe('Admin New Name');
});

test('accept invitation route validates signature', function () {
    $team = Team::create(['name' => 'Testing']);
    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'token' => 'some-token',
    ]);

    // Unsigned url should fail with 403
    $unsignedUrl = route('teams.accept', ['token' => $invitation->token]);
    $this->get($unsignedUrl)->assertForbidden();

    // Signed url should succeed
    $signedUrl = URL::signedRoute('teams.accept', ['token' => $invitation->token]);
    $this->get($signedUrl)->assertOk();
});

test('accept invitation as existing user joins team', function () {
    $team = Team::create(['name' => 'Testing']);
    $user = User::factory()->create(['email' => 'test@example.com']);

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'token' => 'accept-token',
    ]);

    $signedUrl = URL::signedRoute('teams.accept', ['token' => $invitation->token]);

    session(['pending_invitation_url' => $signedUrl]);

    Livewire::actingAs($user)
        ->test(AcceptInvitation::class, ['token' => $invitation->token])
        ->call('accept')
        ->assertRedirect(route('dashboard'));

    expect($user->teams()->where('team_id', $team->id)->exists())->toBeTrue();
    expect($user->teams()->where('team_id', $team->id)->first()->pivot->role)->toBe('view');
    expect($user->hasPermissionTo('team.view'))->toBeTrue();
    expect(TeamInvitation::where('token', 'accept-token')->exists())->toBeFalse();
});

test('registering as new user via invitation links to team', function () {
    $team = Team::create(['name' => 'Register Invite']);

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'newuser@example.com',
        'token' => 'register-token',
        'name' => 'New User',
    ]);

    // Verify registration pre-fills data
    Livewire::withQueryParams(['invitation' => 'register-token'])
        ->test(Register::class)
        ->assertSet('email', 'newuser@example.com')
        ->assertSet('name', 'New User')
        ->assertSet('email_disabled', true);

    // Simulate OTP and successful registration completion
    DB::table('otp_codes')->insert([
        'email' => 'newuser@example.com',
        'code' => '123456',
        'expires_at' => now()->addMinutes(15),
    ]);

    Livewire::withQueryParams(['invitation' => 'register-token'])
        ->test(Register::class)
        ->set('name', 'New User')
        ->set('email', 'newuser@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('otp', '123456')
        ->call('verifyAndRegister')
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->teams()->where('team_id', $team->id)->exists())->toBeTrue();
    expect($user->teams()->where('team_id', $team->id)->first()->pivot->role)->toBe('view');
    expect($user->hasPermissionTo('team.view'))->toBeTrue();
    expect(TeamInvitation::where('token', 'register-token')->exists())->toBeFalse();
});
