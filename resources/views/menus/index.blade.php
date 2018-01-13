@include('layouts.crud_index', [
    'name' => 'Menús',
    'button' => 'Nuevo Menú',
    'route' => 'menus',
    'items' => $menus,
])
