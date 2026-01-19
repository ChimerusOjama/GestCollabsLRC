<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CollaborateurController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Routes d'authentification gérées par Jetstream/Fortify
// NE PAS ajouter de routes /login ou /logout manuelles ici

// Redirection de /register
Route::get('/register', function () {
    return redirect('/login')->with('info', 'Contactez l\'administrateur pour créer un compte.');
})->name('register');

// Routes protégées
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // CRUD Collaborateurs
    Route::resource('collaborateurs', CollaborateurController::class)->names([
        'index' => 'admin.collaborateurs.index',
        'create' => 'admin.collaborateurs.create',
        'store' => 'admin.collaborateurs.store',
        'show' => 'admin.collaborateurs.show',
        'edit' => 'admin.collaborateurs.edit',
        'update' => 'admin.collaborateurs.update',
        'destroy' => 'admin.collaborateurs.destroy',
    ]);
    
    // Logs
    Route::get('/logs', function () {
        $logFiles = glob(storage_path('logs/*.log'));
        return view('admin.logs.index', compact('logFiles'));
    })->name('admin.logs.index');
    
    // Route racine admin
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
});

// Page d'accueil publique
Route::get('/', function () {
    return redirect('/login');
});