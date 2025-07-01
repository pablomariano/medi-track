# 🎯 Plan de Acción: Encuesta de Adherencia Morisky y Análisis de Datos

**Objetivo**: Implementar la escala MMAS-8 para medir adherencia terapéutica subjetiva e integrarla con las métricas objetivas existentes.

## 📋 ESTADO ACTUAL VERIFICADO

### ✅ Lo que YA está implementado:
- Sistema completo de administración de medicamentos
- Cálculo automático de adherencia objetiva
- Dashboard con gráficos básicos de adherencia
- Tablas `estadisticas_consumo` y `resumen_adherencia_paciente`
- Sistema de auditoría y roles funcionando

### ❌ Lo que FALTA implementar:
- Encuesta de adherencia Morisky MMAS-8
- Correlación entre adherencia objetiva vs subjetiva
- Análisis predictivo de patrones
- Alertas inteligentes basadas en discrepancias

---

## 🚀 FASE 1: Implementación Encuesta MMAS-8
**Duración estimada**: 2-3 semanas

### 1.1 Base de Datos
```sql
CREATE TABLE encuestas_adherencia_morisky (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    paciente_id BIGINT NOT NULL,
    fecha_aplicacion DATE NOT NULL,
    pregunta_1 BOOLEAN COMMENT 'Olvida tomar medicamentos',
    pregunta_2 BOOLEAN COMMENT 'Dejó de tomar últimas 2 semanas',
    pregunta_3 BOOLEAN COMMENT 'Redujo dosis sin avisar médico',
    pregunta_4 BOOLEAN COMMENT 'Olvida llevar medicamentos al viajar',
    pregunta_5 BOOLEAN COMMENT 'Tomó medicamentos ayer',
    pregunta_6 BOOLEAN COMMENT 'Deja de tomar cuando se siente mejor',
    pregunta_7 BOOLEAN COMMENT 'Se siente molesto por seguir tratamiento',
    pregunta_8 TINYINT COMMENT 'Frecuencia dificultad recordar (0-4)',
    puntaje_total DECIMAL(3,1),
    categoria_adherencia ENUM('alta', 'media', 'baja'),
    aplicada_por_usuario_id BIGINT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (aplicada_por_usuario_id) REFERENCES users(id),
    INDEX idx_paciente_fecha (paciente_id, fecha_aplicacion)
);
```

### 1.2 Modelo Laravel
```php
// app/Models/EncuestaAdherenciaMorisky.php
class EncuestaAdherenciaMorisky extends Model {
    protected $fillable = [
        'paciente_id', 'fecha_aplicacion', 'pregunta_1', 'pregunta_2',
        'pregunta_3', 'pregunta_4', 'pregunta_5', 'pregunta_6',
        'pregunta_7', 'pregunta_8', 'aplicada_por_usuario_id', 'observaciones'
    ];
    
    public function calcularPuntaje() {
        // Lógica MMAS-8: preguntas 1-7 (1 punto c/u), pregunta 8 (0-4 puntos)
        // Total máximo: 8 puntos
        // Alta: 8, Media: 6-7, Baja: <6
    }
}
```

### 1.3 Controlador
```php
// app/Http/Controllers/EncuestaAdherenciaController.php
class EncuestaAdherenciaController extends Controller {
    public function create($pacienteId) {
        // Formulario de encuesta
    }
    
    public function store(Request $request) {
        // Guardar respuestas y calcular puntaje automáticamente
    }
    
    public function analytics($pacienteId) {
        // Análisis de correlación con adherencia objetiva
    }
}
```

### 1.4 Componente React
```typescript
// resources/js/components/MoriskySurvey.tsx
const MoriskySurvey = ({ pacienteId }: { pacienteId: number }) => {
    const preguntas = [
        "¿Olvida a veces tomar sus medicamentos?",
        "En las últimas 2 semanas, ¿hubo algún día en que no tomó sus medicamentos?",
        // ... resto de preguntas MMAS-8
    ];
    
    // Formulario con validación y cálculo automático
};
```

