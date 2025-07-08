import { useState } from 'react'
import { Head, router } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Calendar, Search, Download, Eye, Filter, Activity, AlertTriangle, Users, Clock } from 'lucide-react'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'

interface AuditLog {
  id: number
  usuario_id: number | null
  created_by_name: string
  accion: string
  tabla_afectada: string | null
  registro_id: number | null
  ip_address: string
  severidad: 'low' | 'medium' | 'high' | 'critical'
  created_at: string
  descripcion_accion: string
  severidad_badge: string
  tiempo_transcurrido: string
  usuario?: {
    id: number
    name: string
    email: string
  }
}

interface Estadisticas {
  total_acciones: number
  acciones_criticas: number
  usuarios_activos: number
  acciones_por_dia: Array<{ fecha: string; total: number }>
  acciones_por_tipo: Array<{ accion: string; total: number }>
}

interface Props {
  logs: {
    data: AuditLog[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  estadisticas: Estadisticas
  filtros: {
    usuario_id?: number
    accion?: string
    tabla?: string
    severidad?: string
    fecha_inicio?: string
    fecha_fin?: string
    busqueda?: string
    page?: string
  }
  usuarios_disponibles: Array<{ id: number; name: string }>
  acciones_disponibles: Array<{ value: string; label: string }>
  tablas_disponibles: Array<{ value: string; label: string }>
  severidades_disponibles: Array<{ value: string; label: string }>
}

export default function AuditIndex({
  logs,
  estadisticas,
  filtros,
  usuarios_disponibles,
  acciones_disponibles,
  tablas_disponibles,
  severidades_disponibles
}: Props) {
  const [localFiltros, setLocalFiltros] = useState(filtros)
  const [showFilters, setShowFilters] = useState(false)

  const handleFiltroChange = (key: string, value: string) => {
    setLocalFiltros(prev => ({
      ...prev,
      [key]: value || undefined
    }))
  }

  const aplicarFiltros = () => {
    router.get(route('audit.index'), localFiltros, {
      preserveState: true,
      preserveScroll: true
    })
  }

  const limpiarFiltros = () => {
    setLocalFiltros({})
    router.get(route('audit.index'))
  }

  const irAPagina = (url: string) => {
    // Extraer el número de página de la URL
    const urlObj = new URL(url, window.location.origin)
    const page = urlObj.searchParams.get('page')
    
    // Combinar filtros actuales con la página
    const parametros = { ...localFiltros }
    if (page) {
      parametros.page = page
    }
    
    router.get(route('audit.index'), parametros, {
      preserveState: true,
      preserveScroll: true
    })
  }

  const exportarCompliance = () => {
    if (!localFiltros.fecha_inicio || !localFiltros.fecha_fin) {
      alert('Selecciona un rango de fechas para exportar')
      return
    }
    
    router.post(route('audit.export-compliance'), {
      fecha_inicio: localFiltros.fecha_inicio,
      fecha_fin: localFiltros.fecha_fin
    })
  }

  const getSeverityColor = (severidad: string) => {
    const colors = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800'
    }
    return colors[severidad as keyof typeof colors] || colors.low
  }

  const getSeverityIcon = (severidad: string) => {
    switch (severidad) {
      case 'critical':
        return <AlertTriangle className="h-4 w-4" />
      case 'high':
        return <AlertTriangle className="h-4 w-4" />
      default:
        return <Activity className="h-4 w-4" />
    }
  }

  return (
    <AppLayout>
      <Head title="Sistema de Auditoría" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Sistema de Auditoría</h1>
            <p className="text-muted-foreground">
              Monitoreo y registro de todas las actividades del sistema
            </p>
          </div>
          <div className="flex space-x-2">
            <Button
              variant="outline"
              onClick={() => router.visit(route('audit.dashboard'))}
            >
              <Activity className="mr-2 h-4 w-4" />
              Dashboard
            </Button>
            <Button
              variant="outline"
              onClick={() => setShowFilters(!showFilters)}
            >
              <Filter className="mr-2 h-4 w-4" />
              Filtros
            </Button>
          </div>
        </div>

        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Acciones</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{estadisticas.total_acciones.toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">Últimos 30 días</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Acciones Críticas</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">
                {estadisticas.acciones_criticas.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">Requieren atención</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Usuarios Activos</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{estadisticas.usuarios_activos}</div>
              <p className="text-xs text-muted-foreground">Con actividad reciente</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Registros Totales</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{logs.total.toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">En la base de datos</p>
            </CardContent>
          </Card>
        </div>

        {/* Panel de Filtros */}
        {showFilters && (
          <Card>
            <CardHeader>
              <CardTitle>Filtros de Búsqueda</CardTitle>
              <CardDescription>
                Utiliza los filtros para encontrar logs específicos
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="text-sm font-medium">Búsqueda general</label>
                  <div className="relative">
                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      placeholder="Buscar en logs..."
                      value={localFiltros.busqueda || ''}
                      onChange={(e) => handleFiltroChange('busqueda', e.target.value)}
                      className="pl-8"
                    />
                  </div>
                </div>

                <div>
                  <label className="text-sm font-medium">Usuario</label>
                  <Select
                    value={localFiltros.usuario_id?.toString() || ''}
                    onValueChange={(value) => handleFiltroChange('usuario_id', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccionar usuario" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Todos los usuarios</SelectItem>
                      {usuarios_disponibles.map((usuario) => (
                        <SelectItem key={usuario.id} value={usuario.id.toString()}>
                          {usuario.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <label className="text-sm font-medium">Acción</label>
                  <Select
                    value={localFiltros.accion || ''}
                    onValueChange={(value) => handleFiltroChange('accion', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccionar acción" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Todas las acciones</SelectItem>
                      {acciones_disponibles.map((accion) => (
                        <SelectItem key={accion.value} value={accion.value}>
                          {accion.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <label className="text-sm font-medium">Tabla</label>
                  <Select
                    value={localFiltros.tabla || ''}
                    onValueChange={(value) => handleFiltroChange('tabla', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccionar tabla" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Todas las tablas</SelectItem>
                      {tablas_disponibles.map((tabla) => (
                        <SelectItem key={tabla.value} value={tabla.value}>
                          {tabla.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <label className="text-sm font-medium">Severidad</label>
                  <Select
                    value={localFiltros.severidad || ''}
                    onValueChange={(value) => handleFiltroChange('severidad', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccionar severidad" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Todas las severidades</SelectItem>
                      {severidades_disponibles.map((severidad) => (
                        <SelectItem key={severidad.value} value={severidad.value}>
                          {severidad.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <label className="text-sm font-medium">Fecha inicio</label>
                  <Input
                    type="date"
                    value={localFiltros.fecha_inicio || ''}
                    onChange={(e) => handleFiltroChange('fecha_inicio', e.target.value)}
                  />
                </div>

                <div>
                  <label className="text-sm font-medium">Fecha fin</label>
                  <Input
                    type="date"
                    value={localFiltros.fecha_fin || ''}
                    onChange={(e) => handleFiltroChange('fecha_fin', e.target.value)}
                  />
                </div>
              </div>

              <div className="flex space-x-2">
                <Button onClick={aplicarFiltros}>
                  <Search className="mr-2 h-4 w-4" />
                  Aplicar Filtros
                </Button>
                <Button variant="outline" onClick={limpiarFiltros}>
                  Limpiar
                </Button>
                <Button variant="outline" onClick={exportarCompliance}>
                  <Download className="mr-2 h-4 w-4" />
                  Exportar CSV
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Tabla de Logs */}
        <Card>
          <CardHeader>
            <CardTitle>Registro de Actividades</CardTitle>
            <CardDescription>
              {logs.total} registros encontrados - Página {logs.current_page} de {logs.last_page}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b">
                    <th className="text-left p-2">Fecha/Hora</th>
                    <th className="text-left p-2">Usuario</th>
                    <th className="text-left p-2">Acción</th>
                    <th className="text-left p-2">Tabla</th>
                    <th className="text-left p-2">IP</th>
                    <th className="text-left p-2">Severidad</th>
                    <th className="text-left p-2">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {logs.data.map((log) => (
                    <tr key={log.id} className="border-b hover:bg-muted/50">
                      <td className="p-2">
                        <div className="text-sm">
                          {format(new Date(log.created_at), 'dd/MM/yyyy HH:mm', { locale: es })}
                        </div>
                        <div className="text-xs text-muted-foreground">
                          {log.tiempo_transcurrido}
                        </div>
                      </td>
                      <td className="p-2">
                        <div className="text-sm font-medium">
                          {log.created_by_name}
                        </div>
                        {log.usuario && (
                          <div className="text-xs text-muted-foreground">
                            ID: {log.usuario_id}
                          </div>
                        )}
                      </td>
                      <td className="p-2">
                        <Badge variant="outline">
                          {log.descripcion_accion}
                        </Badge>
                      </td>
                      <td className="p-2">
                        <span className="text-sm">
                          {log.tabla_afectada || '-'}
                        </span>
                        {log.registro_id && (
                          <div className="text-xs text-muted-foreground">
                            ID: {log.registro_id}
                          </div>
                        )}
                      </td>
                      <td className="p-2">
                        <span className="text-sm font-mono">
                          {log.ip_address}
                        </span>
                      </td>
                      <td className="p-2">
                        <div className="flex items-center space-x-1">
                          {getSeverityIcon(log.severidad)}
                          <Badge className={getSeverityColor(log.severidad)}>
                            {log.severidad.toUpperCase()}
                          </Badge>
                        </div>
                      </td>
                      <td className="p-2">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => router.visit(route('audit.show', log.id))}
                        >
                          <Eye className="h-4 w-4" />
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Paginación */}
            {logs.last_page > 1 && (
              <div className="flex items-center justify-between mt-4">
                <div className="text-sm text-muted-foreground">
                  Mostrando {((logs.current_page - 1) * logs.per_page) + 1} a{' '}
                  {Math.min(logs.current_page * logs.per_page, logs.total)} de {logs.total} registros
                </div>
                <div className="flex space-x-1">
                  {logs.links.map((link, index) => {
                    // Limpiar las etiquetas HTML de los enlaces
                    const cleanLabel = link.label
                      .replace(/&laquo;/g, '«')
                      .replace(/&raquo;/g, '»')
                      .replace(/&hellip;/g, '...')
                    
                    return (
                      <Button
                        key={index}
                        variant={link.active ? "default" : "outline"}
                        size="sm"
                        disabled={!link.url}
                        onClick={() => link.url && irAPagina(link.url)}
                      >
                        {cleanLabel}
                      </Button>
                    )
                  })}
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  )
} 