<p class="control has-top-padding">
    {!! Form::label('name', 'Nombre', ['class' => 'label']) !!}
    {!! Form::text('name', null, ['class' => 'input', 'id' => 'name']) !!}
</p>
<p class="control has-top-padding">
    {!! Form::label('zone_id', 'Zona', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('zone_id', $zones) !!}
    </span>
</p>