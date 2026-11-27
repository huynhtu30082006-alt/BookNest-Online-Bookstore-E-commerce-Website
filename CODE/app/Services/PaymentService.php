<?php

namespace App\Services;

use App\Models\Order;

/**
 * Simulated payment: no real gateway is called.
 *  - cod     => stays Unpaid until the courier collects the money
 *  - card / ewallet => always "succeeds" and is marked Paid immediately
 */
class PaymentService
{
    public function charge(string $method, int $amount): string
    {
        return $method === 'cod' ? Order::PAYMENT_UNPAID : Order::PAYMENT_PAID;
    }
}
