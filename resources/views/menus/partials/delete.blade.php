 {!! Form::open(['url' => ["menus/{$menu->id}"], 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar Menú</button>
{!! Form::close() !!}