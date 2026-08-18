<?php

namespace App\Http\Controllers;

use App\Models\EmployeeInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeeInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = $this->findInvitation($token);
        abort_unless($invitation->isUsable(), 410);

        return view('employee-invitations.accept', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findInvitation($token);
        abort_unless($invitation->isUsable(), 410);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $invitation->user;
        abort_unless(!$user->is_active, 410);

        $user->update([
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('login')->with('success', 'Your UJUZI SHOP MALL account is active. Sign in with your email and new password.');
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->isManager(), 403);
        $roles = $request->user()->isAdmin() ? ['manager', 'staff'] : ['staff'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isManager(), 403);

        $allowedRoles = $actor->isAdmin() ? ['manager', 'staff'] : ['staff'];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
        ]);
        $data['email'] = strtolower(trim($data['email']));

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(40)),
            'role' => $data['role'],
            'is_active' => false,
        ]);

        $token = Str::random(64);
        $invitation = EmployeeInvitation::create([
            'user_id' => $user->id,
            'invited_by' => $actor->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
        ]);

        Mail::to($user->email)->send(new \App\Mail\EmployeeInvitationMail($invitation, $token));

        return redirect()->route('users.index')->with('success', ucfirst($user->role) . ' invitation sent to ' . $user->email . '.');
    }

    private function findInvitation(string $token): EmployeeInvitation
    {
        return EmployeeInvitation::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
