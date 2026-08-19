<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>UJUZI SHOP MALL — Shop online</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}">
</head>
<body class="storefront">
<header class="store-header">
    <div class="store-header-inner">
        <a class="store-logo" href="{{ route('marketplace.home') }}"><span>UZ</span><div><strong>UJUZI SHOP MALL</strong><small>Shop from trusted retailers</small></div></a>
        <form class="store-search" method="GET" action="{{ route('marketplace.home') }}">
            <input name="q" value="{{ request('q') }}" placeholder="Search products, brands and categories" aria-label="Search products">
            <button aria-label="Search">Search</button>
        </form>
        <nav class="store-actions" aria-label="Shopping navigation">
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('cart.index') }}">Cart <span class="cart-count">{{ array_sum(session('shop_cart', [])) }}</span></a>
                    <a href="{{ route('marketplace.home') }}">{{ auth()->user()->name }}</a>
                @else
                    <a href="{{ route('products.index') }}">Staff portal</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button>Sign out</button></form>
            @else
                <a href="{{ route('login') }}">Sign in</a>
                <a class="cart-link" href="{{ route('cart.index') }}">Cart <span class="cart-count">{{ array_sum(session('shop_cart', [])) }}</span></a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @if(session('success'))<div class="store-alert">{{ session('success') }}</div>@endif
    <section class="store-hero">
        <div><span class="eyebrow">WELCOME TO UJUZI</span><h1>Everything you need, all in one mall.</h1><p>Discover products from trusted businesses and retailers. Browse freely, add what you like to your cart, and create an account when you're ready to shop.</p><div class="hero-actions"><a class="store-btn primary" href="#products">Shop products</a><a class="store-btn secondary" href="#categories">Browse categories</a></div></div>
        <div class="hero-card"><span>SHOP SMART</span><strong>Discover. Compare. Cart. Checkout.</strong><small>No account is required just to browse.</small></div>
    </section>

    <section class="store-section" id="categories">
        <div class="section-heading"><div><span class="eyebrow">EXPLORE</span><h2>Shop by category</h2></div></div>
        <div class="category-grid">
            @forelse($categories as $category)
                <a class="category-card" href="{{ route('marketplace.home', ['category' => $category->id]) }}"><strong>{{ $category->name }}</strong><span>Shop now →</span></a>
            @empty
                <p class="muted">Categories will appear here as products are added.</p>
            @endforelse
        </div>
    </section>

    <section class="store-section featured-section">
        <div class="section-heading"><div><span class="eyebrow">DISCOVER</span><h2>Featured products</h2></div><a href="#products">View all →</a></div>
        <div class="product-grid compact">
            @foreach($featured as $product)
                @include('marketplace.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <section class="store-section" id="products">
        <div class="section-heading"><div><span class="eyebrow">SHOP</span><h2>{{ request('q') ? 'Search results' : 'All available products' }}</h2></div><span class="muted">{{ $products->total() }} products</span></div>
        <div class="product-grid">
            @forelse($products as $product)
                @include('marketplace.partials.product-card', ['product' => $product])
            @empty
                <div class="empty-store"><h3>No products found</h3><p>Try another search or browse a different category.</p><a class="store-btn secondary" href="{{ route('marketplace.home') }}">Clear search</a></div>
            @endforelse
        </div>
        <div class="store-pagination">{{ $products->links() }}</div>
    </section>
</main>

<footer class="store-footer"><strong>UJUZI SHOP MALL</strong><span>Shop from trusted businesses and retailers.</span></footer>
</body>
</html>
