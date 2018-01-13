<?php

namespace App\Http\Controllers;

use App\Entities\Kitchen;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;

class KitchensController extends Controller
{
    public function index()
    {
        $kitchens = Kitchen::orderBy('name', 'ASC')->paginate();
        return view('kitchens.index', compact('kitchens'));
    }

    public function create()
    {
        return view('kitchens.create');
    }

    public function edit (Kitchen $kitchen)
    {
        return view('kitchens.edit', compact('kitchen'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);
        
        Kitchen::create($request->all());

        return redirect('kitchens');
    }

    public function update (Kitchen $kitchen, Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        $kitchen->update($request->all());

        return redirect('kitchens');
    }

    public function destroy (Kitchen $kitchen)
    {
        try {
            $kitchen->delete();
        } catch ( QueryException $e) {
            return redirect("kitchens/{$kitchen->id}/edit")->withErrors([
                'error' => 'No es posible eliminar el regitro. Es posible que esté siendo usado en algún pedido.'
            ]);
        }

        return redirect('kitchens');
    }
}
