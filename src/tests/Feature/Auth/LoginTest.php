<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_login_with_email_and_password()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('user.login'), [
            'email' => $user->email,
            'password' => 'password'
        ])->assertOk()
        ->json();

        $this->assertArrayHasKey('token', $response['data']);
    }

    public function test_user_email_not_found()
    {
        $response = $this->postJson(route('user.login'), [
            'email' => 'example@gmail.com',
            'password' => 'secret@123'
        ])->assertUnauthorized();
    }

    public function test_a_user_try_login_with_wrong_password()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('user.login'), [
            'email' => $user->email,
            'password' => 'secret@123'
        ])->assertUnauthorized();
    }
}
