 {!! Form::open(['url' => ["extras/{$extra->id}"], 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar Extra</button>
{!! Form::close() !!}