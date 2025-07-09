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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .summary-section {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .medication-summary {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        .action-items {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .contact-info {
            background: #e7f3ff;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .emoji { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><span class="emoji">👨‍👩‍👧‍👦</span>Reporte de Supervisión - {{ $paciente['nombre'] }}</h1>
        <p>{{ $reporte['periodo']['descripcion'] }}</p>
    </div>

    <div class="content">
        <h2>Estimado/a Apoderado/a 👋</h2>
        <p>Como responsable legal de <strong>{{ $paciente['nombre'] }}</strong>, este reporte te informa sobre el progreso en su tratamiento médico.</p>

        <!-- Resumen Ejecutivo -->
        <div class="summary-section">
            <h3 style="margin-top: 0; color: #155724;"><span class="emoji">📋</span>Resumen Ejecutivo</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Estado General:</strong><br>
                    @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90)
                        <span style="color: #28a745;">✅ Excelente adherencia</span>
                    @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70)
                        <span style="color: #ffc107;">⚠️ Adherencia aceptable</span>
                    @else
                        <span style="color: #dc3545;">🚨 Requiere atención</span>
                    @endif
                </div>
                <div>
                    <strong>Medicamentos Activos:</strong><br>
                    <span>{{ $paciente['medicamentos_activos'] ?? 0 }} medicamento(s)</span>
                </div>
            </div>
        </div>

        <!-- Adherencia General -->
        <div class="metric-card @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
            <div class="metric-title">
                <span class="emoji">📊</span>Adherencia General al Tratamiento
            </div>
            <div class="metric-value @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 90) @elseif($reporte['adherencia_general']['adherencia_porcentaje'] >= 70) warning @else danger @endif">
                {{ $reporte['adherencia_general']['adherencia_porcentaje'] }}%
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                {{ $paciente['nombre'] }} tomó {{ $reporte['adherencia_general']['dosis_administradas'] }} de {{ $reporte['adherencia_general']['total_dosis'] }} dosis programadas
            </p>
            @if($reporte['adherencia_general']['dosis_omitidas'] > 0)
                <p style="color: #dc3545; margin: 10px 0 0 0; font-size: 14px;">
                    <strong>⚠️ {{ $reporte['adherencia_general']['dosis_omitidas'] }} dosis omitidas</strong> - Es importante conversar con el equipo médico
                </p>
            @endif
        </div>

        <!-- Detalle por Medicamento -->
        @if(isset($reporte['medicamentos_detalle']) && !empty($reporte['medicamentos_detalle']))
        <h3><span class="emoji">💊</span>Detalle por Medicamento</h3>
        @foreach($reporte['medicamentos_detalle'] as $medicamento)
        <div class="medication-summary">
            <h4 style="margin: 0 0 10px 0; color: #28a745;">{{ $medicamento['nombre'] }}</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                <div>
                    <strong>Prescripción:</strong> {{ $medicamento['dosis'] }}<br>
                    <strong>Frecuencia:</strong> Cada {{ $medicamento['frecuencia'] }}
                </div>
                <div>
                    <strong>Cumplimiento:</strong> 
                    <span style="font-weight: bold; color: @if($medicamento['adherencia_porcentaje'] >= 90) #28a745 @elseif($medicamento['adherencia_porcentaje'] >= 70) #ffc107 @else #dc3545 @endif;">
                        {{ $medicamento['adherencia_porcentaje'] }}%
                    </span><br>
                    <strong>Dosis tomadas:</strong> {{ $medicamento['dosis_administradas'] }}/{{ $medicamento['total_dosis'] }}
                </div>
            </div>
            @if($medicamento['adherencia_porcentaje'] < 80)
                <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 13px;">
                    <strong>💡 Recomendación:</strong> Este medicamento requiere mayor seguimiento. Considera hablar con el cuidador o equipo médico.
                </div>
            @endif
        </div>
        @endforeach
        @endif

        <!-- Tendencias -->
        @if(isset($reporte['tendencias']) && !empty($reporte['tendencias']))
        <div class="metric-card">
            <div class="metric-title">
                <span class="emoji">📈</span>Evolución del Tratamiento
            </div>
            @if($reporte['tendencias']['mejora_adherencia'])
                <p style="color: #28a745; font-weight: bold;">
                    📈 <strong>Mejora:</strong> La adherencia aumentó {{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }}% respecto al período anterior
                </p>
            @elseif($reporte['tendencias']['adherencia_cambio'] < -5)
                <p style="color: #dc3545; font-weight: bold;">
                    📉 <strong>Atención:</strong> La adherencia disminuyó {{ number_format(abs($reporte['tendencias']['adherencia_cambio']), 1) }}% respecto al período anterior
                </p>
            @else
                <p style="color: #6c757d;">
                    ➡️ <strong>Estable:</strong> La adherencia se mantiene estable respecto al período anterior
                </p>
            @endif
        </div>
        @endif

        <!-- Acciones Recomendadas -->
        @if(($reporte['adherencia_general']['adherencia_porcentaje'] < 85) || (isset($reporte['alertas']) && !empty($reporte['alertas'])))
        <div class="action-items">
            <h3 style="margin-top: 0;"><span class="emoji">📝</span>Acciones Recomendadas</h3>
            
            @if($reporte['adherencia_general']['adherencia_porcentaje'] < 85)
            <div style="margin: 15px 0;">
                <strong>🔍 Revisar seguimiento:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Conversar con {{ $paciente['nombre'] }} sobre la importancia del tratamiento</li>
                    <li>Verificar si hay dificultades para tomar los medicamentos</li>
                    <li>Considerar ajustar horarios o métodos de recordatorio</li>
                </ul>
            </div>
            @endif

            @if($reporte['adherencia_general']['dosis_omitidas'] > 2)
            <div style="margin: 15px 0;">
                <strong>🚨 Intervención necesaria:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Programar reunión con el equipo médico</li>
                    <li>Evaluar necesidad de supervisor adicional</li>
                    <li>Revisar comprensión del régimen de medicamentos</li>
                </ul>
            </div>
            @endif

            <div style="margin: 15px 0;">
                <strong>📞 Próximos pasos:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <li>Mantener comunicación regular con cuidadores</li>
                    <li>Monitorear reportes semanales</li>
                    <li>Reportar cualquier efecto secundario al médico</li>
                </ul>
            </div>
        </div>
        @endif

        <!-- Alertas Críticas -->
        @if(isset($reporte['alertas']) && !empty($reporte['alertas']))
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #721c24;"><span class="emoji">🚨</span>Alertas Importantes</h3>
            @foreach($reporte['alertas'] as $alerta)
            <div style="background: white; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; border-radius: 0 5px 5px 0;">
                <strong>{{ $alerta['tipo'] }}</strong><br>
                <small style="color: #6c757d;">{{ $alerta['fecha'] }}</small><br>
                <span style="margin-top: 5px; display: block;">{{ $alerta['mensaje'] }}</span>
            </div>
            @endforeach
            <p style="margin: 15px 0 0 0; font-weight: bold; color: #721c24;">
                📞 <strong>Se recomienda contactar al equipo médico para atender estas alertas.</strong>
            </p>
        </div>
        @endif

        <!-- Información de Contacto -->
        <div class="contact-info">
            <h3 style="margin-top: 0; color: #0c5460;"><span class="emoji">📞</span>Contacto y Seguimiento</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Para consultas médicas:</strong><br>
                    • Usa la aplicación MediTrack<br>
                    • Contacta al médico tratante<br>
                    • En emergencias: servicio de urgencias
                </div>
                <div>
                    <strong>Para seguimiento del tratamiento:</strong><br>
                    • Revisa reportes semanales<br>
                    • Mantén comunicación con cuidadores<br>
                    • Programa citas de control regulares
                </div>
            </div>
        </div>

        <!-- Mensaje de Tranquilidad -->
        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0;">
            @if($reporte['adherencia_general']['adherencia_porcentaje'] >= 85)
                <h3>¡{{ $paciente['nombre'] }} está siguiendo bien su tratamiento! 🌟</h3>
                <p>Como apoderado/a, puedes estar tranquilo/a sabiendo que está recibiendo la atención médica adecuada.</p>
            @else
                <h3>Estamos trabajando para mejorar 💪</h3>
                <p>Con tu apoyo y el seguimiento del equipo médico, {{ $paciente['nombre'] }} puede lograr una mejor adherencia al tratamiento.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p><strong>MediTrack</strong> - Cuidando la salud de tu familia</p>
        <p>Reporte generado el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p style="font-size: 12px; margin-top: 15px;">
            Para: {{ $paciente['nombre'] }} | Apoderado: {{ $recipient->name ?? 'N/A' }}<br>
            Este reporte es confidencial y está destinado exclusivamente al apoderado legal.
        </p>
    </div>
</body>
</html> 