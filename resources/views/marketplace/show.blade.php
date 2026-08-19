<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $product->product_name }} — UJUZI SHOP MALL</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"><link rel="stylesheet" href="{{ asset('css/storefront.css') }}"></head>
<body class="storefront">
<header class="store-header"><div class="store-header-inner"><a class="store-logo" href="{{ route('marketplace.home') }}"><span>UZ</span><div><strong>UJUZI SHOP MALL</strong><small>Shop from trusted retailers</small></div></a><form class="store-search" method="GET" action="{{ route('marketplace.home') }}"><input name="q" placeholder="Search products, brands and categories"><button>Search</button></form><nav class="store-actions"><a href="{{ route('login') }}">Sign in</a><a href="{{ route('cart.index') }}">Cart <span class="cart-count">{{ array_sum(session('shop_cart', [])) }}</span></a></nav></div></header>
<main class="store-section detail-page">
@if(session('success'))<div class="store-alert">{{ session('success') }}</div>@endif
<div class="product-detail">
    <div class="detail-image">@if($product->product_image)<img src="{{ asset('storage/images/'.$product->product_image) }}" alt="{{ $product->product_name }}">@else<div class="store-image-placeholder">UJUZI</div>@endif</div>
    <div class="detail-copy"><span class="product-category">{{ $product->category }}</span><h1>{{ $product->product_name }}</h1><div class="detail-price">UGX {{ number_format($product->product_price, 0) }}</div><p>{{ $product->description ?: 'Quality products from a trusted UJUZI SHOP MALL retailer.' }}</p><p class="stock-note">{{ $product->stock_quantity }} available</p><form method="POST" action="{{ route('cart.add', $product) }}" class="add-detail">@csrf<label>Quantity<input type="number" name="quantity" min="1" max="{{ $product->stock_quantity }}" value="1"></label><button class="store-btn primary">Add to cart</button></form><a class="text-link" href="{{ route('marketplace.home') }}">← Continue shopping</a></div>
</div></main>
<footer class="store-footer"><strong>UJUZI SHOP MALL</strong><span>Shop from trusted businesses and retailers.</span></footer>
</body></html>
