<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToPriceHistoriesSku extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if(Schema::hasColumn('price_histories', 'product_sku')){
            // Schema::table('price_histories', function (Blueprint $table) {
            //     $table->dropIndex('price_histories_product_sku_index');
            // });
        // }

        Schema::table('price_histories', function (Blueprint $table) {
            $table->index('product_sku', 'price_histories_product_sku_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_histories', function (Blueprint $table) {
            $table->dropIndex('price_histories_product_sku_index');
        });
    }
}
