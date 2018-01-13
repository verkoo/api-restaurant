<table id="results" class="table table-striped">
    <tr>
        <th>#</th>
        <th>Extra</th>
        <th>Incremento</th>
        <th>Acciones</th>
    </tr>

    @foreach ($extras as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->price }}</td>
            <td>
                <span class="fa fa-edit" aria-hidden="true"></span>&nbsp;<a href="{{ url("extras/{$item->id}/edit") }}" id="edit_link">Editar</a>
            </td>
        </tr>
    @endforeach
</table>