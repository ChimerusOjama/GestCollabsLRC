<?php
// app/Models/Administrateur.php

namespace App\Models;

class Administrateur extends Profil
{
    protected $table = 'administrateurs';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'department',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function getPermissions(): array
    {
        return [
            'all',
            'user.create',
            'user.edit',
            'user.delete',
            'collaborateur.manage',
            'collaborateur.view',
            'collaborateur.edit',
            'collaborateur.delete',
            'settings.manage'
        ];
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Relations spécifiques aux administrateurs
     */
    public function managedDepartments()
    {
        // Logique spécifique
        return [];
    }
}