 {!! Form::open(['url' => ["combinations/{$combination->id}"], 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar Combinación</button>
{!! Form::close() !!}