<?php

namespace Tests\Unit;

use App\Models\PriceHistory;
use App\Models\ProductPrices;
use Tests\TestCase;

class ProductPricesRangesTriggerTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
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

        // $m = PriceHistory::first();
        $model = ProductPrices::first();

        // error_log(print_r($m));

        $this->assertTrue($model->lowest_price == 50);
        $this->assertTrue($model->highest_price == 100);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_double_entry_match(){
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
            'regular_price' => 100,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 2);
        $this->assertDatabaseCount('product_prices', 1);

        $model = ProductPrices::first();

        $this->assertTrue($model->lowest_price == 50 && $model->highest_price == 100);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_double_entry(){
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
            'regular_price' => 100,
            'sale_price' => 25,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 2);
        $this->assertDatabaseCount('product_prices', 1);

        $model = ProductPrices::first();

        $this->assertTrue($model->lowest_price == 25);
        $this->assertTrue($model->highest_price == 100);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_double_entry_mismatch_highest(){
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

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 150,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->lowest_price == 50);
         $this->assertTrue($model->highest_price == 150);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_double_entry_mismatch_both(){
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

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 200,
            'sale_price' => 25,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->lowest_price == 25);
         $this->assertTrue($model->highest_price == 200);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_keeps_lowest(){
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

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 100,
            'sale_price' => 75,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->lowest_price == 50);
         $this->assertTrue($model->highest_price == 100);

        PriceHistory::truncate();
        ProductPrices::truncate();
    }

    public function test_keeps_highest(){
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

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 75,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->lowest_price == 50);
         $this->assertTrue($model->highest_price == 100);

        PriceHistory::truncate();
    }

    public function test_keeps_lowest_and_highest(){
        PriceHistory::truncate();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 100,
            'sale_price' => 50,
            'start_date' => now()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('price_histories', 1);
        $this->assertDatabaseCount('product_prices', 1);

        $model = ProductPrices::first();

        PriceHistory::create([
            'product_sku' => 1,
            'regular_price' => 75,
            'sale_price' => 75,
            'start_date' => now()->toDateTimeString()
        ]);

        $model->refresh();

        $this->assertTrue($model->lowest_price == 50);
         $this->assertTrue($model->highest_price == 100);

        'INSERT INTO product_prices(product_sku, highest_price, lowest_price, created_at, updated_at)
        (SELECT product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM `price_histories`
            GROUP BY product_sku);


        ';

        PriceHistory::truncate();
        ProductPrices::truncate();
    }
}
