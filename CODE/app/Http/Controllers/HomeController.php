<?php

namespace App\Http\Controllers;

use App\Models\Product;

/**
 * TEMPORARY placeholder, outside the customer-flow scope: lets "Add to cart" be tried before the
 * product browsing pages (home, categories, search, product detail) are merged. The UI owner replaces it.
 */
class HomeController extends Controller
{
    public function index()
    {
        $products = Product::query()->active()->with('category')->latest('id')->paginate(12);

        return view('shop.home', compact('products'));
    }
}
