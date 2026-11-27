<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('author');
            $table->text('description')->nullable();
            // Prices are whole VND amounts.
            $table->unsignedInteger('guest_price');
            $table->unsignedInteger('member_price');
            $table->unsignedInteger('stock')->default(0);
            $table->string('image_url')->nullable();
            $table->string('status', 20)->default('active'); // active | inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
