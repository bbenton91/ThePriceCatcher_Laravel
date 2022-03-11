<?php

namespace Tests\Unit;

use App\Scripts\GatherRecentlyAddedProducts;
use PHPUnit\Framework\TestCase;

class GatherRecentlyAddedProductsTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_gather_products()
    {
        $methodName = "gatherProducts";
        $gatherer = new GatherRecentlyAddedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [0.5, 5, "1"]);

        $this->assertTrue(count($data->products) > 0);
        $this->assertTrue($data->error == "");
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_gather_and_build()
    {
        $methodName = "gatherProducts";

        $gatherer = new GatherRecentlyAddedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));

        // Get the main method for getting our data
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        // Call the method here
        $data = $method->invokeArgs($gatherer, [0.5, 5, "1"]);

        // Get the buildProduct private method
        $buildProduct = $reflection->getMethod("buildProduct");
        $buildProduct->setAccessible(true);

        // Get the buildRecentlyAdded private method
        $buildRecentlyAdded = $reflection->getMethod("buildRecentlyAdded");
        $buildRecentlyAdded->setAccessible(true);

        // Map the array twice to 'product' and 'recentlyAdded' models using the two methods
        $productModels = array_map(fn($d)=>$buildProduct->invokeArgs($gatherer, [$d]), $data->products);
        $recentModels = array_map(fn($d)=>$buildRecentlyAdded->invokeArgs($gatherer, [$d]), $data->products);

        // Assert some stuff
        $this->assertTrue(count($productModels) > 0);
        $this->assertTrue(count($recentModels) > 0);

        $this->assertIsArray($productModels);
        $this->assertIsArray($recentModels);
    }
}
