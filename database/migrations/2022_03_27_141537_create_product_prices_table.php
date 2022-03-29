<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->integer('product_sku');

            $table->integer('lowest_price')->default(0);
            $table->integer('highest_price')->default(0);
            $table->integer('regular_price')->default(0);
            $table->integer('sale_price')->default(0);
            $table->timestamps();

            $table->unique('product_sku');
            $table->primary('product_sku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_price');
    }
}
