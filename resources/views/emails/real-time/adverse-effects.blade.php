<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Efectos Adversos Reportados - MediTrack</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .critical-header {
            background: linear-gradient(135deg, #721c24 0%, #8b0000 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            border-top: 5px solid #ff0000;
        }

        .critical-icon {
            font-size: 56px;
            margin-bottom: 10px;
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .critical-title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .critical-subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: bold;
        }

        .emergency-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff0000;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid white;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }

        .content {
            padding: 30px;
        }

        .patient-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #721c24;
        }

        .adverse-effects-details {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .medication-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .severity-assessment {
            background: #721c24;
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .immediate-actions {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .medical-protocol {
            background: #cce5ff;
            border: 1px solid #0066cc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .emergency-contact {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            font-size: 16px;
        }

        .button-emergency {
            background: #dc3545;
            animation: pulse-button 2s infinite;
        }

        @keyframes pulse-button {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        .severity-high {
            color: #721c24;
            font-weight: bold;
            font-size: 18px;
        }

        .severity-medium {
            color: #dc3545;
            font-weight: bold;
        }

        .severity-low {
            color: #fd7e14;
            font-weight: bold;
        }

        h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .emoji {
            font-size: 20px;
            margin-right: 8px;
        }

        .effects-list {
            background: white;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .effects-list li {
            margin-bottom: 8px;
            padding: 5px;
            background: #fff5f5;
            border-radius: 3px;
            border-left: 3px solid #dc3545;
            margin-left: 10px;
        }

        .timestamp {
            color: #6c757d;
            font-size: 14px;
            margin-top: 10px;
        }

        .critical-notice {
            background: #8b0000;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            border: 3px solid #ff0000;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Critical Header -->
        <div class="critical-header">
            <div class="emergency-badge">EMERGENCIA</div>
            <span class="critical-icon">⚠️</span>
            <h1 class="critical-title">Efectos Adversos</h1>
            <p class="critical-subtitle">Evaluación médica requerida INMEDIATAMENTE</p>
        </div>

        <div class="content">
            <!-- Critical Notice -->
            <div class="critical-notice">
                🚨 ALERTA MÉDICA CRÍTICA 🚨
                <br>Se han reportado efectos adversos que requieren atención médica inmediata
            </div>

            <!-- Patient Information -->
            <div class="patient-info">
                <h3><span class="emoji">👤</span>Información del Paciente</h3>
                <p><strong>Nombre:</strong> {{ $paciente->nombre }}</p>
                <p><strong>ID del Paciente:</strong> #{{ $paciente->id }}</p>
                @if($paciente->edad)
                <p><strong>Edad:</strong> {{ $paciente->edad }} años</p>
                @endif
                @if($paciente->tipo_sangre)
                <p><strong>Tipo de sangre:</strong> {{ $paciente->tipo_sangre }}</p>
                @endif
                @if($paciente->observaciones_medicas)
                <p><strong>Observaciones médicas:</strong> {{ $paciente->observaciones_medicas }}</p>
                @endif
            </div>

            <!-- Medication Information -->
            <div class="medication-info">
                <h3><span class="emoji">💊</span>Medicamento Involucrado</h3>
                <p><strong>Medicamento:</strong> {{ $medicamento->nombre ?? 'N/A' }}</p>
                @if($medicamento)
                <p><strong>Principio activo:</strong> {{ $medicamento->principio_activo ?? 'N/A' }}</p>
                <p><strong>Concentración:</strong> {{ $medicamento->medida }} {{ $medicamento->unidad_medida }}</p>
                <p><strong>Forma farmacéutica:</strong> {{ $medicamento->forma_farmaceutica ?? 'N/A' }}</p>
                @endif
                <p><strong>Hora de administración:</strong> {{ $administracion->fecha_hora_administrada?->format('d/m/Y H:i') ?? 'No registrada' }}</p>
                @if($administracion->dosis_administrada)
                <p><strong>Dosis administrada:</strong> {{ $administracion->dosis_administrada }}</p>
                @endif
            </div>

            <!-- Adverse Effects Details -->
            <div class="adverse-effects-details">
                <h3><span class="emoji">🩺</span>Efectos Adversos Reportados</h3>
                @if($administracion->efectos_adversos)
                <div class="effects-list">
                    @if(is_array($administracion->efectos_adversos))
                        <ul>
                            @foreach($administracion->efectos_adversos as $efecto)
                            <li>{{ $efecto }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $administracion->efectos_adversos }}</p>
                    @endif
                </div>
                @endif

                @if($administracion->intensidad_sintoma)
                <p><strong>Intensidad reportada:</strong> 
                    <span class="severity-{{ strtolower($administracion->intensidad_sintoma) }}">
                        {{ ucfirst($administracion->intensidad_sintoma) }}
                    </span>
                </p>
                @endif

                <div class="timestamp">
                    <strong>Reportado el:</strong> {{ $alerta->fecha_hora->format('d/m/Y \a \l\a\s H:i:s') }}
                </div>
            </div>

            <!-- Severity Assessment -->
            @if($administracion->intensidad_sintoma)
            <div class="severity-assessment">
                <h3>📊 Evaluación de Gravedad</h3>
                @if(strtolower($administracion->intensidad_sintoma) === 'alta')
                <p><strong>GRAVEDAD ALTA</strong> - Requiere intervención médica inmediata</p>
                <p>🚨 El paciente debe ser evaluado por un médico en las próximas 2 horas</p>
                @elseif(strtolower($administracion->intensidad_sintoma) === 'media')
                <p><strong>GRAVEDAD MODERADA</strong> - Requiere seguimiento médico</p>
                <p>⚠️ El paciente debe ser contactado y evaluado dentro de 6 horas</p>
                @else
                <p><strong>GRAVEDAD LEVE</strong> - Monitoreo continuo requerido</p>
                <p>📋 Seguimiento telefónico recomendado en 24 horas</p>
                @endif
            </div>
            @endif

            <!-- Immediate Actions -->
            <div class="immediate-actions">
                <h3><span class="emoji">🎯</span>Acciones Inmediatas Requeridas</h3>
                <p><strong>{{ $actionRequired }}</strong></p>
            </div>

            <!-- Medical Protocol -->
            <div class="medical-protocol">
                <h3><span class="emoji">📋</span>Protocolo Médico Recomendado</h3>
                <ol style="padding-left: 20px;">
                    @foreach($nextSteps as $step)
                    <li style="margin-bottom: 8px; font-weight: {{ $loop->first ? 'bold' : 'normal' }};">{{ $step }}</li>
                    @endforeach
                </ol>
            </div>

            <!-- Emergency Contact -->
            <div class="emergency-contact">
                <h3>🚨 CONTACTO DE EMERGENCIA</h3>
                <p><strong>Si el paciente presenta síntomas graves:</strong></p>
                <p>• Contacte servicios de emergencia inmediatamente</p>
                <p>• Mantenga al paciente bajo observación constante</p>
                <p>• Prepare la información médica del paciente</p>
                <p>• NO administre más medicación hasta evaluación médica</p>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin: 30px 0;">
                @if(config('app.url'))
                <a href="{{ config('app.url') }}" class="button button-emergency">
                    🚨 ACCEDER A MEDITRACK
                </a>
                @endif
                <br>
                <small style="color: #6c757d; margin-top: 10px; display: block;">
                    Accede inmediatamente para ver detalles completos y tomar acciones
                </small>
            </div>
        </div>

        <div class="footer">
            <p><strong>MediTrack</strong> - Sistema de Seguridad Médica</p>
            <p><strong style="color: #dc3545;">NOTIFICACIÓN CRÍTICA</strong> enviada el {{ $fechaGeneracion->format('d/m/Y \a \l\a\s H:i') }}</p>
            <p style="font-size: 12px; margin-top: 15px;">
                Para: {{ $recipient->name }} ({{ $recipient->email }})<br>
                Paciente: {{ $paciente->nombre }} (ID: {{ $paciente->id }})<br>
                Alerta ID: {{ $alerta->id }}<br>
                <strong style="color: #dc3545;">ESTA ES UNA EMERGENCIA MÉDICA - ACCIÓN INMEDIATA REQUERIDA</strong>
            </p>
        </div>
    </div>
</body>
</html> 