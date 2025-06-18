import { Head, router } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { 
  Activity, 
  AlertTriangle, 
  Users, 
  Clock, 
  Eye, 
  Globe,
  TrendingUp,
  Database 
} from 'lucide-react'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'

interface Estadisticas {
  total_acciones: number
  acciones_criticas: number
  usuarios_activos: number
  acciones_por_dia: Array<{ fecha: string; total: number }>
  acciones_por_tipo: Array<{ accion: string; total: number }>
  usuarios_mas_activos: Array<{ 
    usuario_id: number
    created_by_name: string
    total_acciones: number 
  }>
  tablas_mas_modificadas: Array<{
    tabla_afectada: string
    total: number
  }>
}

interface ActividadSospechosa {
  intentos_fallidos: Array<{ ip_address: string; intentos: number }>
  accesos_fuera_horario: Array<any>
  actividad_intensa: Array<any>
}

interface LogCritico {
  id: number
  created_by_name: string
  accion: string
  tabla_afectada: string | null
  severidad: string
  created_at: string
  descripcion_accion: string
  tiempo_transcurrido: string
  usuario?: {
    name: string
    email: string
  }
}

interface AccesoPorIp {
  ip_address: string
  total: number
  ultimo_acceso: string
}

interface Props {
  estadisticas: Estadisticas
  actividad_sospechosa: ActividadSospechosa
  logs_criticos: LogCritico[]
  accesos_por_ip: AccesoPorIp[]
}

