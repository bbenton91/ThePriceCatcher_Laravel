<?php

namespace Database\Seeders;

use App\Models\MostViewed;
use App\Models\Products;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MostViewedSeeder extends Seeder
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
            MostViewed::factory()->create(['product_sku' => $product->product_sku]);
        }

        DB::transaction (function () use ($products) {
            $products->each(function ($product) {
                $product->save();
            });
        });
    }
}
