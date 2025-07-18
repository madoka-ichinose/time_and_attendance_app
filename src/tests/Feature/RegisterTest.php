<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function name_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);

        $response->assertRedirect();

        $this->assertTrue(session()->hasOldInput('email'));
    }

    /** @test */
    public function email_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $response->assertRedirect();

        $this->assertTrue(session()->hasOldInput('name'));
    }

    /** @test */
    public function password_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertRedirect();
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'short7', 
            'password_confirmation' => 'short7',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertRedirect();
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
    }

     /** @test */
     public function password_confirmation_must_match()
     {
         $response = $this->post('/register', [
             'name' => 'テスト太郎',
             'email' => 'test@example.com',
             'password' => 'password123',
             'password_confirmation' => 'different123',
         ]);
 
         $response->assertSessionHasErrors(['password']);
         $response->assertRedirect();
         $this->assertTrue(session()->hasOldInput('name'));
         $this->assertTrue(session()->hasOldInput('email'));
     }

     /** @test */
    public function user_can_register_successfully_and_is_redirected_to_verification_notice()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }
}
