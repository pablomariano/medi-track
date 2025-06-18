import { Head, router } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center space-x-4">
          <Button
            variant="outline"
            onClick={() => router.visit(route('audit.index'))}
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Volver
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">
              Log de Auditoría #{log.id}
            </h1>
            <p className="text-muted-foreground">
              {log.descripcion_accion} - {log.tiempo_transcurrido}
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Información General */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Activity className="mr-2 h-5 w-5" />
                Información General
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-gray-500">ID</label>
                  <p className="text-lg font-mono">{log.id}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">Acción</label>
                  <div className="mt-1">
                    <Badge variant="outline">{log.descripcion_accion}</Badge>
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-gray-500">Severidad</label>
                  <div className="mt-1">
                    <Badge className={getSeverityColor(log.severidad)}>
                      {log.severidad.toUpperCase()}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">Fecha y Hora</label>
                  <p className="flex items-center mt-1">
                    <Calendar className="mr-2 h-4 w-4" />
                    {format(new Date(log.created_at), 'dd/MM/yyyy HH:mm:ss', { locale: es })}
                  </p>
                </div>
              </div>

              {log.tabla_afectada && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-gray-500">Tabla</label>
                    <p className="flex items-center mt-1">
                      <Database className="mr-2 h-4 w-4" />
                      {log.tabla_afectada}
                    </p>
                  </div>
                  {log.registro_id && (
                    <div>
                      <label className="text-sm font-medium text-gray-500">ID del Registro</label>
                      <p className="text-lg font-mono">{log.registro_id}</p>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Información del Usuario */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <User className="mr-2 h-5 w-5" />
                Usuario y Contexto
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="text-sm font-medium text-gray-500">Usuario</label>
                <p className="text-lg">{log.created_by_name}</p>
                {log.usuario && (
                  <p className="text-sm text-gray-500">
                    ID: {log.usuario_id} - {log.usuario.email}
                  </p>
                )}
              </div>

              <div>
                <label className="text-sm font-medium text-gray-500">Dirección IP</label>
                <p className="flex items-center">
                  <Globe className="mr-2 h-4 w-4" />
                  <span className="font-mono">{log.ip_address}</span>
                </p>
              </div>

              {log.session_id && (
                <div>
                  <label className="text-sm font-medium text-gray-500">ID de Sesión</label>
                  <p className="font-mono text-sm">{log.session_id}</p>
                </div>
              )}

              {log.user_agent && (
                <div>
                  <label className="text-sm font-medium text-gray-500">User Agent</label>
                  <p className="text-sm text-gray-600 break-all">{log.user_agent}</p>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Información de la Request */}
          {(log.metodo_http || log.url || log.ruta) && (
            <Card>
              <CardHeader>
                <CardTitle>Información de la Request</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {log.metodo_http && (
                  <div>
                    <label className="text-sm font-medium text-gray-500">Método HTTP</label>
                    <Badge variant="outline">{log.metodo_http}</Badge>
                  </div>
                )}

                {log.url && (
                  <div>
                    <label className="text-sm font-medium text-gray-500">URL</label>
                    <p className="text-sm font-mono bg-gray-50 p-2 rounded break-all">
                      {log.url}
                    </p>
                  </div>
                )}

                {log.ruta && (
                  <div>
                    <label className="text-sm font-medium text-gray-500">Ruta</label>
                    <p className="text-sm font-mono">{log.ruta}</p>
                  </div>
                )}
              </CardContent>
            </Card>
          )}

          {/* Contexto Adicional */}
          {log.contexto_adicional && (
            <Card>
              <CardHeader>
                <CardTitle>Contexto Adicional</CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="text-xs bg-gray-50 p-4 rounded-lg overflow-auto max-h-40">
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
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {cambios_detallados.map((cambio, index) => (
                  <div key={index} className="border rounded-lg p-4">
                    <h4 className="font-medium mb-2">Campo: {cambio.campo}</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="text-sm font-medium text-red-600">Valor Anterior</label>
                        <div className="mt-1 p-2 bg-red-50 rounded">
                          {formatValue(cambio.anterior)}
                        </div>
                      </div>
                      <div>
                        <label className="text-sm font-medium text-green-600">Valor Nuevo</label>
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
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {log.datos_anteriores && (
            <Card>
              <CardHeader>
                <CardTitle>Datos Anteriores</CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="text-xs bg-gray-50 p-4 rounded-lg overflow-auto max-h-60">
                  {JSON.stringify(log.datos_anteriores, null, 2)}
                </pre>
              </CardContent>
            </Card>
          )}

          {log.datos_nuevos && (
            <Card>
              <CardHeader>
                <CardTitle>Datos Nuevos</CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="text-xs bg-gray-50 p-4 rounded-lg overflow-auto max-h-60">
                  {JSON.stringify(log.datos_nuevos, null, 2)}
                </pre>
              </CardContent>
            </Card>
          )}
        </div>
      </div>
    </AppLayout>
  )
} 