<?php

use App\Http\Controllers\BrowseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'show']);

Route::get('/browse', function() {
    return redirect("/browse/topSales/-1");
});

Route::get('/search', function(Request $request) {
    $searchQuery = $request->input('query');
    return redirect()->route('search', ['searchQuery' => $searchQuery]);
});

Route::get('/result/{id}', [ResultController::class, 'show']);

Route::controller(BrowseController::class)->group(function(){
    Route::get('/browse/topSales/{depID}', 'showTopSales');
    Route::get('/browse/recentlyChanged/{depID}', 'showRecentlyChanged');
    Route::get('/browse/recentlyAdded/{depID}', 'showRecentlyAdded');
    Route::get('/search/{searchQuery}', 'showSearch')->name('search');
});
