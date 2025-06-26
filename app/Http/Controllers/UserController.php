<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->latest('created_at')
            ->paginate(10);
        
        return Inertia::render('Usuarios/Index', [
            'usuarios' => $users
        ]);
    }

    public function create()
    {
        $roles = Role::where('activo', true)->get();
        
        return Inertia::render('Usuarios/Create', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'nullable|exists:roles,id',
            'activo' => 'boolean',
            'email_verificado' => 'boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = $validated['email_verificado'] ? now() : null;
        
        // Crear el campo name para compatibilidad
        $validated['name'] = trim(
            ($validated['nombre'] ?? '') . ' ' . 
            ($validated['apellido_paterno'] ?? '') . ' ' . 
            ($validated['apellido_materno'] ?? '')
        );
        
        unset($validated['email_verificado']);

        User::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $usuario)
    {
        $usuario->load('role');
        
        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario
        ]);
    }

    public function edit(User $usuario)
    {
        $usuario->load('role');
        $roles = Role::where('activo', true)->get();
        
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'nullable|exists:roles,id',
            'activo' => 'boolean',
            'email_verificado' => 'boolean'
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['email_verified_at'] = $validated['email_verificado'] ? now() : null;
        
        // Actualizar el campo name para compatibilidad
        $validated['name'] = trim(
            ($validated['nombre'] ?? '') . ' ' . 
            ($validated['apellido_paterno'] ?? '') . ' ' . 
            ($validated['apellido_materno'] ?? '')
        );
        
        unset($validated['email_verificado']);

        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        // Verificar si el usuario tiene relaciones antes de eliminar
        if ($usuario->personalMedico()->exists() || $usuario->pacientes()->exists()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene registros asociados.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
} 