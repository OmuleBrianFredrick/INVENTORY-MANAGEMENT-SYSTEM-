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
        for($i=0;$i<5;$i++){$this->post(route('login.post'),['email'=>$email,'password'=>'wrong-password'])->assertSessionHasErrors('email');}
        $this->post(route('login.post'),['email'=>$email,'password'=>'wrong-password'])->assertSessionHasErrors(['email'=>'Too many sign-in attempts. Please try again in 1 minute(s).']);
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $user=User::create(['name'=>'Staff','email'=>'staff@example.test','password'=>Hash::make('correct-password'),'role'=>'staff','is_active'=>true]);
        $this->post(route('login.post'),['email'=>$user->email,'password'=>'wrong-password'])->assertSessionHasErrors('email');
        $this->post(route('login.post'),['email'=>$user->email,'password'=>'correct-password'])->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_last_active_admin_cannot_be_removed(): void
    {
        $admin=User::create(['name'=>'Admin','email'=>'admin@example.test','password'=>Hash::make('password'),'role'=>'admin','is_active'=>true]);
        $this->actingAs($admin)->put(route('users.update',$admin->id),['name'=>'Admin','email'=>$admin->email,'role'=>'manager','is_active'=>0])->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users',['id'=>$admin->id,'role'=>'admin','is_active'=>true]);
    }

    public function test_admin_can_create_manager_employee(): void
    {
        $admin=User::create(['name'=>'Admin','email'=>'admin-create@example.test','password'=>Hash::make('password'),'role'=>'admin','is_active'=>true]);
        $this->actingAs($admin)->post(route('users.store'),['name'=>'Manager','email'=>'manager-create@example.test','role'=>'manager','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users',['email'=>'manager-create@example.test','role'=>'manager','is_active'=>true]);
    }

    public function test_manager_can_create_staff_but_cannot_create_manager(): void
    {
        $manager=User::create(['name'=>'Manager','email'=>'manager-create2@example.test','password'=>Hash::make('password'),'role'=>'manager','is_active'=>true]);
        $this->actingAs($manager)->post(route('users.store'),['name'=>'Staff','email'=>'staff-create@example.test','role'=>'staff','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users',['email'=>'staff-create@example.test','role'=>'staff']);
        $this->actingAs($manager)->post(route('users.store'),['name'=>'Manager Two','email'=>'manager-two@example.test','role'=>'manager','password'=>'password123','password_confirmation'=>'password123'])->assertStatus(422);
        $this->assertDatabaseMissing('users',['email'=>'manager-two@example.test']);
    }

    public function test_staff_cannot_manage_employee_accounts(): void
    {
        $staff=User::create(['name'=>'Staff','email'=>'staff-create2@example.test','password'=>Hash::make('password'),'role'=>'staff','is_active'=>true]);
        $this->actingAs($staff)->get(route('users.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('users.store'),['name'=>'Another','email'=>'another-staff@example.test','role'=>'staff','password'=>'password123','password_confirmation'=>'password123'])->assertForbidden();
    }
}
