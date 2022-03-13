<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>BestBuy Price Tracking</title>
    {{-- <link rel="stylesheet" href="styles/styles.css"> --}}
    {{-- <link href="{{ asset('css/styles.css') }}" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script> -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800&display=swap" rel="stylesheet">
  </head>
    <div id="header" class="flex border-2 drop-shadow h-18 items-stretch bg-white">
        <div id="title" class="pl-4 mb-2">
            <a href={{$prepend."/"}} class="flex">
                <img src={{$prepend."/img/graph.png"}} alt="" class="w-14 h-14 self-center mr-2">
                <div>
                    <h2 class="text-2xl font-bold text-blue-500">Price</h2>
                    <h2 class="pl-2 text-2xl font-bold text-blue-500">Catcher</h2>
                </div>

            </a>
        </div>
        <nav class="flex self-center text-xl pl-20 items-stretch self-stretch">
            {{-- <div class="header_divider"></div> --}}
            <a href={{$prepend."/"}} class="flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48 ">Home</a>
            <a href={{$prepend."/browse"}} class="flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48">Browse Products</a>
            <a href={{$prepend."/about"}} class="flex ml-8 px-2 items-center justify-center text-center h-full hover:bg-blue-100 w-48">About</a>
        </nav>
        {{-- @include('dropdown') --}}
    </div>
</html>
