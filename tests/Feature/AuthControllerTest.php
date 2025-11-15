<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'department' => 'Engineering',
            'job_title' => 'Software Developer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'department', 'job_title'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'user',
            'department' => 'Engineering',
            'job_title' => 'Software Developer',
        ]);
    }

    /**
     * Test user registration without optional fields.
     */
    public function test_user_can_register_without_optional_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'department' => null,
            'job_title' => null,
        ]);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration fails without required fields.
     */
    public function test_registration_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration fails with invalid email.
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration fails with short password.
     */
    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration fails with mismatched password confirmation.
     */
    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'DifferentPassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user can login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('SecurePassword123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('CorrectPassword123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails for inactive user.
     */
    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('SecurePassword123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login revokes previous tokens.
     */
    public function test_login_revokes_previous_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('SecurePassword123'),
        ]);

        // Create an old token
        $oldToken = $user->createToken('old-token')->plainTextToken;

        // Login again (should revoke old token)
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response->assertStatus(200);

        // Old token should not work
        $this->withHeader('Authorization', 'Bearer ' . $oldToken)
            ->getJson('/api/user')
            ->assertStatus(401);
    }

    /**
     * Test user can logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // Token should no longer work
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user')
            ->assertStatus(401);
    }

    /**
     * Test logout requires authentication.
     */
    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can retrieve their profile.
     */
    public function test_authenticated_user_can_retrieve_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'role' => 'user',
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot retrieve profile.
     */
    public function test_unauthenticated_user_cannot_retrieve_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
