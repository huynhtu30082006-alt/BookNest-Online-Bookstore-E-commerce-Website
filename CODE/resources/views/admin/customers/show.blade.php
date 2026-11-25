<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Customer Details: {{ $customer->name }}</h1>
            <a href="{{ route('admin.customers.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back to List</a>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white shadow-md rounded p-6 mb-6">
            <h2 class="text-lg font-bold text-[#334155] mb-4">Account Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="font-semibold">Full Name:</span> {{ $customer->name }}</div>
                <div><span class="font-semibold">Email:</span> {{ $customer->email }}</div>
                <div><span class="font-semibold">Joined Date:</span> {{ $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                <div><span class="font-semibold">Total Orders:</span> {{ $customer->orders->count() }}</div>
            </div>
        </div>

        <!-- Order History -->
        <h2 class="text-xl font-bold text-[#0F172A] mb-4">Order History</h2>
        <div class="bg-white shadow-md rounded overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Order ID</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Total Price</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @forelse($customer->orders as $order)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F4F7FA]">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium">#{{ $order->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold
                                    {{ $order->status === 'Completed' ? 'bg-[#DBEAFE] text-[#1E3A5F]' :
                                       ($order->status === 'Cancelled' ? 'bg-red-200 text-red-800' : 'bg-[#DBEAFE] text-[#1E3A5F]') }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-right font-bold">{{ number_format($order->total_price) }} đ</td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white px-3 py-1 rounded text-xs">View Order</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 px-6 text-center text-[#64748B]">This customer has no orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>