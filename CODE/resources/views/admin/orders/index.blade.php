<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Order Management</h1>
            <a href="{{ route('admin.products.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back to Products</a>
        </div>

        @if(session('success'))
            <div class="bg-[#E0F2FE] border border-[#7DD3FC] text-[#1E3A5F] px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-6 flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order ID or Customer Name..." class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
            
            <select name="status" class="shadow appearance-none border rounded py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <button type="submit" class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded">Filter</button>
        </form>

        <!-- Orders Table -->
        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Order ID</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-right">Total Amount</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Date</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @forelse($orders as $order)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F4F7FA]">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-bold">#{{ $order->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $order->shipping_name ?? $order->user->name ?? 'Guest' }}</td>
                            <td class="py-3 px-6 text-right font-medium">{{ number_format($order->total_price ?? 0) }} đ</td>
                            <td class="py-3 px-6 text-center">
                                <span class="px-2 py-1 rounded text-xs 
                                    @if($order->status === 'completed') bg-[#DBEAFE] text-[#1E3A5F]
                                    @elseif($order->status === 'processing') bg-[#DBEAFE] text-[#1E3A5F]
                                    @elseif($order->status === 'cancelled') bg-red-200 text-red-800
                                    @else bg-[#DBEAFE] text-[#1E3A5F] @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white px-3 py-1 rounded text-xs">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 px-6 text-center text-[#64748B]">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</body>
</html>