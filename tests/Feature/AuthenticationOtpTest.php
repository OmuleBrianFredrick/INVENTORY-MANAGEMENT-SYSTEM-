<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationOtpTest extends TestCase
{
    use RefreshDatabase;

    private function postLogin(array $credentials)
    {
        return $this->withSession([])->post('/login', array_merge($credentials, ['_token' => csrf_token()]));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession([]);
    }

    public function test_manager_password_sends_otp_instead_of_logging_in(): void
    {
        $user=User::create(['name'=>'Manager','email'=>'manager@example.com','password'=>Hash::make('password123'),'role'=>'manager','is_active'=>true]);
        Mail::fake();
        $response=$this->postLogin(['email'=>$user->email,'password'=>'password123']);
        $response->assertRedirect('/verify-otp');
        $this->assertGuest();
        $this->assertDatabaseHas('otp_challenges',['user_id'=>$user->id]);
        Mail::assertSent(\App\Mail\LoginOtpMail::class);
    }

    public function test_staff_password_logs_in_without_otp(): void
    {
        $user=User::create(['name'=>'Staff','email'=>'staff@example.com','password'=>Hash::make('password123'),'role'=>'staff','is_active'=>true]);
        Mail::fake();
        $response=$this->postLogin(['email'=>$user->email,'password'=>'password123']);
        $response->assertRedirect('/products');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('otp_challenges',0);
        Mail::assertNothingSent();
    }

    public function test_customer_password_logs_in_without_otp(): void
    {
        $user=User::create(['name'=>'Customer','email'=>'customer@example.com','password'=>Hash::make('password123'),'role'=>'customer','is_active'=>true]);
        Mail::fake();
        $response=$this->postLogin(['email'=>$user->email,'password'=>'password123']);
        $response->assertRedirect('/products');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('otp_challenges',0);
        Mail::assertNothingSent();
    }

    public function test_public_registration_creates_customer_role(): void
    {
        $response=$this->post('/register',['name'=>'New Customer','email'=>'newcustomer@example.com','password'=>'password123','password_confirmation'=>'password123','_token'=>csrf_token()]);
        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users',['email'=>'newcustomer@example.com','role'=>'customer']);
    }

    public function test_invalid_password_does_not_create_otp(): void
    {
        Mail::fake();
        $this->postLogin(['email'=>'missing@example.com','password'=>'wrongpassword']);
        $this->assertGuest();
        $this->assertDatabaseCount('otp_challenges',0);
    }
}
