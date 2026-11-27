<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #{{ $order->id }} - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Order Details #{{ $order->id }}</h1>
            <a href="{{ route('admin.orders.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back to Orders</a>
        </div>

        @if(session('success'))
            <div class="bg-[#E0F2FE] border border-[#7DD3FC] text-[#1E3A5F] px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Order Information Card -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
            <h2 class="text-lg font-bold text-[#334155] mb-4 border-b pb-2">Customer & Shipping Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm text-[#64748B]">
                <div>
                    <p><strong>Customer Name:</strong> {{ $order->shipping_name ?? $order->user->name ?? 'Guest' }}</p>
                    <p class="mt-2"><strong>Email:</strong> {{ $order->shipping_email ?? $order->user->email ?? 'N/A' }}</p>
                    <p class="mt-2"><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p><strong>Shipping Address:</strong> {{ $order->shipping_address ?? 'N/A' }}</p>
                    <p class="mt-2"><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                    <p class="mt-2">
                        <strong>Current Status:</strong> 
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            @if($order->status === 'completed') bg-[#DBEAFE] text-[#1E3A5F]
                            @elseif($order->status === 'processing') bg-[#DBEAFE] text-[#1E3A5F]
                            @elseif($order->status === 'cancelled') bg-red-200 text-red-800
                            @else bg-[#DBEAFE] text-[#1E3A5F] @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Update Status Form -->
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="mt-6 pt-4 border-t flex items-center gap-4">
                @csrf
                @method('PUT')
                <label for="status" class="block text-[#334155] text-sm font-bold">Update Status:</label>
                <select name="status" id="status" class="shadow border rounded py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
                    <option value="pending" {{ strtolower($order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ strtolower($order->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ strtolower($order->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ strtolower($order->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded text-sm">Update Status</button>
            </form>
        </div>

        <!-- Ordered Items Table -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-lg font-bold text-[#334155] mb-4 border-b pb-2">Ordered Products</h2>
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-xs leading-normal">
                        <th class="py-3 px-4 text-left">Product Name</th>
                        <th class="py-3 px-4 text-center">Quantity</th>
                        <th class="py-3 px-4 text-right">Price</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @foreach($order->items as $item)
                        <tr class="border-b border-[#E2E8F0]">
                            <td class="py-3 px-4 text-left font-medium">{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td class="py-3 px-4 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-right">{{ number_format($item->price) }} đ</td>
                            <td class="py-3 px-4 text-right font-medium">{{ number_format($item->price * $item->quantity) }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 text-right">
                <p class="text-lg font-bold text-[#0F172A]">Total Amount: <span class="text-[#0284C7]">{{ number_format($order->total_price) }} đ</span></p>
            </div>
        </div>
    </div>
</body>
</html>