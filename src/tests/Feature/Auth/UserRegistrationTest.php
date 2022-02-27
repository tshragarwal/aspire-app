<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;
   
    public function test_a_user_can_register()
    {
        $this->postJson(route('user.register'), [
            'name' => 'Tushar',
            'email' => 'tshragarwal@gmail.com',
            'password' => 'secret@123',
            'password_confirmation' => 'secret@123'
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['name' => 'Tushar']);
    }

    public function test_validate_registration_fields_by_register_user_without_email()
    {
        $this->withExceptionHandling();

        $response = $this->postJson(route('user.register'), [
            'name' => 'Tushar',
            'password' => 'secret@123',
            'password_confirmation' => 'secret@123'
        ])->assertStatus(422)
        ->json();

        $this->assertArrayHasKey('email', $response['errors']);
    }
}
