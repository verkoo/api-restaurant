<?php

namespace App\Http\Controllers;

use App\Entities\Menu;
use App\Entities\Combination;
use App\Http\Requests\MenuRequest;
use Verkoo\Common\Entities\Tax;

class MenusController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('name', 'ASC')->paginate();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $taxes = Tax::all()->pluck('name', 'id')->toArray();
        $menu = new Menu();
        return view('menus.create', compact('menu', 'taxes'));
    }

    public function edit (Menu $menu)
    {
        $taxes = Tax::all()->pluck('name', 'id')->toArray();
        $combinations = Combination::all()->pluck('name', 'id')->toArray();
        $menu->load('combinations');

        return view('menus.edit', compact('menu', 'combinations', 'taxes'));
    }

    public function store(MenuRequest $request)
    {
        $menu = Menu::create($request->all());

        return redirect("menus/{$menu->id}/edit");
    }

    public function update (Menu $menu, MenuRequest $request)
    {
        $menu->update($request->all());

        return redirect('menus');
    }

    public function destroy (Menu $menu)
    {
        $menu->delete();
        return redirect('menus');
    }
}

