<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the first registered user is automatically approved and made admin', function () {
    Setting::set('registration_enabled', true);

    $response = $this->post('/register', [
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard');

    $user = User::first();
    expect($user->is_approved)->toBeTrue();
    expect($user->is_admin)->toBeTrue();
    expect(Setting::isEnabled('registration_enabled'))->toBeFalse();
});

test('subsequent users are not approved or admin', function () {
    // Create first user
    User::factory()->create([
        'email' => 'first@example.com',
        'is_approved' => true,
        'is_admin' => true,
    ]);

    Setting::set('registration_enabled', true);

    $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'second@example.com')->first();
    expect($user->is_approved)->toBeFalse();
    expect($user->is_admin)->toBeFalse();
    // registration_enabled should remain true because this wasn't the first user registration
    expect(Setting::isEnabled('registration_enabled'))->toBeTrue();
});
