<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 9999),
            'stock' => fake()->numberBetween(0, 100),
            'enabled' => true,
        ];
    }
}
