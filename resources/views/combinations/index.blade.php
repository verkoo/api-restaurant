@include('layouts.crud_index', [
    'name' => 'Combinaciones de Menú',
    'button' => 'Nueva Combinación',
    'route' => 'combinations',
    'items' => $combinations,
])