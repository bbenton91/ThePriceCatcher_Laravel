<?php

use App\Models\ProductPriceRanges;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PopulateProductPricesAndRanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // This will grab all min and max prices of all products and insert it into the product_prices table
        $sql = 'INSERT IGNORE INTO product_prices(product_sku, highest_price, lowest_price, created_at, updated_at)
                (SELECT product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM `price_histories`
                    GROUP BY product_sku);
        ';

        // This will select the latest row of each sku and insert the regular and sale price
        $sql2 = 'INSERT INTO product_prices(product_sku, regular_price, sale_price, created_at, updated_at)
                    SELECT ph1.product_sku, ph1.regular_price, ph1.sale_price, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                    FROM price_histories ph1 LEFT JOIN price_histories ph2
                    ON (ph1.product_sku = ph2.product_sku AND ph1.id < ph2.id)
                    WHERE ph2.id IS NULL
                ON DUPLICATE KEY UPDATE
                regular_price=VALUES(regular_price), sale_price=VALUES(sale_price), updated_at=VALUES(updated_at)
        ';


        DB::unprepared($sql);
        DB::unprepared($sql2);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
