<?php

namespace App\Http\Controllers;

use App\Models\MostViewed;
use App\Models\PriceHistory;
use DateTime;
use Error;
use Exception;
use paha\SimpleBestBuy\APIOptions;
use paha\SimpleBestBuy\APIQueryBuilder;
use paha\SimpleBestBuy\BestBuyAPI;
use paha\SimpleBestBuy\ProductOptions;

class ResultController extends Controller
{
    public function show($sku){
        $sku = intval($sku);
        session_start();

        $product = null;

        try{
            $this->incrementMostViewed($sku);

            $counter = 0;

            while($counter < 5 && empty((array)$product)){
                $apiData = $this->getApiData($sku);
                $product = empty($apiData->products) ? [] : $apiData->products[0];
                sleep(1);
                $counter++;
            }


        }catch(Error | Exception $e){
            // $logger->log("Failed to get information on result for sku {$sku}", ILogger::LEVEL_ERROR);
            // $logger->log($e->getMessage());
            // $logger->log($e->getTraceAsString());
            header("Location: /error500");
            return;
        }

        if($apiData->error != ""){
            // $logger->log($apiData->error);
            // $logger->log("".$sku);
            header("Location: /error500");
            return;
        }

        if(empty($product)){
            header("Location: /error500");
            return;
        }

        try{
            $curr_date = new DateTime('now');
            $curr_date = $curr_date->format('Y-m-d');

            $output = $this->makeResultChart($sku, $curr_date);

            // echo $output;

            return view('result', [
                'product' => $product,
                'output'=>$output,
                'prepend'=>'',
            ]);

        }catch(Error | Exception $e){
            // $logger->log("Something went wrong render data for {$sku}", ILogger::LEVEL_ERROR);
            // $logger->log($e->getMessage());
            // $logger->log($e->getTraceAsString());
        }

        header("Location: /error500");
    }

    private function getApiData(int $sku) : object {
        $options = new APIOptions();
        $options->optionsToShow = [ProductOptions::sku(), ProductOptions::name(), ProductOptions::regularPrice(), ProductOptions::salePrice(), ProductOptions::class(),
                ProductOptions::classId(), ProductOptions::subclass(), ProductOptions::subclassId(), ProductOptions::department(), ProductOptions::departmentId(), ProductOptions::categoryPath(),
                ProductOptions::itemUpdateDate(), ProductOptions::longDescription(), ProductOptions::largeImage(), ProductOptions::url(), ProductOptions::startDate(), ProductOptions::new(),
                ProductOptions::addToCartUrl()];

        $options->restrictions = ProductOptions::sku()."=$sku";

        $api = new BestBuyAPI();
        $apiData = $api->fetch(APIQueryBuilder::products($options)); // Try to get the product apiData from the store API

        return $apiData;
    }

    private function incrementMostViewed(int $sku){
        $model = MostViewed::find($sku);
        $counter = ($model == null ? 0 : $model->counter) + 1;

        MostViewed::updateOrCreate(
            [
                'product_sku' => $sku
            ],
            [
                'counter' => $counter
            ]
        );
    }

    private function makeResultChart(int $sku, $currDate):string {
        $output = "";

        $models = PriceHistory::where('product_sku', $sku)->get();
        $count = count($models);

        if(!isset($_SESSION['index']))
            $_SESSION['index'] = 0;

        $data = [];
        $dates = [];
        if($models){
            // Records each price history point and date it was done
            foreach ($models as $key => $value) {
                $data[] = (float)($value->sale_price)/100;
                $d = explode(' ',  $value->start_date)[0]; // We only want the year/date/month
                $dates[] = $d; //Adds todays date to the data
            }

            // Then if we don't have a price today, add a redundant price and date to make it look nice
            if($dates[$count-1] != $currDate){
                $data[] = $data[$count-1]; // Add redundant price to the end
                $d = explode(' ', $currDate)[0]; // We only want the year/date/month
                $dates[] = $d; //Adds todays date to the data
            }

            //Some more trickery. These need to be in the form of strings when passed, like "2020-02-06"
            $labels = '"'.implode('", "', $dates).'"';
            $priceData = implode(",", $data);

            // We do some trickery here to output a javascript call with parameters from our php results
            $output = "drawChart([$priceData], [$labels]);";

            $rv = $_SESSION['recently_viewed'][$_SESSION['index']] = ["product_sku"=>$sku];
            $_SESSION['index'] = ($_SESSION['index']+1)%4; //Our index to implement a circular array for our recently viewed

        }else{
            $output = "No item found";
        }

        return $output;
    }
}
