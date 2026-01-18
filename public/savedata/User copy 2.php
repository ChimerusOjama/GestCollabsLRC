<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'user_type',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation polymorphique avec le profil
     */
    public function profil(): MorphTo
    {
        return $this->morphTo('profilable', 'profilable_type', 'profilable_id');
    }

    /**
     * Factory pour créer des instances du bon type
     */
    public static function createWithRole(array $data, string $roleType)
    {
        return \DB::transaction(function () use ($data, $roleType) {
            // Créer l'utilisateur de base
            $user = self::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'user_type' => $roleType,
                'email_verified_at' => now(),
            ]);

            // Créer le profil spécifique
            $profilClass = "App\\Models\\" . ucfirst($roleType);
            $profil = $profilClass::create($data);
            
            // Associer le profil à l'utilisateur
            $user->profil()->associate($profil);
            $user->save();

            return $user;
        });
    }

    /**
     * Helper pour accéder aux données du profil
     */
    public function getProfilAttribute()
    {
        if (!$this->profil()->exists()) {
            $this->load('profil');
        }
        return $this->getRelation('profil');
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->user_type === $role;
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdministrateur(): bool
    {
        return $this->hasRole('administrateur');
    }

    /**
     * Vérifie si l'utilisateur est manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Vérifie si l'utilisateur est collaborateur
     */
    public function isCollaborateur(): bool
    {
        return $this->hasRole('collaborateur');
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('user_type', $type);
    }
}