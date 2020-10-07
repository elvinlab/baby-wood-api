<DOCTYPE html>
   <html lang="en-US">
     <head>
     <meta charset="utf-8">
     </head>
     <body>
     <h2>Hola {{$data['name']}}, Te damos la bienvenida a nuestra familia Baby Wood, los detalles de tu cuenta son: <br>
</h3>
<h3>Email: </h3><p>{{$data['email']}}</p>
<h3>emal: </h3><p>El apellido</p>
<h3>telefono: </h3><p>123456</p>

Gracias,<br>
{{ config('app.name') }}
</body>
</html>