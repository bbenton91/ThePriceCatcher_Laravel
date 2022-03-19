<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use App\Models\TopSale;
use App\Services\PriceHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use paha\SimpleBestBuy\APIOptions;
use paha\SimpleBestBuy\APIQueryBuilder;
use paha\SimpleBestBuy\BestBuyAPI;
use paha\SimpleBestBuy\ProductOptions;

class BrowseController extends Controller
{
    public function showSearch($searchQuery){
        // $searchQuery = $request->input('query');

        $api = new BestBuyAPI();
        $options = new APIOptions();
        $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
            ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
            ProductOptions::itemUpdateDate(), ProductOptions::longDescription(), ProductOptions::largeImage(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
            ProductOptions::addToCartUrl()];

        //If our search GET is set, search for items!
        if($searchQuery != ""){

            $array = explode("+", $searchQuery);
            $options->restrictions = "search=".implode("&", $array);

            # Get search results from the api call
            //TODO Need to check for errors here or surround with try/catch
            $results = $api->fetch(APIQueryBuilder::products($options))->products;

            $skus = array_map(fn($o)=>$o->sku, $results);

            // Or maybe this handles errors/empty products?
            if(count($skus) > 0){
                $ranges = DB::query()->fromSub(function($query){
                    $query->selectRaw('product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price')
                        ->from('price_histories')
                        ->groupBy('product_sku');
                }, 'ph')
                    ->join('price_histories AS ph2', 'ph2.product_sku', '=', 'ph.product_sku')
                    ->whereIn('ph2.product_sku', $skus)
                    ->groupBy('ph2.product_sku')
                    ->get();


                // Map by product_sku
                $orderedModels = $ranges->mapWithKeys(fn($item, $key) =>
                    [$item->product_sku => $item]
                );

                // Combine the api and models together
                $results = $this->combineData($orderedModels, $results);

                // Add to the price history table
                //TODO This breaks stuff for some reason
                // PriceHistoryService::addToPriceHistoryTable($results);
            }

            return view('browse', [
                'products' => $results,
                'departments' => $this->getDepartments(),
                'selected' => -1,
                'search' => $searchQuery,
                'prepend'=>""
            ]);
        }
    }

    public function showTopSales($depID){
        $recents = DB::query()->fromSub(function($query){
            $query->selectRaw('product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price')
                ->from('price_histories')
                ->groupBy('product_sku');
        }, 'ph')
            ->join('top_sales AS ts', 'ts.product_sku', '=', 'ph.product_sku')
            ->groupBy('ts.product_sku')
            ->limit(100)
            ->get();

        $finalProducs = $this->getApiData(collect($recents));

        return view('browse', [
            'products' => $finalProducs,
            'departments' => $this->getDepartments(),
            'selected' => $depID,
            'prepend'=>""
        ]);
    }

    public function showRecentlyChanged($depID){
        $recents = DB::query()->fromSub(function($query){
            $query->selectRaw('product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price')
                ->from('price_histories')
                ->groupBy('product_sku');
        }, 'ph')
            ->join('recently_changed AS rc', 'rc.product_sku', '=', 'ph.product_sku')
            ->groupBy('rc.product_sku')
            ->limit(100)
            ->get();


        $finalProducs = $this->getApiData(collect($recents));

        // error_log(print_r($orderedProducts));
        // die();

        return view('browse', [
            'products' => $finalProducs,
            'departments' => $this->getDepartments(),
            'selected' => $depID,
            'prepend'=>""
        ]);
    }

    public function showRecentlyAdded($depID){
        $recents = DB::query()->fromSub(function($query){
            $query->selectRaw('product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price')
                ->from('price_histories')
                ->groupBy('product_sku');
        }, 'ph')
            ->join('recently_added AS ra', 'ra.product_sku', '=', 'ph.product_sku')
            // ->on('ra.product_sku', '=', 'ph.product_sku')
            ->groupBy('ra.product_sku')
            ->limit(100)
            ->get();

        $finalProducs = $this->getApiData(collect($recents));

        return view('browse', [
            'products' => $finalProducs,
            'departments' => $this->getDepartments(),
            'selected' => $depID,
            'prepend'=>""
        ]);
    }

    private function getApiData(Collection $recents){
        $skus = $recents->map(function($item, $key){
            return $item->product_sku;
        })->toArray();

        $orderedModels = $recents->mapWithKeys(fn($item, $key) =>
            [$item->product_sku => $item]
        );

        $s = ProductOptions::sku()." in (".implode(",", $skus).")";
        // error_log(print_r($s));
        // die();

        $api = new BestBuyAPI();
        $options = new APIOptions();
        $options->restrictions = ProductOptions::sku()." in (".implode(",", $skus).")";
        $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
            ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
            ProductOptions::itemUpdateDate(), ProductOptions::longDescription(), ProductOptions::largeImage(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
            ProductOptions::addToCartUrl()];

        $data = $api->fetch(APIQueryBuilder::products($options))->products;

        return $this->combineData($orderedModels, $data);
    }

    private function combineData($orderedModels, $apiProducts): array{
        $finalProducts = [];

        foreach ($apiProducts as $p) {
            $dbp = null;
            if(isset($orderedModels[$p->sku]))
                $dbp = $orderedModels[$p->sku]; // Get the product from the orderProducts using the $p->sku from the API call

            $newProduct = (object)[
                'product_sku'=> $p->sku,
                'product_name' => $p->name,
                'regular_price' => $p->regularPrice*100,
                'sale_price' => $p->salePrice*100,
                'image_url' => $p->largeImage,
                'product_url' => $p->url,
                'description' => $p->longDescription,
                'lowest_price' => isset($dbp->lowest_price) ? $dbp->lowest_price : 0,
                'highest_price' => isset($dbp->highest_price) ? $dbp->highest_price : 0
            ];

            $finalProducts[] = $newProduct;
        }

        return $finalProducts;
    }

    private function getDepartments() : Collection{
        return Departments::all();
    }
}
