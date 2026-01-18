<?php
// database/factories/ManagerFactory.php

namespace Database\Factories;

use App\Models\Manager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ManagerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manager::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('manager123'), // Mot de passe par défaut
            'role' => 'manager',
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'date_of_birth' => $this->faker->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'login_attempts' => 0,
            'is_locked' => false,
            'manager_id' => null, // Les managers n'ont pas de manager supérieur
            'permissions' => json_encode([
                'manage_collaborators' => true,
                'view_reports' => true,
                'create_tasks' => true,
            ]),
        ];
    }

    /**
     * Indicate that the manager has a specific department.
     *
     * @return Factory
     */
    public function withDepartment(string $department): Factory
    {
        return $this->state(function (array $attributes) use ($department) {
            return [
                'permissions' => json_encode([
                    'department' => $department,
                    'manage_collaborators' => true,
                    'view_department_reports' => true,
                ]),
            ];
        });
    }

    /**
     * Indicate that the manager is senior.
     *
     * @return Factory
     */
    public function senior(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'permissions' => json_encode([
                    'manage_managers' => true,
                    'manage_all_departments' => true,
                    'approve_budgets' => true,
                ]),
            ];
        });
    }

    /**
     * Indicate that the manager is locked.
     *
     * @return Factory
     */
    public function locked(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_locked' => true,
                'login_attempts' => 5,
            ];
        });
    }

    /**
     * Indicate that the manager has never logged in.
     *
     * @return Factory
     */
    public function neverLoggedIn(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'last_login_at' => null,
            ];
        });
    }
}