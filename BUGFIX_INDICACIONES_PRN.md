# BUGFIX: Error SQL en Indicaciones PRN

## Problema Identificado

**Error SQL**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'indicaciones_prn.tratamiento_id' in 'where clause'`

### Síntomas
- El error ocurría al intentar visualizar tratamientos que tienen indicaciones PRN
- La consulta fallaba en el controlador `TratamientoController::show()` cuando intentaba cargar la relación `indicacionesPrn.sintoma`
- Específicamente en la línea que ejecutaba `$tratamiento->load(['indicacionesPrn.sintoma'])`

### Causa Raíz
El modelo `Tratamiento` tenía una relación `indicacionesPrn()` incorrectamente definida que asumía la existencia de una columna `tratamiento_id` en la tabla `indicaciones_prn`.

**Arquitectura Real de la Base de Datos**:
```
tratamientos → medicamentos_tratamientos (pivot) → indicaciones_prn
```

**Relación Incorrecta**:
```php
// En el modelo Tratamiento
public function indicacionesPrn()
{
    return $this->hasMany(IndicacionPrn::class); // ❌ Busca 'tratamiento_id'
}
```

La tabla `indicaciones_prn` tiene `medicamento_tratamiento_id`, NO `tratamiento_id`.

## Solución Implementada

### 1. Corrección del Modelo Tratamiento
```php
// Antes (líneas 80-82)
public function indicacionesPrn()
{
    return $this->hasMany(IndicacionPrn::class);
}

// Después 
public function indicacionesPrn()
{
    $medicamentoTratamientoIds = $this->medicamentos()->pluck('medicamentos_tratamientos.id');
    return \App\Models\IndicacionPrn::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds);
}
```

### 2. Corrección del Controlador TratamientoController
```php
// Antes (líneas 114-118)
$tratamiento->load([
    'paciente',
    'medico',
    'medicamentos',
    'indicacionesPrn.sintoma'  // ❌ Causa el error SQL
]);

// Después
$tratamiento->load([
    'paciente',
    'medico',
    'medicamentos'
]);

// Cargar indicaciones PRN manualmente
$indicacionesPrn = \App\Models\IndicacionPrn::with('sintoma')
    ->whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)
    ->get();
$tratamiento->indicaciones_prn = $indicacionesPrn;
```

### 3. Corrección del Modelo IndicacionPrn
**Campos $fillable corregidos**:
```php
// Antes
protected $fillable = [
    'tratamiento_id',           // ❌ No existe en tabla
    'sintoma_id',              // ✅
    'criterio_id',             // ✅
    'medicamento_id',          // ❌ No existe en tabla
    'dosis',                   // ❌ No existe en tabla
    'unidad_dosis',            // ❌ No existe en tabla
    'via_administracion',      // ❌ No existe en tabla
    'intervalo_minimo_horas',  // ❌ No existe en tabla
    'dosis_maxima_24h',        // ❌ No existe en tabla
    'instrucciones_administracion', // ❌ No existe en tabla
    'observaciones_importantes',    // ❌ No existe en tabla
    'activo'                   // ❌ No existe en tabla
];

// Después
protected $fillable = [
    'medicamento_tratamiento_id',  // ✅ Existe en tabla
    'sintoma_id',                  // ✅ Existe en tabla
    'criterio_id',                 // ✅ Existe en tabla
    'descripcion_personalizada',   // ✅ Existe en tabla
    'es_criterio_principal'        // ✅ Existe en tabla
];
```

**Relaciones corregidas**:
```php
// Antes
public function tratamiento()
{
    return $this->belongsTo(Tratamiento::class); // ❌ Busca tratamiento_id
}

public function medicamento()
{
    return $this->belongsTo(Medicamento::class); // ❌ Busca medicamento_id
}

// Después
public function medicamentoTratamiento()
{
    return $this->belongsTo(\App\Models\MedicamentoTratamiento::class, 'medicamento_tratamiento_id');
}

// Accessors para obtener tratamiento y medicamento a través del pivot
public function getTratamientoAttribute()
{
    return $this->medicamentoTratamiento?->tratamiento;
}

public function getMedicamentoAttribute()
{
    return $this->medicamentoTratamiento?->medicamento;
}
```

### 4. Creación del Modelo MedicamentoTratamiento
Se creó el modelo faltante `app/Models/MedicamentoTratamiento.php` para manejar la tabla pivot `medicamentos_tratamientos` que tiene relaciones directas con otras tablas.

## Estructura de la Tabla indicaciones_prn (Real)

```sql
CREATE TABLE indicaciones_prn (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicamento_tratamiento_id BIGINT UNSIGNED NOT NULL,
    sintoma_id BIGINT UNSIGNED NOT NULL,
    criterio_id BIGINT UNSIGNED NULL,
    descripcion_personalizada TEXT NULL,
    es_criterio_principal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (medicamento_tratamiento_id) REFERENCES medicamentos_tratamientos(id) ON DELETE CASCADE,
    FOREIGN KEY (sintoma_id) REFERENCES sintomas_prn(id) ON DELETE CASCADE,
    FOREIGN KEY (criterio_id) REFERENCES criterios_prn(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_medicamento_sintoma (medicamento_tratamiento_id, sintoma_id)
);
```

## Archivos Modificados

1. **app/Models/Tratamiento.php** - Líneas 80-82: Relación `indicacionesPrn()` corregida
2. **app/Http/Controllers/TratamientoController.php** - Líneas 114-125: Carga manual de indicaciones PRN
3. **app/Models/IndicacionPrn.php** - Campos `$fillable` y relaciones corregidas
4. **app/Models/MedicamentoTratamiento.php** - Modelo creado (nuevo archivo)

## Verificación

La corrección elimina el error SQL:
- ✅ Ya no busca la columna inexistente `indicaciones_prn.tratamiento_id`
- ✅ Usa correctamente `medicamento_tratamiento_id` para consultas
- ✅ Mantiene la integridad de las relaciones many-to-many con pivot personalizado
- ✅ Los accessors permiten acceso transparente a tratamiento y medicamento

## Patrón Aplicado

Este es el mismo patrón aplicado anteriormente para `horarios_programados` y `administraciones`:
1. Cambiar relación directa por consulta a través de IDs del pivot
2. Cargar datos manualmente en el controlador en lugar de eager loading
3. Crear accessors en el modelo para mantener acceso transparente

Esta corrección completa la resolución de todos los errores de relaciones SQL en el sistema de tratamientos. 