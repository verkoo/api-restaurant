<table id="results" class="table table-striped">
    <tr>
        <th>#</th>
        <th>Combinaciones</th>
        <th>Acciones</th>
    </tr>

    @foreach ($combinations as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>
                <span class="fa fa-edit" aria-hidden="true"></span>&nbsp;<a href="{{ url("combinations/{$item->id}/edit") }}" id="edit_link">Editar</a>
            </td>
        </tr>
    @endforeach
</table>