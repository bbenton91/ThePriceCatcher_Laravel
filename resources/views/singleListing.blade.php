{{-- <script>

    document.addEventListener('DOMContentLoaded', function(event) {
        const items = document.getElementsByClassName('product_listing_layout');
        let time = 0;
        for (let i = 0; i < items.length; i++) {
            const element = items[i];
            setTimeout( function(){
                element.classList.add('fade-in');
            }, time);
            time += 50;
        }
    })
</script> --}}



@php
    $redirect = "/result/".$item->product_sku;
    $hp = isset($item->highest_price) ? $item->highest_price : 0;
    $lp = isset($item->lowest_price) ? $item->lowest_price : 0;

    $hp = number_format($hp/100, 2);
    $lp = number_format($lp/100, 2);
    $rp = number_format($item->regular_price/100, 2);
    $sp = number_format($item->sale_price/100, 2);
    $was = ($item->sale_price != $item->regular_price ? $item->sale_price/100.0 : 0);
    $name = $item->product_name;
    $length = 60;
    if (strlen($name) > $length){
        $name = Str::substr($name, 0, $length)."...";
    }
@endphp

<div class="h-48 flex flex-col drop-shadow-lg border rounded bg-gray-50 {{$size}}">
    <div class="listing w-auto mb-2 flex">
        <img src={{$item->image_url}} alt="" class="ml-2 mt-2 max-w-70">
        <div class="flex flex-col place-content-around w-full items-end mr-2">
             @if ($rp != $sp)
                 <span class="italic line-through">${{$rp}}</span>
                 <span class="">${{$sp}}</span>
             @else
                 <span>${{$rp}}</span>
             @endif
             <br>

         </div>
    </div>

    <div class="flex flex-col mx-2 h-full">
        {{-- <a href={{$item->product_url}}> --}}
            <span class="text-center w-full grow font-semibold lg:text-inherit">{{$name}}</span>
        {{-- </a> --}}
    </div>

    <span class="border mb-2 mx-2"></span>

    <div class="flex w-full h-24">
        <a href={{$item->product_url}} class="w-full mx-2">
            {{-- <button class="w-full bestbuy-button h-full font-semibold text-base">View Product</button> --}}
            <span class="text-blue-600">View Product</span>
        </a>

        <a href={{$redirect}} class="w-full mx-2">
            {{-- <button class="w-full bestbuy-button h-full font-semibold text-base">View Product</button> --}}
            <span class="text-blue-600">View History</span>
        </a>

        <div class="flex w-full place-items-end place-content-end">
            <span class="text-red-800 font-bold mr-2 bg-red-300 p-0.5 rounded">${{$hp}}</span>
            <span class="text-green-800 font-bold mr-1 bg-green-300 p-0.5 rounded">${{$lp}}</span>
        </div>
    </div>
</div>
