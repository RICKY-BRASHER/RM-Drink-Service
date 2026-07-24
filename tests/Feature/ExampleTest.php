<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login.pro'));
});

test('authenticated admin personnel can visit the dashboard', function () {
    $user = User::factory()->create([
        'role_u' => 'admin',
        'type_u' => 'personnel',
    ]);

    $this->actingAs($user, 'web');
    $response = $this->get(route('accueil.admin'));
    $response->assertOk();
});


