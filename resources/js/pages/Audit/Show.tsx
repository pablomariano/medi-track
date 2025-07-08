import { Head, router } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft, User, Globe, Calendar, Database, Activity } from 'lucide-react'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'

interface AuditLog {
  id: number
  usuario_id: number | null
  created_by_name: string
  accion: string
  tabla_afectada: string | null
  registro_id: number | null
  datos_anteriores: any
  datos_nuevos: any
  ip_address: string
  user_agent: string | null
  metodo_http: string | null
  url: string | null
  ruta: string | null
  contexto_adicional: any
  session_id: string | null
  severidad: 'low' | 'medium' | 'high' | 'critical'
  created_at: string
  descripcion_accion: string
  tiempo_transcurrido: string
  usuario?: {
    id: number
    name: string
    email: string
  }
}

interface Props {
  log: AuditLog
  cambios_detallados: Array<{
    campo: string
    anterior: any
    nuevo: any
  }> | null
}

export default function AuditShow({ log, cambios_detallados }: Props) {
  const getSeverityColor = (severidad: string) => {
    const colors = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800'
    }
    return colors[severidad as keyof typeof colors] || colors.low
  }

  const formatValue = (value: any) => {
    if (value === null || value === undefined) {
      return <span className="text-gray-400 italic">null</span>
    }
    if (typeof value === 'boolean') {
      return value ? 'true' : 'false'
    }
    if (typeof value === 'object') {
      return <pre className="text-xs bg-gray-50 p-2 rounded">{JSON.stringify(value, null, 2)}</pre>
    }
    return String(value)
  }

  return (
    <AppLayout>
      <Head title={`Log de Auditoría #${log.id}`} />

      <div className="container mx-auto p-6 space-y-6 max-w-none">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.visit(route('audit.index'))}
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Log de Auditoría #{log.id}</h1>
            <p className="text-muted-foreground">
              {log.descripcion_accion} &middot; {log.tiempo_transcurrido}
            </p>
          </div>
        </div>

        <div className="space-y-6 w-full max-w-6xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Información General */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Activity className="h-5 w-5" /> Información General
                </CardTitle>
                <CardDescription>Datos principales del evento registrado</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">ID</label>
                    <p className="text-lg font-mono">{log.id}</p>
                  </div>
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">Acción</label>
                    <div className="mt-1">
                      <Badge variant="outline">{log.descripcion_accion}</Badge>
                    </div>
                  </div>
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">Severidad</label>
                    <div className="mt-1">
                      <Badge className={getSeverityColor(log.severidad)}>
                        {log.severidad.toUpperCase()}
                      </Badge>
                    </div>
                  </div>
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">Fecha y Hora</label>
                    <p className="flex items-center mt-1">
                      <Calendar className="mr-2 h-4 w-4" />
                      {format(new Date(log.created_at), 'dd/MM/yyyy HH:mm:ss', { locale: es })}
                    </p>
                  </div>
                  {log.tabla_afectada && (
                    <div className="col-span-1 sm:col-span-2">
                      <label className="text-xs font-medium text-muted-foreground">Tabla</label>
                      <p className="flex items-center mt-1">
                        <Database className="mr-2 h-4 w-4" />
                        {log.tabla_afectada}
                        {log.registro_id && (
                          <span className="ml-4 text-xs text-muted-foreground">ID: {log.registro_id}</span>
                        )}
                      </p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Información del Usuario */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" /> Usuario y Contexto
                </CardTitle>
                <CardDescription>Información del usuario y contexto de sesión</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="text-xs font-medium text-muted-foreground">Usuario</label>
                  <p className="text-base font-medium">{log.created_by_name}</p>
                  {log.usuario && (
                    <p className="text-xs text-muted-foreground">
                      ID: {log.usuario_id} &middot; {log.usuario.email}
                    </p>
                  )}
                </div>
                <div>
                  <label className="text-xs font-medium text-muted-foreground">Dirección IP</label>
                  <p className="flex items-center">
                    <Globe className="mr-2 h-4 w-4" />
                    <span className="font-mono text-sm">{log.ip_address}</span>
                  </p>
                </div>
                {log.session_id && (
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">ID de Sesión</label>
                    <p className="font-mono text-xs">{log.session_id}</p>
                  </div>
                )}
                {log.user_agent && (
                  <div>
                    <label className="text-xs font-medium text-muted-foreground">User Agent</label>
                    <p className="text-xs text-muted-foreground break-all bg-muted rounded p-2 mt-1">{log.user_agent}</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Información de la Request y Contexto */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {(log.metodo_http || log.url || log.ruta) && (
              <Card>
                <CardHeader>
                  <CardTitle>Información de la Request</CardTitle>
                  <CardDescription>Detalles técnicos de la petición HTTP</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {log.metodo_http && (
                    <div>
                      <label className="text-xs font-medium text-muted-foreground">Método HTTP</label>
                      <div className="mt-1">
                        <Badge variant="outline">{log.metodo_http}</Badge>
                      </div>
                    </div>
                  )}
                  {log.url && (
                    <div>
                      <label className="text-xs font-medium text-muted-foreground">URL</label>
                      <p className="text-xs font-mono bg-muted p-2 rounded break-all mt-1">
                        {log.url}
                      </p>
                    </div>
                  )}
                  {log.ruta && (
                    <div>
                      <label className="text-xs font-medium text-muted-foreground">Ruta</label>
                      <p className="text-xs font-mono mt-1">{log.ruta}</p> 
                    </div>
                  )}
                </CardContent>
              </Card>
            )}
            {log.contexto_adicional && (
              <Card>
                <CardHeader>
                  <CardTitle>Contexto Adicional</CardTitle>
                  <CardDescription>Información extra relevante para el evento</CardDescription>
                </CardHeader>
                <CardContent>
                  <pre className="text-xs bg-muted p-4 rounded-lg overflow-auto max-h-40">
                    {JSON.stringify(log.contexto_adicional, null, 2)}
                  </pre>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Cambios Detallados */}
          {cambios_detallados && cambios_detallados.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Cambios Realizados</CardTitle>
                <CardDescription>Comparación de valores antes y después</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {cambios_detallados.map((cambio, index) => (
                    <div key={index} className="border rounded-lg p-4 bg-muted/50">
                      <h4 className="font-medium mb-2">Campo: <span className="font-mono text-xs">{cambio.campo}</span></h4>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label className="text-xs font-medium text-red-600">Valor Anterior</label>
                          <div className="mt-1 p-2 bg-red-50 rounded">
                            {formatValue(cambio.anterior)}
                          </div>
                        </div>
                        <div>
                          <label className="text-xs font-medium text-green-600">Valor Nuevo</label>
                          <div className="mt-1 p-2 bg-green-50 rounded">
                            {formatValue(cambio.nuevo)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}

          {/* Datos Completos */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {log.datos_anteriores && (
              <Card>
                <CardHeader>
                  <CardTitle>Datos Anteriores</CardTitle>
                  <CardDescription>Estado previo del registro</CardDescription>
                </CardHeader>
                <CardContent>
                  <pre className="text-xs bg-muted p-4 rounded-lg overflow-auto max-h-60">
                    {JSON.stringify(log.datos_anteriores, null, 2)}
                  </pre>
                </CardContent>
              </Card>
            )}
            {log.datos_nuevos && (
              <Card>
                <CardHeader>
                  <CardTitle>Datos Nuevos</CardTitle>
                  <CardDescription>Estado posterior del registro</CardDescription>
                </CardHeader>
                <CardContent>
                  <pre className="text-xs bg-muted p-4 rounded-lg overflow-auto max-h-60">
                    {JSON.stringify(log.datos_nuevos, null, 2)}
                  </pre>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  )
} 