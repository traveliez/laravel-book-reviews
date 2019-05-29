<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->password = 'password123';
        $this->user = factory(User::class)->create([
            'password' => Hash::make($this->password),
        ]);

        $this->userRegister = [
            'name' => 'Resi Dwi',
            'email' => 'residwithawasa@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    public function login_route()
    {
        return route('auth.login');
    }

    public function register_route()
    {
        return route('auth.register');
    }

    /**
     * @test
     */
    public function user_can_login()
    {
        $response = $this->json('POST', $this->login_route(), [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'email',
                'access_token',
                'token_type',
                'expires_in',
            ])
        ;

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * @test
     */
    public function user_cannot_login_with_incorrect_password()
    {
        $response = $this->json('POST', $this->login_route(), [
            'email' => $this->user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function user_cannot_login_with_email_that_does_not_exist()
    {
        $response = $this->json('POST', $this->login_route(), [
            'email' => 'test@mail.com',
            'password' => 'invalid-password',
        ]);
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function user_cannot_make_requests_more_than_sixty_attempts_in_one_minute()
    {
        // 100 attempts request
        for ($i = 0; $i <= 100; ++$i) {
            $response = $this->json('POST', $this->login_route(), [
                'email' => $this->user->email,
                'password' => $this->password,
            ]);
        }

        $response->assertSee('Too Many Attempts');
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * @test
     */
    public function user_can_register()
    {
        $this->json('POST', $this->register_route(), $this->userRegister)
            ->assertStatus(201)
            ->assertJsonStructure([
                'name',
                'email',
                'access_token',
                'token_type',
                'expires_in',
            ])
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_without_name()
    {
        $this->userRegister['name'] = '';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name')
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_without_email()
    {
        $this->userRegister['email'] = '';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email')
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_with_invalid_email()
    {
        $this->userRegister['email'] = 'invalid_email';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email')
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_without_password()
    {
        $this->userRegister['password'] = '';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('password')
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_without_password_confirmation()
    {
        $this->userRegister['password_confirmation'] = '';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('password')
        ;
    }

    /**
     * @test
     */
    public function user_cannot_register_with_passwords_not_matching()
    {
        $this->userRegister['password'] = 'password';
        $this->userRegister['password_confirmation'] = 'secret';

        $response = $this->json('POST', $this->register_route(), $this->userRegister);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('password')
        ;
    }
}
