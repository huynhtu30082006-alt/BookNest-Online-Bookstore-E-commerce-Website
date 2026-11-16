<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $guest = fake()->numberBetween(60, 300) * 1000;

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'author' => fake()->name(),
            'description' => fake()->paragraph(),
            'guest_price' => $guest,
            'member_price' => (int) round($guest * 0.9 / 1000) * 1000, // 10% member discount
            'stock' => 10,
            'image_url' => null,
            'status' => Product::STATUS_ACTIVE,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
