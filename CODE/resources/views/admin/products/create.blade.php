<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8 max-w-xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Create New Product</h1>
            <a href="{{ route('admin.products.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back</a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="name">Product Name</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" name="name" value="{{ old('name') }}" required>
                </div>

                <!-- Author field added here -->
                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="author">Author</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="author" type="text" name="author" value="{{ old('author') }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="category_id">Category</label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="guest_price">Guest Price</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="guest_price" type="number" step="0.01" name="guest_price" value="{{ old('guest_price') }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="member_price">Member Price (Must be <= Guest Price)</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="member_price" type="number" step="0.01" name="member_price" value="{{ old('member_price') }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="stock">Stock</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="stock" type="number" name="stock" value="{{ old('stock', 0) }}" required>
                </div>

                <div class="mb-6">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="description">Description</label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-between">
                    <button class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>