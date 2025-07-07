<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Obtener el rol de paciente por defecto
        $rolPaciente = User::getDefaultRole();
        
        $userData = [
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . ($request->apellido_materno ?? '')),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activo' => true, // Activar por defecto
        ];

        // Asignar rol de paciente si existe
        if ($rolPaciente) {
            $userData['rol_id'] = $rolPaciente->id;
        }

        $user = User::create($userData);

        // Si es un paciente, crear automáticamente el registro en la tabla pacientes
        if ($rolPaciente && $user->hasRole('paciente')) {
            \App\Models\Paciente::create([
                'usuario_id' => $user->id,
                'nombre' => $user->display_name,
                'fecha_nacimiento' => null,
                'genero_id' => null,
                'numero_documento' => null,
                'tipo_documento' => null,
                'tipo_sangre' => null,
                'altura' => null,
                'direccion' => null,
                'telefono_emergencia' => null,
                'observaciones_medicas' => null,
                'activo' => true,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        // Verificar si el usuario necesita onboarding
        if ($this->userNeedsOnboarding($user)) {
            return redirect()->route('welcome.new-user');
        }

        return to_route('dashboard');
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
                // Para pacientes, verificar si ha completado información adicional
                $paciente = $user->pacientes()->first();
                if (!$paciente) {
                    return false; // No tiene registro de paciente
                }
                
                // Verificar si ha completado información básica o tiene tratamientos
                $hasBasicInfo = !empty($paciente->fecha_nacimiento) ||
                               !empty($paciente->telefono_emergencia) ||
                               !empty($paciente->genero_id);
                               
                $hasTratamientos = $paciente->tratamientos()->exists();
                
                return $hasBasicInfo || $hasTratamientos;
                
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
