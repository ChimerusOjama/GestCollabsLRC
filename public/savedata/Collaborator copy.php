<?php

namespace App\Models;

class Collaborator extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('collaborator', function ($builder) {
            $builder->where('role', 'collaborator');
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CollaboratorFactory::new();
    }

    /**
     * Get department from permissions.
     */
    public function getDepartmentAttribute(): ?string
    {
        return $this->permissions['department'] ?? null;
    }

    /**
     * Get manager details.
     */
    public function getManagerDetails(): ?array
    {
        if (!$this->manager) {
            return null;
        }

        return [
            'id' => $this->manager->id,
            'name' => $this->manager->full_name,
            'email' => $this->manager->email,
            'phone' => $this->manager->phone,
        ];
    }
}