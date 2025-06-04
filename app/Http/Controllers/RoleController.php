<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::latest()->paginate(10);
        
        return Inertia::render('Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        return Inertia::render('Roles/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        Role::create($validated);

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        return Inertia::render('Roles/Edit', [
            'role' => $role
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $role->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
} 