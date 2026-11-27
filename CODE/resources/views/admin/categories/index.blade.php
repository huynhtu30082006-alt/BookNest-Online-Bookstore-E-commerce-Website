<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Category Management</h1>
            <a href="{{ route('admin.categories.create') }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded">Add New Category</a>
        </div>

        @if(session('success'))
            <div class="bg-[#E0F2FE] border border-[#7DD3FC] text-[#1E3A5F] px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search category by name..." class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Search</button>
        </form>

        <!-- Categories Table -->
        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-[#E2E8F0] text-[#64748B] uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Description</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[#64748B] text-sm font-light">
                    @forelse($categories as $category)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F4F7FA]">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $category->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $category->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $category->description }}</td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="bg-[#0284C7] hover:bg-[#0369A1] text-white px-3 py-1 rounded text-xs">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 px-6 text-center text-[#64748B]">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
</body>
</html>