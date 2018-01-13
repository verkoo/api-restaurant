@include('layouts.crud_index', [
    'name' => 'Platos del Menú',
    'button' => 'Nuevo Plato',
    'route' => 'dishes',
    'items' => $dishes,
])