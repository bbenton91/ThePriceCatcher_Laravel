<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $sql = "
            CREATE PROCEDURE `clear_from_products` (IN old_sku int)
            BEGIN
                DECLARE
                    recently_added_count INT ; DECLARE recently_changed_count INT ;
                SELECT
                    COUNT(*)
                INTO recently_added_count
            FROM
                recently_added
            WHERE
                product_sku = old_sku ;
            SELECT
                COUNT(*)
            INTO recently_changed_count
            FROM
                recently_changed
            WHERE
                product_sku = old_sku ;
                IF recently_added_count <= 0 && recently_changed_count <= 0 THEN
                    DELETE
                    FROM
                        products
                    WHERE
                        product_sku = old_sku ;
                END IF ;

            END;";

            $trigger = "
                CREATE TRIGGER clear_from_products_on_recently_added_trigger
                AFTER DELETE ON recently_added
                FOR EACH ROW
                BEGIN
                    CALL clear_from_products(old.product_sku);
                END;

                CREATE TRIGGER clear_from_products_on_recently_changed_trigger
                AFTER DELETE ON recently_changed
                FOR EACH ROW
                BEGIN
                    CALL clear_from_products(old.product_sku);
                END;
            ";

            DB::unprepared('DROP PROCEDURE IF EXISTS `clear_from_products`;');
            DB::unprepared('DROP TRIGGER IF EXISTS `clear_from_products_on_recently_added_trigger`;');
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
        DB::unprepared('DROP TRIGGER IF EXISTS clear_from_products_on_recently_added_trigger');
        DB::unprepared('DROP TRIGGER IF EXISTS clear_from_products_on_recently_changed_trigger');
        DB::unprepared('DROP PROCEDURE IF EXISTS clear_from_products');
    }
}
