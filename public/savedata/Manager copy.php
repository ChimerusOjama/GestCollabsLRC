<?php

namespace App\Models;

class Manager extends User
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
        static::addGlobalScope('manager', function ($builder) {
            $builder->where('role', 'manager');
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ManagerFactory::new();
    }

    /**
     * Get department from permissions.
     */
    public function getDepartmentAttribute(): ?string
    {
        return $this->permissions['department'] ?? null;
    }

    /**
     * Get dashboard statistics for this manager.
     */
    public function getDashboardStats(): array
    {
        return [
            'team_size' => $this->managedCollaborators()->count(),
            'active_team' => $this->managedCollaborators()->active()->count(),
            'locked_team' => $this->managedCollaborators()->where('is_locked', true)->count(),
        ];
    }
}