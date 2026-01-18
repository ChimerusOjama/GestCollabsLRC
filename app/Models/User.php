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
        'email_verified_at',
        'userable_type',
        'userable_id',
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
     * Relation polymorphique
     */
    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Vérifie le rôle
     */
    public function hasRole(string $role): bool
    {
        return $this->userable_type === "App\\Models\\" . ucfirst($role);
    }

    public function isAdministrateur(): bool
    {
        return $this->hasRole('Administrateur');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    public function isCollaborateur(): bool
    {
        return $this->hasRole('Collaborateur');
    }

    /**
     * Scope par type
     */
    public function scopeOfType($query, string $type)
    {
        $modelClass = "App\\Models\\" . ucfirst($type);
        return $query->where('userable_type', $modelClass);
    }

    /**
     * Accesseurs
     */
    public function getFullNameAttribute()
    {
        if ($this->userable && method_exists($this->userable, 'getFullName')) {
            return $this->userable->getFullName();
        }
        return $this->email;
    }

    public function getPermissionsAttribute()
    {
        if ($this->userable && method_exists($this->userable, 'getPermissions')) {
            return $this->userable->getPermissions();
        }
        return [];
    }
}