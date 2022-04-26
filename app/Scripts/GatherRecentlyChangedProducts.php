<?php

// This script will call BestBuy's API and traverses through all
// recently added products. Specifically, these products are new to BestBuy's
// store within the last 30 days.

namespace App\Scripts;

require __DIR__."/../../vendor/autoload.php";

use App\Models\Products;
use App\Models\RecentlyChanged;
use App\Models\SkuEmail;
use App\Scripts\GatherRecentlyAddedProducts as ScriptsGatherRecentlyAddedProducts;
use App\Services\EmailService;
use App\Services\PriceHistoryService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use paha\SimpleBestBuy\APIOptions;
use paha\SimpleBestBuy\APIQueryBuilder;
use paha\SimpleBestBuy\BestBuyAPI;
use paha\SimpleBestBuy\ProductOptions;

class GatherRecentlyChangedProducts{

    public function gather(){
        echo "starting product gather \n";
        // $days_ago = "10";
        $data = $this->gatherProducts(1, 5, env('SCRIPT_GATHER_RECENTLY_ADDED_DAYS', '1'));

        $recentModels = array_map(fn($p) => $this->buildRecentlyChanged($p), $data->products);
        $productModels = array_map(fn($p) => $this->buildProduct($p), $data->products);

        $count = $this->clearRecentlyChanged();
        echo "removing ".$count." old recentlyChanged models \n";

        $count = $this->removeOldProducts();
        echo "removing ".$count." old products \n";

        $recentModels = array_map(fn($p) => $p->refresh(), $recentModels);
        $productModels = array_map(fn($p) => $p->refresh(), $productModels);

        $this->addToRecentlyChanged(collect($recentModels));
        $this->addToProducts(collect($productModels));

        echo "added ".count($recentModels)." to recently_changed and products tables \n";

        $count = PriceHistoryService::addToPriceHistoryTable($data->products);

        echo "added ".$count." to price_histories table \n";

        $emails = $this->gatherEmails($this->getProductsDroppedPrice($data->products));

        // EmailService::sendPriceDrop($this->getProductsDroppedPrice($data->products));
    }

    /**
     * Gets the products that have dropped in price since last record
     *
     * @param array $apiProducts
     * @return Collection
     */
    private function getProductsDroppedPrice(array $apiProducts): Collection{
        $arr = [];

        // Gather all skus for a query
        $skus = [];
        foreach($apiProducts as $p){
            $skus[] = $p->sku;
        }

        // We only want to select certain columns. Not the regular and sale price columns (even though they should match)
        $products = DB::table('products')->select('product_sku', 'product_name', 'description', 'product_url', 'image_url', 'department_id');

        // Use the skus here to get a subset of data
        $latestProducts = DB::table("product_prices")
            ->whereIn('product_prices.product_sku', $skus)
            ->joinSub($products, 'products', 'product_prices.product_sku', '=', 'products.product_sku')
            ->get();

        // Map into an associative array, product_sku as key
        $latestProducts = $latestProducts->mapWithKeys(function($item, $key){
            return [$item->product_sku => $item];
        })->toArray();


        // Then we compare and check if our current sale price is below the last sale price
        foreach ($apiProducts as $product) {
            $model = $latestProducts[$product->sku];
            if($product->salePrice < $model->sale_price){
                $model->sale_price = $product->salePrice; // We assign this here to return the updated value
                $arr[] = $model;
            }
        }

        // Then we return it
        return collect($arr);
    }

    /**
     * Undocumented function
     *
     * @param Collection $products
     * @return integer
     */
    private function gatherEmails(Collection $productModels): array{
        $skus = array();

        // Gather the list of skys here
        foreach ($productModels as $model) {
            $skus[] = $model->product_sku;
        }

        // We remap the collection to be referenced by product sku
        $productModels = $productModels->mapWithKeys(function($item, $key){
            return [$item->product_sku => $item];
        });

        //Get the SkuEmail models joined with their email (that match the skus we gathered)
        $models = SkuEmail::whereIn('product_sku', $skus)
            ->join('emails', 'sku_emails.email_id', '=', 'emails.id')
            ->get();


        // Fun part ----
        // We need to build a map of email -> object {email_id, array of products}
        $map = [];

        // So for each email we gathered
        foreach ($models as $emailModel) {
            // If the email doesn't exist in the map
            if(!isset($map[$emailModel->email]))
                $map[$emailModel->email] = (object)['id'=>$emailModel->id, 'products'=>[]];

            // error_log(print_r($emailModel));

            // If the product we want exists
            if(isset($productModels[$emailModel->product_sku]))
                $map[$emailModel->email]->products[] = $productModels[$emailModel->product_sku]; // We append the product
        }

        return $map;
    }

    private function clearRecentlyChanged(): int {
        $models = RecentlyChanged::all();

        foreach ($models as $model) {
            $model->delete();
        }

        return count($models);
    }

    private function removeOldProducts(): int{
        $products = Products::all();

        $products = $products->filter(function($value, $key){
            return strtotime('-1 day') > strtotime($value->created_at); // Checks if the model is older than 1 day
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
        $options->restrictions = ProductOptions::priceUpdateDate() . ">=" . date("Y-m-d", strtotime("-".$daysAgo." days")); // Set our restriction
        // Set our options to show
        $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
            ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
            ProductOptions::itemUpdateDate(), ProductOptions::description(), ProductOptions::largeImage(), ProductOptions::image(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
            ProductOptions::addToCartUrl()];

        $builder = new APIQueryBuilder();
        $dg = new BestBuyAPI();

        $data = $dg->fetchAll($builder->products($options), $timeBetweenCalls, $failCount);

        $total = count($data->products);
        $time = microtime(true)-$start;

        return $data;
    }

    private function addToRecentlyChanged(Collection $models){
        DB::transaction (function () use ($models) {
            $models->each(function ($item) {
                RecentlyChanged::updateOrCreate([
                    'product_sku' => $item->product_sku
                ]);
            });
        });
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

    /**
     * Builds an array of inserts in the format of a RecentlyAddedObject
     *
     * @param $p The object to build from
     * @return RecentlyAddedModel The model created from the data
     */
    private function buildRecentlyChanged($p) : RecentlyChanged
    {
      $model = new RecentlyChanged();
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
