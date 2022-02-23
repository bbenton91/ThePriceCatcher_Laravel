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
    $redirect = "results/".$item->product_sku;
    $hp = isset($item->highest_price) ? $item->highest_price : 0;
    $lp = isset($item->lowest_price) ? $item->lowest_price : 0;

    $hp = number_format($hp/100, 2);
    $lp = number_format($lp/100, 2);
    $rp = number_format($item->regular_price/100, 2);
    $sp = number_format($item->sale_price/100, 2);
    $was = ($item->sale_price != $item->regular_price ? $item->sale_price/100.0 : 0);
    $name = $item->product_name;
    if (strlen($name) > 30){
        $name = Str::substr($name, 0, 30)."...";
    }
@endphp

<div class="w-48 h-64 flex flex-col drop-shadow border rounded">
    <div class="listing w-auto mb-2">
        <img src={{$item->image_url}} alt="" class="mx-auto my-2 h-full">
    </div>
    <div class="flex flex-col mx-2 h-full">
        <span class="text-center w-full grow">{{$name}}</span>

        <div class="flex">
            <div class="flex flex-col place-content-around w-full items-center">

                @if ($rp != $sp)
                    <span class="line-through">${{$rp}}</span>
                    <span>${{$sp}}</span>
                @else
                    <span>${{$rp}}</span>
                @endif
                <br>

            </div>
            <div class="flex flex-col w-full items-center">
                <span>${{$hp}}</span>
                <span>${{$lp}}</span>
            </div>
        </div>

    </div>
    <div class="flex w-full h-24">
        <a href={{$item->product_url}} class="w-full">
            <button class="w-full bestbuy-button h-full font-semibold text-base">View Item</button>
        </a>
    </div>
</div>
