<?php

namespace App\Http\Controllers;

use App\Services\UserRegistrationService;
use App\Http\Requests\CreateUserByTypeRequest;
use App\Models\Role;
use App\Models\Genero;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnifiedUserController extends Controller
{
    protected $userRegistrationService;

    public function __construct(UserRegistrationService $userRegistrationService)
    {
        $this->userRegistrationService = $userRegistrationService;
    }

    /**
     * Mostrar formulario de selección de tipo de usuario
     */
    public function selectType()
    {
        $userTypes = $this->userRegistrationService->getUserTypes();
        
        return Inertia::render('Usuarios/SelectType', [
            'userTypes' => $userTypes
        ]);
    }

    /**
     * Mostrar formulario de creación según el tipo
     */
    public function create(Request $request)
    {
        $tipo = $request->query('tipo');
        
        if (!$tipo || !in_array($tipo, ['medico', 'cuidador', 'apoderado', 'paciente', 'admin'])) {
            return redirect()->route('usuarios.select-type')
                ->with('error', 'Debe seleccionar un tipo de usuario válido.');
        }

        $data = [
            'tipo' => $tipo,
            'tipoInfo' => $this->userRegistrationService->getUserTypes()[$tipo]
        ];

        // Agregar datos específicos según el tipo
        if ($tipo === 'paciente') {
            $data['generos'] = Genero::all();
        }

        return Inertia::render('Usuarios/CreateByType', $data);
    }

    /**
     * Almacenar el usuario según su tipo
     */
    public function store(CreateUserByTypeRequest $request)
    {
        $userData = $request->input('user_data', []);
        $specificData = $request->input('specific_data', []);
        $tipo = $request->input('tipo_usuario');

        try {
            switch ($tipo) {
                case 'medico':
                    $user = $this->userRegistrationService->createMedico($userData, $specificData);
                    $redirectRoute = 'personal-medico.index';
                    $message = 'Personal médico creado exitosamente.';
                    break;

                case 'cuidador':
                    $user = $this->userRegistrationService->createCuidador($userData, $specificData);
                    $redirectRoute = 'cuidadores.index';
                    $message = 'Cuidador creado exitosamente.';
                    break;

                case 'apoderado':
                    $user = $this->userRegistrationService->createApoderado($userData, $specificData);
                    $redirectRoute = 'apoderados.index';
                    $message = 'Apoderado creado exitosamente.';
                    break;

                case 'paciente':
                    // Para pacientes, el usuario es opcional
                    $userDataForPaciente = empty($userData['email']) ? null : $userData;
                    $paciente = $this->userRegistrationService->createPaciente($specificData, $userDataForPaciente);
                    $redirectRoute = 'pacientes.index';
                    $message = 'Paciente creado exitosamente.';
                    break;

                case 'admin':
                    $user = $this->userRegistrationService->createAdmin($userData);
                    $redirectRoute = 'usuarios.index';
                    $message = 'Administrador creado exitosamente.';
                    break;

                default:
                    return back()->with('error', 'Tipo de usuario no válido.');
            }

            return redirect()->route($redirectRoute)
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos para formularios dinámicos
     */
    public function getFormData(Request $request)
    {
        $tipo = $request->query('tipo');
        $data = [];

        switch ($tipo) {
            case 'paciente':
                $data['generos'] = Genero::all();
                $data['tiposDocumento'] = [
                    ['value' => 'rut', 'label' => 'RUT'],
                    ['value' => 'ci', 'label' => 'Cédula de Identidad'],
                    ['value' => 'passport', 'label' => 'Pasaporte'],
                    ['value' => 'otro', 'label' => 'Otro'],
                ];
                $data['tiposSangre'] = [
                    ['value' => 'A+', 'label' => 'A+'],
                    ['value' => 'A-', 'label' => 'A-'],
                    ['value' => 'B+', 'label' => 'B+'],
                    ['value' => 'B-', 'label' => 'B-'],
                    ['value' => 'AB+', 'label' => 'AB+'],
                    ['value' => 'AB-', 'label' => 'AB-'],
                    ['value' => 'O+', 'label' => 'O+'],
                    ['value' => 'O-', 'label' => 'O-'],
                ];
                break;
        }

        return response()->json($data);
    }
} 