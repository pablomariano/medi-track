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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 4px solid #28a745;
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
            color: #28a745;
        }
        .metric-value.warning {
            color: #856404;
        }
        .metric-value.danger {
            color: #721c24;
        }
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: #28a745;
            transition: width 0.3s ease;
        }
        .progress-fill.warning {
            background: #ffc107;
        }
        .progress-fill.danger {
            background: #dc3545;
        }
        .medication-list {
            margin: 20px 0;
        }
        .medication-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        .recommendation {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .recommendation.high {
            background: #fff2e7;
            border-left-color: #ff6600;
        }
        .alert-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .alert-info { background: #d1ecf1; color: #0c5460; }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .emoji { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><span class="emoji">📊</span>Tu Reporte de Adherencia</h1>
        <p>{{ $reporte['periodo']['descripcion'] }}</p>
    </div>

    <div class="content">
        <h2>¡Hola {{ $paciente['nombre'] }}! 👋</h2>
        <p>Aquí tienes un resumen de cómo has estado siguiendo tu tratamiento.</p>

        <!-- Adherencia General -->
        <div class="metric-card @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) success @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
            <div class="metric-title">
                <span class="emoji">🎯</span>Adherencia General
            </div>
            <div class="metric-value @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
                {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif" 
                     style="width: {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%"></div>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                Has tomado {{ $reporte['adherencia_general']['dosis_administradas'] }} de {{ $reporte['adherencia_general']['total_dosis'] }} dosis programadas
            </p>
        </div>

        <!-- Puntualidad -->
        <div class="metric-card @if($reporte['adherencia_general']['puntualidad_porcentaje'] >= 80) success @elseif($reporte['adherencia_general']['puntualidad_porcentaje'] >= 60) warning @else danger @endif">
            <div class="metric-title">
                <span class="emoji">⏰</span>Puntualidad
            </div>
            <div class="metric-value @if($reporte['adherencia_general']['puntualidad_porcentaje'] >= 80) @elseif($reporte['adherencia_general']['puntualidad_porcentaje'] >= 60) warning @else danger @endif">
                {{ $reporte['adherencia_general']['puntualidad_porcentaje'] }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill @if($reporte['adherencia_general']['puntualidad_porcentaje'] >= 80) @elseif($reporte['adherencia_general']['puntualidad_porcentaje'] >= 60) warning @else danger @endif" 
                     style="width: {{ $reporte['adherencia_general']['puntualidad_porcentaje'] }}%"></div>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                @if($reporte['adherencia_general']['dosis_tardias'] > 0)
                    {{ $reporte['adherencia_general']['dosis_tardias'] }} dosis fueron tomadas fuera de horario
                @else
                    ¡Todas tus dosis fueron tomadas en horario!
                @endif
            </p>
        </div>

        <!-- Tendencias -->
        @if(isset($reporte['tendencias']) && !empty($reporte['tendencias']))
        <div class="metric-card">
            <div class="metric-title">
                <span class="emoji">📈</span>Tu Progreso
            </div>
            @if($reporte['tendencias']['mejora_adherencia'])
                <p style="color: #28a745;"><strong>¡Excelente!</strong> Tu adherencia ha mejorado {{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }} puntos respecto al período anterior.</p>
            @elseif($reporte['tendencias']['adherencia_cambio'] < -5)
                <p style="color: #dc3545;"><strong>Atención:</strong> Tu adherencia ha bajado {{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }} puntos. ¡Podemos mejorar!</p>
            @else
                <p style="color: #6c757d;">Tu adherencia se mantiene estable respecto al período anterior.</p>
            @endif
        </div>
        @endif

        <!-- Medicamentos Detalle -->
        @if(isset($reporte['medicamentos_detalle']) && !empty($reporte['medicamentos_detalle']))
        <h3><span class="emoji">💊</span>Detalles por Medicamento</h3>
        <div class="medication-list">
            @foreach($reporte['medicamentos_detalle'] as $medicamento)
            <div class="medication-item">
                <h4 style="margin: 0 0 10px 0;">{{ $medicamento['nombre'] }}</h4>
                <p style="margin: 5px 0; color: #6c757d;">{{ $medicamento['dosis'] }} cada {{ $medicamento['frecuencia'] }}</p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $medicamento['dosis_administradas'] }}/{{ $medicamento['total_dosis'] }} dosis</span>
                    <span style="font-weight: bold; color: @if($medicamento['adherencia_porcentaje'] >= 90) #28a745 @elseif($medicamento['adherencia_porcentaje'] >= 70) #ffc107 @else #dc3545 @endif;">
                        {{ $medicamento['adherencia_porcentaje'] }}%
                    </span>
                </div>
                <div class="progress-bar" style="margin-top: 10px;">
                    <div class="progress-fill @if($medicamento['adherencia_porcentaje'] >= 90) @elseif($medicamento['adherencia_porcentaje'] >= 70) warning @else danger @endif" 
                         style="width: {{ $medicamento['adherencia_porcentaje'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Recomendaciones -->
        @if(isset($reporte['recomendaciones']) && !empty($reporte['recomendaciones']))
        <h3><span class="emoji">💡</span>Recomendaciones para Ti</h3>
        @foreach($reporte['recomendaciones'] as $recomendacion)
        <div class="recommendation @if($recomendacion['prioridad'] === 'alta') high @endif">
            <strong>{{ ucfirst($recomendacion['tipo']) }}:</strong>
            {{ $recomendacion['mensaje'] }}
        </div>
        @endforeach
        @endif

        <!-- Alertas Activas -->
        @if(isset($reporte['alertas']) && !empty($reporte['alertas']))
        <h3><span class="emoji">🚨</span>Alertas Recientes</h3>
        @foreach($reporte['alertas'] as $alerta)
        <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #ffc107;">
            <span class="alert-badge alert-{{ strtolower($alerta['nivel']) }}">{{ $alerta['nivel'] }}</span>
            <strong>{{ $alerta['tipo'] }}</strong><br>
            <small>{{ $alerta['fecha'] }}</small><br>
            {{ $alerta['mensaje'] }}
        </div>
        @endforeach
        @endif

        <!-- Motivación -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0;">
            @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90)
                <h3>¡Excelente trabajo! 🌟</h3>
                <p>Tu adherencia es excepcional. ¡Sigue así!</p>
            @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70)
                <h3>¡Buen trabajo! 👍</h3>
                <p>Vas por buen camino. Con pequeños ajustes puedes llegar al 90%.</p>
            @else
                <h3>¡Podemos mejorar juntos! 💪</h3>
                <p>Cada dosis cuenta. Habla con tu médico sobre estrategias para mejorar tu adherencia.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p><strong>MediTrack</strong> - Tu salud, nuestro compromiso</p>
        <p>Reporte generado el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p style="font-size: 12px; margin-top: 15px;">
            Si tienes preguntas sobre este reporte, contacta a tu equipo médico.<br>
            Este correo fue enviado automáticamente, por favor no respondas a esta dirección.
        </p>
    </div>
</body>
</html> 