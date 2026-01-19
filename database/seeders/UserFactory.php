<?php
// app/Services/UserFactory.php

// namespace App\Services;

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Administrateur;
use App\Models\Manager;
use App\Models\Collaborateur;

class UserFactory extends Factory
{
    public static function create(array $data, string $role): User
    {
        // Convertir le rôle en minuscule pour la correspondance
        $role = strtolower($role);
        
        return match($role) {
            'administrateur' => self::createAdministrateur($data),
            'manager' => self::createManager($data),
            'collaborateur' => self::createCollaborateur($data),
            default => throw new \InvalidArgumentException("Rôle inconnu: $role"),
        };
    }

    private static function createAdministrateur(array $data): User
    {
        // La méthode createWithRole attend 'Administrateur' avec majuscule
        return User::createWithRole($data, 'Administrateur');
    }

    private static function createManager(array $data): User
    {
        return User::createWithRole($data, 'Manager');
    }

    private static function createCollaborateur(array $data): User
    {
        return User::createWithRole($data, 'Collaborateur');
    }
}