<!DOCTYPE html>
<html lang="en" dir="ltr">

  <head>
    <meta charset="utf-8">
    <title>BestBuy Price Tracking</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800&display=swap" rel="stylesheet">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7853339352311833"
     crossorigin="anonymous"></script>
  </head>

  <body>

    <!-- Require our search bar here -->
    @include('header', ['prepend' => $prepend])
    <div class="mt-10"></div>
    @include('search_bar', ['prepend' => $prepend, "search_query" => ""])

    <div id="main_page_div" class="w-full lg:w-10/12 m-auto mt-4">


      <section id="introduction_section" class = "info_section mt-8 lg:mt-0">
        <h2 id="siteName" class="font-bold text-8xl lg:text-5xl text-center text-zinc-700 ">Price Catcher</h2>
        <h3 class="italic pt-2 pb-2 text-center text-3xl lg:text-lg text-gray-600 font-heavy border-t border-b mt-3">A website dedicated to tracking BestBuy prices.</h3>
      </section>

      {{-- <section class="text-center">
          {{$productsTracked}} total products being tracked.
      </section> --}}

      <section class = "text-2xl lg:text-xl :mt-5 w-10/12 mx-auto">
        <p>Welcome to a price tracking website for <a href="https://www.bestbuy.com/">BestBuy.com</a>. The price tracking began on Febuary 6th 2020 and
            has been tracking all prices since.</p>
        <h2 class ="text-2xl font-semibold mt-4 text-slate-800">Good for tracking. Bad for flash sales.</h2>
        <p class="mt-2"> The price tracking on this site doesn't immediately catch sale prices. It actually
          scans a number of times a day to find product that have recently changed thanks to BestBuy's
          detailed API. Therefore, flash sales might not be recorded and regular sales might have a delay
          to when they are recorded into the history data.
        </p>
      </section>

      {{-- {% import "listings.html" as listings %} --}}

      @if (count($recentlyAdded) > 0)
        <section id="recentlyViewedSection" class="w-10/12 mx-auto">
            <h2 class = "text-5xl lg:text-4xl mt-10 ml-4 mb-2">Recently Added Items</h2>
            <div id="recently_changed_list" class="preview_item_list flex flex-col lg:flex-row mx-auto place-items-center">
                @foreach ($recentlyAdded as $item)
                    <span class="ml-4 w-11/12 lg:w-1/3 mt-10 lg:mt-0">
                        @include('singleListing', ['item' => $item, 'size' => 'w-full'])
                    </span>
                @endforeach
            </div>
        </section>
      @endif


      @if (count($mostViewed) > 0)
        <section id="mostViewedSection" class="w-10/12 mx-auto">
            <h2 class = "text-5xl lg:text-4xl mt-10 ml-4 mb-2">Most Viewed Items</h2>
            <div id="mostViewedList" class="preview_item_list flex flex-col lg:flex-row mx-auto place-items-center">
                @foreach ($mostViewed as $item)
                    <span class="ml-4 w-11/12 lg:w-1/3 mt-10 lg:mt-0">
                        @include('singleListing', ['item' => $item, 'size' => 'w-full'])
                    </span>
                @endforeach
            </div>
        </section>
      @endif


      @if (count($recentlyChanged) > 0)
        <section id="recentlyChangedSection" class="w-10/12 mx-auto">
            <h2 class = "text-5xl lg:text-4xl mt-10 ml-4 mb-2">Recently Changed Items</h2>
            <div id="recentlyAddedList" class="preview_item_list flex flex-col lg:flex-row mx-auto place-items-center">
                @foreach ($recentlyChanged as $item)
                    <span class="ml-4 w-11/12 lg:w-1/3 mt-10 lg:mt-0">
                        @include('singleListing', ['item' => $item, 'size' => 'w-full'])
                    </span>
                @endforeach
            </div>
        </section>
      @endif



      {{-- @if most_recently_viewed|length > 0
        <section id="recently_viewed_section">
                <h2 class = "recent_section_title">Recently Viewed Items</h2>
                <div id="recently_changed_list" class="preview_item_list">
                {{ listings.output_listing(recently_viewed) }}
                </div>
        </section>
      @endif

      <section id="recently_changed_section">
        <h2 class = "recent_section_title">Recently Changed Items</h2>
        <div id="recently_changed_item_list"  class="preview_item_list">
          {{ listings.output_listing(recently_changed) }}
        </div>
      </section>

      <section id="recently_added_section">
        <h2 class = "recent_section_title">Recently Added Items</h2>
        <div id="recently_added_item_list"  class="preview_item_list">
          {{ listings.output_listing(recently_added) }}
        </div>
      </section>

      <section id="top_viewed_section">
        <h2 class = "recent_section_title">Top Viewed Items</h2>
        <div id="recently_added_item_list"  class="preview_item_list">
          {{ listings.output_listing(most_viewed) }}
        </div>
      </section> --}}
    </div>

    {{-- @include('footer') --}}
    @include('footer')

  </body>
</html>
