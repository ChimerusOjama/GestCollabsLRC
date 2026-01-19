<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use App\Models\User;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class CollaborateurController extends Controller
{
    public function index(Request $request)
    {
        $query = Collaborateur::with(['manager', 'user'])->latest();
        
        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricule', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $collaborateurs = $query->paginate(15);
        $departements = Collaborateur::distinct()->pluck('department');
        
        return view('admin.collaborateurs.index', compact('collaborateurs', 'departements'));
    }

    public function create()
    {
        $managers = Manager::all();
        return view('admin.collaborateurs.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'required|unique:collaborateurs|max:20',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|unique:users,email', // ← ENLEVER "unique:collaborateurs"
            'password' => 'required|min:8',
            // Ajoutez email_pro si nécessaire
            'email_pro' => 'nullable|email|unique:collaborateurs,email_pro',
            'phone' => 'nullable|max:20',
            'address' => 'nullable',
            'date_of_birth' => 'nullable|date',
            'department' => 'required|max:100',
            'poste' => 'required|max:100',
            'date_embauche' => 'required|date',
            'statut' => 'required|in:actif,inactif,congé,licencié',
            'salaire' => 'nullable|numeric',
            'manager_id' => 'nullable|exists:managers,id',
        ]);

        try {
            DB::beginTransaction();

            // 1. Créer l'utilisateur
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'collaborateur',
            ]);

            // 2. Créer le collaborateur
            $collaborateur = Collaborateur::create([
                'user_id' => $user->id,
                'matricule' => $validated['matricule'],
                'email_pro' => $validated['email_pro'] ?? null, // Email professionnel
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'department' => $validated['department'],
                'poste' => $validated['poste'],
                'date_embauche' => $validated['date_embauche'],
                'statut' => $validated['statut'],
                'salaire' => $validated['salaire'] ?? null,
                'manager_id' => $validated['manager_id'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.collaborateurs.index')
                ->with('success', 'Collaborateur créé avec succès!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function show(Collaborateur $collaborateur)
    {
        $collaborateur->load(['manager', 'user']);
        return view('admin.collaborateurs.show', compact('collaborateur'));
    }

    public function edit(Collaborateur $collaborateur)
    {
        $managers = Manager::all();
        return view('admin.collaborateurs.edit', compact('collaborateur', 'managers'));
    }

    public function update(Request $request, Collaborateur $collaborateur)
    {
        $validated = $request->validate([
            'matricule' => 'sometimes|unique:collaborateurs,matricule,' . $collaborateur->id,
            'email' => 'sometimes|email|unique:collaborateurs,email,' . $collaborateur->id,
            'statut' => 'sometimes|in:actif,inactif,congé,licencié',
        ]);

        try {
            DB::beginTransaction();
            
            $oldData = $collaborateur->toArray();
            $collaborateur->update($validated);
            
            // Mettre à jour l'email de l'utilisateur si modifié
            if ($request->has('email') && $collaborateur->user) {
                $collaborateur->user->update(['email' => $validated['email']]);
            }
            
            DB::commit();
            
            // Log des changements
            $changes = array_diff_assoc($collaborateur->fresh()->toArray(), $oldData);
            if (!empty($changes)) {
                Log::channel('api')->info('📝 COLLABORATEUR MODIFIÉ VIA WEB', [
                    'id' => $collaborateur->id,
                    'changes' => $changes,
                    'updated_by' => auth()->id()
                ]);
            }
            
            return redirect()->route('admin.collaborateurs.index')
                ->with('success', 'Collaborateur mis à jour avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('api')->error('💥 ERREUR MODIFICATION COLLABORATEUR WEB', [
                'id' => $collaborateur->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function destroy(Collaborateur $collaborateur)
    {
        try {
            DB::beginTransaction();
            
            $collaborateur->delete();
            if ($collaborateur->user) {
                $collaborateur->user->delete();
            }
            
            DB::commit();
            
            Log::channel('api')->info('🗑️  COLLABORATEUR SUPPRIMÉ VIA WEB', [
                'id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'deleted_by' => auth()->id()
            ]);
            
            return redirect()->route('admin.collaborateurs.index')
                ->with('success', 'Collaborateur supprimé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('api')->error('💥 ERREUR SUPPRESSION COLLABORATEUR WEB', [
                'id' => $collaborateur->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}