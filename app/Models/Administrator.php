<?php

namespace App\Models;

class Administrator extends User
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
        static::addGlobalScope('administrator', function ($builder) {
            $builder->where('role', 'administrator');
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\AdministratorFactory::new();
    }

    /**
     * Get all users (admins can see everyone).
     */
    public function getAllUsers()
    {
        return User::orderBy('last_name')->orderBy('first_name')->get();
    }

    /**
     * Get statistics for the dashboard.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_collaborators' => User::collaborators()->count(),
            'total_managers' => User::managers()->count(),
            'total_admins' => User::administrators()->count(),
            'locked_accounts' => User::where('is_locked', true)->count(),
        ];
    }
}