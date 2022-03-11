<?php

// This script will call BestBuy's API and traverses through all
// recently added products. Specifically, these products are new to BestBuy's
// store within the last 30 days.

namespace App\Scripts;

require __DIR__."/../../vendor/autoload.php";

use App\Models\Products;
use App\Models\RecentlyAdded;
use App\Scripts\GatherRecentlyAddedProducts as ScriptsGatherRecentlyAddedProducts;
use App\Services\PriceHistoryService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use paha\SimpleBestBuy\APIOptions;
use paha\SimpleBestBuy\APIQueryBuilder;
use paha\SimpleBestBuy\BestBuyAPI;
use paha\SimpleBestBuy\ProductOptions;

class GatherRecentlyAddedProducts{

    public function gather(){
        echo "starting product gather \n";

        $data = $this->gatherProducts(1, 5, "3");

        $recentModels = array_map(fn($p) => $this->buildRecentlyAdded($p), $data->products);
        $productModels = array_map(fn($p) => $this->buildProduct($p), $data->products);

        $count = $this->clearRecentlyAdded();
        echo "removing ".$count." old RecentlyAdded models \n";

        $count = $this->removeOldProducts();
        echo "removing ".$count." old products \n";

        $recentModels = array_map(fn($p) => $p->refresh(), $recentModels);
        $productModels = array_map(fn($p) => $p->refresh(), $productModels);

        echo "added ".count($recentModels)." to recently_added and products tables \n";

        $this->addToRecentlyAdded(collect($recentModels));
        $this->addToProducts(collect($productModels));

        $count = $this->addToPriceHistoryTable($data->products);
        echo "added ".$count." to price_histories table \n";
    }

    private function clearRecentlyAdded(): int {
        $models = RecentlyAdded::all();

        foreach ($models as $model) {
            $model->delete();
        }

        return count($models);
    }

    private function removeOldProducts(): int{
        $products = Products::all();

        $products = $products->filter(function($value, $key){
            return strtotime('-1 day') < strtotime($value->created_at); // Checks if the model is older than 1 day
        });

        foreach ($products as $model) {
            $model->delete();
        }

        return count($products);
    }


    function checkForDuplicates(array $array){
      foreach ($array as $value) {
        $results = array_filter($array, fn($o)=> $o->sku == $value->sku);
        if(count($results) > 1){
          echo "We have a problem here for sku $value[0] <br>";
        }
      }
    }

    private function gatherProducts(float $timeBetweenCalls, int $failCount, string $daysAgo){
        $start = microtime(true);

        $options = new APIOptions();
        $options->restrictions = ProductOptions::startDate().">=".date("Y-m-d", strtotime("-$daysAgo days"))."&new=true"; // Set our restriction
        // Set our options to show
        $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
            ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
            ProductOptions::itemUpdateDate(), ProductOptions::description(), ProductOptions::largeImage(), ProductOptions::image(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
            ProductOptions::addToCartUrl()];

        $builder = new APIQueryBuilder();
        $dg = new BestBuyAPI();

        $data = $dg->fetchAll($builder->products($options), $timeBetweenCalls, $failCount);
        // error_log(print_r($data));
        // die();

        $total = count($data->products);
        $time = microtime(true)-$start;

        return $data;
    }

    private function addToRecentlyAdded(Collection $models){
        DB::transaction (function () use ($models) {
            $models->each(function ($item) {
                RecentlyAdded::updateOrCreate([
                    'product_sku' => $item->product_sku
                ]);
            });
        });


        // DB::transaction (function () use ($models) {
        //     $models->each(function ($item) {
        //         DB::table('recently_added')->insert(
        //             [
        //                 'product_sku' => $item->product_sku,
        //                 'created_at' => now(),
        //                 'added_at' => now()
        //             ]
        //         );
        //     });
        // });

        // foreach ($models as $model) {
        //     $model->save();
        // }

    //   $raQuery = new RecentlyAddedQuery(new RecentlyAddedModel(), $pdo); // Query object for recently added

    //   $formattedInserts = array_map(fn($o)=>buildFromProduct($o), $products);

    //   // Clear the table
    //   $raQuery->fragment("DELETE FROM recentlyadded")->execute();

    //   $chunks = array_chunk($formattedInserts, 100);
    //   // We chunk this so we don't overload the query
    //   foreach ($chunks as $chunk) {
    //     $raQuery->insertMany($chunk)->execute();
    //   }
    }

    private function addToProducts(Collection $models){
        DB::transaction (function () use ($models) {
            $models->each(function ($item) {
                Products::updateOrCreate([
                    'product_sku' => $item->product_sku
                ],
                [
                    'product_name' => $item->product_name,
                    'description' => $item->description,
                    'regular_price' => $item->regular_price,
                    'sale_price' => $item->sale_price,
                    'product_url' => $item->product_url,
                    'image_url' => $item->image_url,
                    'department_id' => $item->department_id
                ]);
            });
        });
    }

    private function addToPriceHistoryTable(array $apiProducts):int
    {
        $sql = 'SELECT *
            FROM price_histories ph
            INNER JOIN (
                SELECT MAX(start_date) maxdate, product_sku
                FROM price_histories
                GROUP BY product_sku
            ) ph2
            on ph.product_sku = ph2.product_sku
            and ph.start_date = ph2.maxdate
            LIMIT 1;
        ';

        $latestProducts = DB::select(DB::raw($sql));

        // error_log(print_r($apiProducts));
        // die();

        $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect($apiProducts), collect($latestProducts));

        DB::transaction (function () use ($result) {
            $result->each(function ($item) {
                $item->save();
            });
        });

        // foreach ($differences as $diff) {
        //     $diff->save();
        // }

    //   $phQuery = new PriceHistoryQuery(new PriceHistoryModel(), $pdo); // Query object for price history

    //   // Insert into the price history table
    //   $skus = array_map(fn($o)=>$o->sku, $products); # Get sku array
    //   $queryData = $phQuery->selectLatestPriceHistory($skus); # We query the price history table for the latest prices for each sku

    //   // Then we compare the table data with the API data
    //   $inserts = PriceHistoryController::CompareAPIResultsWithPriceHistory($products, $queryData);

    //   // We chunk this so we don't overload the query
    //   $chunks = array_chunk($inserts, 100);
    //   foreach ($chunks as $chunk) {
    //     $phQuery->insertMany($chunk)->execute();
    //   }

    //   return count($inserts);

        return count($result);
    }

    /**
     * Builds an array of inserts in the format of a RecentlyAddedObject
     *
     * @param $p The object to build from
     * @return RecentlyAddedModel The model created from the data
     */
    private function buildRecentlyAdded($p) : RecentlyAdded
    {
      $model = new RecentlyAdded();
      $model->product_sku = $p->sku;
      return $model;
    }

    private function buildProduct($p): Products {
        $model = new Products();
        $model->product_sku = $p->sku;
        $model->product_name = $p->name;
        $model->description = $p->description ?? " ";
        $model->regular_price = $p->regularPrice*100;
        $model->sale_price = $p->salePrice*100;
        $model->product_url = $p->url;
        $model->image_url = $p->largeImage ?? $p->image ?? " ";
        $model->department_id = $p->departmentId;
        return $model;
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
    $gatherer = new ScriptsGatherRecentlyAddedProducts();
    $gatherer->gather();
}



?>
