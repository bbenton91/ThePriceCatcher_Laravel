<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>BestBuy Price Tracking</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800&display=swap" rel="stylesheet">
  </head>
    <div id="header" class="flex border-2 drop-shadow lg:h-20 h-36 items-stretch bg-white">
        <div id="title" class="pl-4 mb-2 flex">
            <a href={{$prepend."/"}} class="flex">
                <img src={{$prepend."/img/graph.png"}} alt="" class="w-24 h-24 lg:w-14 lg:h-14 self-center mr-2">
                <div class="flex flex-col justify-center">
                    <h2 class="text-5xl lg:text-2xl font-bold text-blue-500">Price</h2>
                    <h2 class="pl-2 text-5xl lg:text-2xl font-bold text-blue-500">Catcher</h2>
                </div>

            </a>
        </div>
        <nav class="flex self-center text-xl pl-20 items-stretch self-stretch">
            {{-- <div class="header_divider"></div> --}}
            <a href={{$prepend."/"}} class="lg:flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48 mobile-hidden">Home</a>
            <a href={{$prepend."/browse"}} class="lg:flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48 mobile-hidden">Browse Products</a>
            <a href={{$prepend."/about"}} class="lg:flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48 mobile-hidden">About</a>
        </nav>
        {{-- @include('dropdown') --}}

        @include('dropdown')

    </div>
</html>
