<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Entities\Menu;
use App\Entities\DishMenu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuDishesController extends Controller
{
    public function index(Menu $menu)
    {
        return $menu->dishes;
    }

    public function store(Menu $menu, Request $request)
    {
        $dishMenu = new DishMenu($request->only('dish_id'));

        $menu->dishes()->save($dishMenu);
        
        return response()->json($menu->dishes);
    }

    public function destroy(Menu $menu, DishMenu $dish)
    {
        $dish->delete();

        return response()->json($menu->dishes);
    }
}
