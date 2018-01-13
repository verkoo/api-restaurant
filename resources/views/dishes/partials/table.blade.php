<table id="results" class="table table-striped">
    <tr>
        <th>Orden</th>
        <th>Plato</th>
        <th>Acciones</th>
    </tr>

    @foreach ($dishes as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->priority }}</td>
            <td>{{ $item->name }}</td>
            <td>
                <span class="fa fa-edit" aria-hidden="true"></span>&nbsp;<a href="{{ url("dishes/{$item->id}/edit") }}" id="edit_link">Editar</a>
            </td>
        </tr>
    @endforeach
</table>