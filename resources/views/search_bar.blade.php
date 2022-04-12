<div id="searchBarDiv" class="m-auto flex flex-col items-center z-0 relative">

    <form id="search_form" action={{"/search" }} class="w-10/12 lg:w-4/12 relative searchbar z-0" method="get">
        <input id="search_bar" type="text" name="query" value="{{ $search ?? "" }}" placeholder=" Search for items here" class="relative border-2 w-full h-20 lg:h-12 drop-shadow rounded text-4xl lg:text-lg px-2 z-0">
        {{-- <button id="search_button" type="submit"><img src={{$prepend."/img/search-white-48dp.svg"}}></button> --}}
    </form>

    <div id="search_options" class="mt-6 lg:mt-2">

        <a href={{$prepend."/browse/topSales/-1"}}><button class="border-2 mr-2 w-72 h-16 lg:h-10 lg:w-48 rounded-md border-blue-300 text-blue-100 font-bold bg-blue-500 text-3xl lg:text-base">Top Sales</button></a>

        <a href={{$prepend."/browse/recentlyChanged/-1"}}><button class="border-2 mr-2 w-72 h-16 lg:h-10 lg:w-48 rounded border-green-500 text-green-100 font-bold bg-green-500 text-3xl lg:text-base">Recently Changed</button></a>

        <a href={{$prepend."/browse/recentlyAdded/-1"}}><button class="border-2 mr-2 w-72 h-16 lg:h-10 lg:w-48 rounded border-yellow-100 text-yellow-100 font-bold bg-yellow-500 text-3xl lg:text-base">Recently Added</button></a>

    </div>
</div>
