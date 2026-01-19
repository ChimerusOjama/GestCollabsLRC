<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'full_name',
    ];

    /**
     * Accessor pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Relation avec le profil Administrateur
     */
    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }

    /**
     * Relation avec le profil Manager
     */
    public function manager()
    {
        return $this->hasOne(Manager::class);
    }

    /**
     * Relation avec le profil Collaborateur
     */
    public function collaborateur()
    {
        return $this->hasOne(Collaborateur::class);
    }

    /**
     * Retourne le profil spécifique selon le rôle
     */
    public function profil()
    {
        return match($this->role) {
            'admin' => $this->administrateur,
            'manager' => $this->manager,
            'collaborateur' => $this->collaborateur,
            default => null,
        };
    }

    /**
     * Permissions selon le rôle
     */
    public function getPermissions(): array
    {
        return match($this->role) {
            'admin' => [
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
                'report.view',
                'team.manage',
            ],
            'collaborateur' => [
                'profile.view',
                'profile.edit',
                'documents.view',
                'leave.request',
            ],
            default => [],
        };
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permission, $this->getPermissions());
    }
}