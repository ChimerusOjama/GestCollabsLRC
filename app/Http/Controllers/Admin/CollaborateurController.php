<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use App\Models\User;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'email' => 'required|email|unique:collaborateurs|unique:users,email',
            'password' => 'required|min:8',
            'department' => 'required|max:100',
            'poste' => 'required|max:100',
            'date_embauche' => 'required|date',
            'statut' => 'required|in:actif,inactif,congé,licencié',
        ]);

        try {
            DB::beginTransaction();
            
            // Créer le collaborateur
            $collaborateur = Collaborateur::create($validated);
            
            // Créer l'utilisateur associé
            User::create([
                'email' => $collaborateur->email,
                'password' => bcrypt($validated['password']),
                'email_verified_at' => now(),
                'userable_type' => Collaborateur::class,
                'userable_id' => $collaborateur->id,
            ]);
            
            DB::commit();
            
            Log::channel('api')->info('👥 COLLABORATEUR CRÉÉ VIA WEB', [
                'id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'created_by' => auth()->id()
            ]);
            
            return redirect()->route('admin.collaborateurs.index')
                ->with('success', 'Collaborateur créé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('api')->error('💥 ERREUR CRÉATION COLLABORATEUR WEB', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password'])
            ]);
            
            return back()->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
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