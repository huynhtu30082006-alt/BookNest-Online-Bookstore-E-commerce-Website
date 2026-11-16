<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Session-based shopping cart (works for guests and members).
 * The session only stores product_id => quantity; prices are always computed
 * from the database so the guest/member price is applied consistently.
 */
class CartService
{
    private const KEY = 'cart';

    /** @return array<int,int> product_id => quantity */
    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->raw();
        $new = ($cart[$product->id] ?? 0) + $quantity;
        $this->assertQuantity($product, $new);

        $cart[$product->id] = $new;
        session([self::KEY => $cart]);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($product->id);

            return;
        }

        $this->assertQuantity($product, $quantity);

        $cart = $this->raw();
        $cart[$product->id] = $quantity;
        session([self::KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    /**
     * Cart lines priced for the current visitor (member price if logged in, guest price otherwise).
     * Products that were deleted or deactivated are silently dropped from the cart.
     *
     * @return Collection<int, array{product: Product, quantity: int, unit_price: int, line_total: int}>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();
        if ($raw === []) {
            return collect();
        }

        $user = Auth::user();
        $products = Product::query()->active()->whereIn('id', array_keys($raw))->get()->keyBy('id');

        $lines = collect();
        foreach ($raw as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                $this->remove($productId);

                continue;
            }

            $unit = $product->priceFor($user);
            $lines->push([
                'product' => $product,
                'quantity' => (int) $quantity,
                'unit_price' => $unit,
                'line_total' => $unit * (int) $quantity,
            ]);
        }

        return $lines;
    }

    /**
     * Stock check run before placing an order.
     *
     * @return array<int,string> list of customer-facing problems (empty when the cart is OK)
     */
    public function stockProblems(): array
    {
        $problems = [];
        foreach ($this->lines() as $line) {
            $p = $line['product'];
            if ($p->stock < 1) {
                $problems[] = "\"{$p->name}\" is out of stock. Please remove it from your cart.";
            } elseif ($line['quantity'] > $p->stock) {
                $problems[] = "Only {$p->stock} copies of \"{$p->name}\" are left (you have {$line['quantity']} in your cart).";
            }
        }

        return $problems;
    }

    private function assertQuantity(Product $product, int $quantity): void
    {
        if (! $product->isPurchasable()) {
            throw new CartException("\"{$product->name}\" is currently unavailable.");
        }

        $max = config('booknest.max_quantity_per_item');
        if ($quantity > $max) {
            throw new CartException("You can order at most {$max} copies of the same book.");
        }

        if ($quantity > $product->stock) {
            throw new CartException("Only {$product->stock} copies of \"{$product->name}\" are in stock.");
        }
    }
}
