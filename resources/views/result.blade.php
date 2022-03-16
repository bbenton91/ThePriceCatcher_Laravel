<script>

function addEmail(emailField, successText, failText, urlParams){
    const params = new URLSearchParams(urlParams);
    let data = `email=${emailField.value}&sku=${params.get('sku')}`;

    var request = new XMLHttpRequest();

    // Get data back from the POST request. This will validate info server side in case client side
    // doesn't catch it
    request.onload = () => {
        const resp = request.responseText;
        console.log(resp);

        if(resp.length != 0){ // Don't apply errors if we have no response data
            const errors = JSON.parse(resp);
            if('email' in errors){
                failText.classList.remove("hidden");
                successText.classList.add("hidden");
            }
            // applyErrors(errors, form)
        }else{
            successText.classList.remove("hidden");
            failText.classList.add("hidden");
            emailField.value = "";
        }
        // closeForm();
    }

    request.open('POST', '../php/EmailSubscribe.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send(data);
}

</script>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Price History For {{$product->sku}}</title>

        {{-- <link rel="stylesheet" href={{prepend ~ "/styles/styles.css"}}> --}}
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800&display=swap" rel="stylesheet">
        <script src="../Chart.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        {{-- <script src="../chart.js"></script> --}}
        <script type="text/javascript" src="{{ URL::asset('js/Chart.bundle.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

    </head>


    <body>
        {{-- {% include "header_template.html" %} --}}
        {{-- {% include "search_bar_template.html" %} --}}

         <!-- Require our search bar here -->
        @include('header', ['prepend' => $prepend])
        <div class="mt-10"></div>
        @include('search_bar', ['prepend' => $prepend, "search_query" => ""])

        <br>
        <div id="innerBody" class="w-3/5 h-auto mx-auto">
            <div id="canvas_div" class="text-black text-center text-xl">
                <h1>Price History for `{{$product->name}}`</h1>
                <canvas id="myChart" max-width="400" max-height="400"></canvas>
            </div>

            {{-- {% autoescape false %} --}}
            <script type="text/javascript"> {{!! $output !!}} </script>
            {{-- {% endautoescape %} --}}

            <div id="productInfoSection" class="mt-5 flex place-content-around w-3/5 mx-auto">
                <form action={{ $product->url }}>
                <button class="bestbuy-button border border-black rounded p-2 font-semibold w-80">
                    ${{$product->salePrice}} on BestBuy.com</button></form>

                <form action={{$product->addToCartUrl}}>
                <button class="bestbuy-button border border-black rounded p-2 font-semibold w-80">Add to Cart</button></form>
            </div>

            <br>

            <div id="emailSubscribeDiv">
                <h2>Subscribe to price drops for this product</h2>
                <div id="emailInputDiv">
                    <input type="email" name="" id="emailInputText">
                    <button type="button" class="button accept"
                        onclick="addEmail(document.getElementById('emailInputText'),
                        document.getElementById('subscribeSuccessText'),
                        document.getElementById('subscribeFailText'),
                        window.location.search)">Send</button>
                    <br>
                    <p class="successful hidden subscribeText" id="subscribeSuccessText">Success! Thanks for subscribing!</p>
                    <p class="error-text hidden subscribeText" id="subscribeFailText">Invalid email address. Please enter a valid email</p>
                </div>
            </div>
        </div>

        {{-- {% include "footer_template.html" %} --}}
        @include('footer', ['prepend' => $prepend])


    </body>
</html>
