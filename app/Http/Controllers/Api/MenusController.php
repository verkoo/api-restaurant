<?php

namespace App\Http\Controllers\Api;

use App\Entities\Menu;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MenusController extends Controller
{
    public function index()
    {
        $menus = Menu::whereActive(true)->get();
        
        return response()->json($menus);
    }
}
