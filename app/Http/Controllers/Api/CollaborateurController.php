<?php
// app/Http/Controllers/Api/CollaborateurController.php

namespace App\Http\Controllers\Api;

use App\Models\Collaborateur;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\CollaborateurResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CollaborateurController extends Controller
{
    /**
     * Afficher tous les collaborateurs
     */
    public function index()
    {
        Log::channel('api')->info('📋 LISTE DES COLLABORATEURS DEMANDÉE', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $collaborateurs = Collaborateur::with(['manager', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        Log::channel('api')->info('✅ LISTE DES COLLABORATEURS RÉCUPÉRÉE', [
            'count' => $collaborateurs->count(),
            'total' => $collaborateurs->total()
        ]);

        return CollaborateurResource::collection($collaborateurs);
    }

    /**
     * Créer un nouveau collaborateur
     */
    public function store(Request $request)
    {
        Log::channel('api')->info('🆕 CRÉATION COLLABORATEUR DEMANDÉE', [
            'user_id' => auth()->id(),
            'data' => $request->except(['password']),
            'ip' => request()->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'matricule' => 'required|unique:collaborateurs|max:20',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|unique:collaborateurs|unique:users,email',
            'password' => 'required|min:8',
            'department' => 'required|max:100',
            'poste' => 'required|max:100',
            'date_embauche' => 'required|date',
            'statut' => 'required|in:actif,inactif,congé,licencié',
            'salaire' => 'nullable|numeric|min:0',
            'phone' => 'nullable|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'manager_id' => 'nullable|exists:managers,id',
            'competences' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            Log::channel('api')->warning('❌ VALIDATION ÉCHOUÉE POUR CRÉATION COLLABORATEUR', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->except(['password'])
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            Log::info('🎯 DÉBUT CRÉATION COLLABORATEUR', [
                'matricule' => $request->matricule,
                'email' => $request->email
            ]);

            // Créer le collaborateur
            $collaborateur = Collaborateur::create($request->all());

            Log::info('✅ COLLABORATEUR CRÉÉ', [
                'id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'email' => $collaborateur->email
            ]);

            // Créer l'utilisateur associé
            $user = User::create([
                'email' => $collaborateur->email,
                'password' => bcrypt($request->password),
                'email_verified_at' => now(),
                'userable_type' => Collaborateur::class,
                'userable_id' => $collaborateur->id,
            ]);

            Log::info('👤 UTILISATEUR ASSOCIÉ CRÉÉ', [
                'user_id' => $user->id,
                'collaborateur_id' => $collaborateur->id
            ]);

            DB::commit();

            Log::channel('api')->info('🎉 COLLABORATEUR CRÉÉ AVEC SUCCÈS', [
                'collaborateur_id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'created_by' => auth()->id(),
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collaborateur créé avec succès',
                'data' => new CollaborateurResource($collaborateur->load(['manager', 'user']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('api')->error('💥 ERREUR CRITIQUE LORS DE LA CRÉATION COLLABORATEUR', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $request->except(['password'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du collaborateur',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Afficher un collaborateur spécifique
     */
    public function show(Collaborateur $collaborateur)
    {
        Log::channel('api')->info('👁️ CONSULTATION COLLABORATEUR', [
            'collaborateur_id' => $collaborateur->id,
            'matricule' => $collaborateur->matricule,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        return new CollaborateurResource($collaborateur->load(['manager', 'user']));
    }

    /**
     * Mettre à jour un collaborateur
     */
    public function update(Request $request, Collaborateur $collaborateur)
    {
        Log::channel('api')->info('🔄 MISE À JOUR COLLABORATEUR DEMANDÉE', [
            'collaborateur_id' => $collaborateur->id,
            'matricule' => $collaborateur->matricule,
            'user_id' => auth()->id(),
            'changes' => $request->all(),
            'ip' => request()->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'matricule' => 'sometimes|unique:collaborateurs,matricule,' . $collaborateur->id,
            'email' => 'sometimes|email|unique:collaborateurs,email,' . $collaborateur->id,
            'statut' => 'sometimes|in:actif,inactif,congé,licencié',
            'salaire' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:managers,id',
            'date_embauche' => 'sometimes|date',
            'date_of_birth' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            Log::channel('api')->warning('❌ VALIDATION ÉCHOUÉE POUR MISE À JOUR COLLABORATEUR', [
                'collaborateur_id' => $collaborateur->id,
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldData = $collaborateur->toArray();
            
            // Mettre à jour le collaborateur
            $collaborateur->update($request->all());

            // Mettre à jour l'email de l'utilisateur associé si besoin
            if ($request->has('email') && $collaborateur->user) {
                $oldEmail = $collaborateur->user->email;
                $collaborateur->user->update(['email' => $request->email]);
                
                Log::info('📧 EMAIL UTILISATEUR MIS À JOUR', [
                    'collaborateur_id' => $collaborateur->id,
                    'old_email' => $oldEmail,
                    'new_email' => $request->email
                ]);
            }

            DB::commit();

            // Log des changements
            $newData = $collaborateur->fresh()->toArray();
            $changes = array_diff_assoc($newData, $oldData);

            if (!empty($changes)) {
                Log::channel('api')->info('✅ COLLABORATEUR MIS À JOUR AVEC SUCCÈS', [
                    'collaborateur_id' => $collaborateur->id,
                    'matricule' => $collaborateur->matricule,
                    'changes' => $changes,
                    'updated_by' => auth()->id(),
                    'ip' => request()->ip()
                ]);
            } else {
                Log::channel('api')->info('ℹ️  MISE À JOUR COLLABORATEUR SANS CHANGEMENTS', [
                    'collaborateur_id' => $collaborateur->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Collaborateur mis à jour avec succès',
                'data' => new CollaborateurResource($collaborateur->fresh()->load(['manager', 'user']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('api')->error('💥 ERREUR CRITIQUE LORS DE LA MISE À JOUR COLLABORATEUR', [
                'collaborateur_id' => $collaborateur->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du collaborateur',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Supprimer un collaborateur (soft delete)
     */
    public function destroy(Collaborateur $collaborateur)
    {
        Log::channel('api')->warning('🗑️  SUPPRESSION COLLABORATEUR DEMANDÉE', [
            'collaborateur_id' => $collaborateur->id,
            'matricule' => $collaborateur->matricule,
            'nom_complet' => $collaborateur->first_name . ' ' . $collaborateur->last_name,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        try {
            DB::beginTransaction();

            // Soft delete du collaborateur
            $collaborateur->delete();

            // Soft delete de l'utilisateur associé
            if ($collaborateur->user) {
                $collaborateur->user->delete();
                Log::info('👤 UTILISATEUR SUPPRIMÉ (SOFT DELETE)', [
                    'user_id' => $collaborateur->user->id,
                    'collaborateur_id' => $collaborateur->id
                ]);
            }

            DB::commit();

            Log::channel('api')->info('✅ COLLABORATEUR SUPPRIMÉ AVEC SUCCÈS', [
                'collaborateur_id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'deleted_by' => auth()->id(),
                'ip' => request()->ip(),
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collaborateur supprimé avec succès',
                'deleted_at' => $collaborateur->deleted_at
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('api')->error('💥 ERREUR CRITIQUE LORS DE LA SUPPRESSION COLLABORATEUR', [
                'collaborateur_id' => $collaborateur->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du collaborateur',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restaurer un collaborateur supprimé
     */
    public function restore($id)
    {
        Log::channel('api')->info('♻️  RESTAURATION COLLABORATEUR DEMANDÉE', [
            'collaborateur_id' => $id,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        try {
            $collaborateur = Collaborateur::withTrashed()->findOrFail($id);
            
            DB::beginTransaction();
            
            // Restaurer le collaborateur
            $collaborateur->restore();

            // Restaurer l'utilisateur associé
            if ($collaborateur->user) {
                $collaborateur->user->restore();
                Log::info('👤 UTILISATEUR RESTAURÉ', [
                    'user_id' => $collaborateur->user->id,
                    'collaborateur_id' => $collaborateur->id
                ]);
            }

            DB::commit();

            Log::channel('api')->info('✅ COLLABORATEUR RESTAURÉ AVEC SUCCÈS', [
                'collaborateur_id' => $collaborateur->id,
                'matricule' => $collaborateur->matricule,
                'restored_by' => auth()->id(),
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collaborateur restauré avec succès',
                'data' => new CollaborateurResource($collaborateur->fresh()->load(['manager', 'user']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('api')->error('💥 ERREUR CRITIQUE LORS DE LA RESTAURATION COLLABORATEUR', [
                'collaborateur_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration du collaborateur',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Forcer la suppression d'un collaborateur (hard delete)
     */
    public function forceDestroy($id)
    {
        Log::channel('api')->critical('💣 SUPPRESSION DÉFINITIVE COLLABORATEUR DEMANDÉE', [
            'collaborateur_id' => $id,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        try {
            $collaborateur = Collaborateur::withTrashed()->findOrFail($id);
            
            DB::beginTransaction();
            
            // Supprimer définitivement l'utilisateur associé
            if ($collaborateur->user) {
                $collaborateur->user->forceDelete();
                Log::info('👤 UTILISATEUR SUPPRIMÉ DÉFINITIVEMENT', [
                    'user_id' => $collaborateur->user->id,
                    'collaborateur_id' => $collaborateur->id
                ]);
            }
            
            // Supprimer définitivement le collaborateur
            $collaborateur->forceDelete();

            DB::commit();

            Log::channel('api')->critical('🗑️  COLLABORATEUR SUPPRIMÉ DÉFINITIVEMENT', [
                'collaborateur_id' => $id,
                'matricule' => $collaborateur->matricule,
                'deleted_by' => auth()->id(),
                'ip' => request()->ip(),
                'permanent' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collaborateur supprimé définitivement'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('api')->error('💥 ERREUR CRITIQUE LORS DE LA SUPPRESSION DÉFINITIVE COLLABORATEUR', [
                'collaborateur_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression définitive',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}