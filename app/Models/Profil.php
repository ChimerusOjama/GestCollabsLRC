<?php
// app/Models/Profil.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class Profil extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profilable');
    }

    /**
     * Méthode abstraite pour les permissions spécifiques
     */
    abstract public function getPermissions(): array;

    /**
     * Méthode abstraite pour le nom complet
     */
    abstract public function getFullName(): string;
}