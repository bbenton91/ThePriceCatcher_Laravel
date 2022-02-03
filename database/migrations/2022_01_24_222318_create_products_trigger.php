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
        /**
         * This trigger is so that we can have a table stup as follows
         *
         * products table that has info regarding products
         * recently_changed -> holds skus that have been recently changed
         * recently_added -> holds skus that have been recently added
         *
         * whenever a product is added to recently_changed or recently_added, it will be added to the products table
         * Whenever a product is removed from recently_changed or recently_added, it checks both tables to see if there are any references to the product left.
         * If not, the sku is removed from the products table.
         *
         * This will keep the products table clean and up to date with the other two tables.
         *
         */
        DB::unprepared('
            CREATE OR REPLACE FUNCTION clear_from_products()
            RETURNS trigger AS $$
            DECLARE
                has_row boolean;
                ra_row recently_added%rowtype;
                rc_row recently_changed%rowtype;
            BEGIN
                IF (SELECT count(*) FROM recently_added WHERE product_sku=old.product_sku) <= 0 AND (SELECT count(*) FROM recently_changed WHERE product_sku=old.product_sku) <= 0 THEN
                    DELETE FROM products WHERE product_sku=old.product_sku;
                END IF;
                RETURN NULL;
            END
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER clear_from_products_on_recently_added_trigger
            AFTER DELETE ON recently_added
            FOR EACH ROW
            EXECUTE PROCEDURE clear_from_products();

            CREATE TRIGGER clear_from_products_on_recently_changed_trigger
            AFTER DELETE ON recently_changed
            FOR EACH ROW
            EXECUTE PROCEDURE clear_from_products();
        ');

    // DB::unprepared('
    // DELIMITER $$
    // CREATE PROCEDURE clear_from_products(old_product_sku INT)
    // BEGIN
    //     DECLARE
    //         recently_added_count INT ; DECLARE recently_changed_count INT ;
    //     SELECT
    //         COUNT(*)
    //     INTO recently_added_count
    // FROM
    //     recently_added
    // WHERE
    //     product_sku = old_product_sku ;
    // SELECT
    //     COUNT(*)
    // INTO recently_changed_count
    // FROM
    //     recently_changed
    // WHERE
    //     product_sku = old_product_sku ;
    //     IF recently_added_count <= 0 && recently_changed_count <= 0 THEN
    //         DELETE
    //         FROM
    //             products
    //         WHERE
    //             product_sku = old_product_sku ;
    //     END IF ;
    // END$$
    // DELIMITER ;
    // ');

    // DB::unprepared('
    //     DELIMITER  $$

    //     CREATE TRIGGER clear_from_products_on_recently_added_trigger(OLD.product_sku)
    //     AFTER DELETE
    //     ON recently_added FOR EACH ROW
    //     BEGIN
    //         CALL clear_from_products();
    //     END$$

    //     DELIMITER ;
    // ');

        // $$ LANGUAGE plpgsql;

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS clear_from_products_on_recently_added_trigger ON recently_added');
        DB::unprepared('DROP TRIGGER IF EXISTS clear_from_products_on_recently_changed_trigger ON recently_changed');
    }
}
