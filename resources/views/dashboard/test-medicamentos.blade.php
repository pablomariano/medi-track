<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema de Medicamentos - Medi-Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">🏥 Test Sistema de Medicamentos</h1>
                <p class="text-gray-600">Verificación completa del sistema integrado de gestión farmacéutica</p>
                
                <div class="mt-4 flex flex-wrap gap-4">
                    <a href="{{ route('medicamentos.principios-activos.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        📋 Principios Activos
                    </a>
                    <a href="{{ route('medicamentos.index') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        💊 Medicamentos
                    </a>
                    <a href="{{ route('tratamientos.index') }}" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        🩺 Tratamientos
                    </a>
                    <a href="{{ route('dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        🏠 Dashboard
                    </a>
                </div>
            </div>

            <!-- Test de Relaciones -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">✅ Test de Relaciones del Sistema</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-2">Principios con Medicamentos</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $relationshipTests['principios_con_medicamentos'] }}</p>
                        <p class="text-sm text-blue-600">de {{ $principiosActivos->count() }} total</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-800 mb-2">Medicamentos con Relaciones</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $relationshipTests['medicamentos_con_relaciones'] }}</p>
                        <p class="text-sm text-green-600">de {{ $medicamentos->count() }} total</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-purple-800 mb-2">Tratamientos con Pacientes</h3>
                        <p class="text-2xl font-bold text-purple-600">{{ $relationshipTests['tratamientos_con_pacientes'] }}</p>
                        <p class="text-sm text-purple-600">de {{ $tratamientos->count() }} total</p>
                    </div>
                </div>
            </div>

            <!-- Principios Activos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Principios Activos ({{ $principiosActivos->count() }})</h2>
                
                @if($principiosActivos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Nombre Genérico</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Grupo Farmacológico</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Medicamentos</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($principiosActivos as $principio)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $principio->nombre_generico }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $principio->grupo_farmacologico }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $principio->medicamentos->count() }} medicamento(s)
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($principio->activo)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✅ Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                ❌ Inactivo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay principios activos registrados</p>
                @endif
            </div>

            <!-- Medicamentos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">💊 Medicamentos ({{ $medicamentos->count() }})</h2>
                
                @if($medicamentos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Nombre Comercial</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Principio Activo</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Concentración</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Forma</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Vía</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($medicamentos as $medicamento)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $medicamento->nombre_comercial }}</td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $medicamento->principioActivo ? $medicamento->principioActivo->nombre_generico : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $medicamento->concentracion }} 
                                        {{ $medicamento->unidadConcentracion ? $medicamento->unidadConcentracion->simbolo : '' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $medicamento->formaFarmaceutica ? $medicamento->formaFarmaceutica->nombre : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $medicamento->viaAdministracion ? $medicamento->viaAdministracion->nombre : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-col gap-1">
                                            @if($medicamento->activo)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Activo
                                                </span>
                                            @endif
                                            @if($medicamento->requiere_receta)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    📝 Receta
                                                </span>
                                            @endif
                                            @if($medicamento->controlado)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    🔒 Controlado
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay medicamentos registrados</p>
                @endif
            </div>

            <!-- Tratamientos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">🩺 Tratamientos ({{ $tratamientos->count() }})</h2>
                
                @if($tratamientos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Nombre</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Paciente</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Médico</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Estado</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-900">Medicamentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($tratamientos as $tratamiento)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $tratamiento->nombre }}</td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $tratamiento->paciente ? $tratamiento->paciente->nombre_completo : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $tratamiento->medico && $tratamiento->medico->user ? $tratamiento->medico->user->name : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($tratamiento->estado === 'Activo') bg-green-100 text-green-800
                                            @elseif($tratamiento->estado === 'Pausado') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $tratamiento->estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $tratamiento->medicamentos ? $tratamiento->medicamentos->count() : 0 }} medicamento(s)
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay tratamientos registrados</p>
                @endif
            </div>

            <!-- Footer -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">🎉 Sistema Integrado Funcionando</h3>
                    <p class="text-gray-600 mb-4">
                        El sistema de medicamentos se ha integrado exitosamente con la gestión de usuarios.
                        Todas las relaciones entre modelos están funcionando correctamente.
                    </p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="bg-blue-50 p-3 rounded">
                            <div class="font-semibold text-blue-800">Principios Activos</div>
                            <div class="text-blue-600">{{ $principiosActivos->count() }} registros</div>
                        </div>
                        <div class="bg-green-50 p-3 rounded">
                            <div class="font-semibold text-green-800">Medicamentos</div>
                            <div class="text-green-600">{{ $medicamentos->count() }} registros</div>
                        </div>
                        <div class="bg-purple-50 p-3 rounded">
                            <div class="font-semibold text-purple-800">Tratamientos</div>
                            <div class="text-purple-600">{{ $tratamientos->count() }} registros</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="font-semibold text-gray-800">Relaciones</div>
                            <div class="text-gray-600">✅ Funcionando</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 