export default function AuditDashboard({
  estadisticas,
  actividad_sospechosa,
  logs_criticos,
  accesos_por_ip
}: Props) {
  const getSeverityColor = (severidad: string) => {
    const colors = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800'
    }
    return colors[severidad as keyof typeof colors] || colors.low
  }

  const getActionColor = (accion: string) => {
    const colors = {
      create: 'bg-green-100 text-green-800',
      update: 'bg-blue-100 text-blue-800',
      delete: 'bg-red-100 text-red-800',
      access: 'bg-gray-100 text-gray-800',
      login: 'bg-purple-100 text-purple-800',
      logout: 'bg-orange-100 text-orange-800'
    }
    return colors[accion as keyof typeof colors] || colors.access
  }

  return (
    <AppLayout>
      <Head title="Dashboard de Auditoría" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Dashboard de Auditoría</h1>
            <p className="text-muted-foreground">
              Vista ejecutiva del sistema de monitoreo y actividad
            </p>
          </div>
          <Button
            variant="outline"
            onClick={() => router.visit(route('audit.index'))}
          >
            <Database className="mr-2 h-4 w-4" />
            Ver Todos los Logs
          </Button>
        </div>

        {/* Métricas Principales */}
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
              <CardTitle className="text-sm font-medium">Actividad Sospechosa</CardTitle>
              <AlertTriangle className="h-4 w-4 text-orange-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-orange-600">
                {actividad_sospechosa.intentos_fallidos.length + 
                 actividad_sospechosa.accesos_fuera_horario.length +
                 actividad_sospechosa.actividad_intensa.length}
              </div>
              <p className="text-xs text-muted-foreground">Eventos detectados</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Actividad por Tipo */}
          <Card>
            <CardHeader>
              <CardTitle>Actividad por Tipo</CardTitle>
              <CardDescription>Distribución de acciones en los últimos 30 días</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {estadisticas.acciones_por_tipo.slice(0, 6).map((item, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                      <Badge className={getActionColor(item.accion)}>
                        {item.accion}
                      </Badge>
                    </div>
                    <div className="text-sm font-medium">
                      {item.total.toLocaleString()} acciones
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Usuarios Más Activos */}
          <Card>
            <CardHeader>
              <CardTitle>Usuarios Más Activos</CardTitle>
              <CardDescription>Top usuarios por número de acciones</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {estadisticas.usuarios_mas_activos.slice(0, 5).map((usuario, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                      <div className="flex items-center justify-center w-6 h-6 bg-primary/10 rounded-full text-xs font-medium">
                        {index + 1}
                      </div>
                      <span className="text-sm font-medium">{usuario.created_by_name}</span>
                    </div>
                    <Badge variant="outline">
                      {usuario.total_acciones} acciones
                    </Badge>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Tablas Más Modificadas */}
          <Card>
            <CardHeader>
              <CardTitle>Tablas Más Modificadas</CardTitle>
              <CardDescription>Recursos con mayor actividad</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {estadisticas.tablas_mas_modificadas.slice(0, 5).map((tabla, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                      <Database className="h-4 w-4 text-muted-foreground" />
                      <span className="text-sm font-medium">{tabla.tabla_afectada}</span>
                    </div>
                    <Badge variant="outline">
                      {tabla.total} modificaciones
                    </Badge>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Accesos por IP */}
          <Card>
            <CardHeader>
              <CardTitle>Accesos por IP</CardTitle>
              <CardDescription>Direcciones IP más activas (últimos 7 días)</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {accesos_por_ip.slice(0, 5).map((acceso, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                      <Globe className="h-4 w-4 text-muted-foreground" />
                      <span className="text-sm font-mono">{acceso.ip_address}</span>
                    </div>
                    <div className="text-right">
                      <div className="text-sm font-medium">{acceso.total} accesos</div>
                      <div className="text-xs text-muted-foreground">
                        {format(new Date(acceso.ultimo_acceso), 'dd/MM HH:mm', { locale: es })}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Logs Críticos Recientes */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <AlertTriangle className="mr-2 h-5 w-5 text-red-500" />
              Eventos Críticos Recientes
            </CardTitle>
            <CardDescription>
              Últimas 10 acciones marcadas como críticas
            </CardDescription>
          </CardHeader>
          <CardContent>
            {logs_criticos.length > 0 ? (
              <div className="space-y-3">
                {logs_criticos.map((log) => (
                  <div key={log.id} className="flex items-center justify-between p-3 border rounded-lg bg-red-50/50">
                    <div className="flex-1">
                      <div className="flex items-center space-x-2">
                        <Badge className={getSeverityColor(log.severidad)}>
                          {log.severidad.toUpperCase()}
                        </Badge>
                        <span className="font-medium">{log.descripcion_accion}</span>
                      </div>
                      <div className="text-sm text-muted-foreground mt-1">
                        Por {log.created_by_name} - {log.tiempo_transcurrido}
                        {log.tabla_afectada && (
                          <span className="ml-2">en {log.tabla_afectada}</span>
                        )}
                      </div>
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => router.visit(route('audit.show', log.id))}
                    >
                      <Eye className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-center text-muted-foreground py-8">
                No hay eventos críticos recientes
              </p>
            )}
          </CardContent>
        </Card>

        {/* Actividad Sospechosa */}
        {(actividad_sospechosa.intentos_fallidos.length > 0 || 
          actividad_sospechosa.accesos_fuera_horario.length > 0 ||
          actividad_sospechosa.actividad_intensa.length > 0) && (
          <Card className="border-orange-200">
            <CardHeader>
              <CardTitle className="flex items-center text-orange-700">
                <AlertTriangle className="mr-2 h-5 w-5" />
                Actividad Sospechosa Detectada
              </CardTitle>
              <CardDescription>
                El sistema ha detectado patrones de actividad que requieren revisión
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {actividad_sospechosa.intentos_fallidos.length > 0 && (
                <div>
                  <h4 className="font-medium text-red-700 mb-2">Múltiples Intentos Fallidos</h4>
                  <div className="space-y-2">
                    {actividad_sospechosa.intentos_fallidos.map((item, index) => (
                      <div key={index} className="flex justify-between items-center p-2 bg-red-50 rounded">
                        <span className="font-mono text-sm">{item.ip_address}</span>
                        <Badge variant="destructive">{item.intentos} intentos</Badge>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {actividad_sospechosa.accesos_fuera_horario.length > 0 && (
                <div>
                  <h4 className="font-medium text-orange-700 mb-2">
                    Accesos Fuera de Horario Normal ({actividad_sospechosa.accesos_fuera_horario.length} eventos)
                  </h4>
                  <p className="text-sm text-muted-foreground">
                    Se detectaron accesos entre las 22:00 y 6:00
                  </p>
                </div>
              )}

              {actividad_sospechosa.actividad_intensa.length > 0 && (
                <div>
                  <h4 className="font-medium text-yellow-700 mb-2">
                    Actividad Inusualmente Intensa ({actividad_sospechosa.actividad_intensa.length} usuarios)
                  </h4>
                  <p className="text-sm text-muted-foreground">
                    Usuarios con más de 50 acciones en la última hora
                  </p>
                </div>
              )}
            </CardContent>
          </Card>
        )}

        {/* Actividad por Día */}
        {estadisticas.acciones_por_dia.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <TrendingUp className="mr-2 h-5 w-5" />
                Tendencia de Actividad (Últimos 30 días)
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {estadisticas.acciones_por_dia.slice(0, 7).map((dia, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <span className="text-sm">{format(new Date(dia.fecha), 'dd/MM/yyyy', { locale: es })}</span>
                    <div className="flex items-center space-x-2">
                      <div className="bg-primary/10 h-2 rounded-full" style={{
                        width: `${Math.max(10, (dia.total / Math.max(...estadisticas.acciones_por_dia.map(d => d.total))) * 100)}px`
                      }} />
                      <span className="text-sm font-medium">{dia.total}</span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  )
} 