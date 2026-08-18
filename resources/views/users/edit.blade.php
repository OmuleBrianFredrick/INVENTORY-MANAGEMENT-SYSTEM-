@extends('layout.app')
@section('title','Edit Employee')
@section('page_title','Edit Employee')
@section('content')
<div class="page-head"><div><span class="eyebrow">EMPLOYEE ACCOUNTS</span><h1>Edit {{ $user->name }}</h1><p class="muted">Change this employee's role, status or credentials.</p></div></div>
<div class="panel form-panel">
    <form method="POST" action="{{ route('users.update',$user->id) }}">
        @csrf @method('PUT')
        <div class="grid-2">
            <label>Name<input name="name" value="{{ $user->name }}" required></label>
            <label>Email<input type="email" name="email" value="{{ $user->email }}" required></label>
            <label>Role<select name="role">@foreach($roles as $role)<option value="{{ $role }}" @selected($user->role===$role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
            <label>Status<select name="is_active"><option value="1" @selected($user->is_active)>Active</option><option value="0" @selected(!$user->is_active)>Inactive</option></select></label>
            <label>New password<input type="password" name="password"></label>
            <label>Confirm password<input type="password" name="password_confirmation"></label>
        </div>
        <div class="form-actions"><a class="btn btn-light" href="{{ route('users.index') }}">Cancel</a><button class="btn btn-primary">Save changes</button></div>
    </form>
</div>
@endsection