---

## 🔬 FASE 2: Análisis de Correlación
**Duración estimada**: 1-2 semanas

### 2.1 Service de Análisis
```php
// app/Services/AdherenceAnalysisService.php
class AdherenceAnalysisService {
    public function generateReport(int $pacienteId, Carbon $fechaInicio, Carbon $fechaFin) {
        $adherenciaObjetiva = $this->getObjectiveAdherence($pacienteId, $fechaInicio, $fechaFin);
        $adherenciaSubjetiva = $this->getSubjectiveAdherence($pacienteId, $fechaInicio, $fechaFin);
        
        return [
            'adherencia_objetiva' => $adherenciaObjetiva,
            'adherencia_subjetiva' => $adherenciaSubjetiva,
            'discrepancia' => abs($adherenciaObjetiva - $adherenciaSubjetiva),
            'factores_riesgo' => $this->identifyRiskFactors($pacienteId),
            'recomendaciones' => $this->generateRecommendations($adherenciaObjetiva, $adherenciaSubjetiva)
        ];
    }
}
```

### 2.2 Dashboard Actualizado
```typescript
// Nuevos componentes para dashboard
const AdherenceCorrelationChart = () => {
    // Gráfico que muestra adherencia objetiva vs subjetiva
};

const RiskFactorsWidget = () => {
    // Widget que identifica factores de riesgo específicos
};
```

---

## 📊 FASE 3: Funcionalidades Avanzadas
**Duración estimada**: 2-3 semanas

### 3.1 Alertas Inteligentes
- Discrepancia >20% entre adherencia objetiva y subjetiva
- Tendencia descendente en puntaje Morisky
- Identificación automática de barreras específicas

### 3.2 Reportes Automatizados
- Reporte semanal para médicos con análisis de adherencia
- Dashboard específico por rol (médico, cuidador, administrador)
- Métricas comparativas entre pacientes

### 3.3 Análisis Predictivo
- Identificación de patrones temporales
- Factores de riesgo personalizados
- Recomendaciones automáticas de intervención

---

## 🎯 MÉTRICAS DE ÉXITO

### Implementación:
- [ ] Encuesta MMAS-8 funcional en 100% de casos
- [ ] Dashboard con correlación visual implementado
- [ ] Reportes automatizados generándose semanalmente
- [ ] Sistema de alertas funcionando

### Impacto:
- [ ] Correlación >0.6 entre adherencia objetiva y subjetiva
- [ ] Identificación temprana de riesgo en >85% de casos
- [ ] Reducción 30% tiempo análisis médico manual
- [ ] Mejora 10% en adherencia general del sistema

---

## 📅 CRONOGRAMA RECOMENDADO

### Semana 1-2: Base Técnica
- Migración base de datos
- Modelo Eloquent y relaciones
- Controlador básico con CRUD

### Semana 3-4: Frontend
- Componente React de encuesta
- Integración con dashboard existente
- Validaciones y UX

### Semana 5-6: Análisis
- Service de correlación
- Métricas avanzadas
- Reportes automatizados

### Semana 7-8: Optimización
- Testing integral
- Performance optimization
- Documentación y capacitación

---

## 🔧 CONSIDERACIONES DE IMPLEMENTACIÓN

### Simplicidad (MVP):
1. Empezar con encuesta básica MMAS-8
2. Correlación simple objetiva vs subjetiva
3. Dashboard con gráficos básicos
4. Alertas por umbrales fijos

### Escalabilidad (Futuro):
1. Machine learning para predicciones
2. Análisis multivariable avanzado
3. Integración con wearables
4. API para sistemas externos

### Datos Requeridos:
- Aplicar MMAS-8 mensualmente por paciente
- Mantener consistencia con adherencia objetiva
- Registrar contexto de aplicación (quién, cuándo, dónde)
- Documentar observaciones cualitativas

Este plan mantiene el enfoque en simplicidad inicial con capacidad de escalamiento futuro, proporcionando valor inmediato mientras construye la base para análisis más sofisticados. 