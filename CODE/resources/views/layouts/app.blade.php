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
        <span aria-hidden="true">✦</span> A good book makes every day a little brighter.
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-booknest navbar-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
            <span class="brand-mark" aria-hidden="true">B</span>
            <span>BookNest<small class="brand-tagline d-block">Your cozy book corner</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Browse books</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('orders.track') }}">Track an order</a></li>
            </ul>
            <ul class="navbar-nav align-items-md-center gap-md-2">
                <li class="nav-item">
                    <a class="nav-link cart-link" href="{{ route('cart.index') }}">
                        <span aria-hidden="true">🛒</span> Cart
                        <span id="cart-count" class="badge rounded-pill bg-warning text-dark">{{ app(\App\Services\CartService::class)->count() }}</span>
                    </a>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">My profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">My orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log in</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-sm btn-nav-join" href="{{ route('register') }}">Join BookNest</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4 py-lg-5 flex-grow-1">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="store-footer mt-5">
    <div class="container py-4 py-lg-5">
        <div class="row gy-4 align-items-center">
            <div class="col-md-7">
                <a class="footer-brand" href="{{ route('home') }}">BookNest</a>
                <p class="mb-0 mt-2">Thoughtfully picked reads, delivered right to your door.</p>
            </div>
            <div class="col-md-5 text-md-end small">
                <a href="{{ route('home') }}">Browse books</a>
                <span class="footer-separator">·</span>
                <a href="{{ route('orders.track') }}">Track your order</a>
                <div class="mt-2">© {{ date('Y') }} BookNest · Prices in VND · Demo payments</div>
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
