<?php
// app/Services/UserService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public static function createUser(array $data, string $role): User
    {
        // Préparer les données de base
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'department' => $data['department'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'email_verified_at' => now(),
        ];

        // Définir les permissions selon le rôle (convertir en JSON)
        $userData['permissions'] = self::getDefaultPermissions($role);
        
        // Créer l'utilisateur
        $user = User::create($userData);

        return $user;
    }

    private static function getDefaultPermissions(string $role): string
    {
        $permissions = match($role) {
            'administrator' => [
                'all',
                'user.create',
                'user.edit',
                'user.delete',
                'collaborateur.manage',
                'collaborateur.view',
                'collaborateur.edit',
                'collaborateur.delete',
                'settings.manage'
            ],
            'manager' => [
                'collaborateur.view',
                'collaborateur.edit',
                'collaborateur.create',
                'report.view'
            ],
            'collaborator' => [
                'profile.view',
                'profile.edit'
            ],
            default => []
        };

        // Convertir le tableau en JSON string
        return json_encode($permissions);
    }
}