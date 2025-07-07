<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Verificar si el usuario necesita onboarding
        if ($this->userNeedsOnboarding($user)) {
            return redirect()->intended(route('welcome.new-user', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Verificar si el usuario necesita onboarding
     */
    private function userNeedsOnboarding($user): bool
    {
        // Si ya completó el onboarding
        if (session('onboarding_completed')) {
            return false;
        }

        // Si es un usuario recién creado (menos de 7 días) y es paciente
        $userAge = $user->created_at->diffInDays(now());
        if ($userAge > 7) {
            return false;
        }

        // Solo mostrar onboarding para pacientes nuevos
        if (!$user->hasRole('paciente')) {
            return false;
        }

        // Verificar si ha completado acciones básicas
        $hasBasicData = $this->userHasBasicData($user);
        
        return !$hasBasicData;
    }

    /**
     * Verificar si el usuario tiene datos básicos
     */
    private function userHasBasicData($user): bool
    {
        // Verificar según el rol
        switch ($user->role->nombre ?? 'paciente') {
            case 'paciente':
                // Verificar si tiene paciente asociado o tratamientos
                return $user->pacientes()->exists();
                
            case 'medico':
                // Verificar si tiene perfil médico completo
                return $user->personalMedico()->exists();
                
            case 'cuidador':
                // Verificar si tiene perfil de cuidador
                return $user->cuidadores()->exists();
                
            case 'apoderado':
                // Verificar si tiene perfil de apoderado
                return $user->apoderados()->exists();
                
            default:
                return false;
        }
    }
}
