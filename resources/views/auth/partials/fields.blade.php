<p class="control has-top-padding">
    <label for="name" class="label">Nombre</label>
    {!! Form::text('name', null, ['class' => 'input', 'id' => 'name']) !!}
</p>

<p class="control has-top-padding">
    <label for="email" class="label">E-Mail</label>
    {!! Form::text('email', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    <label for="role" class="label">Rol</label>
    <span class="select">
        {!! Form::select('role', $roles) !!}
    </span>
</p>

<p class="control has-top-padding">
    <label for="username" class="col-md-4 control-label">Usuario</label>
    {!! Form::text('username', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    <label for="password" class="label">Contraseña</label>
    <input id="password" type="password" class="input" name="password">
</p>

<p class="control has-top-padding">
    <label for="password-confirm" class="label">Confirmar Contraseña</label>
    <input id="password-confirm" type="password" class="input" name="password_confirmation">
</p>