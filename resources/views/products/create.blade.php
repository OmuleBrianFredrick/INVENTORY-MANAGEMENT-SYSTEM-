@extends('layout.app')
@section('title','Add Product')
@section('content')
<div class="page-head"><div><span class="eyebrow">INVENTORY</span><h1>Add product</h1><p class="muted">Create a product and record its opening stock.</p></div></div>
<div class="panel form-panel"><form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">@csrf
<div class="grid-2"><label>Product name<input name="product_name" required></label><label>SKU<input name="sku" required></label><label>Category<input name="category" required></label><label>Selling price (UGX)<input type="number" step="0.01" name="product_price" required></label><label>Cost price (UGX)<input type="number" step="0.01" name="cost_price"></label><label>Opening stock<input type="number" min="0" name="stock_quantity" value="0" required></label><label>Reorder level<input type="number" min="0" name="reorder_level" value="5" required></label><label>Product image<input type="file" name="product_image" accept="image/*"></label></div><label>Description<textarea name="description" rows="4"></textarea></label><div class="form-actions"><a class="btn btn-light" href="{{ route('products.index') }}">Cancel</a><button class="btn btn-primary">Create product</button></div></form></div>
@endsection
