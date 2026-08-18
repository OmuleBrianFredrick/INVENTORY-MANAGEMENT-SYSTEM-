@extends('layout.app')
@section('title','Accept UJUZI SHOP MALL Invitation')
@section('page_title','Accept Employee Invitation')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">UJUZI SHOP MALL</span>
        <h1>Welcome, {{ $invitation->user->name }}</h1>
        <p class="muted">You have been invited as a {{ ucfirst($invitation->user->role) }}. Create your personal password to activate your account.</p>
    </div>
</div>
<div class="panel form-panel">
    <form method="POST" action="{{ route('employee-invitation.accept', $token) }}">
        @csrf
        <label>Email<input value="{{ $invitation->user->email }}" disabled></label>
        <div class="grid-2">
            <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
        </div>
        <p class="muted">This invitation is single-use and expires {{ $invitation->expires_at->diffForHumans() }}.</p>
        <div class="form-actions">
            <a class="btn btn-light" href="{{ route('login') }}">Cancel</a>
            <button class="btn btn-primary">Activate account</button>
        </div>
    </form>
</div>
@endsection
