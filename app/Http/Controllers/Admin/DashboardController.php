<?php

// app/Http/Controllers/Admin/DashboardController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;

class DashboardController extends Controller
{
    public function index()
    {
        // Calcul des statistiques
        $stats = [
            'total' => Collaborateur::count(),
            'actifs' => Collaborateur::where('statut', 'actif')->count(),
            'inactifs' => Collaborateur::where('statut', 'inactif')->count(),
            'conges' => Collaborateur::where('statut', 'congé')->count(),
            'licencies' => Collaborateur::where('statut', 'licencié')->count(),
            'departements_count' => Collaborateur::distinct('department')->count('department'),
            'salaire_moyen' => Collaborateur::avg('salaire') ?? 0,
        ];

        // Derniers collaborateurs
        $recentCollaborateurs = Collaborateur::with('manager')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentCollaborateurs'));
    }
}