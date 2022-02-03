<?php

namespace Tests\Unit;

use App\Models\Products;
use App\Models\RecentlyChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentlyChangedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        RecentlyChanged::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyChanged::find(1);
        $this->assertTrue($model != null);

        $this->refreshDatabase();
    }

    public function test_products_match(){
        Products::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyChanged::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyChanged::join('products', 'recently_changed.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model != null);

        $this->refreshDatabase();
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
        RecentlyChanged::factory()->create([
            'product_sku' => 1
        ]);

        $model = RecentlyChanged::join('products', 'recently_changed.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model == null);

        $this->refreshDatabase();
    }

    public function test_adding(){
        RecentlyChanged::factory()->count(10)->create();
        $models = RecentlyChanged::all()->count();
        $this->assertTrue($models == 10);

        $this->refreshDatabase();
    }

}
