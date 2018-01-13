<?php

namespace App\Http\Controllers\Api;

use App\Entities\Zone;

class ZonesController extends ApiController
{
    public function index()
    {
        return Zone::all();
    }

    public function show(Zone $zone)
    {
        return $this->respond($zone->tables);
    }
}
