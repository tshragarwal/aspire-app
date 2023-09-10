<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $authService;

    public function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    // Test registration
    public function testRegisterUser()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret',
        ];

        $user = $this->authService->registerMe($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
    }

    public function testRegisterWithDuplicateEmail()
    {
        // Create a user with a random email
        $existingUser = User::factory()->create();

        $userData = [
            'name' => 'Test User',
            'email' => $existingUser->email, // Use the same email
            'password' => 'password123',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(INTERNAL_SERVER_ERROR);

        // Attempt to register with a duplicate email should throw an exception
        $this->authService->registerMe($userData);
    }

    // Test login
    public function testLoginUser()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret'),
        ]);

        // Attempt to log in
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'secret',
        ];

        $authResponse = $this->authService->login($loginData);

        $this->assertArrayHasKey('user', $authResponse);
        $this->assertArrayHasKey('token', $authResponse);

        // Check if the user is logged in
        $this->assertTrue(Auth::check());
    }

    public function testLoginWithInvalidCredentials()
    {
        // Create a user
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];
        User::create($userData);

        $loginData = [
            'email' => $userData['email'],
            'password' => 'invalidpassword', // Invalid password
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(INVALID_CREDENTIALS);

        // Attempt to login with invalid credentials should throw an exception
        $this->authService->login($loginData);
    }

    // Test logout
    // public function testLogoutUser()
    // {
    //     // Create a test user
    //     $user = User::factory()->create();

    //     // Log in the user
    //     Auth::login($user);

    //     // Attempt to log out
    //     $result = $this->authService->logout();

    //     $this->assertTrue($result);

    //     // Check if the user is logged out
    //     $this->assertFalse(Auth::check());
    // }
}