@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-bn mb-1">Create a member account</h1>
                <p class="text-muted small">Members get lower prices and can follow their order history. You can still check out as a guest.</p>
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0901234567"
                                   class="form-control @error('phone') is-invalid @enderror" required autocomplete="tel">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Delivery address <span class="text-muted">(optional)</span></label>
                        <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                  autocomplete="street-address">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">At least 8 characters, with letters and numbers.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-bn w-100">Register</button>
                </form>
                <p class="text-center small mt-3 mb-0">Already a member? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
