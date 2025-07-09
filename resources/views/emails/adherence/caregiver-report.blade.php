<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Adherencia - {{ $paciente['nombre'] }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #fff;
            padding: 30px;
            border: 1px solid #e1e5e9;
            border-top: none;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e1e5e9;
            border-top: none;
            font-size: 14px;
            color: #6c757d;
        }
        .metric-card {
            background: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .metric-card.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .metric-card.danger {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .metric-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #17a2b8;
        }
        .metric-value.warning {
            color: #856404;
        }
        .metric-value.danger {
            color: #721c24;
        }
        .care-tips {
            background: #e7f3ff;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .medication-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        .alert-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .emoji { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><span class="emoji">👩‍⚕️</span>Reporte de Cuidado - {{ $paciente['nombre'] }}</h1>
        <p>{{ $reporte['periodo']['descripcion'] }}</p>
    </div>

    <div class="content">
        <h2>Estimado/a Cuidador/a 👋</h2>
        <p>Este reporte te ayudará a brindar el mejor cuidado a <strong>{{ $paciente['nombre'] }}</strong>.</p>

        <!-- Adherencia General -->
        <div class="metric-card @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
            <div class="metric-title">
                <span class="emoji">📊</span>Adherencia al Tratamiento
            </div>
            <div class="metric-value @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
                {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                {{ $paciente['nombre'] }} ha tomado {{ $reporte['adherencia_general']['dosis_administradas'] }} de {{ $reporte['adherencia_general']['total_dosis'] }} dosis programadas
            </p>
            @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90)
                <p style="color: #28a745; font-weight: bold;">¡Excelente trabajo cuidando a {{ $paciente['nombre'] }}! 🌟</p>
            @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70)
                <p style="color: #856404; font-weight: bold;">Buen progreso. Con pequeños ajustes podemos llegar al 90%. 👍</p>
            @else
                <p style="color: #dc3545; font-weight: bold;">Se necesita más atención en el seguimiento de medicamentos. 🚨</p>
            @endif
        </div>

        <!-- Medicamentos que Requieren Atención -->
        @if(isset($reporte['medicamentos_detalle']) && !empty($reporte['medicamentos_detalle']))
        <h3><span class="emoji">💊</span>Medicamentos Bajo tu Cuidado</h3>
        @foreach($reporte['medicamentos_detalle'] as $medicamento)
        <div class="medication-item">
            <h4 style="margin: 0 0 10px 0; color: #17a2b8;">{{ $medicamento['nombre'] }}</h4>
            <p style="margin: 5px 0; color: #6c757d;"><strong>Dosis:</strong> {{ $medicamento['dosis'] }} cada {{ $medicamento['frecuencia'] }}</p>
            <p style="margin: 5px 0;">
                <span style="font-weight: bold; color: @if($medicamento['adherencia_porcentaje'] >= 90) #28a745 @elseif($medicamento['adherencia_porcentaje'] >= 70) #ffc107 @else #dc3545 @endif;">
                    Adherencia: {{ $medicamento['adherencia_porcentaje'] }}%
                </span>
                ({{ $medicamento['dosis_administradas'] }}/{{ $medicamento['total_dosis'] }} dosis)
            </p>
            @if($medicamento['adherencia_porcentaje'] < 80)
                <p style="background: #fff3cd; padding: 8px; border-radius: 4px; margin: 10px 0; font-size: 14px;">
                    <strong>💡 Consejo:</strong> Considera establecer alarmas o recordatorios para este medicamento.
                </p>
            @endif
        </div>
        @endforeach
        @endif

        <!-- Consejos de Cuidado -->
        <div class="care-tips">
            <h3 style="margin-top: 0; color: #0c5460;"><span class="emoji">💡</span>Consejos de Cuidado</h3>
            
            @if($reporte['adherencia_general']['adherencia_porcentaje'] < 80)
            <div style="margin: 15px 0;">
                <strong>🔔 Recordatorios:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Establece alarmas en el teléfono para cada medicamento</li>
                    <li>Usa un pastillero semanal para organizar las dosis</li>
                    <li>Coloca notas recordatorio en lugares visibles</li>
                </ul>
            </div>
            @endif

            @if($reporte['adherencia_general']['dosis_tardias'] > 0)
            <div style="margin: 15px 0;">
                <strong>⏰ Puntualidad:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Trata de administrar los medicamentos a la misma hora cada día</li>
                    <li>Si hay retraso, anota la hora real de administración</li>
                    <li>Consulta al médico sobre ventanas de tolerancia</li>
                </ul>
            </div>
            @endif

            <div style="margin: 15px 0;">
                <strong>👀 Observación:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Observa efectos secundarios o reacciones adversas</li>
                    <li>Anota cualquier cambio en el estado del paciente</li>
                    <li>Comunica inmediatamente cualquier preocupación al equipo médico</li>
                </ul>
            </div>
        </div>

        <!-- Alertas Importantes -->
        @if(isset($reporte['alertas']) && !empty($reporte['alertas']))
        <div class="alert-section">
            <h3 style="margin-top: 0;"><span class="emoji">🚨</span>Alertas Importantes</h3>
            @foreach($reporte['alertas'] as $alerta)
            <div style="background: white; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 0 5px 5px 0;">
                <strong>{{ $alerta['tipo'] }}</strong><br>
                <small style="color: #6c757d;">{{ $alerta['fecha'] }}</small><br>
                <span style="margin-top: 5px; display: block;">{{ $alerta['mensaje'] }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Contacto de Emergencia -->
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #721c24;"><span class="emoji">📞</span>¿Tienes Dudas o Emergencias?</h3>
            <p style="margin: 10px 0;">Si tienes preguntas sobre el cuidado de {{ $paciente['nombre'] }} o notas algo preocupante:</p>
            <ul style="margin: 10px 0 0 20px;">
                <li><strong>Contacta al equipo médico inmediatamente</strong></li>
                <li>Usa la aplicación MediTrack para reportar síntomas</li>
                <li>En emergencias, llama al número de emergencia local</li>
            </ul>
        </div>

        <!-- Motivación -->
        <div style="background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0;">
            <h3>¡Tu Cuidado Hace la Diferencia! 💙</h3>
            <p>Gracias por dedicarte al bienestar de {{ $paciente['nombre'] }}. Tu atención y cuidado son fundamentales para su recuperación y salud.</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>MediTrack</strong> - Apoyando a cuidadores como tú</p>
        <p>Reporte generado el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p style="font-size: 12px; margin-top: 15px;">
            Para paciente: {{ $paciente['nombre'] }}<br>
            Si tienes preguntas, contacta al equipo médico a través de la aplicación.
        </p>
    </div>
</body>
</html> 