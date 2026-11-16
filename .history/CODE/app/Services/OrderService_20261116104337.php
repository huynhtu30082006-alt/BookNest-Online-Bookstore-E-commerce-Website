<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Exceptions\CouponException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cart,
        private PricingService $pricing,
        private PaymentService $payment,
    ) {
    }

    /**
     * Create an order from the current cart for a guest (null user) or a member.
     *
     * @param array{name:string,email:string,phone:string,address:string} $delivery
     * @throws CartException|CouponException
     */
    public function place(array $delivery, string $paymentMethod, ?User $user): Order
    {
        return DB::transaction(function () use ($delivery, $paymentMethod, $user) {
            if ($this->cart->isEmpty()) {
                throw new CartException('Your cart is empty.');
            }

            // Lock the rows so two simultaneous checkouts cannot oversell the last copies.
            Product::query()->whereIn('id', array_keys($this->cart->raw()))->lockForUpdate()->get();

            if ($problems = $this->cart->stockProblems()) {
                throw new CartException(implode(' ', $problems));
            }

            // Prices are recomputed here (never trusted from the browser).
            $summary = $this->pricing->summary();
            if ($summary->isEmpty()) {
                throw new CartException('Your cart is empty.');
            }

            // A guest's email is only known now, so the "one use per customer" rule is checked again here.
            if ($summary->coupon && $summary->coupon->hasBeenUsedBy($user, $delivery['email'])) {
                $this->pricing->removeCoupon();
                throw new CouponException("The coupon {$summary->coupon->code} was already used with this account or email, so it was removed. Please review your total and place the order again.");
            }
            
            $order = Order::create([
                'user_id' => $user?->id,
                'coupon_id' => $summary->coupon?->id,
                'guest_name' => $delivery['name'],
                'guest_email' => $delivery['email'],
                'guest_phone' => $delivery['phone'],
                'guest_address' => $delivery['address'],
                'subtotal' => $summary->subtotal,
                'discount' => $summary->discount,
                'delivery_fee' => $summary->deliveryFee,
                'status' => Order::STATUS_PENDING,
                'payment_method' => $paymentMethod,
                'payment_status' => $this->payment->charge($paymentMethod, $summary->total),
            ]);

            foreach ($summary->lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['unit_price'], // snapshot of the guest/member price
                ]);
                $line['product']->decrement('stock', $line['quantity']);
            }

            $this->cart->clear();
            $this->pricing->removeCoupon();

            return $order;
        });
    }
}
