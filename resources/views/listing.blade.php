<script>

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
    </script>

    <div class="grid">
        @if length > 0
            @for item in items
                @php
                    $redirect = "results/".item.sku;
                    $hp = "$".("%.2f"|format(item.highestPrice/100.0));
                    $lp = "$".("%.2f"|format(item.lowestPrice/100.0));
                    $price = "$".("%.2f"|format(item.salePrice/100.0));
                    $was = (item.salePrice != item.regularPrice ? item.regularPrice/100.0 : 0);
                    $name = item.name;
                    if (Str::length($name) > 90){
                        $name = Str::substr($name, 0, 90)."...";
                    }
                @endphp

                <div class="product_listing_layout">

                    <div class="product_listing_top is-flex margin-top-10 margin-left-10">

                        <div class="is-flex flex-self-center flex-content-center image-height-128"><img class="flex-center-self" src={{ $item.imageUrl }} alt="" /></div>
                        <div class="flex-content-center flex-grow-2 is-flex margin-left-10"><a class="product_listing_name" href={{ $prepend."/".redirect }}> {{ name }}</a></div>
                        <div class="product_prices is-flex flex-content-right margin-right-10">
                            <div>{{$price}}
                                @if was > 0
                                    <br><div class="small-grey"><s>was ${{was|number_format(2)}}</s></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="div_divider margin-top-10"></div>

                    <div class="div_grid_highest_lowest_avg margin-top-10">
                        <div title="Highest Price" class="highest_price bold">{{ $hp }}</div>
                        <div title="Lowest Price" class="lowest_price bold">{{ $lp }}</div>
                    </div>
                    <div class="product_listing_item_link"><a href={{ $item.url }}>Visit Product Page</a></div>

                </div>
            @endfor
        @endif
    </div>
