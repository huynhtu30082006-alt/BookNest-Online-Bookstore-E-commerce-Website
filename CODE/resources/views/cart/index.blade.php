@extends('layouts.app')

@section('title', 'Shopping cart')

@section('content')
<h1 class="h3 text-bn mb-3">Shopping cart</h1>

<div id="cart-empty" class="text-center py-5 {{ $summary->isEmpty() ? '' : 'd-none' }}">
    <div class="display-4">🛒</div>
    <p class="lead mb-3">Your cart is empty.</p>
    <a href="{{ route('home') }}" class="btn btn-bn">Browse books</a>
</div>

@unless ($summary->isEmpty())
<div class="row g-4" id="cart-content">
    <div class="col-lg-8">
        @if ($memberSavings)
            <div class="alert alert-info">
                💡 <a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">register</a> to pay member prices
                and save <strong>{{ \App\Support\Money::vnd($memberSavings) }}</strong> on this cart.
            </div>
        @endif

        <div class="card">
            <ul class="list-group list-group-flush" id="cart-lines">
                @foreach ($summary->lines as $line)
                    @php($p = $line['product'])
                    <li class="list-group-item p-3" data-line="{{ $p->id }}">
                        <div class="d-flex gap-3">
                            <div class="book-cover cart-thumb">
                                @if ($p->image_url) <img src="{{ $p->image_url }}" alt="Cover of {{ $p->name }}"> @else 📖 @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="small text-muted">{{ $p->author }}</div>
                                <div class="small">
                                    <span class="{{ auth()->check() ? 'price-member' : '' }}" data-line-field="unit_price">{{ \App\Support\Money::vnd($line['unit_price']) }}</span>
                                    <span class="text-muted">{{ auth()->check() ? '(member price)' : '(guest price)' }}</span>
                                </div>
                                <div class="small text-muted">{{ $p->stock }} in stock</div>

                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                    <form method="POST" action="{{ route('cart.update', $p) }}" data-qty-form class="d-flex gap-2">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1"
                                               max="{{ min($p->stock, config('booknest.max_quantity_per_item')) }}"
                                               class="form-control form-control-sm qty-input" aria-label="Quantity of {{ $p->name }}">
                                        <button type="submit" class="btn btn-sm btn-outline-bn" data-qty-submit>Update</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.remove', $p) }}" data-remove-form>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger">Remove</button>
                                    </form>
                                </div>
                            </div>
                            <div class="fw-semibold text-end" data-line-field="line_total">{{ \App\Support\Money::vnd($line['line_total']) }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('home') }}" class="btn btn-link mt-2">← Continue shopping</a>
    </div>

    <div class="col-lg-4">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Order summary</h2>
                @include('partials.coupon-form')
                @include('partials.price-summary')
                <a href="{{ route('checkout.show') }}" class="btn btn-bn w-100 mt-3">Proceed to checkout</a>
                @guest
                    <div class="small text-muted text-center mt-2">No account needed – you can check out as a guest.</div>
                @endguest
            </div>
        </div>
    </div>
</div>
@endunless
@endsection
