<?php

namespace App\Services;

use App\Exceptions\CouponException;
use App\Support\CartSummary;

/** Builds the full price breakdown: subtotal, discount, delivery fee and total. */
class PricingService
{
    private const COUPON_KEY = 'coupon_code';

    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {
    }

    public function appliedCouponCode(): ?string
    {
        return session(self::COUPON_KEY);
    }

    /** @throws CouponException */
    public function applyCoupon(string $code): void
    {
        $subtotal = (int) $this->cart->lines()->sum('line_total');
        $coupon = $this->coupons->validate($code, $subtotal, auth()->user());
        session([self::COUPON_KEY => $coupon->code]);
    }

    public function removeCoupon(): void
    {
        session()->forget(self::COUPON_KEY);
    }

    public function summary(): CartSummary
    {
        $lines = $this->cart->lines();
        $subtotal = (int) $lines->sum('line_total');

        $coupon = null;
        $notice = null;
        $discount = 0;

        if ($code = $this->appliedCouponCode()) {
            try {
                $coupon = $this->coupons->validate($code, $subtotal, auth()->user());
                $discount = $this->coupons->discountFor($coupon, $subtotal);
            } catch (CouponException $e) {
                // The cart changed (e.g. subtotal dropped below the minimum) or the coupon was disabled.
                $this->removeCoupon();
                $notice = 'Coupon "'.$code.'" was removed: '.lcfirst($e->getMessage());
            }
        }

        $deliveryFee = $lines->isEmpty() ? 0 : (int) config('booknest.delivery_fee');
        $total = max(0, $subtotal - $discount) + $deliveryFee;

        return new CartSummary($lines, $subtotal, $discount, $deliveryFee, $total, $coupon, $notice);
    }
}
