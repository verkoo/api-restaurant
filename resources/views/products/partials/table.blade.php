<table id="results" class="table table-striped is-fullwidth">
    <tr>
        <th>Categoria</th>
        <th>Producto</th>
        <th>Acciones</th>
    </tr>

    @foreach ($products as $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $item->category }}</td>
            <td>{{ $item->name }}</td>
            <td>
                <i class="fa fa-edit" aria-hidden="true"></i>&nbsp;<a href="{{ url("products/{$item->id}/edit") }}" id="edit_link">Editar</a>
                <i class="fa fa-leaf" aria-hidden="true"></i>&nbsp;<a href="{{ url("products/{$item->id}/allergens") }}" id="edit_link">Alérgenos</a>
                <i class="fa fa-user" aria-hidden="true"></i>&nbsp;<a href="{{ url("products/{$item->id}/customers") }}" id="edit_link">Precio Clientes</a>
                <i class="fa fa-pagelines" aria-hidden="true"></i>&nbsp;<a href="{{ url("products/{$item->id}/extras") }}" id="edit_link">Extras</a>
                @can('destroy', $item)
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;<a href="" class="btn-delete">Borrar</a>
                @endcan
            </td>
        </tr>
    @endforeach
</table>