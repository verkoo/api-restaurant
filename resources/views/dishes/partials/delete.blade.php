 {!! Form::open(['url' => ["dishes/{$dish->id}"], 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar Plato</button>
{!! Form::close() !!}