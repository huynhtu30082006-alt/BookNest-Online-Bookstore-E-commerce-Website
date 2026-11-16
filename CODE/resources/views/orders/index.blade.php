@extends('layouts.app')

@section('title', 'My orders')

@section('content')
<h1 class="h3 text-bn mb-3">My orders</h1>

@forelse ($orders as $order)
    @if ($loop->first)
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Order</th><th>Date</th><th class="text-center">Items</th><th class="text-end">Total</th><th>Status</th><th>Payment</th><th></th></tr>
                </thead>
                <tbody>
    @endif
                    <tr>
                        <td class="fw-semibold">{{ $order->reference }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">{{ $order->items_count }}</td>
                        <td class="text-end">{{ \App\Support\Money::vnd($order->total_price) }}</td>
                        <td>@include('partials.status-badge', ['status' => $order->status])</td>
                        <td>{{ $order->payment_status }}</td>
                        <td class="text-end"><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-bn">View</a></td>
                    </tr>
    @if ($loop->last)
                </tbody>
            </table>
        </div>
    </div>
    @endif
@empty
    <div class="text-center py-5">
        <div class="display-4">📦</div>
        <p class="lead">You have not placed any orders yet.</p>
        <a href="{{ route('home') }}" class="btn btn-bn">Start shopping</a>
    </div>
@endforelse

<div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
