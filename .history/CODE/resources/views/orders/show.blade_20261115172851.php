@extends('layouts.app')

@section('title', $confirmation ? 'Order confirmed' : 'Order '.$order->reference)

@section('content')
@php
    $steps = ['Pending', 'Confirmed', 'Shipping', 'Completed'];
    $currentIndex = array_search($order->status, $steps, true);
@endphp

@if ($confirmation)
    <div class="text-center mb-4">
        <div class="display-4">✅</div>
        <h1 class="h3 text-bn">Thank you, {{ $order->guest_name }}!</h1>
        <p class="mb-1">Your order <strong>{{ $order->reference }}</strong> has been placed.</p>
        <p class="text-muted small">
            Keep your order reference{{ $order->isGuestOrder() ? ' and email' : '' }} to follow the status of your order.
            @if ($order->isGuestOrder())
                You can look it up any time on the <a href="{{ route('orders.track') }}">Track order</a> page.
            @endif
        </p>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 text-bn mb-0">Order {{ $order->reference }}</h1>
        @auth <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-bn">← My orders</a> @endauth
    </div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
            <div>Status: @include('partials.status-badge', ['status' => $order->status])</div>
            <div class="text-muted small">Placed {{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>

        @if ($order->status === 'Cancelled')
            <div class="alert alert-danger mb-0">This order was cancelled.</div>
        @else
            <div class="status-timeline">
                @foreach ($steps as $i => $step)
                    <div class="status-step {{ $i < $currentIndex ? 'done' : '' }} {{ $i === $currentIndex ? 'current' : '' }}">{{ $step }}</div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Book</th><th class="text-end">Unit price</th><th class="text-center">Qty</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}<div class="small text-muted">{{ $item->product->author }}</div></td>
                                <td class="text-end">{{ \App\Support\Money::vnd($item->price) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ \App\Support\Money::vnd($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <dl class="row mb-0">
                    <dt class="col-8 fw-normal">Subtotal</dt><dd class="col-4 text-end">{{ \App\Support\Money::vnd($order->subtotal) }}</dd>
                    @if ($order->discount > 0)
                        <dt class="col-8 fw-normal text-success">Discount @if($order->coupon)({{ $order->coupon->code }})@endif</dt>
                        <dd class="col-4 text-end text-success">− {{ \App\Support\Money::vnd($order->discount) }}</dd>
                    @endif
                    <dt class="col-8 fw-normal">Delivery fee</dt><dd class="col-4 text-end">{{ \App\Support\Money::vnd($order->delivery_fee) }}</dd>
                    <dt class="col-8 fs-5">Total</dt><dd class="col-4 text-end fs-5 fw-bold text-bn">{{ \App\Support\Money::vnd($order->total_price) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6">Delivery to</h2>
                <div>{{ $order->guest_name }}</div>
                <div class="small">{{ $order->guest_phone }} · {{ $order->guest_email }}</div>
                <div class="small text-muted">{{ $order->guest_address }}</div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6">Payment</h2>
                <div>{{ $order->payment_method_label }}</div>
                <span class="badge text-bg-{{ $order->payment_status === 'Paid' ? 'success' : 'warning' }}">{{ $order->payment_status }}</span>
                @if ($order->payment_status === 'Unpaid')
                    <div class="small text-muted mt-1">Pay the courier when your order arrives.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-4 text-center"><a href="{{ route('home') }}" class="btn btn-bn">Continue shopping</a></div>
@endsection
