<?php

namespace App\Http\Controllers;

use App\Entities\Dish;
use Illuminate\Http\Request;


class DishesController extends Controller
{
    public function index()
    {
        $dishes = Dish::orderBy('priority', 'ASC')->paginate();
        
        return view('dishes.index', compact('dishes'));
    }

    public function create()
    {
        $dish = new Dish();
        
        return view('dishes.create', compact('dish'));
    }

    public function edit (Dish $dish)
    {
        return view('dishes.edit', compact('dish'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        Dish::create($request->all());

        return redirect('dishes');
    }

    public function update (Dish $dish, Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        $attributes = $request->all();

        if ( !$request->has('pass')) {
            $attributes["pass"] = 0;
        }

        $dish->update($attributes);

        return redirect('dishes');
    }

    public function destroy (Dish $dish)
    {
        $dish->delete();
        
        return redirect('dishes');
    }
}
