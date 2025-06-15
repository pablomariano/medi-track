<?php

namespace Database\Factories;

use App\Models\Tratamiento;
use App\Models\User;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class TratamientoFactory extends Factory
{
    protected $model = Tratamiento::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->sentence(3),
            'descripcion' => $this->faker->paragraph(),
            'diagnostico' => $this->faker->sentence(),
            'objetivo' => $this->faker->sentence(),
            'tipo' => $this->faker->randomElement([Tratamiento::TIPO_PROGRAMADO, Tratamiento::TIPO_PRN]),
            'estado' => $this->faker->randomElement([Tratamiento::ESTADO_ACTIVO, Tratamiento::ESTADO_COMPLETADO, Tratamiento::ESTADO_SUSPENDIDO]),
            'fecha_inicio' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha_fin' => $this->faker->dateTimeBetween('now', '+1 month'),
            'observaciones' => $this->faker->optional()->paragraph(),
            'medico_id' => User::factory(),
            'paciente_id' => Paciente::factory(),
        ];
    }

    public function programado(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => Tratamiento::TIPO_PROGRAMADO,
        ]);
    }

    public function prn(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => Tratamiento::TIPO_PRN,
        ]);
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => Tratamiento::ESTADO_ACTIVO,
        ]);
    }

    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => Tratamiento::ESTADO_COMPLETADO,
        ]);
    }

    public function suspendido(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => Tratamiento::ESTADO_SUSPENDIDO,
        ]);
    }
} 