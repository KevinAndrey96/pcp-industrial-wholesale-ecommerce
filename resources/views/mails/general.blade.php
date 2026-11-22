<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notificación PCP Industrial' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #334155;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #0056b3;
            color: #ffffff;
            padding: 24px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .email-header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
            font-size: 15px;
            line-height: 1.6;
        }
        .email-body h2 {
            color: #0f172a;
            font-size: 18px;
            margin-top: 0;
        }
        .email-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>PCP INDUSTRIAL</h1>
            <p>Plataforma B2B de Distribución y Abastecimiento</p>
        </div>
        <div class="email-body">
            @if(isset($subject))
                <h2>{{ $subject }}</h2>
            @endif
            <div>
                {!! $text !!}
            </div>
        </div>
        <div class="email-footer">
            <p>Este es un correo automático generado por la plataforma PCP Industrial Wholesale.</p>
            <p>&copy; {{ date('Y') }} PCP Plásticos. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
