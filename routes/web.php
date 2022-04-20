<?php

use App\Http\Controllers\BrowseController;
use App\Http\Controllers\EmailSubscribeController;
use App\Http\Controllers\FeedbackController;
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

// Route::get('/email_subscribe', [EmailSubscribeController::class, 'store']);
Route::post('/email_subscribe', [EmailSubscribeController::class, 'store'])->name('email_subscribe.post');
Route::post('/send_feedback', [FeedbackController::class, 'send'])->name('send_feedback.post');

Route::get('/', [HomeController::class, 'show']);

Route::get('/browse', function() {
    return redirect("/browse/topSales/-1");
});

Route::get('/search', function(Request $request) {
    $searchQuery = $request->input('query');
    return redirect()->route('search', ['searchQuery' => $searchQuery]);
});

Route::get('/browse/topSales/dep', function(Request $request) {
    $dep = $request->input('department');
    return redirect()->route('topSales', ['depID' => $dep]);
});

Route::get('/browse/recentlyChanged/dep', function(Request $request) {
    $dep = $request->input('department');
    return redirect()->route('recentlyChanged', ['depID' => $dep]);
});

Route::get('/browse/recentlyAdded/dep', function(Request $request) {
    $dep = $request->input('department');
    return redirect()->route('recentlyAdded', ['depID' => $dep]);
});

Route::get('/result/{id}', [ResultController::class, 'show']);

Route::controller(BrowseController::class)->group(function(){
    Route::get('/browse/topSales/{depID}', 'showTopSales')->name('topSales');
    Route::get('/browse/recentlyChanged/{depID}', 'showRecentlyChanged')->name('recentlyChanged');
    Route::get('/browse/recentlyAdded/{depID}', 'showRecentlyAdded')->name('recentlyAdded');
    Route::get('/search/{searchQuery}', 'showSearch')->name('search');
});
