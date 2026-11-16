<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // discount_value is a fixed VND amount taken off the subtotal.
        Coupon::updateOrCreate(['code' => 'WELCOME25'], ['discount_value' => 25000, 'min_order_value' => 200000, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'BOOK50'], ['discount_value' => 50000, 'min_order_value' => 500000, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'EXPIRED10'], ['discount_value' => 10000, 'min_order_value' => 0, 'is_active' => false]);
    }
}
