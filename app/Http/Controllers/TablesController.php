<?php

namespace App\Http\Controllers;

use App\Entities\Table;
use App\Entities\Zone;
use Illuminate\Http\Request;

use App\Http\Requests;

class TablesController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('name', 'ASC')->paginate();
        return view('tables.index', compact('tables'));
    }

    public function create()
    {
        $zones = Zone::all()->pluck('name', 'id');
        return view('tables.create', compact('zones'));
    }

    public function edit (Table $table)
    {
        $zones = Zone::all()->pluck('name', 'id');
        return view('tables.edit', compact('table', 'zones'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'zone_id' => 'required'
        ]);
        Table::create($request->all());

        return redirect('tables');
    }

    public function update (Table $table, Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'zone_id' => 'required'
        ]);

        $table->update($request->all());

        return redirect('tables');
    }

    public function destroy (Table $table)
    {
        $table->delete();

        return redirect('tables');
    }
}
