<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            'genero' => $this->faker->randomElement(['masculino', 'femenino', 'otro']),
            'tipo_documento' => $this->faker->randomElement(['cedula', 'pasaporte', 'carnet']),
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'tipo_sangre' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'altura' => $this->faker->numberBetween(150, 200), // cm
            'peso' => $this->faker->numberBetween(50, 120), // kg
            'direccion' => $this->faker->address(),
            'telefono_emergencia' => $this->faker->phoneNumber(),
            'alergias' => $this->faker->optional()->sentence(),
            'enfermedades_cronicas' => $this->faker->optional()->sentence(),
            'medicamentos_actuales' => $this->faker->optional()->sentence(),
            'observaciones' => $this->faker->optional()->paragraph(),
        ];
    }

    public function masculino(): static
    {
        return $this->state(fn (array $attributes) => [
            'genero' => 'masculino',
        ]);
    }

    public function femenino(): static
    {
        return $this->state(fn (array $attributes) => [
            'genero' => 'femenino',
        ]);
    }

    public function conAlergias(): static
    {
        return $this->state(fn (array $attributes) => [
            'alergias' => $this->faker->randomElement([
                'Penicilina, sulfonamidas',
                'Mariscos, nueces',
                'Polen, polvo',
                'Aspirina, antiinflamatorios'
            ]),
        ]);
    }

    public function conEnfermedadesCronicas(): static
    {
        return $this->state(fn (array $attributes) => [
            'enfermedades_cronicas' => $this->faker->randomElement([
                'Diabetes tipo 2',
                'Hipertensión arterial',
                'Asma bronquial',
                'Artritis reumatoide'
            ]),
        ]);
    }
} 