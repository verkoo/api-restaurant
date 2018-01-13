<table id="results" class="table table-striped">
    <tr>
        <th>#</th>
        <th>Menú</th>
        <th>Acciones</th>
    </tr>

    @foreach ($menus as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>
                <span class="fa fa-edit" aria-hidden="true"></span>&nbsp;<a href="{{ url("menus/{$item->id}/edit") }}" id="edit_link">Editar</a>&nbsp;
                <span class="fa fa-cutlery" aria-hidden="true"></span>&nbsp;<a href="{{ url("menus/{$item->id}/products") }}">Productos</a>
            </td>
        </tr>
    @endforeach
</table>