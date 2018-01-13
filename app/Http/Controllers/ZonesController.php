<?php

namespace App\Http\Controllers;

use App\Entities\Zone;
use Illuminate\Http\Request;

use App\Http\Requests;

class ZonesController extends Controller
{
    public function index()
    {
        $zones = Zone::orderBy('name', 'ASC')->paginate();
        return view('zones.index', compact('zones'));
    }

    public function create()
    {
        return view('zones.create');
    }

    public function edit (Zone $zone)
    {
        return view('zones.edit', compact('zone'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);
        Zone::create($request->all());

        return redirect('zones');
    }

    public function update (Zone $zone, Request $request)
    {
        $this->validate($request,[
            'name' => 'required'
        ]);

        $zone->update($request->all());

        return redirect('zones');
    }

    public function destroy (Zone $zone)
    {
        $zone->delete();

        return redirect('zones');
    }
}
