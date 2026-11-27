@extends('layouts.app')

@section('title', 'My profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 text-bn mb-0">My profile</h1>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-bn">My orders</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}" novalidate>
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Delivery address</label>
                        <textarea id="address" name="address" rows="3"
                                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Used to pre-fill the checkout form.</div>
                    </div>
                    <button type="submit" class="btn btn-bn">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
