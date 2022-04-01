<?php

namespace App\Http\Controllers;

use App\Models\MostViewed;
use App\Models\RecentlyAdded;
use App\Models\RecentlyChanged;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $recentlyChanged = [];
        $recentlyAdded = [];
        $mostViewed = [];

        try{
            $recentlyChanged = $this->getRecentlyChanged(3);

            $end_db = microtime(true)-$start;
            Log::debug('RecentlyChanged gathering took '.$end_db.' seconds');
            $start = microtime(true);

            $recentlyAdded = $this->getRecentlyAdded(3);

            $end_db = microtime(true)-$start;
            Log::debug('RecentlyAdded database gathering took '.$end_db.' seconds');
            $start = microtime(true);

            $mostViewed = $this->getMostViewed(3);

            $end_db = microtime(true)-$start;
            Log::debug('MostViewed database gathering took '.$end_db.' seconds to get '.count($mostViewed).' products');
            // Log::channel('daily')->debug('Home controller database gathering took '.$end_db.' seconds');

            // $start_api = microtime(true);

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
            'recentlyChanged'=> $recentlyChanged,
            'recentlyAdded'=>$recentlyAdded,
            'mostViewed'=>$mostViewed,
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
        // This is how we do it for ALL rows in most_viewed

        $sql = ' SELECT * from most_viewed as mv
                JOIN product_prices as pp
                ON mv.product_sku = pp.product_sku
                JOIN products as p
                ON mv.product_sku = p.product_sku
                LIMIT '.$limit.';
        ';

        $models = collect(DB::select(DB::raw($sql)));

        return $models;
      }

      private static function getRecentlyViewed(array $recentlyViewedSession):array
      {

        return [];
      }

    private function getRecentlyAdded(int $limit)
    {

        $sql = ' SELECT * from recently_added as ra
                JOIN product_prices as pp
                ON ra.product_sku = pp.product_sku
                JOIN products as p
                ON ra.product_sku = p.product_sku
                LIMIT '.$limit.';
        ';

        $models = collect(DB::select(DB::raw($sql)));

        // echo $models;

        return $models;
      }

    private function getRecentlyChanged(int $limit) {
        $sql = ' SELECT * from recently_changed as rc
                JOIN product_prices as pp
                ON rc.product_sku = pp.product_sku
                JOIN products as p
                ON rc.product_sku = p.product_sku
                LIMIT '.$limit.';
        ';

        $models = collect(DB::select(DB::raw($sql)));

        return $models;
      }
}
