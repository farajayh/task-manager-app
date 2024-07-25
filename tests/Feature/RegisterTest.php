<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the user can register with complete details
     */
    public function test_user_can_register_with_complete_data(): void
    {
        // Attempt to register a new user
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Assert that the response status is 201
        $response->assertStatus(201);

         // Assert that the returned response structure matches the expected structure
         $response->assertJsonStructure([
            'status',
            'user',
            'authorisation' => [
                'token',
                'type',
            ],
        ]);

        // Assert that the returned user data matches the created user
        $response->assertJson([
            'status' => 'success',
            'user' => [
                'email' => 'test@example.com',
            ],
            'authorisation' => [
                'type' => 'bearer',
            ],
        ]);

        // Assert that a valid user id is returned
        $response->assertJsonPath('user.id', fn (int $id) => $id > 0);

        // Assert that a non-empty token is returned
        $response->assertJsonPath('authorisation.token', fn (string $token) => strlen($token) >= 3);

        // Assert that the user is in the database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Assert that the user is authenticated
        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user, 'api');
    }

    /**
     * Test that the user cannot register without name
     */
    public function test_user_cannot_register_without_name(): void
    {
        // Attempt to register a new user without name
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'name' => ['The name field is required.'],
            ],
        ]);
    }

    /**
     * Test that the user cannot register without email address
     */
    public function test_user_cannot_register_without_email(): void
    {
        // Attempt to register a new user without email address
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'password' => 'password123'
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'email' => ['The email field is required.'],
            ],
        ]);
    }
    
    /**
     * Test that the user cannot register without password
     */
    public function test_user_cannot_register_without_password(): void
    {
        // Attempt to register a new user without password
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'password' => ['The password field is required.'],
            ],
        ]);
    }

    /**
     * Test that the user cannot register with short password
     */
    public function test_user_cannot_register_with_short_password(): void
    {
        // Attempt to register a new user with password less than 8 characters
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'passwor'
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'password' => ['The password field must be at least 8 characters.'],
            ],
        ]);
    }

    /**
     * Test that the user cannot register with invalid email address
     */
    public function test_user_cannot_register_with_invalid_email(): void
    {
        // Attempt to register a new user with an invalid email address
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test.com',
            'password' => 'password123'
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'email' => ['The email field must be a valid email address.'],
            ],
        ]);
    }

    /**
     * Test that a user cannot use email address that already exists
     */
    public function test_user_cannot_register_with_existing_email(): void
    {
        $email = 'test@example.com';
        // Attempt to register a new user
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123'
        ]);

        // Attempt to register a new user with same email
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123'
        ]);

        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'email' => ['The email has already been taken.'],
            ],
        ]);
    }
    
}
