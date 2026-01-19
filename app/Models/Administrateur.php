<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'department',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
}