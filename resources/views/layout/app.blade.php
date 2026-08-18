<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Inventory Management System')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/actions.css') }}">
</head>
<body>
    <div class="app-shell">
        @auth
            @include('layout.sidebar')
        @endauth

        <main class="main">
            <header class="topbar">
                <div>
                    <span class="eyebrow">ADVANCED INVENTORY</span>
                    <strong>@yield('page_title','Inventory Management')</strong>
                </div>

                @auth
                    <div class="top-user">
                        <a class="btn btn-light" href="{{ route('alerts.index') }}">
                            Alerts
                            @php($unreadAlerts = \App\Models\InventoryAlert::where('user_id', auth()->id())->whereNull('read_at')->count())
                            @if($unreadAlerts)
                                <span class="role-pill">{{ $unreadAlerts }}</span>
                            @endif
                        </a>

                        {{ auth()->user()->name }}
                        <span class="role-pill">{{ ucfirst(auth()->user()->role) }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-light">Logout</button>
                        </form>
                    </div>
                @endauth
            </header>

            <section class="content">
                @if(session('success'))
                    <div class="alert success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
