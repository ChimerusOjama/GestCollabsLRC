<?php
// app/Http/Controllers/Api/CollaborateurController.php

namespace App\Http\Controllers\Api;

use App\Models\Collaborateur;
use App\Http\Controllers\Controller;
use App\Http\Resources\CollaborateurResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollaborateurController extends Controller
{
    /**
     * Afficher tous les collaborateurs
     */
    public function index()
    {
        $collaborateurs = Collaborateur::with(['manager', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CollaborateurResource::collection($collaborateurs);
    }

    /**
     * Créer un nouveau collaborateur
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matricule' => 'required|unique:collaborateurs|max:20',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|unique:collaborateurs',
            'department' => 'required|max:100',
            'poste' => 'required|max:100',
            'date_embauche' => 'required|date',
            'statut' => 'required|in:actif,inactif,congé,licencié',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Utiliser le UserFactory pour créer un collaborateur complet
        $user = \App\Services\UserFactory::create($request->all(), 'collaborateur');

        return response()->json([
            'success' => true,
            'message' => 'Collaborateur créé avec succès',
            'data' => new CollaborateurResource($user->profil)
        ], 201);
    }

    /**
     * Afficher un collaborateur spécifique
     */
    public function show(Collaborateur $collaborateur)
    {
        return new CollaborateurResource($collaborateur->load(['manager', 'user']));
    }

    /**
     * Mettre à jour un collaborateur
     */
    public function update(Request $request, Collaborateur $collaborateur)
    {
        $validator = Validator::make($request->all(), [
            'matricule' => 'sometimes|unique:collaborateurs,matricule,' . $collaborateur->id,
            'email' => 'sometimes|email|unique:collaborateurs,email,' . $collaborateur->id,
            'statut' => 'sometimes|in:actif,inactif,congé,licencié',
            'salaire' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $collaborateur->update($request->all());

        // Mettre à jour l'email de l'utilisateur associé si besoin
        if ($request->has('email') && $collaborateur->user) {
            $collaborateur->user->update(['email' => $request->email]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Collaborateur mis à jour avec succès',
            'data' => new CollaborateurResource($collaborateur->fresh())
        ]);
    }

    /**
     * Supprimer un collaborateur
     */
    public function destroy(Collaborateur $collaborateur)
    {
        // Supprimer l'utilisateur associé (cascade)
        if ($collaborateur->user) {
            $collaborateur->user->delete();
        }

        $collaborateur->delete();

        return response()->json([
            'success' => true,
            'message' => 'Collaborateur supprimé avec succès'
        ]);
    }
}