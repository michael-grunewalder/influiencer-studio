<?php

use App\Models\User;

test('guests are redirected to login', function () {
    $this->get('/')
        ->assertRedirect('/login');
});

test('authenticated users can access the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});
