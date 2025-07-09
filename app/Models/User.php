<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', // Mantenemos para compatibilidad temporal
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'telefono',
        'rol_id',
        'activo',
        'email_verified_at',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes that should be ignored during mass assignment and auditing
     *
     * @var array
     */
    protected $guarded = [
        '_original_for_audit'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    // Relación con el rol
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Relación con personal médico
    public function personalMedico()
    {
        return $this->hasOne(PersonalMedico::class, 'usuario_id');
    }

    // Relación con pacientes (donde este usuario es el paciente)
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'usuario_id');
    }

    // Método para verificar si el usuario está activo
    public function isActive()
    {
        return $this->activo;
    }

    // Método para obtener el nombre del rol
    public function getRolNombreAttribute()
    {
        return $this->role ? $this->role->nombre : 'Sin rol';
    }

    // Método para obtener el nombre completo
    public function getNombreCompletoAttribute()
    {
        $partes = array_filter([
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno
        ]);
        
        return !empty($partes) ? implode(' ', $partes) : $this->name;
    }

    // Método para obtener el nombre completo formateado
    public function getDisplayNameAttribute()
    {
        // Si existen los campos separados, usarlos; sino usar el campo name original
        if ($this->nombre || $this->apellido_paterno) {
            return $this->nombre_completo;
        }
        
        return $this->name;
    }

    // Setter para el campo name que actualiza los campos separados
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        // Si se está estableciendo el campo name, intentar separarlo automáticamente
        if ($value && (!$this->nombre && !$this->apellido_paterno)) {
            $partes = explode(' ', trim($value));
            
            $this->attributes['nombre'] = $partes[0] ?? '';
            $this->attributes['apellido_paterno'] = $partes[1] ?? '';
            $this->attributes['apellido_materno'] = $partes[2] ?? '';
            
            // Si hay más de 3 partes, asignar el resto al apellido materno
            if (count($partes) > 3) {
                $this->attributes['apellido_materno'] = implode(' ', array_slice($partes, 2));
            }
        }
    }

    // Método para verificar si el email está verificado
    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // === MÉTODOS DE AUTORIZACIÓN ===

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permisos()->where('nombre', $permission)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permisos()->whereIn('nombre', $permissions)->exists();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->nombre === $roleName;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role && in_array($this->role->nombre, $roles);
    }

    /**
     * Sobrescribir el método can de Laravel para usar nuestro sistema de permisos
     */
    public function can($abilities, $arguments = []): bool
    {
        // Si se pasa un string simple, usar nuestro sistema de permisos
        if (is_string($abilities) && empty($arguments)) {
            return $this->hasPermission($abilities);
        }
        
        // Para otros casos, usar el comportamiento original de Laravel
        return parent::can($abilities, $arguments);
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Obtener todos los permisos del usuario
     */
    public function getAllPermissions()
    {
        if (!$this->role) {
            return collect();
        }

        return $this->role->permisos;
    }

    /**
     * Obtener el rol por defecto para usuarios nuevos
     */
    public static function getDefaultRole(): ?Role
    {
        // Buscar el rol de paciente
        $role = Role::where('nombre', 'paciente')->first();
        
        // Si no existe, crearlo automáticamente
        if (!$role) {
            $role = Role::create([
                'nombre' => 'paciente',
                'descripcion' => 'Paciente - acceso a su propia información médica',
                'activo' => true
            ]);
        }
        
        return $role;
    }

    /**
     * Asignar rol por defecto si el usuario no tiene uno
     */
    public function ensureHasRole(): void
    {
        if (!$this->rol_id && $defaultRole = self::getDefaultRole()) {
            $this->update(['rol_id' => $defaultRole->id]);
        }
    }

    // Método para verificar si el usuario es médico
    public function isMedico()
    {
        return $this->hasRole('medico');
    }

    // Método para verificar si el usuario es paciente
    public function isPaciente()
    {
        return $this->hasRole('paciente');
    }

    // Método para verificar si el usuario es cuidador
    public function isCuidador()
    {
        return $this->hasRole('cuidador');
    }

    // Método para verificar si el usuario es apoderado
    public function isApoderado()
    {
        return $this->hasRole('apoderado');
    }

    // Accessor para email_verificado (compatibilidad)
    public function getEmailVerificadoAttribute()
    {
        return $this->email_verified_at !== null;
    }

    // Scope para usuarios por rol
    public function scopePorRol($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('nombre', $roleName);
        });
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
