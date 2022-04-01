<?php

namespace Tests\Unit;

use App\Models\Products;
use App\Models\RecentlyAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentlyAddedTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        RecentlyAdded::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyAdded::find(1);
        $this->assertTrue($model != null);

        RecentlyAdded::truncate();
    }

    public function test_products_match(){
        Products::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyAdded::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyAdded::join('products', 'recently_added.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model != null);

        RecentlyAdded::truncate();
        Products::truncate();
    }

    /**
     * Tests that if we add two different products
     *
     * @return void
     */
    public function test_products_dont_match(){
        Products::factory()->create([
            'product_sku' => 2
        ]);
        RecentlyAdded::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyAdded::join('products', 'recently_added.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model == null);

        RecentlyAdded::truncate();
        Products::truncate();
    }

    public function test_adding(){
        RecentlyAdded::factory()->count(10)->create();
        $models = RecentlyAdded::all()->count();
        $this->assertTrue($models == 10);

        RecentlyAdded::truncate();
        Products::truncate();
    }

}
