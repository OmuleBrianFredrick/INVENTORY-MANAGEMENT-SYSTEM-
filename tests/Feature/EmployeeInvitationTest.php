<?php

namespace Tests\Feature;

use App\Mail\EmployeeInvitationMail;
use App\Models\EmployeeInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_a_manager_without_setting_a_password(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        Mail::fake();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Manager',
            'email' => 'manager@example.test',
            'role' => 'manager',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'manager@example.test',
            'role' => 'manager',
            'is_active' => false,
        ]);
        $this->assertDatabaseCount('employee_invitations', 1);
        Mail::assertSent(EmployeeInvitationMail::class);
    }

    public function test_manager_can_invite_staff_but_cannot_invite_manager(): void
    {
        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.test',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'is_active' => true,
        ]);
        Mail::fake();

        $allowed = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Staff User',
            'email' => 'staff@example.test',
            'role' => 'staff',
        ]);
        $allowed->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.test',
            'role' => 'staff',
            'is_active' => false,
        ]);

        $blocked = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Second Manager',
            'email' => 'manager2@example.test',
            'role' => 'manager',
        ]);
        $blocked->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'manager2@example.test']);
    }

    public function test_invitation_activation_sets_password_and_makes_employee_active(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $employee = User::create([
            'name' => 'Pending Staff',
            'email' => 'pending@example.test',
            'password' => Hash::make(Str::random(40)),
            'role' => 'staff',
            'is_active' => false,
        ]);
        $token = Str::random(64);
        $invitation = EmployeeInvitation::create([
            'user_id' => $employee->id,
            'invited_by' => $admin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post(route('employee-invitation.accept', $token), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => true,
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertTrue(Hash::check('newpassword123', $employee->fresh()->password));
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $employee = User::create([
            'name' => 'Expired Staff',
            'email' => 'expired@example.test',
            'password' => Hash::make(Str::random(40)),
            'role' => 'staff',
            'is_active' => false,
        ]);
        $token = Str::random(64);
        EmployeeInvitation::create([
            'user_id' => $employee->id,
            'invited_by' => $admin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('employee-invitation.show', $token))->assertStatus(410);
        $this->assertFalse($employee->fresh()->is_active);
    }

    public function test_admin_can_revoke_a_pending_invitation(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $employee = User::create([
            'name' => 'Pending Staff',
            'email' => 'pending@example.test',
            'password' => Hash::make(Str::random(40)),
            'role' => 'staff',
            'is_active' => false,
        ]);
        $token = Str::random(64);
        $invitation = EmployeeInvitation::create([
            'user_id' => $employee->id,
            'invited_by' => $admin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($admin)->post(route('users.revoke-invitation', $employee->id));

        $response->assertRedirect(route('users.index'));
        $this->assertNotNull($invitation->fresh()->revoked_at);

        $this->post(route('logout'));
        $this->get(route('employee-invitation.show', $token))->assertStatus(410);
    }

    public function test_public_registration_remains_customer_only(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.test',
            'role' => 'customer',
        ]);
    }
}
