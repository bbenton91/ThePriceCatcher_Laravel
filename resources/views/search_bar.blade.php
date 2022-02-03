<div id="search_bar_div" class="m-auto flex flex-col items-center">

    <form id="search_form" action={{"/pricecatcher/search/" }} class="w-4/12" method="get">
        <input id="search_bar" type="text" name="query" value="{{ $search_query ?? "" }}" placeholder=" Search for items here" class="border-2 w-full h-12 drop-shadow rounded">
        {{-- <button id="search_button" type="submit"><img src={{$prepend."/img/search-white-48dp.svg"}}></button> --}}
    </form>

    <div id="search_options" class="mt-2">

        <a href={{$prepend."/browse/topSales/-1"}}><button class="border-2 mr-2 h-10 w-48 rounded-md border-blue-300 text-blue-100 font-bold bg-blue-500">Top Sales</button></a>

        <a href={{$prepend."/browse/recentlyChanged/-1"}}><button class="border-2 h-10 mr-2 w-48 rounded border-green-500 text-green-100 font-bold bg-green-500">Recently Changed</button></a>

        <a href={{$prepend."/browse/recentlyAdded/-1"}}><button class="border-2 h-10 mr-2 w-48 rounded border-yellow-100 text-yellow-100 font-bold bg-yellow-500">Recently Added</button></a>

    </div>
</div>
