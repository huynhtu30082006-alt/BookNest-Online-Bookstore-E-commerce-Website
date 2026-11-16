<?php

namespace App\Services;

use App\Exceptions\CouponException;
use App\Models\Coupon;
use App\Support\Money;
use App\Models\User;

/**
 * One simple coupon rule: a coupon takes a fixed amount (discount_value, VND) off the
 * subtotal when the subtotal reaches min_order_value and the coupon is active.
 */
class CouponService
{
    /** @throws CouponException */
        public function validate(string $code, int $subtotal, ?User $user = null, ?string $email = null): Coupon
    {
        $code = trim($code);
        if ($code === '') {
            throw new CouponException('Please enter a coupon code.');
        }
        
        if (! $user) {
            throw new CouponException('Please log in or register to use a discount code.');
        }

        $coupon = Coupon::query()->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();
        if (! $coupon) {
            throw new CouponException('This coupon code does not exist.');
        }

        if (! $coupon->is_active) {
            throw new CouponException('This coupon is no longer active.');
        }

        if ($subtotal < $coupon->min_order_value) {
            throw new CouponException('This coupon requires a minimum order of '.Money::vnd($coupon->min_order_value).'.');
        }

        if ($coupon->hasBeenUsedBy($user, $email)) {
            throw new CouponException('You have already used this coupon.');
        }

        return $coupon;
    }

    /** Discount never exceeds the subtotal. */
    public function discountFor(Coupon $coupon, int $subtotal): int
    {
        return min($coupon->discount_value, $subtotal);
    }
}
