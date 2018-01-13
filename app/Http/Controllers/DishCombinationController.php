<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Combination;


class DishCombinationController extends Controller
{
    public function store(Combination $combination, Request $request)
    {
        $this->validate($request, [
            'dish_id' => 'required',
            'quantity' => 'required|numeric'
        ]);

        $combination->addDish($request->dish_id, $request->quantity);

        return redirect("combinations/{$combination->id}/edit");
    }

    public function destroy(Combination $combination, $dish)
    {
        $combination->dishes()->detach($dish);

        return redirect("combinations/{$combination->id}/edit");
    }
}
