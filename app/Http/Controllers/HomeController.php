<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Mostrar la página de inicio simple o redirigir según el rol
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirigir según el rol del usuario
        if ($user) {
            if ($user->hasRole('paciente')) {
                return redirect()->route('mi-dashboard');
            }
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            // Médicos, cuidadores y apoderados van al dashboard general por ahora
        }
        
        return Inertia::render('Home', [
            'user' => $user ? [
                'name' => $user->name,
                'role' => $user->role?->name ?? null,
            ] : null,
        ]);
    }
} 