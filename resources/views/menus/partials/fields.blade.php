<p class="control has-top-padding">
    {!! Form::label('name', 'Nombre', ['class' => 'label']) !!}
    {!! Form::text('name', null, ['class' => 'input', 'id' => 'name']) !!}
</p>
<p class="control has-top-padding">
    {!! Form::label('tax_id', 'Tipo de Iva', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('tax_id', [ '' => '(Utilizar Iva por defecto)' ] + $taxes) !!}
    </span>
</p>
<p class="control has-top-padding">
    {!! Form::checkbox('active', 1, $menu->active) !!} Activo
</p>
<p class="control has-top-padding">
    {!! Form::checkbox('salad', 1, $menu->salad) !!} Incluye Ensalada
</p>
<p class="control has-top-padding">
    {!! Form::checkbox('bread', 1, $menu->bread) !!} Incluye Pan
</p>
<p class="control has-top-padding">
    {!! Form::label('description', 'Descripción', ['class' => 'label']) !!}
    {!! Form::textarea('description', null, ['class' => 'textarea']) !!}
</p>