@extends('layout.app')
@section('title','Categories')
@section('content')
<div class="page-head"><div><span class="eyebrow">INVENTORY</span><h1>Categories</h1><p class="muted">Maintain the controlled product classification used across the inventory.</p></div></div>
<div class="grid-2">
<div class="panel form-panel"><h2>Create category</h2><form method="POST" action="{{ route('categories.store') }}">@csrf<label>Name<input name="name" value="{{ old('name') }}" required></label><label>Description<textarea name="description" rows="4">{{ old('description') }}</textarea></label><button class="btn btn-primary">Create category</button></form></div>
<div class="panel"><h2>Category directory</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead><tbody>@forelse($categories as $category)<tr><td><strong>{{ $category->name }}</strong><br><small class="muted">{{ $category->description ?: 'No description' }}</small></td><td>{{ $category->products_count }}</td><td>{{ $category->is_active ? 'Active' : 'Archived' }}</td><td>@if($category->is_active)<form method="POST" action="{{ route('categories.archive',$category->id) }}" style="display:inline">@csrf<button class="btn btn-light" onclick="return confirm('Archive this category?')">Archive</button></form>@else<span class="muted">Archived</span>@endif</td></tr>@empty<tr><td colspan="4" class="muted">No categories yet.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
