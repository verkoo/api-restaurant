@include('layouts.crud_index', [
    'name' => 'Extras de Producto',
    'button' => 'Nuevo Extra',
    'route' => 'extras',
    'items' => $extras,
])
