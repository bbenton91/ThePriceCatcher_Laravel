<?php

namespace App\Http\Controllers;

use App\Models\MostViewed;
use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        session_start();

        // session_destroy();
        if(!isset($_SESSION['index']))
            $_SESSION['index'] = 0;
        if(!isset($_SESSION['recently_viewed']))
            $_SESSION['recently_viewed'] = [];

        $start = microtime(true);

        try{
            // First, we get the recently view list from our session
            $most_recently_viewed = HomeController::getRecentlyViewed($_SESSION['recently_viewed']);

            // Get the first set of skus
            $skus = array_map(fn($o) => $o->sku, $most_recently_viewed);

            // Secondly, we get the some randomly picked recently added products from our database
            $recently_added = HomeController::getRecentlyAdded(5);

            // Add our previous existing skus to the new skus from recently_added
            // $skus = array_merge($skus, array_map(function($o){return $o->sku;}, $recently_added));

            // Get 5 randomly recently changed items
            $recently_changed = HomeController::getRecentlyChanged(5);

            // $qo = new QueryObject(new MostViewedModel(), IndexController::$pdo);

            MostViewed::all()->limit(15)->get();

            // Get the top 4 most viewed items
            // $most_viewed = $qo->select()->orderBy("counter", "desc")->limit(15)->execute()->fetchAll();
            // $most_viewed = ObjectHelper::rekey_array_by_sku($most_viewed); // Rekey the array by sku

            // $skus = array_merge($skus, array_map(function($o){return $o->sku;}, $most_viewed));

            $end_db = microtime(true)-$start;
            $start_api = microtime(true);

        }catch (Exception $e){
            // $logger->log($e->getMessage(), ILogger::LEVEL_ERROR);
            // $logger->log($e->getTraceAsString(), ILogger::LEVEL_ERROR);
            // header("Location: error500.php");
        }

        // Add our previous existing skus to the new skus from recently_added
        // $skus = array_merge($skus, array_map(function($o){return $o->sku;}, $recently_changed));
        // $skus = array_filter($skus, fn($o)=>$o > 0); // We need to filter out only positive (non-zero) values.

        // $options = new APIOptions();
        // $options->restrictions = ProductOptions::sku()." in (".implode(",", $skus).")";
        // $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
        //     ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
        //     ProductOptions::itemUpdateDate(), ProductOptions::longDescription(), ProductOptions::largeImage(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
        //     ProductOptions::addToCartUrl()];

        // $api = new BestBuyAPI();

        // Lastly, we make 1 api call to gather all of the products by sku number here.
        // $products = $sd->get_by_sku($skus);
        // $data = $api->fetch(APIQueryBuilder::products($options));
        // if($data->error !== ""){
        //     $logger->log("There was an error getting products on the front page.", ILogger::LEVEL_ERROR);
        //     header("Location: /error500");
        //     return;
        // }

        // $products = $data->products;
        // $products = ObjectHelper::rekey_array_by_sku($products);

        // var_dump($products);
        // $end_api = microtime(true)-$start_api;


        // return view('user.profile', [
        //     'products' => $products,
        //     'recently_changed'=>HomeController::prepare_listing($recently_changed, $products, 4),
        //     'recently_added'=>HomeController::prepare_listing($recently_added, $products, 4),
        //     'most_viewed'=>HomeController::prepare_listing($most_viewed, $products, 4),
        //     'recently_viewed'=>HomeController::prepare_listing($most_recently_viewed, $products, 4),
        //     'search_query'=>"",
        //     'prepend'=>Environment::PrependUrl()
        // ]);

        return view('home', [
            'products' => [],
            'recentlyChanged'=> $this->getRecentlyChanged(6),
            'recentlyAdded'=>$this->getRecentlyAdded(6),
            'mostViewed'=>$this->getMostViewed(6),
            'recentlyViewed'=>[],
            'search_query'=>"",
            'prepend'=>""
        ]);
    }

    private static function prepare_listing(array $array, array $products, int $how_many):array{
        $counter = 0;
        $data = [];

        foreach ($array as $sku => $value) {
          // Match our most_recently_viewed sku to our products list sku
          $p = isset($products[$value->sku]) ? $products[$value->sku] : NULL;
          if($p === NULL)
            continue;

          $info = []; # Start a new array

          $result_url = "/results/{$p->sku}";

          $info[] = $result_url; # The url to follow when clicked
          $info[] = $p->largeImage; # The image url to display the image
          $info[] = $p->name; # The name of the listing

          $counter++;
          $data[] = $info; # Append the info to the data array
          if($counter >= $how_many)
            break;
        }

        return $data; # Return the data array
      }

      private function getMostViewed(int $limit): Collection
      {
        $models = MostViewed::join('products', 'most_viewed.product_sku', '=', 'products.product_sku')
        ->orderBy('most_viewed.updated_at')
        ->take($limit)
        ->get();

        return $models;
      }

      private static function getRecentlyViewed(array $recentlyViewedSession):array
      {
        // // Make them into standard product objects
        // $most_recently_viewed = array_map(function($o){return Product::from_table_object($o);}, $recentlyViewedSession);


        // // Rekey the array by sku
        // $most_recently_viewed = ObjectHelper::rekey_array_by_sku($most_recently_viewed);

        return [];
      }

      private function getRecentlyAdded(int $limit): Collection
      {
          $models = RecentlyAdded::join('products', 'recently_added.product_sku', '=', 'products.product_sku')
            ->orderBy('recently_added.updated_at')
            ->take($limit)
            ->get();

        return $models;
      }

      private function getRecentlyChanged(int $limit):Collection {
          $models = RecentlyChanged::join('products', 'recently_changed.product_sku', '=', 'products.product_sku')
            ->orderBy('recently_changed.updated_at')
            ->take($limit)
            ->get();

        return $models;
      }
}
