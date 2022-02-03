<?php

namespace App\Http\Controllers;

use App\Models\RecentlyChanged;
use Illuminate\Http\Request;

class RecentlyChangedController extends Controller
{
    public function getRecentlyChanged(int $limit){
        return RecentlyChanged::all()->random()->limit($limit);
    }
}
