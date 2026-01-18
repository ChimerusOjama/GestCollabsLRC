<?php
// app/Models/Administrateur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Administrateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'department',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Permissions spécifiques
     */
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
}