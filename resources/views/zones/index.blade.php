@include('layouts.crud_index', [
    'name' => 'Zonas',
    'button' => 'Nueva Zona',
    'route' => 'zones',
    'items' => $zones,
])
