<?php
// app/Models/Collaborateur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborateur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'email',
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
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Relation avec le manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }

    /**
     * Permissions spécifiques
     */
    public function getPermissions(): array
    {
        return [
            'profile.view',
            'profile.edit',
            'documents.view',
            'leave.request',
        ];
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
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