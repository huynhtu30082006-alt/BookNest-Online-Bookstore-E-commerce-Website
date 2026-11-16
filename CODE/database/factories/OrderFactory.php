<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'coupon_id' => null,
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => '09'.fake()->numerify('########'),
            'guest_address' => fake()->address(),
            'subtotal' => 100000,
            'discount' => 0,
            'delivery_fee' => 30000,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
        ];
    }

    /** Attach one order line so the order detail page has something to show. */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $product = Product::factory()->create();
            $order->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 100000]);
        });
    }
}
