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
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
            border-left: 4px solid #6c757d;
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
        .metric-card.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .metric-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #6c757d;
        }
        .metric-value.warning {
            color: #856404;
        }
        .metric-value.danger {
            color: #721c24;
        }
        .metric-value.success {
            color: #155724;
        }
        .info-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
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
        .emoji { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><span class="emoji">📊</span>Reporte de Adherencia - {{ $paciente['nombre'] }}</h1>
        <p>{{ $reporte['periodo']['descripcion'] }}</p>
    </div>

    <div class="content">
        <h2>Reporte de Tratamiento 📋</h2>
        <p>Este reporte contiene información sobre el seguimiento del tratamiento médico de <strong>{{ $paciente['nombre'] }}</strong>.</p>

        <!-- Información del Paciente -->
        <div class="info-section">
            <h3 style="margin-top: 0;"><span class="emoji">👤</span>Información del Paciente</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Nombre:</strong> {{ $paciente['nombre'] }}<br>
                    @if(isset($paciente['edad']))
                    <strong>Edad:</strong> {{ $paciente['edad'] }} años<br>
                    @endif
                    <strong>ID Paciente:</strong> {{ $paciente['id'] }}
                </div>
                <div>
                    <strong>Tratamientos Activos:</strong> {{ $paciente['tratamientos_activos'] ?? 0 }}<br>
                    <strong>Medicamentos:</strong> {{ $paciente['medicamentos_activos'] ?? 0 }}<br>
                    <strong>Período de Análisis:</strong> {{ $reporte['periodo']['dias'] }} días
                </div>
            </div>
        </div>

        <!-- Adherencia General -->
        <div class="metric-card @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) success @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
            <div class="metric-title">
                <span class="emoji">📈</span>Adherencia General al Tratamiento
            </div>
            <div class="metric-value @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) success @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
                {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                Se administraron {{ $reporte['adherencia_general']['dosis_administradas'] }} de {{ $reporte['adherencia_general']['total_dosis'] }} dosis programadas
            </p>
        </div>

        <!-- Métricas Adicionales -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0;">
            <div class="metric-card">
                <div class="metric-title">
                    <span class="emoji">⏰</span>Puntualidad
                </div>
                <div class="metric-value">
                    {{ $reporte['adherencia_general']['puntualidad_porcentaje'] }}%
                </div>
                <p style="font-size: 12px; margin: 5px 0 0 0;">
                    {{ $reporte['adherencia_general']['dosis_tardias'] }} dosis tardías
                </p>
            </div>

            <div class="metric-card @if($reporte['adherencia_general']['dosis_omitidas'] == 0) success @else danger @endif">
                <div class="metric-title">
                    <span class="emoji">⚠️</span>Dosis Omitidas
                </div>
                <div class="metric-value @if($reporte['adherencia_general']['dosis_omitidas'] == 0) success @else danger @endif">
                    {{ $reporte['adherencia_general']['dosis_omitidas'] }}
                </div>
                <p style="font-size: 12px; margin: 5px 0 0 0;">
                    {{ round(($reporte['adherencia_general']['dosis_omitidas'] / max($reporte['adherencia_general']['total_dosis'], 1)) * 100, 1) }}% del total
                </p>
            </div>
        </div>

        <!-- Detalle por Medicamento -->
        @if(isset($reporte['medicamentos_detalle']) && !empty($reporte['medicamentos_detalle']))
        <h3><span class="emoji">💊</span>Detalle por Medicamento</h3>
        @foreach($reporte['medicamentos_detalle'] as $medicamento)
        <div class="medication-item">
            <h4 style="margin: 0 0 10px 0; color: #495057;">{{ $medicamento['nombre'] }}</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                <div>
                    <strong>Dosis:</strong> {{ $medicamento['dosis'] }}<br>
                    <strong>Frecuencia:</strong> {{ $medicamento['frecuencia'] }}
                </div>
                <div>
                    <strong>Adherencia:</strong> 
                    <span style="font-weight: bold; color: @if($medicamento['adherencia_porcentaje'] >= 90) #28a745 @elseif($medicamento['adherencia_porcentaje'] >= 70) #ffc107 @else #dc3545 @endif;">
                        {{ $medicamento['adherencia_porcentaje'] }}%
                    </span><br>
                    <strong>Dosis:</strong> {{ $medicamento['dosis_administradas'] }}/{{ $medicamento['total_dosis'] }}
                </div>
            </div>
        </div>
        @endforeach
        @endif

        <!-- Tendencias -->
        @if(isset($reporte['tendencias']) && !empty($reporte['tendencias']))
        <div class="info-section">
            <h3 style="margin-top: 0;"><span class="emoji">📊</span>Análisis de Tendencias</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Cambio en Adherencia:</strong><br>
                    @if($reporte['tendencias']['mejora_adherencia'])
                        <span style="color: #28a745;">↗ +{{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }}%</span>
                    @elseif($reporte['tendencias']['adherencia_cambio'] < -2)
                        <span style="color: #dc3545;">↘ {{ number_format($reporte['tendencias']['adherencia_cambio'], 1) }}%</span>
                    @else
                        <span style="color: #6c757d;">→ Estable</span>
                    @endif
                </div>
                <div>
                    <strong>Cambio en Puntualidad:</strong><br>
                    @if($reporte['tendencias']['mejora_puntualidad'])
                        <span style="color: #28a745;">↗ +{{ number_format(abs($reporte['tendencias']['puntualidad_cambio']), 1) }}%</span>
                    @elseif($reporte['tendencias']['puntualidad_cambio'] < -2)
                        <span style="color: #dc3545;">↘ {{ number_format($reporte['tendencias']['puntualidad_cambio'], 1) }}%</span>
                    @else
                        <span style="color: #6c757d;">→ Estable</span>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Alertas -->
        @if(isset($reporte['alertas']) && !empty($reporte['alertas']))
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0;"><span class="emoji">🚨</span>Alertas Activas</h3>
            @foreach($reporte['alertas'] as $alerta)
            <div style="background: white; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 0 5px 5px 0;">
                <strong>{{ $alerta['tipo'] }}</strong><br>
                <small style="color: #6c757d;">{{ $alerta['fecha'] }}</small><br>
                <span style="margin-top: 5px; display: block;">{{ $alerta['mensaje'] }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Recomendaciones -->
        @if(isset($reporte['recomendaciones']) && !empty($reporte['recomendaciones']))
        <div style="background: #e7f3ff; border: 1px solid #bee5eb; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0;"><span class="emoji">💡</span>Recomendaciones</h3>
            @foreach($reporte['recomendaciones'] as $recomendacion)
            <div style="background: white; border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; border-radius: 0 5px 5px 0;">
                <strong>{{ ucfirst($recomendacion['tipo']) }}:</strong>
                {{ $recomendacion['mensaje'] }}
                @if($recomendacion['prioridad'] === 'alta')
                <div style="margin-top: 5px;">
                    <span style="padding: 2px 6px; background: #dc3545; color: white; border-radius: 3px; font-size: 11px;">
                        PRIORIDAD ALTA
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Resumen -->
        <div style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0;">
            <h3>Estado del Tratamiento</h3>
            @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90)
                <p>El seguimiento del tratamiento es <strong>excelente</strong>. Se está cumpliendo adecuadamente con la prescripción médica.</p>
            @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70)
                <p>El seguimiento del tratamiento es <strong>aceptable</strong>. Con pequeños ajustes se puede lograr un cumplimiento óptimo.</p>
            @else
                <p>El seguimiento del tratamiento <strong>requiere atención</strong>. Se recomienda contactar al equipo médico para mejorar la adherencia.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p><strong>MediTrack</strong> - Sistema de Monitoreo de Adherencia</p>
        <p>Reporte generado el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p style="font-size: 12px; margin-top: 15px;">
            Paciente: {{ $paciente['nombre'] }} (ID: {{ $paciente['id'] }})<br>
            Este reporte es confidencial y está destinado exclusivamente a personal autorizado.<br>
            Para consultas médicas, contacte al equipo tratante.
        </p>
    </div>
</body>
</html> 