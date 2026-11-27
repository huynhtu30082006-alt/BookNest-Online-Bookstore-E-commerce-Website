@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h1 class="h3 text-bn mb-3">Checkout</h1>

@guest
    <div class="alert alert-info">
        Checking out as a <strong>guest</strong> – just fill in your delivery details.
        Have an account? <a href="{{ route('login') }}">Log in</a> for member prices.
    </div>
@endguest

<div class="row g-4">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('checkout.store') }}" novalidate id="checkout-form">
            @csrf
            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Delivery information</h2>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $defaults['name']) }}"
                               class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $defaults['email']) }}"
                                   class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $defaults['phone']) }}" placeholder="0901234567"
                                   class="form-control @error('phone') is-invalid @enderror" required autocomplete="tel">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="address" class="form-label">Delivery address</label>
                        <textarea id="address" name="address" rows="3" required
                                  class="form-control @error('address') is-invalid @enderror"
                                  placeholder="House number, street, ward, district, city">{{ old('address', $defaults['address']) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">Payment method</h2>
                    <p class="small text-muted">Payment is simulated – no real money is charged.</p>
                    @foreach (config('booknest.payment_methods') as $key => $label)
                        <div class="form-check mb-2">
                            <input class="form-check-input @error('payment_method') is-invalid @enderror" type="radio"
                                   name="payment_method" id="pm-{{ $key }}" value="{{ $key }}"
                                   {{ old('payment_method', 'cod') === $key ? 'checked' : '' }}>
                            <label class="form-check-label" for="pm-{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                    @error('payment_method') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-bn btn-lg w-100" id="place-order-btn">Place order</button>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card summary-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Your order</h2>
                <ul class="list-unstyled small mb-3">
                    @foreach ($summary->lines as $line)
                        <li class="d-flex justify-content-between gap-2 mb-1">
                            <span>{{ $line['quantity'] }} × {{ $line['product']->name }}
                                <span class="text-muted">@ {{ \App\Support\Money::vnd($line['unit_price']) }}</span></span>
                            <span>{{ \App\Support\Money::vnd($line['line_total']) }}</span>
                        </li>
                    @endforeach
                </ul>
                @include('partials.coupon-form')
                @include('partials.price-summary')
                <a href="{{ route('cart.index') }}" class="btn btn-link btn-sm mt-2 p-0">← Edit cart</a>
            </div>
        </div>
    </div>
</div>
@endsection
