<?php

namespace App\Http\Controllers;

use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use App\Models\TopSale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use paha\SimpleBestBuy\APIOptions;
use paha\SimpleBestBuy\APIQueryBuilder;
use paha\SimpleBestBuy\BestBuyAPI;
use paha\SimpleBestBuy\ProductOptions;

class BrowseController extends Controller
{
    public function showTopSales($depID){

        // I'm not sure how to express this in eloquent ORM
        // $recents = DB::select(DB::raw
        // ('SELECT *, lowest_price, highest_price
        //     FROM (
        //         SELECT product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
        //         FROM price_histories
        //         GROUP BY product_sku
        //         ) as ph
        //     JOIN top_sales as ts
        //     ON ts.product_sku = ph.product_sku
        //     GROUP BY ts.product_sku
        //     LIMIT 100;
        // '));

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
            'prepend'=>""
        ]);
    }

    public function showRecentlyChanged($depID){
        // I'm not sure how to express this in eloquent ORM
        // $recents = DB::select(DB::raw
        // ('SELECT *, lowest_price, highest_price
        //     FROM (
        //         SELECT product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
        //         FROM price_histories
        //         GROUP BY product_sku
        //         ) as ph
        //     JOIN recently_changed as rc
        //     ON rc.product_sku = ph.product_sku
        //     GROUP BY rc.product_sku
        //     LIMIT 100;
        // '));

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
            'prepend'=>""
        ]);
    }

    public function showRecentlyAdded($orderedProducts){
        // I'm not sure how to express this in eloquent ORM
        // $recents = DB::select(DB::raw
        // ('SELECT *, lowest_price, highest_price
        //     FROM (
        //         SELECT product_sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
        //         FROM price_histories
        //         GROUP BY product_sku
        //         ) as ph
        //     JOIN recently_added as ra
        //     ON ra.product_sku = ph.product_sku
        //     GROUP BY ra.product_sku
        //     LIMIT 100;
        // '));

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

        // error_log(print_r($recents));
        // die();


        $finalProducs = $this->getApiData(collect($recents));

        // error_log(print_r($orderedProducts));
        // die();

        return view('browse', [
            'products' => $finalProducs,
            'prepend'=>""
        ]);
    }

    private function getApiData(Collection $recents){
        $skus = $recents->map(function($item, $key){
            return $item->product_sku;
        })->toArray();

        $orderedProducts = $recents->mapWithKeys(fn($item, $key) =>
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

        $finalProducts = [];

        foreach ($data as $p) {
            $dbp = $orderedProducts[$p->sku]; // Get the product from the orderProducts using the $p->sku from the API call

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
}
