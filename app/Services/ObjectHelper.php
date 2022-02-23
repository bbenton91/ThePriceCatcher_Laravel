<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use stdClass;

    class ObjectHelper{
        static function rekey_array_by_sku(Collection $array, string $arrKey = 'product_sku'):Collection{
            
            return $array->mapWithKeys(function($item, $key) use ($arrKey){
                return [$item[$arrKey] => $item];
            });
        }
}

?>
