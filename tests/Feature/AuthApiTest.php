<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login.
     */
    public function test_login_successful(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ]
                ]
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_invalid_credentials(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password salah.',
            ]);
    }

    /**
     * Test login for inactive user.
     */
    public function test_login_inactive_user(): void
    {
        $user = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi HR.',
            ]);
    }

    /**
     * Test profile retrieval and logout.
     */
    public function test_profile_and_logout(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        // Get Profile
        $responseProfile = $this->getJson('/api/auth/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $responseProfile->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'john@example.com',
                    ]
                ]
            ]);

        // Logout
        $responseLogout = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $responseLogout->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout berhasil.',
            ]);

        $this->app['auth']->forgetGuards();

        // Access Profile again - should fail
        $responseProfileAgain = $this->getJson('/api/auth/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $responseProfileAgain->assertStatus(401);
    }

    /**
     * Test change password functionality.
     */
    public function test_change_password(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        // Change Password
        $responseChange = $this->putJson('/api/auth/change-password', [
            'old_password' => 'secret123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $responseChange->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password berhasil diubah.',
            ]);

        // Test login with old password - should fail
        $responseLoginOld = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);
        $responseLoginOld->assertStatus(401);

        // Test login with new password - should succeed
        $responseLoginNew = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'newpassword123',
        ]);
        $responseLoginNew->assertStatus(200);
    }
}
