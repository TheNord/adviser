<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTestTest extends TestCase
{
    public function testForm()
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('Register');
    }

    public function testErrors()
    {
        $response = $this->post('/register',[
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function testSuccess()
    {
        $user = factory(User::class)->make();

        $response = $this->post('/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $response
            ->assertStatus(302)
            ->assertRedirect('/login')
            ->assertSessionHas('info', 'Check your email and click on the link to verify.');
    }

    public function testVerifyIncorrect()
    {
        $response = $this->get('/verify/' . Str::uuid());

        $response
            ->assertStatus(302)
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Sorry your link cannot be identified.');
    }

    public function testVerify()
    {
        $user = factory(User::class)->create([
            'status' => User::STATUS_WAIT,
            'verify_token' => Str::uuid(),
        ]);

        $reponse = $this->get('/verify/' . $user->verify_token);

        $reponse
            ->assertStatus(302)
            ->assertRedirect('/login')
            ->assertSessionHas('success', 'Your e-mail is verified. You can now login.');
    }
}
