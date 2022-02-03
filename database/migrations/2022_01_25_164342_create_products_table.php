<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integer('product_sku');
            $table->string('product_name');
            $table->string('description');
            $table->integer('regular_price');
            $table->integer('sale_price');
            $table->string('product_url');
            $table->string('image_url');
            $table->integer('department_id');
            $table->timestamps();

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
        Schema::dropIfExists('products');
    }
}
