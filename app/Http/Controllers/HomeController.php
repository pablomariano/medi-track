<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Mostrar la página de inicio simple
     */
    public function index()
    {
        $user = Auth::user();
        
        return Inertia::render('Home', [
            'user' => $user ? [
                'name' => $user->name,
                'role' => $user->role?->name ?? null,
            ] : null,
        ]);
    }
} 