@include('layouts.crud_index', [
    'name' => 'Cocinas',
    'button' => 'Nueva Cocina',
    'route' => 'kitchens',
    'items' => $kitchens,
])