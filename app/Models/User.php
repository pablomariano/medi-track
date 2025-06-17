<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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
}
