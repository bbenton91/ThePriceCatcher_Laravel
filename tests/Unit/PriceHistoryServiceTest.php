<?php

namespace Tests\Unit;

use App\Models\PriceHistory;
use App\Services\PriceHistoryService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceHistoryServiceTest extends TestCase
{
    // use RefreshDatabase;


    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_no_updates()
    {
        $apiProduct = [
            'sku' => 1,
            'regularPrice' => 19.99,
            'salePrice' => 18.99,
        ];

        PriceHistory::factory()->create([
            'product_sku' => 1,
            'regular_price' => 1999,
            'sale_price' => 1899,
            'start_date' => now(),
        ]);


        $priceHistoryModel = PriceHistory::find(1)->first();

        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect([$apiProduct]), collect([$priceHistoryModel]));

        // error_log(print_r($result));

        DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                // error_log(print_r($item));
                PriceHistory::factory()->create(...$item);
                // $item->save();
            });
        });

        $priceHistoryModel->refresh();
        $this->assertTrue($priceHistoryModel['sale_price'] == 1899);

        DB::table('price_histories')->truncate();
    }

    public function test_api_product_price_lower()
    {

        $apiProduct = [
            'sku' => 1,
            'regularPrice' => 19.99,
            'salePrice' => 16.99,
        ];

        // Create a model and save to the database
        PriceHistory::factory()->create([
            'product_sku' => 1,
            'regular_price' => 1999,
            'sale_price' => 1899,
            'start_date' => now()->toDateTimeString(),
        ]);

        // Retrieve the model we just saved
        $priceHistoryModel = PriceHistory::all()->first();

        // Compare and get what needs to be resaved
        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect([$apiProduct]), collect([$priceHistoryModel]));

        $this->assertDatabaseCount('price_histories', 1);
        $this->assertCount(1, $result);
        $this->assertTrue(PriceHistory::find(2) == null);

        // error_log(print_r($result->first()));

        DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                $item->save();
                // PriceHistory::factory()->create(...$item);
            });
        });

        $this->assertDatabaseCount('price_histories', 2);

        $priceHistoryModel = PriceHistory::orderBy('id', 'DESC')->take(1)->first();

        $this->assertTrue($priceHistoryModel['sale_price'] == 1699);

        DB::table('price_histories')->truncate();
    }

    public function test_api_product_price_higher()
    {
        $apiProduct = [
            'sku' => 1,
            'regularPrice' => 19.99,
            'salePrice' => 18.99,
        ];

        // Create a model and save to the database
        PriceHistory::factory()->create([
            'product_sku' => 1,
            'regular_price' => 1999,
            'sale_price' => 1699,
            'start_date' => now()->toDateTimeString(),
        ]);

        // Retrieve the model we just saved
        $priceHistoryModel = PriceHistory::find(1)->first();

        // Compare and get what needs to be resaved
        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect([$apiProduct]), collect([$priceHistoryModel]));

        error_log(print_r($result->first()));

        DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                $item->save();
            });
        });

        $this->assertDatabaseCount('price_histories', 2);

        // Check that the most recent price history is correct
        $priceHistoryModel = PriceHistory::orderBy('id', 'DESC')->take(1)->first();
        $this->assertTrue($priceHistoryModel['sale_price'] == 1899);


        // Check that when we query for the first value we still get it
        $priceHistoryModel = PriceHistory::orderBy('id', 'ASC')->take(1)->first();
        $this->assertTrue($priceHistoryModel['sale_price'] == 1699);

        DB::table('price_histories')->truncate();
    }

    public function test_price_mismatch()
    {
        $apiProduct = [
            'sku' => 1,
            'regularPrice' => 29.99,
            'salePrice' => 28.99,
        ];

        // Create a model and save to the database
        PriceHistory::factory()->create([
            'product_sku' => 1,
            'regular_price' => 2799,
            'sale_price' => 2699,
            'start_date' => now()->toDateTimeString(),
        ]);

        // Retrieve the model we just saved
        $priceHistoryModel = PriceHistory::find(1)->first();

        // Compare and get what needs to be resaved
        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect([$apiProduct]), collect([$priceHistoryModel]));

        // DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                $item->save();
            });
        // });

        $this->assertDatabaseCount('price_histories', 2);

        $priceHistoryModel = PriceHistory::orderBy('id', 'DESC')->take(1)->first();

        $this->assertTrue($priceHistoryModel['sale_price'] == 2899);
        $this->assertTrue($priceHistoryModel['regular_price'] == 2999);

        DB::table('price_histories')->truncate();
    }

    public function test_history_product_doesnt_exist()
    {

        $apiProduct = [
            'sku' => 1,
            'regularPrice' => 29.99,
            'salePrice' => 28.99,
        ];

        $this->assertDatabaseCount('price_histories', 0);

        // Compare and get what needs to be resaved
        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect([$apiProduct]), collect([]));

        DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                $item->save();
            });
        });

        $this->assertDatabaseCount('price_histories', 1);

        $priceHistoryModel = PriceHistory::orderBy('id', 'DESC')->take(1)->first();

        $this->assertTrue($priceHistoryModel['regular_price'] == 2999);
        $this->assertTrue($priceHistoryModel['sale_price'] == 2899);

        DB::table('price_histories')->truncate();
    }
}
