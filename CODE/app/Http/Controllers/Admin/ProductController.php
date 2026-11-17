<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products with search and pagination.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search product by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(10)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'guest_price' => 'required|numeric|min:0',
            // Ensure guest_price >= member_price using lte (less than or equal to guest_price)
            'member_price' => 'required|numeric|min:0|lte:guest_price',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'guest_price' => 'required|numeric|min:0',
            'member_price' => 'required|numeric|min:0|lte:guest_price',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove or deactivate the specified product.
     */
    public function destroy(Product $product)
    {
        // Business rule: Do not delete products linked to existing orders.
        // Instead, update status to inactive.
        if (method_exists($product, 'orderItems') && $product->orderItems()->count() > 0) {
            $product->update(['status' => 'inactive']);
            return redirect()->route('admin.products.index')
                ->with('warning', 'Product is linked to existing orders and cannot be deleted. It has been set to inactive instead.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}