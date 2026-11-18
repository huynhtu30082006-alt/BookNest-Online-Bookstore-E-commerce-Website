<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search customer by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount('orders')->latest()->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Display the specified customer details and order history.
     */
    public function show(User $customer)
    {
        $customer->load('orders.items.product', 'orders.coupon');
        return view('admin.customers.show', compact('customer'));
    }
}