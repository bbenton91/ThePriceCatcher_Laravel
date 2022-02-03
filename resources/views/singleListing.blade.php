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
    // $hp = "$".("%.2f"|format($item.highestPrice/100.0));
    // $lp = "$".("%.2f"|format($item.lowestPrice/100.0));
    // $price = "$".("%.2f"|format($item.salePrice/100.0));
    $rp = number_format($item->regular_price/100, 2);
    $sp = number_format($item->sale_price/100, 2);
    $was = ($item->salePrice != $item->regular_price ? $item->sale_price/100.0 : 0);
    $name = $item->product_name;
    if (strlen($name) > 90){
        $name = Str::substr($name, 0, 90)."...";
    }
@endphp

<div class="w-48 h-48 flex flex-col drop-shadow border rounded">
    <div>
        <img src={{$item->image_url}}" alt="">
    </div>
    <div class="flex flex-col">
        <span class="mx-2 text-center w-full grow">{{$name}}</span>
        <div class="flex place-content-around">
            <span>${{$rp}}</span>
            @if ($rp != $sp)
                <span>${{$sp}}</span>
            @endif
        </div>
    </div>
    <div class="flex w-full h-full">
        <a href={{$item->product_url}} class="w-full">
            <button class="w-full">View Item</button>
        </a>
    </div>
</div>
