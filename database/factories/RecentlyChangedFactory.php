<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecentlyChangedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_sku' => $this->faker->numberBetween(0),
        ];
    }
}
