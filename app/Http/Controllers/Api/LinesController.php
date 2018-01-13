<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use Illuminate\Http\Request;

class LinesController extends ApiController
{
    public function store(Request $request)
    {
        $this->validate($request,[
            'id'         => 'required',
            'type'       => 'required',
            'quantity'   => 'required',
            'price'      => 'required'
        ]);

        $document = getAssociatedModel($request->type, $request->id);

        $line = new Line($request->all());

        $document->lines()->save($line);

        return $this->respond($line);
    }

    public function update(Request $request, Line $line)
    {
        $this->validate($request,[
            'quantity'   => 'required',
            'price'      => 'required'
        ]);

        $line->update($request->all());

        return $this->respond([
            'line' => $line,
            'product' => $line->product
        ]);
    }

    public function destroy(Line $line)
    {
        $line->delete();
        return $this->respond([
            'success' => true,
            'product' => $line->product
        ]);
    }
}
