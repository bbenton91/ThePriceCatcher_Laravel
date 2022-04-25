<?php

namespace Tests\Unit;

use App\Models\Emails;
use App\Models\SkuEmail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;


class EmailModelTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_basic_email_insertion()
    {
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);

        // Retrieve
        $email = Emails::first();

        // Test
        $this->assertDatabaseCount("emails", 1);
        $this->assertTrue($email->email == "test@gmail.com");
    }

    public function test_email_sku_insertion(){
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);

        // Retrieve
        $email = Emails::first();

        // Add the SkuEmail to the database
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $email->id]);

        // Retrieve
        $skuEmail = SkuEmail::first();

        // Test
        $this->assertDatabaseCount("emails", 1);
        $this->assertDatabaseCount("sku_emails", 1);
        $this->assertTrue($email->email == "test@gmail.com");
        $this->assertTrue($skuEmail->product_sku == 1122334455);
        $this->assertTrue($skuEmail->email_id == $email->id);
    }

    /**
     * Test a single email to multiple sku_email models.
     *
     * @return void
     */
    public function test_email_multiple_sku_insertion(){
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);

        // Retrieve
        $email = Emails::first();

        // Add the SkuEmail to the database
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $email->id]);
        SkuEmail::factory()->create(['product_sku' => 223344, 'email_id' => $email->id]);

        // Retrieve
        $skuEmails = SkuEmail::all();

        // Test
        $this->assertDatabaseCount("emails", 1);
        $this->assertDatabaseCount("sku_emails", 2);
        $this->assertTrue($email->email == "test@gmail.com");
        $this->assertTrue($skuEmails[0]->product_sku == 1122334455);
        $this->assertTrue($skuEmails[1]->product_sku == 223344);
        $this->assertTrue($skuEmails[0]->email_id == $email->id);
        $this->assertTrue($skuEmails[1]->email_id == $email->id);
    }

    /**
     * Tests multiple emails to one sku number.
     * Ensures that we get unique SkuEmail models that all contain the same product_sku
     *
     * @return void
     */
    public function test_multiple_email_sku_insertion(){
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);
        Emails::factory()->create(['email' => 'test2@gmail.com']);
        Emails::factory()->create(['email' => 'test3@gmail.com']);

        // Retrieve
        $emails = Emails::all();

        // Add the SkuEmail to the database
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[0]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[1]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[2]->id]);

        // Retrieve
        $skuEmails = SkuEmail::all();

        // Test
        $this->assertDatabaseCount("emails", 3);
        $this->assertDatabaseCount("sku_emails", 3);
        $this->assertTrue($skuEmails[0]->product_sku == $skuEmails[1]->product_sku);
        $this->assertTrue($skuEmails[1]->product_sku == $skuEmails[2]->product_sku);
        $this->assertTrue($skuEmails[2]->product_sku == $skuEmails[0]->product_sku);
        $this->assertTrue($skuEmails[0]->email_id == $emails[0]->id);
        $this->assertTrue($skuEmails[1]->email_id == $emails[1]->id);
        $this->assertTrue($skuEmails[2]->email_id == $emails[2]->id);
    }

    /**
     * Tests multiple emails to one sku number and retrieval methods for both
     *
     * @return void
     */
    public function test_multiple_email_sku_retrievals(){
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);
        Emails::factory()->create(['email' => 'test2@gmail.com']);
        Emails::factory()->create(['email' => 'test3@gmail.com']);

        // Retrieve
        $emails = Emails::all();

        // Add the SkuEmail to the database
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[0]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[1]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[2]->id]);

        // Retrieve
        $skuEmails = SkuEmail::where('product_sku', '=', 1122334455)->get();

        $relatedEmail = $skuEmails[0]->email;

        // error_log(print_r($relatedEmail));

        // Test
        $this->assertDatabaseCount("emails", 3);
        $this->assertDatabaseCount("sku_emails", 3);
        $this->assertCount(3, $skuEmails);
        $this->assertTrue($skuEmails[0]->product_sku == $skuEmails[1]->product_sku);
        $this->assertTrue($skuEmails[1]->product_sku == $skuEmails[2]->product_sku);
        $this->assertTrue($skuEmails[2]->product_sku == $skuEmails[0]->product_sku);
        $this->assertTrue($skuEmails[0]->email_id == $emails[0]->id);
        $this->assertTrue($skuEmails[1]->email_id == $emails[1]->id);
        $this->assertTrue($skuEmails[2]->email_id == $emails[2]->id);
        $this->assertTrue($relatedEmail->email == 'test@gmail.com');
    }

    /**
     * Tests multiple emails to one sku number and relationship methods for both
     *
     * @return void
     */
    public function test_multiple_email_sku_relationships(){
        // Clear and reset tables
        Schema::disableForeignKeyConstraints();
        Emails::truncate();
        SkuEmail::truncate();
        Schema::enableForeignKeyConstraints();

        // Create
        Emails::factory()->create(['email' => 'test@gmail.com']);
        Emails::factory()->create(['email' => 'test2@gmail.com']);
        Emails::factory()->create(['email' => 'test3@gmail.com']);

        // Retrieve
        $emails = Emails::all();

        // Add the SkuEmail to the database
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[0]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[1]->id]);
        SkuEmail::factory()->create(['product_sku' => 1122334455, 'email_id' => $emails[2]->id]);

        // Add 3 more unique skus for the first email
        SkuEmail::factory()->create(['product_sku' => 22, 'email_id' => $emails[0]->id]);
        SkuEmail::factory()->create(['product_sku' => 33, 'email_id' => $emails[0]->id]);
        SkuEmail::factory()->create(['product_sku' => 44, 'email_id' => $emails[0]->id]);


        // Retrieve
        $skuEmails = SkuEmail::where('product_sku', '=', 1122334455)->get();

        // Find the email related to this sku number
        $relatedEmail = $skuEmails[0]->email;

        // Find sku numbers related to this email
        $relatedSkus = $emails[0]->skus;

        // error_log(print_r($relatedEmail));

        // Test
        $this->assertDatabaseCount("emails", 3);
        $this->assertDatabaseCount("sku_emails", 6);
        $this->assertCount(3, $skuEmails);
        $this->assertCount(4, $relatedSkus);
        $this->assertTrue($relatedEmail->email == 'test@gmail.com');

    }
}
