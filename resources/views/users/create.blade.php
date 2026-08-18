@extends('layout.app')
@section('title','Invite Employee')
@section('page_title','Invite Employee')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">UJUZI SHOP MALL</span>
        <h1>Invite Employee</h1>
        <p class="muted">Employees do not receive passwords from administrators. Send a secure invitation so they can create their own password.</p>
    </div>
</div>
<div class="panel form-panel">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="grid-2">
            <label>Name<input name="name" value="{{ old('name') }}" required autocomplete="name"></label>
            <label>Company email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Role<select name="role" required>@foreach($roles as $role)<option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
        </div>
        <p class="muted">The account starts pending and inactive. The employee becomes active only after accepting the email invitation and setting a password. The invitation expires after 24 hours.</p>
        <div class="form-actions">
            <a class="btn btn-light" href="{{ route('users.index') }}">Cancel</a>
            <button class="btn btn-primary">Send invitation</button>
        </div>
    </form>
</div>
@endsection
