<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            /** @var \Illuminate\Contracts\Auth\MustVerifyEmail $user */
            $user = $request->user();

            event(new Verified($user));
            
            // Verificar si el usuario necesita onboarding después de la verificación
            if ($this->userNeedsOnboarding($user)) {
                return redirect()->route('welcome.new-user')
                    ->with('status', 'email-verified')
                    ->with('message', '¡Email verificado exitosamente! Completa tu perfil para continuar.');
            }
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
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
                $paciente = $user->pacientes()->first();
                if (!$paciente) {
                    return false;
                }
                
                $hasBasicInfo = !empty($paciente->fecha_nacimiento) ||
                               !empty($paciente->telefono_emergencia) ||
                               !empty($paciente->genero_id);
                               
                $hasTratamientos = $paciente->tratamientos()->exists();
                
                return $hasBasicInfo || $hasTratamientos;
                
            default:
                return false;
        }
    }
}
