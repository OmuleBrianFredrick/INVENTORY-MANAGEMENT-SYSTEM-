@extends('layout.app')
@section('title','Create Customer Account')
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand large">
            <span class="brand-mark">UZ</span>
            <div><strong>UJUZI SHOP MALL</strong><small>Smart Inventory &amp; Shopping Management</small></div>
        </div>
        <h1>Create customer account</h1>
        <p class="muted">Public signup is for customers only. UJUZI SHOP MALL employees and managers are created by authorized company administrators.</p>
        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <label>Full name<input name="name" value="{{ old('name') }}" required></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
            <div class="grid-2">
                <label>Password<input type="password" name="password" required></label>
                <label>Confirm password<input type="password" name="password_confirmation" required></label>
            </div>
            <button class="btn btn-primary full">Create customer account</button>
        </form>
        <p class="muted center"><a href="{{ route('login') }}">Back to sign in</a></p>
    </div>
</div>
@endsection
