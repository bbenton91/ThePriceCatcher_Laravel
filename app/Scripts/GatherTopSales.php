<?php

// This script will call BestBuy's API and traverses through all
// recently added products. Specifically, these products are new to BestBuy's
// store within the last 30 days.

namespace App\Scripts;

require __DIR__."/../../vendor/autoload.php";

use App\Models\TopSale;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;

/**
     * This script will query the price history table for the top 250 sales of the day. We pull this data from
     * the RecentlyChanged and RecentlyAdded tables.
     *
     * 1. Query the recently_added and recently_changed tables for all models
     * 2. Join the with price history table to get the most recent regular and sale price
     * 2. Sort by the difference between current regular and sale price
     * 3. Apply to the top_sales table
     */

class GatherTopSales{

    public function gather(){
        echo "starting top sale gather \n";

        $this->clearTopSales();

        $sql = 'SELECT p.product_sku, p.regular_price, p.sale_price, (p.regular_price/p.sale_price) as ratio
                FROM product_prices as pp
                JOIN products as p
                on p.product_sku = pp.product_sku
                ORDER BY ratio DESC;
        ';

        // $sql = 'SELECT rc.product_sku, pp.regular_price, pp.sale_price, (pp.regular_price/pp.sale_price) as n FROM recently_changed as rc
        //     CROSS JOIN (SELECT r.product_sku from recently_added as r) as ra
        //     JOIN (SELECT p.product_sku, p.regular_price, p.sale_price FROM product_prices as p) as pp
        //     ON ra.product_sku = pp.product_sku
        //     ORDER BY n DESC
        // ';


        // Get all models from recently_added and recently_changed
        $models = collect(DB::select(DB::raw($sql)));

        error_log(print_r($models[0]));

        echo "Retrieved a total of ".count($models)." models to process \n";
        echo "Sorting... \n";

        // Sort by regular_price - sale_price
        // $models->sort(function($a, $b) {
        //     return ($b->regular_price - $b->sale_price) - ($a->regular_price - $a->sale_price);
        // }, SORT_NUMERIC);

        $models = $models->splice(0, min(250, count($models))); // Get the first 250 results

        echo "Done, about to insert ".count($models)." models into top_sales \n";

        foreach ($models as $m) {
            TopSale::insertOrIgnore(['product_sku' => $m->product_sku]);
        }

        echo "Done\n";
    }

    private function clearTopSales(): int {
        $models = TopSale::all();

        foreach ($models as $model) {
            $model->delete();
        }

        return count($models);
    }

}

if (!debug_backtrace()) {
    //This boots up the needed stuff for laravel when we run it as a one off script
    require __DIR__.'/../../vendor/autoload.php';
    $app = require_once __DIR__.'/../../bootstrap/app.php';
    $kernel = $app->make(Kernel::class);
    $response = $kernel->handle(
        $request = HttpRequest::capture()
    );

    // Then we start the gather
    $gatherer = new GatherTopSales();
    $gatherer->gather();
}



?>
