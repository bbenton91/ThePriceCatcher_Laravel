<?php

namespace Tests\Unit;

use App\Facades\EmailServiceFacade;
use App\Models\Emails;
use App\Models\ProductPrices;
use App\Models\Products;
use App\Models\SkuEmail;
use App\Scripts\GatherRecentlyChangedProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GatherRecentlyChangedProductsTest extends TestCase
{
     /**
     * Tests if the price function getProductsDroppedPrice on GatherRecentlyChangedProducts will detect a price drop accurately
     *
     * @return void
     */
    public function test_recognize_price_drop()
    {
        ProductPrices::truncate();
        Products::truncate();

        // Our test api product
        $testApiProducts = [(object)['sku' => 1, 'salePrice' => 5, 'regularPrice' => 10]];

        // Create our product_prices model
        DB::table('product_prices')->insert([
            'product_sku'=>1,
            'lowest_price'=>3,
            'highest_price'=>10,
            'regular_price'=>10,
            'sale_price'=>7
        ]);

        // Create our products model
        DB::table('products')->insert([
            'product_sku'=>1,
            'product_name'=>'test',
            'description'=>'test desc',
            'regular_price'=>10,
            'sale_price'=>1,
            'product_url'=>'url here',
            'image_url'=>'image here',
            'department_id'=>5,
        ]);

        // Some assertions
        $this->assertDatabaseCount('product_prices', 1);
        $this->assertDatabaseCount('products', 1);

        // Now we want to invoke the pricate method
        $methodName = "getProductsDroppedPrice";
        $gatherer = new GatherRecentlyChangedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$testApiProducts]);

        // Make sure we have the data
        $this->assertTrue(count($data) > 0);
        $this->assertTrue($data[0]->sale_price == 5);
    }

    /**
     * Tests if the price function getProductsDroppedPrice on GatherRecentlyChangedProducts will detect a price drop accurately
     *
     * @return void
     */
    public function test_gather_emails()
    {
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Our test api product
        $testApiProducts = [(object)['sku' => 1, 'salePrice' => 5, 'regularPrice' => 10]];

       // Using data from previous test function

        // Some assertions
        $this->assertDatabaseCount('product_prices', 1);
        $this->assertDatabaseCount('products', 1);

        Emails::factory()->create(['email'=>'test@gmail.com']);
        $email = Emails::first();

        SkuEmail::factory()->create(['product_sku'=>1,'email_id'=>$email->id]);
        $skuEmail = SkuEmail::first();

        $this->assertDatabaseCount('emails', 1);
        $this->assertDatabaseCount('sku_emails', 1);

        // Now we want to invoke the pricate method
        $methodName = "getProductsDroppedPrice";
        $gatherer = new GatherRecentlyChangedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$testApiProducts]);

        // Make sure we have the data
        $this->assertTrue(count($data) > 0);
        $this->assertTrue($data[0]->sale_price == 5);

        $methodName = "gatherEmails";
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$data]);

        // error_log(print_r($data));

        // Make sure we have the data
        $this->assertTrue(count($data) > 0);
        $this->assertTrue(isset($data['test@gmail.com']));
        $this->assertTrue(count($data['test@gmail.com']->products) > 0);
        $this->assertTrue($data['test@gmail.com']->products[0]->product_sku == 1);
        $this->assertTrue($data['test@gmail.com']->products[0]->sale_price == 5);
    }

    public function test_recognize_larger_price_drop()
    {
        ProductPrices::truncate();
        Products::truncate();

        // Our test api product
        $testApiProducts = [
            (object)['sku' => 1, 'salePrice' => 5, 'regularPrice' => 10],
            (object)['sku' => 2, 'salePrice' => 8, 'regularPrice' => 10],
            (object)['sku' => 3, 'salePrice' => 2, 'regularPrice' => 10],
            (object)['sku' => 4, 'salePrice' => 7, 'regularPrice' => 10],
        ];

        // Create our product_prices model
        DB::table('product_prices')->insert([
            'product_sku'=>1,
            'lowest_price'=>3,
            'highest_price'=>10,
            'regular_price'=>10,
            'sale_price'=>7
        ]);
        DB::table('product_prices')->insert([
            'product_sku'=>2,
            'lowest_price'=>3,
            'highest_price'=>10,
            'regular_price'=>10,
            'sale_price'=>7
        ]);
        DB::table('product_prices')->insert([
            'product_sku'=>3,
            'lowest_price'=>3,
            'highest_price'=>10,
            'regular_price'=>10,
            'sale_price'=>7
        ]);
        DB::table('product_prices')->insert([
            'product_sku'=>4,
            'lowest_price'=>3,
            'highest_price'=>10,
            'regular_price'=>10,
            'sale_price'=>7
        ]);

        // Create our products model
        DB::table('products')->insert([
            'product_sku'=>1,
            'product_name'=>'test',
            'description'=>'test desc',
            'regular_price'=>10,
            'sale_price'=>7,
            'product_url'=>'url here',
            'image_url'=>'image here',
            'department_id'=>5,
        ]);
        // Create our products model
        DB::table('products')->insert([
            'product_sku'=>2,
            'product_name'=>'test2',
            'description'=>'test desc',
            'regular_price'=>10,
            'sale_price'=>7,
            'product_url'=>'url here',
            'image_url'=>'image here',
            'department_id'=>5,
        ]);
        // Create our products model
        DB::table('products')->insert([
            'product_sku'=>3,
            'product_name'=>'test3',
            'description'=>'test desc',
            'regular_price'=>10,
            'sale_price'=>7,
            'product_url'=>'url here',
            'image_url'=>'image here',
            'department_id'=>5,
        ]);
        // Create our products model
        DB::table('products')->insert([
            'product_sku'=>4,
            'product_name'=>'test4',
            'description'=>'test desc',
            'regular_price'=>10,
            'sale_price'=>7,
            'product_url'=>'url here',
            'image_url'=>'image here',
            'department_id'=>5,
        ]);

        // Some assertions
        $this->assertDatabaseCount('product_prices', 4);
        $this->assertDatabaseCount('products', 4);

        // Now we want to invoke the pricate method
        $methodName = "getProductsDroppedPrice";
        $gatherer = new GatherRecentlyChangedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$testApiProducts]);

        // Make sure we have the data
        $this->assertCount(2, $data);
        $this->assertTrue($data[0]->sale_price == 5);

        $test = new EmailServiceFacade;
    }

    /**
     * Tests if the price function getProductsDroppedPrice on GatherRecentlyChangedProducts will detect a price drop accurately
     *
     * @return void
     */
    public function test_larger_gather_emails()
    {
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Our test api products
        $testApiProducts = [
            (object)['sku' => 1, 'salePrice' => 5, 'regularPrice' => 10],
            (object)['sku' => 2, 'salePrice' => 8, 'regularPrice' => 10],
            (object)['sku' => 3, 'salePrice' => 2, 'regularPrice' => 10],
            (object)['sku' => 4, 'salePrice' => 7, 'regularPrice' => 10],
        ];

       // Using data from previous test function

        // Some assertions
        $this->assertDatabaseCount('product_prices', 4);
        $this->assertDatabaseCount('products', 4);

        Emails::factory()->create(['email'=>'test@gmail.com']);
        Emails::factory()->create(['email'=>'test2@gmail.com']);
        Emails::factory()->create(['email'=>'test3@gmail.com']);

        $email1 = Emails::where('email', '=', 'test@gmail.com')->first();
        $email2 = Emails::where('email', '=', 'test2@gmail.com')->first();
        $email3 = Emails::where('email', '=', 'test3@gmail.com')->first();

        SkuEmail::factory()->create(['product_sku'=>1,'email_id'=>$email1->id]);
        SkuEmail::factory()->create(['product_sku'=>1,'email_id'=>$email2->id]);
        SkuEmail::factory()->create(['product_sku'=>1,'email_id'=>$email3->id]);

        SkuEmail::factory()->create(['product_sku'=>2,'email_id'=>$email1->id]);
        SkuEmail::factory()->create(['product_sku'=>2,'email_id'=>$email2->id]);
        SkuEmail::factory()->create(['product_sku'=>2,'email_id'=>$email3->id]);

        SkuEmail::factory()->create(['product_sku'=>3,'email_id'=>$email1->id]);
        SkuEmail::factory()->create(['product_sku'=>3,'email_id'=>$email2->id]);
        SkuEmail::factory()->create(['product_sku'=>3,'email_id'=>$email3->id]);

        SkuEmail::factory()->create(['product_sku'=>4,'email_id'=>$email1->id]);

        $this->assertDatabaseCount('emails', 3);
        $this->assertDatabaseCount('sku_emails', 10);

        // Now we want to invoke the pricate method
        $methodName = "getProductsDroppedPrice";
        $gatherer = new GatherRecentlyChangedProducts();

        $reflection = new \ReflectionClass(get_class($gatherer));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$testApiProducts]);

        // Make sure we have the data
        $this->assertCount(2, $data);
        $this->assertTrue($data[0]->sale_price == 5);

        $methodName = "gatherEmails";
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $data = $method->invokeArgs($gatherer, [$data]);

        // error_log(print_r($data));

        // Make sure all 3 emails come back.
        $this->assertCount(3, $data);

        $this->assertTrue(isset($data['test@gmail.com']));
        $this->assertTrue(count($data['test@gmail.com']->products) > 0);
        $this->assertTrue($data['test@gmail.com']->products[0]->product_sku == 1);
        $this->assertTrue($data['test@gmail.com']->products[0]->sale_price == 5);

        // error_log(print_r($data['test@gmail.com']->products));

        $this->assertCount(2, $data['test@gmail.com']->products);
        $this->assertCount(2, $data['test2@gmail.com']->products);
    }
}
