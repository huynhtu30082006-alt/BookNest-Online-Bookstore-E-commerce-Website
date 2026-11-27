<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Product Management</h1>
            <a href="{{ route('admin.products.create') }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded">Add New Product</a>
        </div>

        @if(session('success'))
            <div class="bg-[#E0F2FE] border border-[#7DD3FC] text-[#1E3A5F] px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-[#E0F2FE] border border-[#7DD3FC] text-[#1E3A5F] px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product by name..." class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Search</button>
        </form>

        <!-- Products Table -->
        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Category</th>
                        <th class="py-3 px-6 text-right">Guest Price</th>
                        <th class="py-3 px-6 text-right">Member Price</th>
                        <th class="py-3 px-6 text-center">Stock</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @forelse($products as $product)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F4F7FA]">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $product->id }}</td>
                            <td class="py-3 px-6 text-left font-medium">{{ $product->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $product->category->name ?? 'N/A' }}</td>
                            <td class="py-3 px-6 text-right">{{ number_format($product->guest_price) }} đ</td>
                            <td class="py-3 px-6 text-right">{{ number_format($product->member_price) }} đ</td>
                            <td class="py-3 px-6 text-center">{{ $product->stock }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="px-2 py-1 rounded text-xs {{ $product->status === 'inactive' ? 'bg-red-200 text-red-800' : 'bg-[#DBEAFE] text-[#1E3A5F]' }}">
                                    {{ $product->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white px-3 py-1 rounded text-xs">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete or deactivate this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 px-6 text-center text-[#64748B]">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</body>
</html>