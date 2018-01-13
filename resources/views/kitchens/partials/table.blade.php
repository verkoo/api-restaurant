<table id="results" class="table table-striped">
    <tr>
        <th>#</th>
        <th>Cocina</th>
        <th>Acciones</th>
    </tr>

    @foreach ($kitchens as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>
                <span class="fa fa-edit" aria-hidden="true"></span>&nbsp;<a href="{{ route('kitchens.edit', $item) }}" id="edit_link">Editar</a>
                {{--<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;<a href="" class="btn-delete">Borrar</a>--}}
            </td>
        </tr>
    @endforeach
</table>