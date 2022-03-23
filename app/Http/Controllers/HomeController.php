<?php

namespace App\Http\Controllers;

use App\Models\MostViewed;
use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
            $recently_added = $this->getRecentlyAdded(3);

            // Get 5 randomly recently changed items
            $recently_changed = $this->getRecentlyChanged(3);

            MostViewed::all()->limit(3)->get();

            $end_db = microtime(true)-$start;

            error_log("Took ".$end_db." seconds to pull from database");

            $start_api = microtime(true);

        }catch (Exception $e){
            // $logger->log($e->getMessage(), ILogger::LEVEL_ERROR);
            // $logger->log($e->getTraceAsString(), ILogger::LEVEL_ERROR);
            // header("Location: error500.php");
        }

        $individualProductsTracked = DB::select(DB::raw(' SELECT COUNT(*) as total
            FROM (SELECT product_sku FROM price_histories GROUP BY product_sku)
            AS ph;
        '))[0]->total;

        return view('home', [
            'products' => [],
            'recentlyChanged'=> $this->getRecentlyChanged(3),
            'recentlyAdded'=>$this->getRecentlyAdded(3),
            'mostViewed'=>$this->getMostViewed(3),
            'recentlyViewed'=>[],
            'productsTracked'=>$individualProductsTracked,
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

      private function getMostViewed(int $limit)
      {
        $sql = 'SELECT product_name, product_sku, regular_price, sale_price, image_url, product_url, lowest_price, highest_price, created_at, updated_at FROM
        (
             SELECT * FROM most_viewed AS ts
             JOIN (
                   SELECT product_sku as sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
                   FROM price_histories
                   GROUP BY product_sku
             ) AS ph
              ON ts.product_sku = ph.sku
              JOIN (
                  SELECT product_name, product_sku as ps, regular_price, sale_price, image_url, product_url
                  FROM products
              ) AS p
              ON p.ps = ts.product_sku
              LIMIT '.$limit.'

        ) AS lp;';

        $models = collect(DB::select(DB::raw($sql)));

        return $models;
      }

      private static function getRecentlyViewed(array $recentlyViewedSession):array
      {

        return [];
      }

      private function getRecentlyAdded(int $limit)
      {
          $sql = 'SELECT product_name, product_sku, regular_price, sale_price, image_url, product_url, lowest_price, highest_price, created_at, updated_at FROM
          (
               SELECT * FROM recently_added AS ts
               JOIN (
                     SELECT product_sku as sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
                     FROM price_histories
                     GROUP BY product_sku
               ) AS ph
                ON ts.product_sku = ph.sku
                JOIN (
                    SELECT product_name, product_sku as ps, regular_price, sale_price, image_url, product_url
                    FROM products
                ) AS p
                ON p.ps = ts.product_sku
                LIMIT '.$limit.'

          ) AS lp;';

          $models = collect(DB::select(DB::raw($sql)));

        return $models;
      }

      private function getRecentlyChanged(int $limit) {
        $sql = 'SELECT product_name, product_sku, regular_price, sale_price, image_url, product_url, lowest_price, highest_price, created_at, updated_at FROM
        (
             SELECT * FROM recently_changed AS ts
             JOIN (
                   SELECT product_sku as sku, MAX(regular_price) as highest_price, MIN(sale_price) as lowest_price
                   FROM price_histories
                   GROUP BY product_sku
             ) AS ph
              ON ts.product_sku = ph.sku
              JOIN (
                  SELECT product_name, product_sku as ps, regular_price, sale_price, image_url, product_url
                  FROM products
              ) AS p
              ON p.ps = ts.product_sku
              LIMIT '.$limit.'

        ) AS lp;';

        $models = collect(DB::select(DB::raw($sql)));

        return $models;
      }
}
