<?php

namespace App\Support;

use App\Models\Coupon;
use Illuminate\Support\Collection;

/** Immutable price breakdown shown in the cart, at checkout and stored on the order. */
class CartSummary
{
    /**
     * @param Collection<int, array{product: \App\Models\Product, quantity: int, unit_price: int, line_total: int}> $lines
     */
    public function __construct(
        public readonly Collection $lines,
        public readonly int $subtotal,
        public readonly int $discount,
        public readonly int $deliveryFee,
        public readonly int $total,
        public readonly ?Coupon $coupon = null,
        public readonly ?string $couponNotice = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->lines->isEmpty();
    }

    public function itemCount(): int
    {
        return (int) $this->lines->sum('quantity');
    }

    /** Formatted payload used by the AJAX endpoints. */
    public function toArray(): array
    {
        return [
            'count' => $this->itemCount(),
            'lines' => $this->lines->mapWithKeys(fn ($l) => [
                $l['product']->id => [
                    'quantity' => $l['quantity'],
                    'unit_price' => Money::vnd($l['unit_price']),
                    'line_total' => Money::vnd($l['line_total']),
                ],
            ])->all(),
            'subtotal' => Money::vnd($this->subtotal),
            'discount' => Money::vnd($this->discount),
            'has_discount' => $this->discount > 0,
            'delivery_fee' => Money::vnd($this->deliveryFee),
            'total' => Money::vnd($this->total),
            'coupon_code' => $this->coupon?->code,
            'coupon_notice' => $this->couponNotice,
        ];
    }
}
