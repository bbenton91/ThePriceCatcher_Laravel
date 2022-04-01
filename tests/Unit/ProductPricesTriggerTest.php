<?php

namespace Tests\Unit;

use App\Models\PriceHistory;
use App\Models\ProductPrices;
use Tests\TestCase;

class ProductPricesTriggerTest extends TestCase
{
    public function test_simple_entry()
    {
        PriceHistory::truncate();
        ProductPrices::truncate();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 100,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 1);
        $this->assertDatabaseCount('product_prices', 1);

        $model = ProductPrices::first();

        $this->assertTrue($model->sale_price == 50 && $model->regular_price == 100);

        PriceHistory::truncate();
        ProductPrices::truncate();

    }


    public function test_double_change(){
        PriceHistory::truncate();
        ProductPrices::truncate();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 100,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 125,
            'sale_price' => 25,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 2);

        $model = ProductPrices::first();

        $this->assertTrue($model->sale_price == 25);
        $this->assertTrue($model->regular_price == 125);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_triple_entry_change(){
        PriceHistory::truncate();
        ProductPrices::truncate();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 100,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 1);

        $model = ProductPrices::first();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 150,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 10,
            'sale_price' => 10,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->sale_price == 10);
         $this->assertTrue($model->regular_price == 10);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }
}
