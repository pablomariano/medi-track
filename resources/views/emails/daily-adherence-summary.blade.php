<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Diario de Adherencia - MediTrack</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #374151;
        }
        .email-container {
            max-width: 680px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 32px 24px;
        }
        .status-banner {
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status-icon {
            font-size: 24px;
        }
        .status-text {
            flex: 1;
        }
        .status-title {
            font-weight: 600;
            font-size: 18px;
            margin: 0 0 4px 0;
        }
        .status-message {
            margin: 0;
            opacity: 0.8;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .metric-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .metric-title {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 500;
        }
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #1f2937;
        }
        .metric-detail {
            font-size: 12px;
            color: #9ca3af;
            margin: 4px 0 0 0;
        }
        .section {
            margin-bottom: 32px;
        }
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .highlights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .highlight-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .highlight-icon {
            font-size: 20px;
            margin-bottom: 8px;
            display: block;
        }
        .highlight-value {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #1f2937;
        }
        .highlight-title {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 2px 0;
        }
        .highlight-detail {
            font-size: 11px;
            color: #9ca3af;
            margin: 0;
        }
        .actions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .action-item {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .action-item.high-priority {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }
        .action-item.medium-priority {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
        }
        .action-item.low-priority {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .action-icon {
            font-size: 20px;
            margin-top: 2px;
        }
        .action-content {
            flex: 1;
        }
        .action-title {
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #1f2937;
        }
        .action-description {
            margin: 0 0 4px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .action-next {
            margin: 0;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
        }
        .patients-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .patients-table th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        .patients-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .patients-table tr:last-child td {
            border-bottom: none;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-excellent {
            background: #dcfce7;
            color: #166534;
        }
        .status-good {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-fair {
            background: #fef3c7;
            color: #92400e;
        }
        .status-critical {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            background: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .metrics-grid,
            .highlights-grid {
                grid-template-columns: 1fr;
            }
            .patients-table {
                font-size: 14px;
            }
            .patients-table th,
            .patients-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Resumen Diario de Adherencia</h1>
            <p>{{ $analysisDate->format('l, d \d\e F \d\e Y') }}</p>
        </div>

        <div class="content">
            <!-- Status Banner -->
            <div class="status-banner" style="background-color: {{ $adherenceStatus['bgColor'] }}; color: {{ $adherenceStatus['color'] }};">
                <div class="status-icon">{{ $adherenceStatus['icon'] }}</div>
                <div class="status-text">
                    <div class="status-title">Estado General: {{ $adherenceStatus['text'] }}</div>
                    <p class="status-message">{{ $adherenceStatus['message'] }}</p>
                </div>
            </div>

            <!-- Main Metrics -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <p class="metric-title">Adherencia General</p>
                    <p class="metric-value" style="color: {{ $adherenceStatus['color'] }};">{{ $summaryData['adherence_rate'] }}%</p>
                    <p class="metric-detail">{{ $summaryData['total_administered'] }}/{{ $summaryData['total_scheduled'] }} dosis</p>
                </div>
                <div class="metric-card">
                    <p class="metric-title">Puntualidad</p>
                    <p class="metric-value">{{ $summaryData['punctuality_rate'] }}%</p>
                    <p class="metric-detail">Dosis en horario</p>
                </div>
                <div class="metric-card">
                    <p class="metric-title">Pacientes Activos</p>
                    <p class="metric-value">{{ count($summaryData['patients']) }}</p>
                    <p class="metric-detail">Con medicación programada</p>
                </div>
                <div class="metric-card">
                    <p class="metric-title">Dosis Omitidas</p>
                    <p class="metric-value" style="color: {{ $summaryData['total_omitted'] > 0 ? '#ef4444' : '#10b981' }};">{{ $summaryData['total_omitted'] }}</p>
                    <p class="metric-detail">Ayer</p>
                </div>
            </div>

            <!-- Highlights -->
            @if(!empty($highlights))
            <div class="section">
                <h2 class="section-title">✨ Puntos Destacados</h2>
                <div class="highlights-grid">
                    @foreach($highlights as $highlight)
                    <div class="highlight-card">
                        <span class="highlight-icon">{{ $highlight['icon'] }}</span>
                        <p class="highlight-title">{{ $highlight['title'] }}</p>
                        <p class="highlight-value">{{ $highlight['value'] }}</p>
                        <p class="highlight-detail">{{ $highlight['detail'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Priority Actions -->
            @if(!empty($priorityActions))
            <div class="section">
                <h2 class="section-title">📋 Acciones Recomendadas</h2>
                <ul class="actions-list">
                    @foreach($priorityActions as $action)
                    <li class="action-item {{ $action['priority'] }}-priority">
                        <div class="action-icon">{{ $action['icon'] }}</div>
                        <div class="action-content">
                            <p class="action-title">{{ $action['title'] }}</p>
                            <p class="action-description">{{ $action['description'] }}</p>
                            <p class="action-next"><strong>Acción sugerida:</strong> {{ $action['action'] }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Patients needing attention -->
            @if(!empty($summaryData['needs_attention']))
            <div class="section">
                <h2 class="section-title">⚠️ Pacientes que Requieren Atención</h2>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Adherencia</th>
                            <th>Dosis Omitidas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryData['needs_attention'] as $patient)
                        <tr>
                            <td>{{ $patient['name'] }}</td>
                            <td>{{ $patient['adherence_rate'] }}%</td>
                            <td>{{ $patient['omitted'] }}</td>
                            <td>
                                <span class="status-badge status-{{ $patient['status']['emoji'] === '🟢' ? 'excellent' : ($patient['status']['emoji'] === '🔵' ? 'good' : ($patient['status']['emoji'] === '🟡' ? 'fair' : 'critical')) }}">
                                    {{ $patient['status']['emoji'] }} {{ $patient['status']['text'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Top performers -->
            @if(!empty($summaryData['top_performers']))
            <div class="section">
                <h2 class="section-title">🌟 Pacientes Destacados</h2>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Adherencia</th>
                            <th>Total Dosis</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryData['top_performers'] as $patient)
                        <tr>
                            <td>{{ $patient['name'] }}</td>
                            <td>{{ $patient['adherence_rate'] }}%</td>
                            <td>{{ $patient['total_doses'] }}</td>
                            <td>
                                <span class="status-badge status-excellent">
                                    🏆 Excelente
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Medications summary -->
            @if(!empty($summaryData['medications']))
            <div class="section">
                <h2 class="section-title">💊 Resumen por Medicamentos</h2>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis Programadas</th>
                            <th>Dosis Administradas</th>
                            <th>Adherencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryData['medications'] as $medication)
                        <tr>
                            <td>{{ $medication['name'] }}</td>
                            <td>{{ $medication['total_doses'] }}</td>
                            <td>{{ $medication['administered'] }}</td>
                            <td>{{ $medication['adherence_rate'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Este reporte fue generado automáticamente por <strong>MediTrack</strong><br>
                {{ now()->format('d/m/Y H:i') }} · 
                @if($recipient)
                    Dirigido a: {{ $recipient->name }}
                @else
                    Reporte general del sistema
                @endif
            </p>
            <p style="margin-top: 12px; font-size: 12px;">
                Para consultas técnicas o soporte, contacte al administrador del sistema.
            </p>
        </div>
    </div>
</body>
</html> 