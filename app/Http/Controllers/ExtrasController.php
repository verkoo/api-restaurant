<?php

namespace App\Http\Controllers;

use App\Entities\Extra;
use Illuminate\Http\Request;

use App\Http\Requests;

class ExtrasController extends Controller
{
    public function index()
    {
        $extras = Extra::orderBy('name', 'ASC')->paginate();

        return view('extras.index', compact('extras'));
    }

    public function create()
    {
        $extra = new Extra();

        return view('extras.create', compact('extra'));
    }

    public function edit (Extra $extra)
    {
        return view('extras.edit', compact('extra'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'price' => 'required'
        ]);

        Extra::create($request->all());

        return redirect('extras');
    }

    public function update (Extra $extra, Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'price' => 'required'
        ]);

        $extra->update($request->all());

        return redirect('extras');
    }

    public function destroy (Extra $extra)
    {
        $extra->delete();

        return redirect('extras');
    }
}
