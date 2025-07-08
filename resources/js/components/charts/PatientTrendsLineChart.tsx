import React, { useEffect, useState } from 'react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface TrendData {
  fecha: string; // e.g. '2024-06-01'
  promedio_retraso: number; // minutos positivos
  promedio_adelanto: number; // minutos positivos
  variabilidad: number; // std dev
}

interface Props {
  pacienteId: number;
  apiEndpoint: string;
  theme?: 'light' | 'dark';
}

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
};

export default function PatientTrendsLineChart({ pacienteId, apiEndpoint, theme = 'light' }: Props) {
  const [data, setData] = useState<TrendData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    setError(null);
    
    fetch(apiEndpoint, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      credentials: 'same-origin',
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((json) => {
        if (json.success === false) {
          throw new Error(json.message || 'Error en la respuesta del servidor');
        }
        setData(json.data || []);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching trends data:', error);
        setError(error.message);
        setLoading(false);
      });
  }, [apiEndpoint, pacienteId]);

  // Colores del theme
  const colorRetraso = theme === 'dark' ? '#60a5fa' : '#2563eb'; // azul
  const colorAdelanto = theme === 'dark' ? '#38bdf8' : '#0ea5e9'; // celeste
  const colorVariabilidad = theme === 'dark' ? '#facc15' : '#ca8a04'; // amarillo

  return (
    <Card className="bg-background">
      <CardHeader>
        <CardTitle className="text-base font-semibold">Tendencias de Retraso/Adelanto y Variabilidad</CardTitle>
        <span className="text-xs text-muted-foreground">Últimos 21 días</span>
      </CardHeader>
      <CardContent>
        {loading ? (
          <div className="h-72 w-full flex items-center justify-center">
            <div className="text-muted-foreground">Cargando datos...</div>
          </div>
        ) : error ? (
          <div className="h-72 w-full flex items-center justify-center">
            <div className="text-destructive text-center">
              <p className="text-sm font-medium">Error al cargar datos</p>
              <p className="text-xs mt-1">{error}</p>
            </div>
          </div>
        ) : data.length === 0 ? (
          <div className="h-72 w-full flex items-center justify-center">
            <div className="text-muted-foreground text-center">
              <p className="text-sm">No hay datos disponibles</p>
              <p className="text-xs mt-1">No se encontraron administraciones en el período seleccionado</p>
            </div>
          </div>
        ) : (
          <div className="h-72 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={data} margin={{ top: 24, right: 32, left: 8, bottom: 8 }}>
                <CartesianGrid strokeDasharray="3 3" stroke={theme === 'dark' ? '#222' : '#eee'} />
                <XAxis dataKey="fecha" tickFormatter={formatDate} tick={{ fontSize: 12, fill: theme === 'dark' ? '#aaa' : '#444' }} />
                <YAxis tick={{ fontSize: 12, fill: theme === 'dark' ? '#aaa' : '#444' }} />
                <Tooltip
                  contentStyle={{ background: theme === 'dark' ? '#18181b' : '#fff', border: '1px solid #333', borderRadius: 8 }}
                  labelFormatter={(label) => `Día: ${formatDate(label)}`}
                  formatter={(value: any, name: string) => {
                    if (name === 'promedio_retraso') return [`${value} min`, 'Retraso'];
                    if (name === 'promedio_adelanto') return [`${value} min`, 'Adelanto'];
                    if (name === 'variabilidad') return [`${value} min`, 'Variabilidad'];
                    return value;
                  }}
                />
                <Legend
                  verticalAlign="top"
                  align="right"
                  iconType="circle"
                  wrapperStyle={{ fontSize: 13, color: theme === 'dark' ? '#fff' : '#222' }}
                />
                <Line
                  type="monotone"
                  dataKey="promedio_retraso"
                  name="Retraso"
                  stroke={colorRetraso}
                  strokeWidth={3}
                  dot={{ r: 4 }}
                  activeDot={{ r: 6 }}
                />
                <Line
                  type="monotone"
                  dataKey="promedio_adelanto"
                  name="Adelanto"
                  stroke={colorAdelanto}
                  strokeWidth={3}
                  dot={{ r: 4 }}
                  activeDot={{ r: 6 }}
                />
                <Line
                  type="monotone"
                  dataKey="variabilidad"
                  name="Variabilidad"
                  stroke={colorVariabilidad}
                  strokeDasharray="6 3"
                  strokeWidth={2}
                  dot={{ r: 3 }}
                  activeDot={{ r: 5 }}
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        )}
      </CardContent>
    </Card>
  );
} 