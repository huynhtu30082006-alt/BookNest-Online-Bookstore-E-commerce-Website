<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->active()->with('category');
        $search = $request->query('search');
        $category = $request->query('category');
        $sort = $request->query('sort');

        if (is_string($search) && trim($search) !== '') {
            $search = trim($search);
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if (is_string($category) && $category !== '') {
            $query->whereHas('category', fn (Builder $query): Builder => $query->where('slug', $category));
        }

        match (is_string($sort) ? $sort : 'newest') {
            'price_asc' => $query->orderBy('guest_price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('guest_price')->orderByDesc('id'),
            'name_asc' => $query->orderBy('name')->orderByDesc('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(8)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('shop.home', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === Product::STATUS_ACTIVE, 404);
        $product->load('category');

        return view('shop.show', compact('product'));
    }
}
