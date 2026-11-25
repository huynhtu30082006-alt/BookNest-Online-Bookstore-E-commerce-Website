<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Coupon - BookNest Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F4F7FA] font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-8 max-w-xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#0F172A]">Edit Coupon</h1>
            <a href="{{ route('admin.coupons.index') }}" class="bg-[#64748B] hover:bg-[#334155] text-white font-bold py-2 px-4 rounded">Back</a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="code">Coupon Code</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155] uppercase" id="code" type="text" name="code" value="{{ old('code', $coupon->code) }}" required>
                </div>
                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="discount_value">Discount Value (VNĐ)</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155]" id="discount_value" type="number" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" required>
                </div>
                <div class="mb-4">
                    <label class="block text-[#334155] text-sm font-bold mb-2" for="min_order_value">Min Order Value (VNĐ)</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-[#334155]" id="min_order_value" type="number" name="min_order_value" value="{{ old('min_order_value', $coupon->min_order_value) }}">
                </div>
                <div class="mb-6 flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="mr-2">
                    <label class="text-[#334155] text-sm font-bold" for="is_active">Active Status</label>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold py-2 px-4 rounded" type="submit">Update Coupon</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>