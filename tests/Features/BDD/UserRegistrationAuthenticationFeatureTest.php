<?php

namespace Tests\Features\BDD;

use Tests\TestCase;
use App\Models\User;

/**
 * ====================================================================
 * Feature: User Registration and Authentication
 * ====================================================================
 * 
 * As a new user
 * I want to create an account and authenticate
 * So that I can access the application securely
 * 
 * ====================================================================
 */
class UserRegistrationAuthenticationFeatureTest extends TestCase
{
    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: User can register with valid information
     * ────────────────────────────────────────────────────────────────
     * Given:  I am on the registration page
     * When:   I fill the form with valid data and submit
     * Then:   My account is created and I am logged in
     * ────────────────────────────────────────────────────────────────
     */
    public function test_user_can_register_with_valid_information(): void
    {
        // ARRANGE
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        // ACT
        $response = $this->post('/register', $registrationData);

        // ASSERT
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe',
        ]);
    }

    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: Registration fails with duplicate email
     * ────────────────────────────────────────────────────────────────
     * Given:  An existing user with the same email
     * When:   I attempt to register with their email
     * Then:   The registration fails with validation error
     * ────────────────────────────────────────────────────────────────
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // ARRANGE
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $registrationData = [
            'name' => 'Another User',
            'email' => 'existing@example.com', // Already taken
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // ACT
        $response = $this->post('/register', $registrationData);

        // ASSERT
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1); // Only original user exists
    }

    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: Registered user can login successfully
     * ────────────────────────────────────────────────────────────────
     * Given:  I am a registered user
     * When:   I submit login form with correct credentials
     * Then:   I am authenticated and see the dashboard
     * ────────────────────────────────────────────────────────────────
     */
    public function test_registered_user_can_login_successfully(): void
    {
        // ARRANGE
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'SecurePassword123!',
        ]);

        // ACT
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'SecurePassword123!',
        ]);

        // ASSERT
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: Login fails with incorrect password
     * ────────────────────────────────────────────────────────────────
     * Given:  A registered user exists
     * When:   I submit incorrect password
     * Then:   Login fails and I am not authenticated
     * ────────────────────────────────────────────────────────────────
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        // ARRANGE
        $user = User::factory()->create(['password' => 'CorrectPassword123!']);

        // ACT
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword123!',
        ]);

        // ASSERT
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: User can logout
     * ────────────────────────────────────────────────────────────────
     * Given:  I am an authenticated user
     * When:   I click the logout button
     * Then:   I am logged out and redirected to home
     * ────────────────────────────────────────────────────────────────
     */
    public function test_user_can_logout(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $this->actingAs($user);

        // ACT
        $response = $this->post('/logout');

        // ASSERT
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
