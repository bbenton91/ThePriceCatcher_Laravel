<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductPricesTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "

            CREATE PROCEDURE update_product_prices (new_product_sku INT, new_regular_price INT, new_sale_price INT)
            BEGIN
                DECLARE old_sku INT;

                SET old_sku := (SELECT product_sku from product_prices where product_sku = new_product_sku);

                IF (old_sku IS NULL) THEN

                    INSERT INTO product_prices(product_sku, regular_price, sale_price, created_at, updated_at)
                    VALUES (new_product_sku, new_regular_price, new_sale_price, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

                ELSE

                    UPDATE product_prices
                    SET product_prices.sale_price = new_sale_price, product_prices.regular_price = new_regular_price, updated_at = CURRENT_TIMESTAMP
                    where product_prices.product_sku = new_product_sku;

                END IF;

            END;
            ";

            $trigger = "
                CREATE TRIGGER update_product_prices_trigger
                AFTER INSERT ON price_histories
                FOR EACH ROW
                BEGIN
                    CALL update_product_prices(NEW.product_sku, NEW.regular_price, NEW.sale_price);
                END;
            ";

            DB::unprepared('DROP PROCEDURE IF EXISTS `update_product_prices`;');
            DB::unprepared('DROP TRIGGER IF EXISTS update_product_prices_trigger');
            DB::unprepared($sql);
            DB::unprepared($trigger);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `update_product_prices`;');
        DB::unprepared('DROP TRIGGER IF EXISTS update_product_prices_trigger');
    }
}
