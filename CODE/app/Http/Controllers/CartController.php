<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PricingService $pricing,
    ) {
    }

    public function index()
    {
        $summary = $this->pricing->summary();

        return view('cart.index', [
            'summary' => $summary,
            'memberSavings' => $this->memberSavings($summary),
        ]);
    }

    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:99']]);

        try {
            $this->cart->add($product, (int) ($data['quantity'] ?? 1));
        } catch (CartException $e) {
            return $this->fail($request, $e->getMessage());
        }

        $message = "\"{$product->name}\" was added to your cart.";

        if ($request->boolean('buy_now') && !$request->expectsJson()) {
            return redirect()->route('checkout.show')->with('success', $message);
        }

        return $request->expectsJson()
            ? response()->json(['message' => $message] + $this->pricing->summary()->toArray())
            : back()->with('success', $message);
    }

    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);

        try {
            $this->cart->update($product, (int) $data['quantity']);
        } catch (CartException $e) {
            return $this->fail($request, $e->getMessage());
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'Cart updated.'] + $this->pricing->summary()->toArray())
            : redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->cart->remove($product->id);

        return $request->expectsJson()
            ? response()->json(['message' => 'Item removed from your cart.'] + $this->pricing->summary()->toArray())
            : redirect()->route('cart.index')->with('success', 'Item removed from your cart.');
    }

    private function fail(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], 422)
            : back()->with('error', $message);
    }

    /** How much a guest would save by logging in (null for members). */
    private function memberSavings($summary): ?int
    {
        if (auth()->check()) {
            return null;
        }

        $saving = $summary->lines->sum(
            fn ($l) => ($l['product']->guest_price - $l['product']->member_price) * $l['quantity']
        );

        return $saving > 0 ? (int) $saving : null;
    }
}
