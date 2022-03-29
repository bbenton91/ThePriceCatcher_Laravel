<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProductPricesTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "

            CREATE PROCEDURE update_product_price_ranges (new_product_sku INT, new_regular_price INT, new_sale_price INT)
            BEGIN
                DECLARE lowest, highest INT DEFAULT 0;
                DECLARE old_sku INT;

                SET old_sku := (SELECT product_sku from product_price_ranges where product_sku = new_product_sku);

                IF (old_sku IS NULL) THEN

                    INSERT INTO product_price_ranges(product_sku, lowest_price, highest_price, created_at, updated_at)
                    VALUES (new_product_sku, new_sale_price, new_regular_price, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

                ELSE

                    SELECT
                        lowest_price
                    INTO lowest
                    FROM
                        product_price_ranges
                    WHERE
                        product_price_ranges.product_sku = new_product_sku ;


                    SELECT
                        highest_price
                    INTO highest
                    FROM
                        product_price_ranges
                    WHERE
                        product_price_ranges.product_sku = new_product_sku ;

                    IF new_sale_price < lowest && new_regular_price > highest THEN

                        UPDATE product_price_ranges
                        SET product_price_ranges.lowest_price = new_sale_price, product_price_ranges.highest_price = new_regular_price, updated_at = CURRENT_TIMESTAMP
                        where product_price_ranges.product_sku = new_product_sku;

                    ELSEIF new_sale_price < lowest THEN

                        UPDATE product_price_ranges
                        SET product_price_ranges.lowest_price = new_sale_price, updated_at = CURRENT_TIMESTAMP
                        where product_price_ranges.product_sku = new_product_sku;

                    ELSEIF new_regular_price > highest THEN

                        UPDATE product_price_ranges
                        SET product_price_ranges.highest_price = new_regular_price, updated_at = CURRENT_TIMESTAMP
                        where product_price_ranges.product_sku = new_product_sku;

                    END IF ;

                END IF;

            END;
            ";

            $trigger = "
                CREATE TRIGGER update_product_price_ranges_trigger
                AFTER INSERT ON price_histories
                FOR EACH ROW
                BEGIN
                    CALL update_product_price_ranges(NEW.product_sku, NEW.regular_price, NEW.sale_price);
                END;
            ";

            DB::unprepared('DROP PROCEDURE IF EXISTS `update_product_price_ranges`;');
            DB::unprepared('DROP TRIGGER IF EXISTS update_product_price_ranges_trigger');
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
        DB::unprepared('DROP TRIGGER IF EXISTS update_product_price_ranges_trigger');
        DB::unprepared('DROP PROCEDURE IF EXISTS update_product_price_ranges');
    }
}
