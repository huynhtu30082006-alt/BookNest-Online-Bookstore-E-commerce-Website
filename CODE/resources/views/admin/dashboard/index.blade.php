@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard & Sales Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">BookNest System Overview</li>
    </ol>

    <!-- Overview Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">Total Revenue</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="fs-4 fw-bold">{{ number_format($totalRevenue) }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">Total Orders</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="fs-4 fw-bold">{{ $totalOrders }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">Total Customers</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="fs-4 fw-bold">{{ $totalCustomers }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">Total Books</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="fs-4 fw-bold">{{ $totalProducts }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Recent Orders
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name ?? $order->guest_name }}</td>
                        <td>{{ number_format($order->subtotal - $order->discount + $order->delivery_fee) }} đ</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection