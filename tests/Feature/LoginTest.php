<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the user can login using correct username and password
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        // Create a user
        $password = 'password';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        // Assert that a status of 200 is returned
        $response->assertStatus(200);

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
            'status' => true,
            'user' => [
                'email' => 'test@example.com',
            ],
            'authorisation' => [
                'type' => 'bearer',
            ],
        ]);

        // Assert that a valid id is returned
        $response->assertJsonPath('user.id', fn (int $id) => $id > 0);

        // Assert that a non-empty token is returned
        $response->assertJsonPath('authorisation.token', fn (string $token) => strlen($token) >= 3);
    }

    /**
     * Test that the user cannot login without email address
     */
    public function test_user_cannot_login_without_email(): void
    {
        // Attempt to login without email address
        $response = $this->postJson('/api/login', [
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
     * Test that the user cannot login without password
     */
    public function test_user_cannot_login_without_password(): void
    {
        // Attempt to login without password
        $response = $this->postJson('/api/login', [
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
     * Test that the user cannot login using wrong username and password
     */
    public function test_user_cannot_login_with_wrong_credentials(): void
    {
         // Create a user
         $password = 'password';
         $user = User::factory()->create([
             'email' => 'test@example.com',
             'password' => Hash::make($password),
         ]);
 
         // Attempt login
         $response = $this->postJson('/api/login', [
             'email' => 'test@example.com',
             'password' => 'password123',
         ]);

         // Assert that a status of 401 is returned
        $response->assertStatus(401);
         
        // Assert that the returned user data matches the created user
        $response->assertJson([
            'status' => false,
            'message' => 'Unauthorized',
        ]);
    }

     /**
     * Test retrieval of authenticated user
     */
    public function test_can_retrieve_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $token = JWTAuth::fromUser($user);

        // Retrieve authenticated user
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer '. $token,
        ]);

        // Assert that a status of 200 is returned
        $response->assertStatus(200);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
           'status',
            'user'
        ]);  
    }    

    /**
     * Test that user cannot be retrieved without authentication
     */
    public function test_cannot_get_user_without_authentication(): void
    {
        $response = $this->getJson('/api/user');

        
        // Assert that the response status code is 401                 
        $response->assertStatus(401);
        
        // Assert that the returned response has an error messsage
        $response->assertJson([
           'message' => 'Unauthenticated.'
        ]);
    }    

     /**
     * Test that user can log out
     */
    public function test_can_logout_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $token = JWTAuth::fromUser($user);

        // Retrieve authenticated user
        $response = $this->getJson('/api/logout', [
            'Authorization' => 'Bearer '. $token,
        ]);

        // Assert that the response status code is 200                 
        $response->assertStatus(200);

        // Assert that the returned response has a success message
        $response->assertJson([
            'status' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Test refresh token
     */
    public function test_can_get_refresh_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $token = JWTAuth::fromUser($user);

        $response = $this->getJson('/api/refresh', [
            'Authorization' => 'Bearer '. $token,
        ]);

        $response->assertStatus(200);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
            'status',
            'user',
            'authorisation' => [
                'token',
                'type',
            ],
        ]);

        $fresh_token = $response->json('authorisation')['token'];

        $this->assertNotEquals($token, $fresh_token);
    }    
}
