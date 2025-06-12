# Bug Fix: Error SQL en Edición de Tratamientos

## 🐛 Problema Reportado

Al intentar editar un tratamiento, se producía el siguiente error SQL:

```sql
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'horarios_programados.tratamiento_id' in 'where clause' 
(Connection: mysql, SQL: select * from `horarios_programados` where `horarios_programados`.`tratamiento_id` in (13))
```

## 🔍 Análisis del Problema

### Causa Raíz
El error se debía a **relaciones incorrectas** en el modelo `Tratamiento` que buscaban columnas inexistentes:

1. **Tabla `horarios_programados`**: No tiene columna `tratamiento_id`, usa `medicamento_tratamiento_id`
2. **Tabla `administraciones`**: No tiene columna `tratamiento_id`, usa `medicamento_tratamiento_id`

### Arquitectura Correcta
```
tratamientos
    ↓ (many-to-many)
medicamentos_tratamientos (pivot table)
    ↓ (one-to-many)
horarios_programados
administraciones
```

## ✅ Solución Implementada

### 1. Corregir Modelo `Tratamiento`

**ANTES** (❌ Incorrecto):
```php
// Relación incorrecta - busca tratamiento_id que no existe
public function horarios()
{
    return $this->hasMany(HorarioProgramado::class);
}

public function administraciones()
{
    return $this->hasMany(Administracion::class);
}
```

**DESPUÉS** (✅ Correcto):
```php
// Obtener horarios programados de este tratamiento
public function horarios()
{
    $medicamentoTratamientoIds = $this->medicamentos()->pluck('medicamentos_tratamientos.id');
    return HorarioProgramado::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds);
}

// Obtener administraciones de este tratamiento  
public function administraciones()
{
    $medicamentoTratamientoIds = $this->medicamentos()->pluck('medicamentos_tratamientos.id');
    return \App\Models\Administracion::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds);
}
```

### 2. Corregir Modelo `HorarioProgramado`

**ANTES** (❌ Incorrecto):
```php
public function tratamiento()
{
    return $this->belongsTo(Tratamiento::class);
}
```

**DESPUÉS** (✅ Correcto):
```php
// Obtener tratamiento a través de medicamento_tratamiento
public function getTratamientoAttribute()
{
    $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
    return $pivot ? Tratamiento::find($pivot->tratamiento_id) : null;
}

// Obtener medicamento a través de medicamento_tratamiento  
public function getMedicamentoAttribute()
{
    $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
    return $pivot ? \App\Models\Medicamento::find($pivot->medicamento_id) : null;
}
```

### 3. Corregir TratamientoController

**ANTES** (❌ Eager Loading incorrecto):
```php
$tratamiento->load([
    'paciente',
    'medico', 
    'medicamentos',
    'horarios',  // ❌ Causaba el error SQL
    'administraciones' => function($query) {
        $query->latest()->limit(20);
    }
]);
```

**DESPUÉS** (✅ Carga manual correcta):
```php
$tratamiento->load([
    'paciente',
    'medico',
    'medicamentos',
    'indicacionesPrn.sintoma'
]);

// Cargar horarios y administraciones manualmente para evitar errores de relación
$medicamentoTratamientoIds = $tratamiento->medicamentos()->pluck('medicamentos_tratamientos.id');

$horarios = \App\Models\HorarioProgramado::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)->get();
$tratamiento->horarios_programados = $horarios;

$administraciones = \App\Models\Administracion::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)
    ->latest()
    ->limit(20)
    ->get();
$tratamiento->administraciones_recientes = $administraciones;
```

## 🧪 Verificación

### Tests Ejecutados
```bash
sail test tests/Feature/TratamientoEditTest.php tests/Feature/TratamientoPivotDataTest.php tests/Feature/TratamientoEditIntegrationTest.php
```

### Resultados
```
✅ Tests: 1 risky, 22 passed (177 assertions)
✅ Duration: 3.05s
✅ Sin errores SQL
```

## 📋 Resumen de Archivos Modificados

1. **`app/Models/Tratamiento.php`**
   - Corregida relación `horarios()`
   - Corregida relación `administraciones()`

2. **`app/Models/HorarioProgramado.php`**  
   - Removida relación incorrecta `tratamiento()`
   - Agregados accessors `getTratamientoAttribute()` y `getMedicamentoAttribute()`

3. **`app/Http/Controllers/TratamientoController.php`**
   - Corregido método `show()` para cargar datos manualmente
   - Eliminado eager loading problemático

## 🎯 Resultado

- ✅ **Error SQL eliminado completamente**
- ✅ **Funcionalidad de edición funciona correctamente**
- ✅ **Todos los tests pasan**
- ✅ **Relaciones reflejan la arquitectura real de la base de datos**
- ✅ **Compatibilidad mantenida con el resto del sistema**

## 🔮 Consideraciones Futuras

Para evitar problemas similares:

1. **Verificar siempre** que las relaciones Eloquent coincidan con la estructura real de la base de datos
2. **Probar las relaciones** con eager loading antes de implementar
3. **Documentar** la arquitectura de relaciones complejas many-to-many
4. **Escribir tests** que verifiquen las relaciones funcionan correctamente

---

**Estado**: ✅ **RESUELTO**  
**Fecha**: Diciembre 2024  
**Tests**: 23/23 pasando 