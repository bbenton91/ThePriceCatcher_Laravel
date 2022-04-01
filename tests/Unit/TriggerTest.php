<?php

namespace Tests\Unit;

use App\Models\Products;
use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriggerTest extends TestCase
{

    /**
     * Test the trigger interaction between the recently_added and products table
     *
     * @return void
     */
    public function test_recently_added_trigger()
    {
         // Add a model to the products and recently_added tables with the same sku
        Products::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyAdded::factory()->create([
            'product_sku' => 1
        ]);

        // Test that we can get both of them with a join

        $model = RecentlyAdded::join('products', 'recently_added.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model != null);

        // Delete out of the recently changed table and then attempt to find the sku in the products table
        RecentlyAdded::find(1)->delete();
        $model = Products::find(1);

        // error_log(print_r($model));
        // Make sure the model is null. This should test the trigger set on the recently_added table
        $this->assertTrue($model == null);

        Products::truncate();
        RecentlyAdded::truncate();
    }

    /**
     * Tests the trigger interaction between the recently_changed and products table
     *
     * @return void
     */
    public function test_recently_changed_trigger()
    {
         // Add a model to the products and recently_changed tables with the same sku
        Products::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyChanged::factory()->create([
            'product_sku' => 1
        ]);

        // Test that we can get both of them with a join
        $model = RecentlyChanged::join('products', 'recently_changed.product_sku', '=', 'products.product_sku')->first();
        $this->assertTrue($model != null);

        // Delete out of the recently changed table and then attempt to find the sku in the products table
        RecentlyChanged::find(1)->delete();
        $model = Products::find(1);

        // Make sure the model is null. This should test the trigger set on the recently_changed table
        $this->assertTrue($model == null);

        Products::truncate();
        RecentlyAdded::truncate();
    }

    /**
     * Tests the trigger by adding a value to both recently_added and recently_changed and a value to the products table.
     * A value is first deleted from recently_changed and the products table is checked. It should still contain a value
     * Then a value is deleted from recently_added and the products table is checked. It should be empty now.
     *
     * @return void
     */
    public function test_both_recent_trigger()
    {
         // Add a model to the products and recently_changed tables with the same sku
        Products::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyChanged::factory()->create([
            'product_sku' => 1
        ]);
        RecentlyAdded::factory()->create([
            'product_sku' => 1
        ]);

        // Test that we can get both of them with a join
        $model = RecentlyChanged::join('products', 'recently_changed.product_sku', '=', 'products.product_sku')
            ->join('recently_added', 'recently_changed.product_sku', '=', 'recently_added.product_sku')->first();
        $this->assertTrue($model != null);

        // // Test that we can get both of them with a join
        // $model = RecentlyAdded::join('products', 'recently_added.product_sku', '=', 'products.product_sku')->first();
        // $this->assertTrue($model != null);

        // Delete out of the recently changed table and then attempt to find the sku in the products table. Make sure it's not null
        RecentlyChanged::find(1)->delete();
        $model = Products::find(1);
        $this->assertTrue($model != null);

        // Delete out of the recently changed table and then attempt to find the sku in the products table. Make sure IT IS NULL
        RecentlyAdded::find(1)->delete();
        $model = Products::find(1);
        $this->assertTrue($model == null);

        Products::truncate();
        RecentlyAdded::truncate();
    }
}
