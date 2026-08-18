<!doctype html>
<html lang="en">
<body style="font-family:Arial,sans-serif;background:#f5f0e6;padding:32px;color:#302d27">
<div style="max-width:560px;margin:auto;background:#fffdf8;padding:32px;border-radius:18px">
    <h2>UJUZI SHOP MALL</h2>
    <p>Hello {{ $invitation->user->name }},</p>
    <p>You have been invited to join UJUZI SHOP MALL as a <strong>{{ ucfirst($invitation->user->role) }}</strong>.</p>
    <p>Your account has been reserved for you. Use the button below to accept the invitation and create your own password.</p>
    <p><a href="{{ route('employee-invitation.show', $token) }}" style="display:inline-block;padding:12px 18px;background:#302d27;color:#fff;text-decoration:none;border-radius:8px">Accept invitation</a></p>
    <p>This invitation expires on {{ $invitation->expires_at->format('d M Y H:i') }}.</p>
    <p>If you were not expecting this invitation, you can ignore this email.</p>
</div>
</body>
</html>
