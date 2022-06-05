<?php

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\ProductPrices;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use paha\SimpleBestBuy\ProductOptions;

class PriceHistoryService{

    /**
     * Builds an array of inserts from the difference between an array of products from an API fetch and attempted
     * matching data from the PriceHistoryQuery results.
     *
     * @param Collection $products An array of objects from an API fetch
     * @param Collection $models An array of PriceHistoryModel models
     * @param string $date The date to use, defaults to 'NOW()'
     *
     * @return Collection An array of PriceHistoryModel filled with data.
     */
    static function CompareAPIResultsWithPriceHistory(Collection $APIproducts, Collection $models, $date = null): Collection
    {
        $date = $date ?? now()->toDateTimeString();
        $inserts = [];

        // Convert the collection to an array where sku => item
        $orderedProducts = $APIproducts->mapWithKeys(fn($item, $key) =>
            [$item->sku => $item]
        );

        // Conver the collection to an array where sku => item
        $orderedModels = $models->mapWithKeys(fn($item, $key) =>
            [$item->product_sku => $item]
        );


        /** @var array $orderedProducts */
        /** @var string $key */
        foreach ($orderedProducts as $key => $product) {
            $productRP = intval(ceil($product->regularPrice*100));
            $productSP = intval(ceil($product->salePrice*100));

            if(isset($orderedModels[$key])){ // If we can compare it, let's try
                /** @var PriceHistoryModel $model */
                $model = $orderedModels[$key];

                $modelRP = intval(ceil($model->regular_price));
                $modelSP = intval(ceil($model->sale_price));

                // If either regular prices or sale prices don't match...
                if($modelRP != $productRP || $modelSP != $productSP){
                    $newModel = new PriceHistory([
                        'product_sku' => $product->sku,
                        'start_date' => $date,
                        'regular_price' => $productRP,
                        'sale_price' => $productSP
                    ]);
                    // error_log(print_r($newModel));
                    $inserts[] = $newModel;
                }


            }else{ // Otherwise it doesn't exist already, so add it

                $insert = new PriceHistory([
                    'product_sku' => $product->sku,
                    'start_date' => $date,
                    'regular_price' => $productRP,
                    'sale_price' => $productSP
                ]);
                $inserts[] = $insert;
            }
        }

        return collect($inserts);
    }

    public static function addToPriceHistoryTable(array $apiProducts):int
    {
        // Gather all skus for a query
        $skus = [];
        foreach($apiProducts as $p){
            $skus[] = $p->sku;
        }

        $chunks = array_chunk($skus, count($skus)/5);

        foreach ($chunks as $chunk) {

            // Use the skus here to get a subset of data
            $latestProducts = DB::table("product_prices")->whereIn('product_sku', $chunk)->get();

            // Gather a list of products that have changed
            $result = PriceHistoryService::CompareAPIResultsWithPriceHistory(collect($apiProducts), collect($latestProducts));

            // Save all changed products to the database
            DB::transaction (function () use ($result) {
                $result->each(function ($item) {
                    $item->save();
                });
            });

    }

        return count($result);
    }
}

