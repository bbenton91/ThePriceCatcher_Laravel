<?php

namespace Tests\Unit;

use App\Models\Products;
use App\Services\ObjectHelper;
use Tests\TestCase;

class ObjectHelperTest extends TestCase
{
    /**
     * Tests that the ObjectHelper::rekey_array_by_sku functions correctly.
     * It should rekey the internal array in the collection by the product_sku
     *
     * @return void
     */
    public function test_basic()
    {
        Products::factory()->count(2)->create();

        $models = Products::where('product_sku', '>=', 0)->get();

        $models = ObjectHelper::rekey_array_by_sku($models);

        $first = $models->first(); // Get the first model in the collection

        $arr = $models->toArray(); // Transform to an array

        $model = $arr[$first['product_sku']]; // This will get the model by sku number from the array

        $this->assertTrue($model != null); // Check it's not null
    }

    public function test_custom_collection(){
        $object = [
            'product_sku' => 1,
            'description' => 'custom test'
        ];

        $collection = collect([$object]);

        $result = ObjectHelper::rekey_array_by_sku($collection);

        $this->assertArrayHasKey(1, $result->toArray());
    }

    public function test_model(){
        $model = Products::factory()->make(['product_sku'=> 1]);

        $collection = collect([$model]);

        $result = ObjectHelper::rekey_array_by_sku($collection);

        $this->assertArrayHasKey(1, $result->toArray());

    }
}
