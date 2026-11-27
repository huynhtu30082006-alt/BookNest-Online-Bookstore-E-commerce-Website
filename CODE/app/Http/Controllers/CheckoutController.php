<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Exceptions\CouponException;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PricingService;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PricingService $pricing,
        private OrderService $orders,
    ) {
    }

    public function show()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add a book before checking out.');
        }

        if ($problems = $this->cart->stockProblems()) {
            return redirect()->route('cart.index')->with('error', implode(' ', $problems));
        }

        $user = auth()->user();

        return view('checkout.show', [
            'summary' => $this->pricing->summary(),
            // Members get their profile pre-filled; guests only fill in delivery information.
            'defaults' => [
                'name' => $user?->name,
                'email' => $user?->email,
                'phone' => $user?->phone,
                'address' => $user?->address,
            ],
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        try {
            $order = $this->orders->place(
                $request->safe()->only(['name', 'email', 'phone', 'address']),
                $request->validated('payment_method'),
                $request->user(),
            );
        } catch (CartException|CouponException $e) {
            return redirect()->route('cart.index')->withInput()->with('error', $e->getMessage());
        }

        // Lets a guest (no account) open their confirmation / order status during this session.
        $request->session()->push('viewable_orders', $order->id);

        return redirect()->route('orders.confirmation', $order)
            ->with('success', 'Thank you! Your order has been placed.');
    }
}
