<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_loggedin_user_can_logout()
    {
        $user = $this->createUser();
        $this->authUser($user);

        $response = $this->getJson(route('user.logout'))
                    ->assertOk()
                    ->json();

        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function test_user_try_to_logout_without_login()
    {
        $this->withExceptionHandling();

        $response = $this->getJson(route('user.logout'))
                    ->assertUnauthorized();
    }
}
