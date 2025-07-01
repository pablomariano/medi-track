<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     * If user is authenticated, redirect to dashboard.
     */
    public function index()
    {
        // Si el usuario está autenticado, redirigir al dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Mostrar landing page para visitantes no autenticados
        return Inertia::render('Landing');
    }
} 