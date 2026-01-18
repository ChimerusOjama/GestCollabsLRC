<?php
// app/Services/UserFactory.php

namespace App\Services;

use App\Models\User;
use App\Models\Administrateur;
use App\Models\Manager;
use App\Models\Collaborateur;

class UserFactory
{
    public static function create(array $data, string $role): User
    {
        return match($role) {
            'administrateur' => self::createAdministrateur($data),
            'manager' => self::createManager($data),
            'collaborateur' => self::createCollaborateur($data),
            default => throw new \InvalidArgumentException("Rôle inconnu: $role"),
        };
    }

    private static function createAdministrateur(array $data): User
    {
        $administrateur = Administrateur::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'department' => $data['department'] ?? 'Administration',
        ]);

        return User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'user_type' => 'administrateur',
            'email_verified_at' => now(),
        ])->profil()->associate($administrateur);
    }

    private static function createManager(array $data): User
    {
        $manager = Manager::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'department' => $data['department'],
            'level' => $data['level'] ?? 'junior',
        ]);

        return User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'user_type' => 'manager',
            'email_verified_at' => now(),
        ])->profil()->associate($manager);
    }

    private static function createCollaborateur(array $data): User
    {
        $collaborateur = Collaborateur::create([
            'matricule' => $data['matricule'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'department' => $data['department'],
            'poste' => $data['poste'],
            'date_embauche' => $data['date_embauche'] ?? now(),
            'statut' => $data['statut'] ?? 'actif',
            'salaire' => $data['salaire'] ?? null,
            'competences' => $data['competences'] ?? [],
            'notes' => $data['notes'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
        ]);

        return User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'user_type' => 'collaborateur',
            'email_verified_at' => now(),
        ])->profil()->associate($collaborateur);
    }
}