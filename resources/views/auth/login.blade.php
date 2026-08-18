@extends('layout.app')
@section('title','UJUZI SHOP MALL | Sign In')
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand large">
            <span class="brand-mark">UZ</span>
            <div>
                <strong>UJUZI SHOP MALL</strong>
                <small>Smart Inventory &amp; Shopping Management</small>
            </div>
        </div>
        <h1>Welcome back</h1>
        <p class="muted">Sign in with your password. Managers will receive a one-time verification code by email before access is granted.</p>
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
            <label>Password<input type="password" name="password" required></label>
            <button class="btn btn-primary full">Continue</button>
        </form>
        <p class="muted center">No account? <a href="{{ route('register') }}">Create one</a></p>
    </div>
</div>
@endsection
