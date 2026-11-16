@extends('layouts.app')

@section('title', 'Track order')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-bn mb-1">Track your order</h1>
                <p class="text-muted small">Enter the order reference from your confirmation page (e.g. BN-000012) and the email used at checkout.</p>
                @auth
                    <div class="alert alert-info small">Members can see all orders under <a href="{{ route('orders.index') }}">My orders</a>.</div>
                @endauth
                <form method="POST" action="{{ route('orders.track.submit') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="reference" class="form-label">Order reference</label>
                        <input type="text" id="reference" name="reference" value="{{ old('reference') }}" placeholder="BN-000012"
                               class="form-control @error('reference') is-invalid @enderror" required>
                        @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-bn w-100">Track order</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
