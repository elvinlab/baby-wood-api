@component('mail::message')
# Baby Wood: Restablecer contraseña

Restablezca o cambie su contraseña.

@component('mail::button', ['url' => 'http://localhost:4200/cambiar-password?token='.$token])
Cambiar contraseña
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent