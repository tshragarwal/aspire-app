<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_loggedin_user_can_logout()
    {
        $user = $this->createUser();
        $this->authUser($user);

        $response = $this->postJson(route('customer.logout'))
                    ->assertOk()
                    ->json();

        $this->assertEquals('success', $response['status']);
    }

    public function test_user_try_to_logout_without_login()
    {
        $this->withExceptionHandling();

        $response = $this->postJson(route('customer.logout'))
                    ->assertUnauthorized();
    }
}
