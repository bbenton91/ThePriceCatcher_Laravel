<?php
namespace App\Services;

use App\Mail\FeedbackEmail;
use App\Mail\PriceDropEmail;
use App\Models\SkuEmail;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService{

    /**
     * Sends out price drop emails related to recently updated products.
     *
     * @param Collection $productsWithPriceDrop The products that have already been filtered for a price drop.
     * @return void
     */
    public function sendPriceDrop(Collection $productsWithPriceDrop){

        // Gather all the emails in a map
        $emails = $this->gatherEmails($productsWithPriceDrop);

        if(count($emails) > 0){
            try{

                foreach ($emails as $email => $data) {
                    // error_log(print_r($email));
                    $num = count($data->products);
                    $data = [
                        'from' => 'admin@thepricecatcher.com', // We are sending it from ourselves
                        'to' => 'admin@thepricecatcher.com', // This is the address they listed
                        'subject' => "There are {$num} products that have dropped in price!",
                        'emailID' => $data->id,
                        'products' => $data->products,
                        '' => $data->products,
                    ];

                    // Mail::to('admin@thepricecatcher.com')->send(new FeedbackEmail($data));
                    Mail::to('admin@thepricecatcher.com')->send(new PriceDropEmail($data));
                    error_log("Sent Email");
                }

            }catch(Exception $e){
                $errors['server'] = $e;
                Log::error($e->getMessage());
                echo "Error mail";
            }
        }
    }

    /**
     * Using an array of product models; gathers all emails stored in the DB that have subscribed to
     * the product updates.
     *
     * @param Collection $products A list of product models
     * @return array An associative array (map) of 'email' => object('id', 'products')
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

            // If the product we want exists
            if(isset($productModels[$emailModel->product_sku]))
                $map[$emailModel->email]->products[] = $productModels[$emailModel->product_sku]; // We append the product
        }

        return $map;
    }
}

?>
