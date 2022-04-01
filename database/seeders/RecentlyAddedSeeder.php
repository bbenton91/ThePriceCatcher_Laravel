<?php

namespace Database\Seeders;

use App\Models\Products;
use App\Models\RecentlyAdded;
use Database\Factories\RecentlyAddedFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecentlyAddedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Products::factory()->count(10)->make();

        $products->take(10);

        foreach ($products->take(10) as $product) {
            // error_log($product->product_sku);
            RecentlyAdded::factory()->create(['product_sku' => $product->product_sku]);
        }

        DB::transaction (function () use ($products) {
            $products->each(function ($product) {
                $product->save();
            });
        });

        // $recentlyAdded = [];

        // for ($i=0; $i < 20; $i++) {
        //     $recentlyAdded[] = $products[i];
        // }

        // RecentlyAdded::factory()
        //     ->has(Products::factory()->count(1))
        //     ->create();
    }
}
