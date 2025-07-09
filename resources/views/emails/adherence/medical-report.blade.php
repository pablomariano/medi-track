<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Médico de Adherencia - {{ $paciente['nombre'] }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
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
        .patient-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .metric-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .metric-card.critical {
            border-left: 4px solid #dc3545;
            background: #fff5f5;
        }
        .metric-card.warning {
            border-left: 4px solid #ffc107;
            background: #fffbf0;
        }
        .metric-card.good {
            border-left: 4px solid #28a745;
            background: #f8fff9;
        }
        .metric-value {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        .metric-value.critical { color: #dc3545; }
        .metric-value.warning { color: #856404; }
        .metric-value.good { color: #28a745; }
        .metric-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }
        .medication-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .medication-table th,
        .medication-table td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: left;
        }
        .medication-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .medication-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .temporal-metrics {
            background: #e8f4f8;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-item {
            background: white;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 10px 0;
            border-radius: 0 5px 5px 0;
        }
        .alert-item.critical {
            border-left-color: #dc3545;
        }
        .recommendations {
            background: #e7f3ff;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .recommendation-item {
            background: white;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 0 5px 5px 0;
        }
        .recommendation-item.high-priority {
            border-left-color: #dc3545;
        }
        .trend-indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .trend-up { background: #d4edda; color: #155724; }
        .trend-down { background: #f8d7da; color: #721c24; }
        .trend-stable { background: #e2e3e5; color: #383d41; }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px 0;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Reporte Médico de Adherencia</h1>
        <p>{{ $reporte['periodo']['descripcion'] }}</p>
    </div>

    <div class="content">
        <!-- Información del Paciente -->
        <div class="patient-info">
            <h3 style="margin-top: 0;">Información del Paciente</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Nombre:</strong> {{ $paciente['nombre'] }}<br>
                    <strong>ID Paciente:</strong> {{ $paciente['id'] }}
                </div>
                <div>
                    @if(isset($paciente['edad']))
                    <strong>Edad:</strong> {{ $paciente['edad'] }} años<br>
                    @endif
                    <strong>Tratamientos Activos:</strong> {{ $paciente['tratamientos_activos'] }}
                </div>
                <div>
                    <strong>Medicamentos:</strong> {{ $paciente['medicamentos_activos'] }}<br>
                    <strong>Período:</strong> {{ $reporte['periodo']['dias'] }} días
                </div>
            </div>
        </div>

        <!-- Métricas Principales -->
        <h2 class="section-title">Métricas de Adherencia</h2>
        <div class="metrics-grid">
            <div class="metric-card @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) good @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else critical @endif">
                <div class="metric-label">ADHERENCIA GENERAL</div>
                <div class="metric-value @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) good @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else critical @endif">
                    {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%
                </div>
                <div style="font-size: 12px; color: #6c757d;">
                    {{ $reporte['adherencia_general']['dosis_administradas'] }}/{{ $reporte['adherencia_general']['total_dosis'] }} dosis
                </div>
            </div>

            <div class="metric-card @if($reporte['adherencia_general']['puntualidad_porcentaje'] >= 80) good @elseif($reporte['adherencia_general']['puntualidad_porcentaje'] >= 60) warning @else critical @endif">
                <div class="metric-label">PUNTUALIDAD</div>
                <div class="metric-value @if($reporte['adherencia_general']['puntualidad_porcentaje'] >= 80) good @elseif($reporte['adherencia_general']['puntualidad_porcentaje'] >= 60) warning @else critical @endif">
                    {{ $reporte['adherencia_general']['puntualidad_porcentaje'] }}%
                </div>
                <div style="font-size: 12px; color: #6c757d;">
                    {{ $reporte['adherencia_general']['dosis_tardias'] }} dosis tardías
                </div>
            </div>

            <div class="metric-card @if($reporte['adherencia_general']['dosis_omitidas'] == 0) good @elseif($reporte['adherencia_general']['dosis_omitidas'] <= 2) warning @else critical @endif">
                <div class="metric-label">DOSIS OMITIDAS</div>
                <div class="metric-value @if($reporte['adherencia_general']['dosis_omitidas'] == 0) good @elseif($reporte['adherencia_general']['dosis_omitidas'] <= 2) warning @else critical @endif">
                    {{ $reporte['adherencia_general']['dosis_omitidas'] }}
                </div>
                <div style="font-size: 12px; color: #6c757d;">
                    {{ round(($reporte['adherencia_general']['dosis_omitidas'] / max($reporte['adherencia_general']['total_dosis'], 1)) * 100, 1) }}% del total
                </div>
            </div>
        </div>

        <!-- Tendencias -->
        @if(isset($reporte['tendencias']) && !empty($reporte['tendencias']))
        <h2 class="section-title">Análisis de Tendencias</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <strong>Cambio en Adherencia:</strong>
                @if($reporte['tendencias']['mejora_adherencia'])
                    <span class="trend-indicator trend-up">↗ +{{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }}%</span>
                @elseif($reporte['tendencias']['adherencia_cambio'] < -2)
                    <span class="trend-indicator trend-down">↘ {{ number_format($reporte['tendencias']['adherencia_cambio'], 1) }}%</span>
                @else
                    <span class="trend-indicator trend-stable">→ Estable</span>
                @endif
            </div>
            <div>
                <strong>Cambio en Puntualidad:</strong>
                @if($reporte['tendencias']['mejora_puntualidad'])
                    <span class="trend-indicator trend-up">↗ +{{ number_format(abs($reporte['tendencias']['puntualidad_cambio']), 1) }}%</span>
                @elseif($reporte['tendencias']['puntualidad_cambio'] < -2)
                    <span class="trend-indicator trend-down">↘ {{ number_format($reporte['tendencias']['puntualidad_cambio'], 1) }}%</span>
                @else
                    <span class="trend-indicator trend-stable">→ Estable</span>
                @endif
            </div>
        </div>
        @endif

        <!-- Métricas Temporales Avanzadas -->
        @if(isset($reporte['metricas_temporales']) && !empty($reporte['metricas_temporales']))
        <div class="temporal-metrics">
            <h3 style="margin-top: 0; color: #0c5460;">📊 Análisis Temporal Avanzado</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                @if(isset($reporte['metricas_temporales']['puntualidad_promedio']))
                <div>
                    <strong>Score Puntualidad:</strong><br>
                    <span style="font-size: 18px; font-weight: bold;">{{ $reporte['metricas_temporales']['puntualidad_promedio'] }}</span>
                </div>
                @endif
                @if(isset($reporte['metricas_temporales']['tiempo_promedio_retraso']))
                <div>
                    <strong>Retraso Promedio:</strong><br>
                    <span style="font-size: 18px; font-weight: bold;">{{ $reporte['metricas_temporales']['tiempo_promedio_retraso'] }} min</span>
                </div>
                @endif
                @if(isset($reporte['metricas_temporales']['variabilidad_horaria']))
                <div>
                    <strong>Variabilidad:</strong><br>
                    <span style="font-size: 18px; font-weight: bold;">{{ $reporte['metricas_temporales']['variabilidad_horaria'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Detalle por Medicamento -->
        @if(isset($reporte['medicamentos_detalle']) && !empty($reporte['medicamentos_detalle']))
        <h2 class="section-title">Adherencia por Medicamento</h2>
        <table class="medication-table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Frecuencia</th>
                    <th>Administradas</th>
                    <th>Total</th>
                    <th>Adherencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['medicamentos_detalle'] as $medicamento)
                <tr>
                    <td><strong>{{ $medicamento['nombre'] }}</strong></td>
                    <td>{{ $medicamento['dosis'] }}</td>
                    <td>c/{{ $medicamento['frecuencia'] }}</td>
                    <td>{{ $medicamento['dosis_administradas'] }}</td>
                    <td>{{ $medicamento['total_dosis'] }}</td>
                    <td>
                        <span style="font-weight: bold; color: @if($medicamento['adherencia_porcentaje'] >= 90) #28a745 @elseif($medicamento['adherencia_porcentaje'] >= 70) #ffc107 @else #dc3545 @endif;">
                            {{ $medicamento['adherencia_porcentaje'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Alertas Clínicas -->
        @if(isset($reporte['alertas']) && !empty($reporte['alertas']))
        <div class="alert-section">
            <h3 style="margin-top: 0;">🚨 Alertas Clínicas Activas</h3>
            @foreach($reporte['alertas'] as $alerta)
            <div class="alert-item @if($alerta['nivel'] === 'Critica') critical @endif">
                <div style="display: flex; justify-content: between; align-items: center;">
                    <strong>{{ $alerta['tipo'] }}</strong>
                    <span style="font-size: 12px; color: #6c757d;">{{ $alerta['fecha'] }}</span>
                </div>
                <div style="margin-top: 5px;">{{ $alerta['mensaje'] }}</div>
                <div style="margin-top: 5px;">
                    <span style="padding: 2px 6px; background: @if($alerta['nivel'] === 'Critica') #dc3545 @elseif($alerta['nivel'] === 'Advertencia') #ffc107 @else #17a2b8 @endif; color: white; border-radius: 3px; font-size: 11px;">
                        {{ $alerta['nivel'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Recomendaciones Clínicas -->
        @if(isset($reporte['recomendaciones']) && !empty($reporte['recomendaciones']))
        <div class="recommendations">
            <h3 style="margin-top: 0;">💡 Recomendaciones Clínicas</h3>
            @foreach($reporte['recomendaciones'] as $recomendacion)
            <div class="recommendation-item @if($recomendacion['prioridad'] === 'alta') high-priority @endif">
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

        <!-- Resumen Ejecutivo -->
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 30px 0;">
            <h3 style="margin-top: 0;">📋 Resumen Ejecutivo</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <strong>Estado General:</strong><br>
                    @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90)
                        <span style="color: #28a745;">✓ Adherencia Excelente</span>
                    @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70)
                        <span style="color: #ffc107;">⚠ Adherencia Aceptable</span>
                    @else
                        <span style="color: #dc3545;">⚠ Adherencia Deficiente</span>
                    @endif
                </div>
                <div>
                    <strong>Acción Requerida:</strong><br>
                    @if($reporte['adherencia_general']['adherencia_porcentaje'] < 70)
                        <span style="color: #dc3545;">Intervención Inmediata</span>
                    @elseif($reporte['adherencia_general']['adherencia_porcentaje'] < 85)
                        <span style="color: #ffc107;">Monitoreo Reforzado</span>
                    @else
                        <span style="color: #28a745;">Mantenimiento</span>
                    @endif
                </div>
            </div>
            <div style="margin-top: 15px;">
                <strong>Siguiente Evaluación:</strong> Se recomienda revisión en {{ $reporte['adherencia_general']['adherencia_porcentaje'] < 70 ? '3-5 días' : '1 semana' }}.
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>MediTrack</strong> - Sistema de Monitoreo de Adherencia</p>
        <p>Reporte generado el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p style="font-size: 12px; margin-top: 15px;">
            Para paciente: {{ $paciente['nombre'] }} (ID: {{ $paciente['id'] }})<br>
            Reporte automático - Para consultas contacte al administrador del sistema.
        </p>
    </div>
</body>
</html> 