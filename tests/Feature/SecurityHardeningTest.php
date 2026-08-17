<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $response=$this->get('/login');
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options','nosniff');
        $response->assertHeader('X-Frame-Options','SAMEORIGIN');
        $response->assertHeader('Referrer-Policy','strict-origin-when-cross-origin');
    }

    public function test_repeated_invalid_logins_are_throttled(): void
    {
        $email='bruteforce@example.test';
        for($i=0;$i<5;$i++){
            $this->post(route('login.post'),['email'=>$email,'password'=>'wrong-password'])->assertSessionHasErrors('email');
        }
        $this->post(route('login.post'),['email'=>$email,'password'=>'wrong-password'])
            ->assertSessionHasErrors(['email'=>'Too many sign-in attempts. Please try again in 1 minute(s).']);
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $user=User::create(['name'=>'Staff','email'=>'staff@example.test','password'=>Hash::make('correct-password'),'role'=>'staff','is_active'=>true]);
        $this->post(route('login.post'),['email'=>$user->email,'password'=>'wrong-password'])->assertSessionHasErrors('email');
        $this->post(route('login.post'),['email'=>$user->email,'password'=>'correct-password'])->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($user);
    }
}
