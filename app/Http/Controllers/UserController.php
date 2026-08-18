<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function manager(Request $r): void
    {
        abort_unless($r->user()->isManager(), 403);
    }

    private function canManageUser(User $actor, User $target): bool
    {
        if ($actor->isAdmin()) return true;
        return $actor->isManager() && $target->isStaff();
    }

    public function index(Request $r)
    {
        $this->manager($r);
        $users = $r->user()->isAdmin() ? User::latest()->get() : User::where('role', 'staff')->latest()->get();
        return view('users.index', compact('users'));
    }

    public function create(Request $r)
    {
        $this->manager($r);
        $roles = $r->user()->isAdmin() ? ['manager', 'staff'] : ['staff'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $r)
    {
        $this->manager($r);
        $allowedRoles = $r->user()->isAdmin() ? ['manager', 'staff'] : ['staff'];
        $data = $r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','role'=>'required|in:'.implode(',', $allowedRoles),'password'=>'required|string|min:8|confirmed']);
        $data['email'] = strtolower(trim($data['email']));
        User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>$data['role'],'is_active'=>true]);
        return redirect()->route('users.index')->with('success', ucfirst($data['role']).' account created successfully. Provide the employee with their sign-in credentials securely.');
    }

    public function edit(Request $r, $id)
    {
        $this->manager($r);
        $user = User::findOrFail($id);
        abort_unless($this->canManageUser($r->user(), $user), 403);
        $roles = $r->user()->isAdmin() ? ['admin','manager','staff'] : ['staff'];
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $r, $id)
    {
        $this->manager($r);
        $user = User::findOrFail($id);
        abort_unless($this->canManageUser($r->user(), $user), 403);
        $allowedRoles = $r->user()->isAdmin() ? ['admin','manager','staff'] : ['staff'];
        $data=$r->validate(['name'=>'required|string|max:255','email'=>['required','email','max:255','unique:users,email,'.$user->id],'role'=>'required|in:'.implode(',', $allowedRoles),'is_active'=>'required|boolean','password'=>'nullable|string|min:8|confirmed']);
        $data['email']=strtolower(trim($data['email']));
        if($user->id===$r->user()->id&&($data['role']!=='admin'||!$data['is_active']))return back()->withErrors(['role'=>'You cannot remove or deactivate your own administrator access.'])->withInput();
        if($user->isAdmin()&&($data['role']!=='admin'||!$data['is_active'])&&User::where('role','admin')->where('is_active',true)->count()<=1)return back()->withErrors(['role'=>'At least one active administrator must remain.'])->withInput();
        $payload=['name'=>$data['name'],'email'=>$data['email'],'role'=>$data['role'],'is_active'=>$data['is_active']];
        if(!empty($data['password']))$payload['password']=Hash::make($data['password']);
        $user->update($payload);
        return redirect()->route('users.index')->with('success','User updated successfully.');
    }
}
