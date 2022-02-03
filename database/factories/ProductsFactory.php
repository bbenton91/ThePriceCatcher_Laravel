<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_sku' => $this->faker->numberBetween(),
            'product_name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'regular_price' => $this->faker->numberBetween(100, 10000),
            'sale_price' => $this->faker->numberBetween(100, 10000),
            'product_url' => $this->faker->text(),
            'image_url' => 'https://images.gnwcdn.com/2019/articles/2019-09-28-10-35/gpu-power-ladder-all-graphics-cards-tested-1569663337391.jpg/EG11/resize/1200x-1/gpu-power-ladder-all-graphics-cards-tested-1569663337391.jpg',
            'department_id' => $this->faker->numberBetween(0, 10),
        ];
    }
}
