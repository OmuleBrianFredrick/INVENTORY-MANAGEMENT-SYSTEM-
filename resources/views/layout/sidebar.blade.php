<aside class="sidebar">
    <div class="brand">
        <span class="brand-mark">UZ</span>
        <div>
            <strong>UJUZI SHOP MALL</strong>
            <small>Smart Inventory &amp; Shopping Management</small>
        </div>
    </div>

    <nav>
        <a href="{{ route('products.index') }}" class="nav-link">▣ Dashboard</a>

        @if(auth()->user()->isManager())
            <a href="{{ route('products.create') }}" class="nav-link">＋ Add Product</a>
            <a href="{{ route('categories.index') }}" class="nav-link">▤ Categories</a>
            <a href="{{ route('suppliers.index') }}" class="nav-link">♧ Suppliers</a>
            <a href="{{ route('purchase-orders.index') }}" class="nav-link">▱ Purchase Orders</a>
            <a href="{{ route('customers.index') }}" class="nav-link">♙ Customers</a>
            <a href="{{ route('orders.index') }}" class="nav-link">▤ Sales Orders</a>
            <a href="{{ route('users.index') }}" class="nav-link">♙ Employee Accounts</a>
        @endif

        @if(auth()->user()->isAdmin())
            <a href="{{ route('security.logs') }}" class="nav-link">⌁ Security Logs</a>
        @endif
    </nav>

    <div class="sidebar-note">
        <span>Security</span>
        <strong>Secure Sign-In</strong>
        <small>Manager accounts use email OTP verification.</small>
    </div>
</aside>
