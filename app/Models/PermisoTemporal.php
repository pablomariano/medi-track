<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PermisoTemporal extends Model
{
    use HasFactory;

    protected $table = 'permisos_temporales';

    protected $fillable = [
        'usuario_id',
        'permiso',
        'motivo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'otorgado_por',
        'fecha_otorgamiento',
        'ip_otorgamiento',
        'notas_adicionales',
        'ultimo_uso',
        'veces_usado'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_otorgamiento' => 'datetime',
        'ultimo_uso' => 'datetime',
        'activo' => 'boolean',
        'veces_usado' => 'integer'
    ];

    // Relación con el usuario que tiene el permiso
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con el usuario que otorgó el permiso
    public function otorgadoPor()
    {
        return $this->belongsTo(User::class, 'otorgado_por');
    }

    // Scope para permisos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para permisos vigentes (activos y dentro del rango de fechas)
    public function scopeVigentes($query)
    {
        $now = Carbon::now();
        return $query->activos()
            ->where('fecha_inicio', '<=', $now)
            ->where(function($q) use ($now) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', $now);
            });
    }

    // Scope para permisos expirados
    public function scopeExpirados($query)
    {
        return $query->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', Carbon::now());
    }

    // Scope por usuario
    public function scopeParaUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    // Scope por permiso específico
    public function scopeConPermiso($query, $permiso)
    {
        return $query->where('permiso', $permiso);
    }

    // Método para verificar si el permiso está vigente
    public function getEsVigenteAttribute()
    {
        if (!$this->activo) {
            return false;
        }

        $now = Carbon::now();
        
        if ($this->fecha_inicio->gt($now)) {
            return false; // Aún no inicia
        }

        if ($this->fecha_fin && $this->fecha_fin->lt($now)) {
            return false; // Ya expiró
        }

        return true;
    }

    // Método para obtener días restantes
    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_fin) {
            return null; // Indefinido
        }

        if (!$this->es_vigente) {
            return 0; // Expirado o inactivo
        }

        return Carbon::now()->diffInDays($this->fecha_fin, false);
    }

    // Método para obtener duración total en días
    public function getDuracionTotalAttribute()
    {
        if (!$this->fecha_fin) {
            return null; // Indefinido
        }

        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }

    // Método para obtener el estado del permiso
    public function getEstadoAttribute()
    {
        if (!$this->activo) {
            return 'inactivo';
        }

        $now = Carbon::now();

        if ($this->fecha_inicio->gt($now)) {
            return 'pendiente';
        }

        if ($this->fecha_fin && $this->fecha_fin->lt($now)) {
            return 'expirado';
        }

        return 'vigente';
    }

    // Método para registrar uso del permiso
    public function registrarUso()
    {
        $this->increment('veces_usado');
        $this->update(['ultimo_uso' => Carbon::now()]);
    }

    // Método para revocar el permiso
    public function revocar()
    {
        $this->update(['activo' => false]);
    }

    // Método para extender la duración
    public function extender(Carbon $nuevaFechaFin, $motivo = null)
    {
        $this->update([
            'fecha_fin' => $nuevaFechaFin,
            'notas_adicionales' => $this->notas_adicionales . "\n[Extendido hasta {$nuevaFechaFin->format('d/m/Y H:i')}] {$motivo}"
        ]);
    }

    // Método estático para otorgar permiso temporal
    public static function otorgar(array $datos)
    {
        return self::create([
            'usuario_id' => $datos['usuario_id'],
            'permiso' => $datos['permiso'],
            'motivo' => $datos['motivo'] ?? null,
            'fecha_inicio' => $datos['fecha_inicio'] ?? Carbon::now(),
            'fecha_fin' => $datos['fecha_fin'] ?? null,
            'otorgado_por' => auth()->id(),
            'fecha_otorgamiento' => Carbon::now(),
            'ip_otorgamiento' => request()->ip(),
            'notas_adicionales' => $datos['notas_adicionales'] ?? null,
            'activo' => true
        ]);
    }

    // Método para verificar si un usuario tiene un permiso temporal específico
    public static function usuarioTienePermiso($usuarioId, $permiso)
    {
        return self::paraUsuario($usuarioId)
            ->conPermiso($permiso)
            ->vigentes()
            ->exists();
    }

    // Método para obtener permisos temporales de un usuario
    public static function permisosDeUsuario($usuarioId)
    {
        return self::with(['otorgadoPor'])
            ->paraUsuario($usuarioId)
            ->vigentes()
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    // Método para limpiar permisos expirados
    public static function limpiarExpirados()
    {
        $expirados = self::expirados()->count();
        
        self::expirados()->update(['activo' => false]);
        
        return $expirados;
    }
} 