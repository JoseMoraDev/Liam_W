<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Recuperación de contraseña</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      background-color: #f7f7f7;
      color: #333;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .header {
      background-color: #2a6fd0;
      color: #fff;
      text-align: center;
      padding: 20px;
    }
    .content {
      padding: 30px;
      line-height: 1.6;
    }
    .button {
      display: inline-block;
      background-color: #2a6fd0;
      color: white;
      padding: 12px 24px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
    .footer {
      text-align: center;
      font-size: 12px;
      color: #999;
      padding: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Recuperar tu contraseña</h1>
    </div>
    <div class="content">
      <p>Hola,</p>
      <p>Has solicitado restablecer tu contraseña en <strong>Live Ambience Weather & Traffic</strong>.</p>
      <p>Para continuar, haz clic en el siguiente botón:</p>
      <p style="text-align: center;">
        <a href="{{ $resetUrl }}" class="button">Cambiar contraseña</a>
      </p>
      <p>Si no has solicitado este cambio, simplemente ignora este mensaje.</p>
      <p>Este enlace expirará en 60 minutos por motivos de seguridad.</p>
    </div>
    <div class="footer">
      © {{ date('Y') }} Live Ambience. Todos los derechos reservados.
    </div>
  </div>
</body>
</html>
