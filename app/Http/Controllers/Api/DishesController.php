<?php

namespace App\Http\Controllers\Api;

use App\Entities\Dish;
use App\Http\Requests;

class DishesController extends ApiController
{
    public function index() {
        $dishes = Dish::orderBy('name')->get()->toArray();
        return response()->json($dishes,200);
    }
}
