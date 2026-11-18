<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Coupon Management</h1>
            <a href="{{ route('admin.coupons.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Coupon</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search coupon code..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Search</button>
        </form>

        <!-- Table -->
        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Code</th>
                        <th class="py-3 px-6 text-left">Discount Value</th>
                        <th class="py-3 px-6 text-left">Min Order Value</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse($coupons as $coupon)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $coupon->id }}</td>
                            <td class="py-3 px-6 text-left font-bold text-blue-600">{{ $coupon->code }}</td>
                            <td class="py-3 px-6 text-left">{{ number_format($coupon->discount_value) }} đ</td>
                            <td class="py-3 px-6 text-left">{{ number_format($coupon->min_order_value ?? 0) }} đ</td>
                            <td class="py-3 px-6 text-center">
                                <span class="{{ $coupon->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }} py-1 px-3 rounded-full text-xs font-bold">
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Edit</a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 px-6 text-center text-gray-500">No coupons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    </div>
</body>
</html>