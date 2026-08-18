@extends('layout.app')
@section('title','Employee Accounts')
@section('page_title','Employee Accounts')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">UJUZI SHOP MALL</span><h1>Employee Accounts</h1><p class="muted">Authorized managers invite company employees. Public signup is reserved for customers.</p></div>
    <a class="btn btn-primary" href="{{ route('users.create') }}">＋ Invite Employee</a>
</div>
<div class="panel">
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
    @foreach($users as $user)
        @php($pending = !$user->is_active && $user->role !== 'admin')
        <tr>
            <td><strong>{{ $user->name }}</strong></td>
            <td>{{ $user->email }}</td>
            <td><span class="role-pill">{{ ucfirst($user->role) }}</span></td>
            <td><span class="status {{ $user->is_active?'good':'warn' }}">{{ $user->is_active?'Active':'Pending invitation' }}</span></td>
            <td>{{ $user->created_at->format('d M Y') }}</td>
            <td>
                @if($pending && ($loop->parent ?? true))
                    @if(auth()->user()->isAdmin() || $user->role === 'staff')
                        <form method="POST" action="{{ route('users.resend-invitation',$user->id) }}" style="display:inline">
                            @csrf
                            <button class="table-link" type="submit">Resend invitation</button>
                        </form>
                    @endif
                @else
                    <a class="table-link" href="{{ route('users.edit',$user->id) }}">Edit</a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody></table></div>
</div>
@endsection
