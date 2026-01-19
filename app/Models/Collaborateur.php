<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborateur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'matricule',
        'phone',
        'address',
        'date_of_birth',
        'department',
        'poste',
        'date_embauche',
        'statut',
        'salaire',
        'competences',
        'notes',
        'manager_id',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'date_of_birth' => 'date',
        'salaire' => 'decimal:2',
        'competences' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }

    /**
     * Accesseurs pour les attributs de l'utilisateur
     */
    public function getFirstNameAttribute()
    {
        return $this->user->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->user->last_name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getFullNameAttribute()
    {
        return $this->user->full_name;
    }

    public function getRoleAttribute()
    {
        return $this->user->role;
    }

    /**
     * Ancienneté en années
     */
    public function getAncienneteAttribute(): int
    {
        return $this->date_embauche->diffInYears(now());
    }

    /**
     * Salaire formaté
     */
    public function getSalaireFormateAttribute(): string
    {
        return number_format($this->salaire, 2, ',', ' ') . ' €';
    }

    /**
     * Âge actuel
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
}