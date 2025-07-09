<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #374151;
        }
        .content {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white !important;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .security-info {
            background-color: #f3f4f6;
            border-left: 4px solid #fbbf24;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-info h3 {
            margin-top: 0;
            color: #92400e;
            font-size: 16px;
        }
        .security-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .security-info li {
            margin: 5px 0;
        }
        .help-section {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .help-section h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .url-fallback {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            h1 {
                font-size: 24px;
            }
            .button {
                display: block;
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏥 {{ config('app.name') }}</div>
        </div>

        <h1>Restablece tu contraseña</h1>

        <div class="greeting">
            Hola <strong>{{ $user->display_name }}</strong>,
        </div>

        <div class="content">
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>MediTrack</strong>.</p>
            
            <p>Si realizaste esta solicitud, haz clic en el botón de abajo para crear una nueva contraseña:</p>
        </div>

        <div class="button-container">
            <a href="{{ $url }}" class="button">Restablecer contraseña</a>
        </div>

        <div class="security-info">
            <h3>🔒 Por tu seguridad:</h3>
            <ul>
                <li>Este enlace expirará en <strong>{{ config('auth.passwords.users.expire') }} minutos</strong></li>
                <li>Si no realizaste esta solicitud, puedes ignorar este email</li>
                <li>Tu contraseña actual permanecerá sin cambios hasta que crees una nueva</li>
            </ul>
        </div>

        <div class="help-section">
            <h3>❓ ¿Tienes problemas?</h3>
            <p>Si no puedes hacer clic en el botón, copia y pega la siguiente URL en tu navegador:</p>
            <div class="url-fallback">{{ $url }}</div>
        </div>

        <div class="security-info">
            <h3>💡 Consejos de seguridad:</h3>
            <ul>
                <li>Usa una contraseña única y segura</li>
                <li>No compartas tu contraseña con nadie</li>
                <li>Cierra sesión al usar computadoras públicas</li>
            </ul>
        </div>

        <div class="content">
            <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar a nuestro equipo de soporte.</p>
        </div>

        <div class="footer">
            <p>Saludos cordiales,<br>
            <strong>El equipo de {{ config('app.name') }}</strong></p>
            
            <div class="footer-note">
                Este email fue enviado a {{ $user->email }} porque se solicitó un restablecimiento de contraseña para esta cuenta. Si no realizaste esta solicitud, tu cuenta sigue siendo segura.
            </div>
        </div>
    </div>
</body>
</html> 