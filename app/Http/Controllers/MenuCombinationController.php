<?php

namespace App\Http\Controllers;

use App\Entities\Menu;
use Illuminate\Http\Request;

use App\Http\Requests;

class MenuCombinationController extends Controller
{
    public function store(Menu $menu, Request $request)
    {
        $this->validate($request, [
            'combination_id' => 'required',
            'price' => 'required'
        ]);

        $menu->addCombinationWithPrice($request->combination_id, $request->price);

        return redirect("menus/{$menu->id}/edit");
    }

    public function destroy(Menu $menu, $combination)
    {
        $menu->combinations()->detach($combination);

        return redirect("menus/{$menu->id}/edit");
    }
}
