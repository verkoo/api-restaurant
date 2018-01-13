 {!! Form::open(['url' => "zones/{$zone->id}", 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar Zona</button>
{!! Form::close() !!}