<?php

namespace App\Http\Controllers;

use App\Entities\Dish;
use Illuminate\Http\Request;
use App\Entities\Combination;

use App\Http\Requests;

class CombinationsController extends Controller
{
    public function index()
    {
        $combinations = Combination::orderBy('name', 'ASC')->paginate();

        return view('combinations.index', compact('combinations'));
    }

    public function create()
    {
        $combination = new Combination();

        return view('combinations.create', compact('combination'));
    }

    public function edit (Combination $combination)
    {
        $dishes = Dish::all()->pluck('name', 'id')->toArray();
        $combination->load('dishes');
        
        return view('combinations.edit', compact('combination', 'dishes'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        Combination::create($request->all());

        return redirect('combinations');
    }

    public function update (Combination $combination, Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        $combination->update($request->all());

        return redirect('combinations');
    }

    public function destroy (Combination $combination)
    {
        $combination->delete();

        return redirect('combinations');
    }
}
