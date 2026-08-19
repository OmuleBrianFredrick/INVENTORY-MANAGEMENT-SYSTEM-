<article class="store-product-card">
    <a class="store-product-image" href="{{ route('marketplace.product', $product) }}">
        @if($product->product_image)
            <img src="{{ asset('storage/images/'.$product->product_image) }}" alt="{{ $product->product_name }}">
        @else
            <div class="store-image-placeholder">UJUZI</div>
        @endif
    </a>
    <div class="store-product-body">
        <span class="product-category">{{ $product->category }}</span>
        <h3><a href="{{ route('marketplace.product', $product) }}">{{ $product->product_name }}</a></h3>
        <strong class="store-price">UGX {{ number_format($product->product_price, 0) }}</strong>
        <span class="stock-note">{{ $product->stock_quantity }} available</span>
        <form method="POST" action="{{ route('cart.add', $product) }}">@csrf<button class="store-btn primary full">Add to cart</button></form>
    </div>
</article>
