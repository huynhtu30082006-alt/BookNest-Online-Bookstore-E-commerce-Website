<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Customer Management</h1>
            <a href="{{ route('admin.products.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back to Admin</a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer by name or email..." class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Search</button>
        </form>

        <!-- Customers Table -->
        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-center">Total Orders</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @forelse($customers as $customer)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F4F7FA]">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $customer->id }}</td>
                            <td class="py-3 px-6 text-left font-medium">{{ $customer->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $customer->email }}</td>
                            <td class="py-3 px-6 text-center font-bold text-[#0284C7]">{{ $customer->orders_count ?? 0 }}</td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white px-3 py-1 rounded text-xs">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 px-6 text-center text-[#64748B]">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </div>
</body>
</html>