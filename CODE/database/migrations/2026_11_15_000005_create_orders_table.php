<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // NULL for guest orders (User 0..1 --- places --- N Order).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            // Delivery snapshot: filled for guest AND member checkout.
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone', 20);
            $table->text('guest_address');

            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            // total_price is a derived attribute in the ERD: subtotal - discount + delivery_fee.

            $table->string('status', 20)->default('Pending'); // Pending, Confirmed, Shipping, Completed, Cancelled
            $table->string('payment_method', 30);              // cod | card | ewallet (simulated)
            $table->string('payment_status', 20)->default('Unpaid'); // Unpaid | Paid
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
