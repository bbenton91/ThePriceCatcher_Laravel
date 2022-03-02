<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>BestBuy Price Tracking</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  </head>
  <body>
    <div id="innerBody" class="browse-color">
      <!-- Require our search bar here -->
        @include('header', ['prepend' => $prepend])
        <div class="mt-8"></div>

        @include('search_bar', ['prepend' => $prepend, "search_query" => ""])

        <div class="flex place-content-center mt-4">
          <form id="departmentForm" class="margin-top-20" action="department">
              <label class="mr-4 font-semibold text-white" for="department">Department: </label>
              <select id = "department_list" name="department" onchange="this.form.submit()">
              <option value=-1>Any</option>

                @foreach ($departments as $dep)
                    @php
                        $name = strtolower($dep->name)
                    @endphp
                    @if ($selected == $dep->id)
                        <option class="selected-option" selected value = {{ $dep->id }}>{{$name}}</option>
                    @else
                        <option class="selected-option" value = {{$dep->id}}>{{$name}}</option>
                    @endif
                @endforeach

              {{-- {% for department in departmentArray %}
                  {% set name =  department['name']|lower|ucwords %}
                  {% if selected == department['id'] %}
                      <option class="selected-option" selected value = "{{department['id']}}">{{name}}</option>
                  {% else %}
                      <option class="selected-option" value = "{{department['id']}}">{{name}}</option>
                  {% endif %}
              {% endfor %} --}}
              </select>

          </form>
        </div>

        @if (count($products) > 0)
            <section id="productListings" class="w-10/12 mx-auto h-auto">
                <div id="recently_changed_list" class="preview_item_list flex flex-wrap place-content-around">
                    @foreach ($products as $item)
                        <span class="mt-10">
                            @include('singleListing', ['item' => $item])
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- {% include "listings_template.html" %} --}}

    </div>
    {{-- {% include "footer_template.html" %} --}}
  </body>
</html>
