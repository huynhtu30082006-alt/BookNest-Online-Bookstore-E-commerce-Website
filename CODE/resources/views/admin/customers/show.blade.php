<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Customer Details: {{ $customer->name }}</h1>
            <a href="{{ route('admin.customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to List</a>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white shadow-md rounded p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Account Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="font-semibold">Full Name:</span> {{ $customer->name }}</div>
                <div><span class="font-semibold">Email:</span> {{ $customer->email }}</div>
                <div><span class="font-semibold">Joined Date:</span> {{ $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                <div><span class="font-semibold">Total Orders:</span> {{ $customer->orders->count() }}</div>
            </div>
        </div>

        <!-- Order History -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">Order History</h2>
        <div class="bg-white shadow-md rounded overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Order ID</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Total Price</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse($customer->orders as $order)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium">#{{ $order->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="px-2 py-1 rounded text-xs font-bold 
                                    {{ $order->status === 'Completed' ? 'bg-green-200 text-green-800' : 
                                       ($order->status === 'Cancelled' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800') }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-right font-bold">{{ number_format($order->total_price) }} đ</td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">View Order</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 px-6 text-center text-gray-500">This customer has no orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>