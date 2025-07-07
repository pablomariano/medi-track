<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    /**
     * Mostrar página de bienvenida para usuarios nuevos
     */
    public function newUser()
    {
        $user = Auth::user();
        
        // Verificar si el usuario necesita onboarding
        $needsOnboarding = $this->userNeedsOnboarding($user);
        
        // Si no necesita onboarding, redirigir al dashboard
        if (!$needsOnboarding) {
            return redirect()->route('dashboard');
        }

        // Obtener progreso del usuario
        $progress = $this->getUserProgress($user);
        
        // Obtener guía específica por rol
        $userGuide = $this->getUserGuideByRole($user->role->nombre ?? 'paciente');

        return Inertia::render('Welcome/NewUserWelcome', [
            'userGuide' => $userGuide,
            'progress' => $progress,
            'isNewUser' => true,
            'userRole' => $user->role->nombre ?? 'paciente'
        ]);
    }

    /**
     * Actualizar progreso de onboarding
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'step_id' => 'required|string',
            'completed' => 'boolean'
        ]);

        $user = Auth::user();
        $stepId = $request->input('step_id');
        $completed = $request->input('completed', true);

        // Obtener progreso actual
        $progress = $this->getUserProgress($user);
        
        if ($completed && !in_array($stepId, $progress)) {
            $progress[] = $stepId;
        } elseif (!$completed) {
            $progress = array_filter($progress, fn($item) => $item !== $stepId);
        }

        // Guardar progreso (usando session por simplicidad, pero podría ser en BD)
        session(['onboarding_progress' => $progress]);

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'progress_percentage' => $this->calculateProgressPercentage($progress, $user->role->nombre ?? 'paciente')
        ]);
    }

    /**
     * Marcar onboarding como completado
     */
    public function completeOnboarding()
    {
        $user = Auth::user();
        
        // Marcar como completado (podría guardarse en la BD)
        session(['onboarding_completed' => true]);
        
        return redirect()->route('dashboard')->with('success', '¡Bienvenido a MediTrack! Has completado la configuración inicial.');
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

        // Si es un usuario recién creado (menos de 7 días)
        $userAge = $user->created_at->diffInDays(now());
        if ($userAge > 7) {
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

    /**
     * Obtener progreso del usuario
     */
    private function getUserProgress($user): array
    {
        return session('onboarding_progress', []);
    }

    /**
     * Calcular porcentaje de progreso
     */
    private function calculateProgressPercentage(array $progress, string $role): int
    {
        $totalSteps = $this->getTotalStepsByRole($role);
        return $totalSteps > 0 ? round((count($progress) / $totalSteps) * 100) : 0;
    }

    /**
     * Obtener total de pasos por rol
     */
    private function getTotalStepsByRole(string $role): int
    {
        $stepCounts = [
            'paciente' => 4,
            'apoderado' => 4,
            'cuidador' => 3,
            'medico' => 3,
            'admin' => 2
        ];

        return $stepCounts[$role] ?? 4;
    }

    /**
     * Obtener guía específica por rol
     */
    private function getUserGuideByRole(string $role): array
    {
        $guides = [
            'paciente' => [
                'title' => '¡Bienvenido a MediTrack! 👋',
                'subtitle' => 'Te ayudamos a gestionar tu salud de manera sencilla',
                'description' => 'Como paciente, puedes registrar tus medicamentos, establecer recordatorios y mantener un seguimiento completo de tu tratamiento.',
                'primary_action' => [
                    'title' => 'Registrar mi primer medicamento',
                    'href' => '/mis-tratamientos/crear'
                ]
            ],
            'apoderado' => [
                'title' => '¡Bienvenido, Apoderado! 👨‍👩‍👧‍👦',
                'subtitle' => 'Gestiona la salud de las personas que cuidas',
                'description' => 'Como apoderado, puedes registrar y gestionar los tratamientos de las personas bajo tu cuidado.',
                'primary_action' => [
                    'title' => 'Registrar persona a mi cargo',
                    'href' => '/pacientes/crear'
                ]
            ],
            'cuidador' => [
                'title' => '¡Bienvenido, Cuidador! 🩺',
                'subtitle' => 'Herramientas profesionales para el cuidado de pacientes',
                'description' => 'Como cuidador profesional, tienes acceso a herramientas para gestionar múltiples pacientes y sus tratamientos.',
                'primary_action' => [
                    'title' => 'Ver mis pacientes asignados',
                    'href' => '/mis-pacientes'
                ]
            ],
            'medico' => [
                'title' => '¡Bienvenido, Doctor! ⚕️',
                'subtitle' => 'Gestiona tus pacientes y prescripciones digitalmente',
                'description' => 'Como médico, puedes prescribir tratamientos, monitorear pacientes y colaborar con otros profesionales de la salud.',
                'primary_action' => [
                    'title' => 'Crear primera prescripción',
                    'href' => '/prescripciones/crear'
                ]
            ]
        ];

        return $guides[$role] ?? $guides['paciente'];
    }

    /**
     * Obtener estadísticas para motivar al usuario
     */
    public function getMotivationStats()
    {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'treatments_created_today' => 12, // Esto sería una consulta real
            'medications_tracked' => 150, // Esto sería una consulta real
            'success_stories' => 89 // Porcentaje de usuarios que completan onboarding
        ]);
    }
}
