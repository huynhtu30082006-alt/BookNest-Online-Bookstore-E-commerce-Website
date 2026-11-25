<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BookNest') – BookNest Online Bookstore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Lora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/booknest.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<div class="store-note">
    <div class="container d-flex justify-content-center align-items-center gap-2">
        <span aria-hidden="true">✦</span> BookNest member offers — better prices on every book.
    </div>
</div>
<header class="store-header">
    <nav class="navbar navbar-booknest">
        <div class="container header-main">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
            <span class="brand-mark" aria-hidden="true">B</span>
            <span>BookNest<small class="brand-tagline d-block">Your online bookstore</small></span>
        </a>
        <form class="header-search" method="GET" action="{{ route('home') }}" role="search">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Search books, authors..." aria-label="Search books, authors">
            <button type="submit" aria-label="Search"><span aria-hidden="true">⌕</span></button>
        </form>
        <div class="header-actions">
            <a class="header-action cart-link" href="{{ route('cart.index') }}">
                <span class="header-action-icon" aria-hidden="true">🛒
                    <span id="cart-count" class="badge rounded-pill bg-bn-accent text-white">{{ app(\App\Services\CartService::class)->count() }}</span>
                </span>
                <span class="header-action-label">Cart</span>
            </a>
            @auth
                <div class="dropdown">
                    <a class="header-action dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="header-action-icon" aria-hidden="true">♙</span>
                        <span class="header-action-label">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">My account</a></li>
                        <li><a class="dropdown-item" href="{{ route('orders.index') }}">My orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <div class="account-actions">
                    <span class="header-action-icon" aria-hidden="true">♙</span>
                    <span class="header-action-label">Account</span>
                    <span class="account-links"><a href="{{ route('login') }}">Log in</a><span>/</span><a href="{{ route('register') }}">Register</a></span>
                </div>
            @endauth
        </div>
        </div>
    </nav>
</header>

<main class="container store-main py-4 py-lg-5 flex-grow-1">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="store-footer mt-5">
    <div class="container py-4 py-lg-5">
        <div class="row gy-4 align-items-center">
            <div class="col-md-7">
                <a class="footer-brand" href="{{ route('home') }}">BookNest</a>
                <p class="mb-0 mt-2">Great reads for every journey — delivered to your door.</p>
            </div>
            <div class="col-md-5 text-md-end small">
                <a href="{{ route('home') }}">Shop</a>
                <span class="footer-separator">·</span>
                <a href="{{ route('orders.track') }}">Track an order</a>
                <div class="mt-2">© {{ date('Y') }} BookNest · Prices in VND</div>
            </div>
        </div>
    </div>
</footer>

<div id="toast-area" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/booknest.js') }}"></script>
@stack('scripts')
</body>
</html>
