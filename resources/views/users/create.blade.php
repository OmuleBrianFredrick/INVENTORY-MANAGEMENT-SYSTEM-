@extends('layout.app')
@section('title','Create Employee Account')
@section('page_title','Create Employee Account')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">UJUZI SHOP MALL</span>
        <h1>Create Employee Account</h1>
        <p class="muted">Create a controlled company account. Employees do not self-register.</p>
    </div>
</div>
<div class="panel form-panel">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="grid-2">
            <label>Name<input name="name" value="{{ old('name') }}" required></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>Role<select name="role" required>@foreach($roles as $role)<option value="{{ $role }}" @selected(old('role')===$role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
            <label>Password<input type="password" name="password" required minlength="8"></label>
            <label>Confirm password<input type="password" name="password_confirmation" required minlength="8"></label>
        </div>
        <p class="muted">The account is activated immediately. Share the initial credentials with the employee through a secure channel.</p>
        <div class="form-actions">
            <a class="btn btn-light" href="{{ route('users.index') }}">Cancel</a>
            <button class="btn btn-primary">Create account</button>
        </div>
    </form>
</div>
@endsection
