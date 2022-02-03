<?php

use App\Http\Controllers\BrowseController;
use App\Http\Controllers\HomeController;
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
    return redirect("/browse/topsales/-1");
});
Route::get('/browse/topsales/{depId}', [BrowseController::class, 'showTopSales']);
Route::get('/browse/recentlychanged/{depId}', [BrowseController::class, 'showRecentlyChanged']);
Route::get('/browse/recentlyadded/{depId}', [BrowseController::class, 'showRecentlyAdded']);

// Route::get('/', function () {
//     return view('welcome');
// });
