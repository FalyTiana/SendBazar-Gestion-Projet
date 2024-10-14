<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use App\Models\Tache;
use App\Models\Projet;
use App\Models\Employe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TacheController extends Controller
{
    /**
     * Affiche toutes les tâches d'un projet.
     */
    public function index($projet_id)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projet_id);

            // Vérifier si l'utilisateur est chef ou membre du projet
            $estMembre = $projet->membres()->where('employe_id', $user->id)->exists();
            $estChef = $projet->chefs()->where('employe_id', $user->id)->exists();

            // Vérifier si l'utilisateur est un administrateur
            if ($user instanceof Administrateur) {
                $employe = Employe::where('email', $user->email)->first();
                // Vérifier si l'utilisateur est chef ou membre du projet
                $estMembre = $projet->membres()->where('employe_id', $employe->id)->exists();
                $estChef = $projet->chefs()->where('employe_id', $employe->id)->exists();
            }

            if (!$estMembre && !$estChef) {
                return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de voir les tâches de ce projet'], 403);
            }

            // Récupérer toutes les tâches du projet avec l'employé assigné
            $taches = Tache::where('projet_id', $projet_id)
                ->with('assignable') // Charger la relation 'assignable' (l'employé)
                ->get();

            return response()->json($taches);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors d\'affiche toutes les tâches d\'un projet',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Crée une nouvelle tâche pour un projet et l'assigne à un employé (membre ou chef).
     */
    public function store(Request $request, $projet_id)
    {

        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'nullable|string',
                'date_fin' => 'nullable|string',
                'employe_id' => 'nullable|exists:employes,id',
            ]);

            // Trouver le projet
            $projet = Projet::findOrFail($projet_id);

            // Vérifier si l'utilisateur est chef du projet
            $estChef = $projet->chefs()->where('employe_id', $user->id)->exists();

            // Vérifier si l'utilisateur est un administrateur
            if ($user instanceof Administrateur) {
                $employeUser = Employe::where('email', $user->email)->first();
                // Vérifier si l'utilisateur est chef du projet
                $estChef = $projet->chefs()->where('employe_id', $employeUser->id)->exists();
            }

            if (!$estChef) {
                return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de voir les tâches de ce projet'], 403);
            }

            // Vérifier si l'employé est bien un membre ou un chef du projet
            $employe = Employe::findOrFail($request->employe_id);

            $isMembre = ($projet->membres()->where('employe_id', $employe->id)->exists());
            $isChef =  ($projet->chefs()->where('employe_id', $employe->id)->exists());
            if (!$isMembre && !$isChef) {
                return response()->json(['error' => 'Cet employé n\'est ni un membre, ni un chef du projet'], 400);
            }

            // if ($request->assignable_type === 'membre_projet') {
            //     // Vérifier si l'employé est un membre du projet
            //     if (!$projet->membres()->where('employe_id', $employe->id)->exists()) {
            //         return response()->json(['error' => 'Cet employé n\'est pas un membre du projet'], 400);
            //     }
            // } elseif ($request->assignable_type === 'chef_projet') {
            //     // Vérifier si l'employé est un chef de projet
            //     if (!$projet->chefs()->where('employe_id', $employe->id)->exists()) {
            //         return response()->json(['error' => 'Cet employé n\'est pas un chef du projet'], 400);
            //     }
            // }

            // Créer la tâche
            $tache = new Tache($request->only(['titre', 'description', 'date_debut', 'date_fin']));
            $tache->projet_id = $projet->id;
            $tache->assignable()->associate($employe);

            $tache->save();

            return response()->json(['message' => 'Tâche créée avec succès', 'tache' => $tache], 201);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors de création une nouvelle tâche pour un projet et \'assigne à un employé (membre ou chef).',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affiche une tâche spécifique.
     */
    public function show($projet_id, $id)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projet_id);

            // Vérifier si l'utilisateur est chef ou membre du projet
            $estMembre = $projet->membres()->where('employe_id', $user->id)->exists();
            $estChef = $projet->chefs()->where('employe_id', $user->id)->exists();

            // Vérifier si l'utilisateur est un administrateur
            if ($user instanceof Administrateur) {
                $employe = Employe::where('email', $user->email)->first();
                // Vérifier si l'utilisateur est chef ou membre du projet
                $estMembre = $projet->membres()->where('employe_id', $employe->id)->exists();
                $estChef = $projet->chefs()->where('employe_id', $employe->id)->exists();
            }

            if (!$estMembre && !$estChef) {
                return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de voir les tâches de ce projet'], 403);
            }

            // Vérifier si la tâche appartient bien au projet donné
            $tache = Tache::where('projet_id', $projet_id)
                ->with('assignable')
                ->findOrFail($id);

            return response()->json($tache);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors d\'affiche une tâche spécifique.',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Met à jour une tâche existante.
     */
    public function update(Request $request, $projet_id, $id)
    {

        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Valider les données de la requête
            $request->validate([
                'titre' => 'string|max:255',
                'description' => 'string',
                'date_debut' => 'string',
                'date_fin' => 'string',
                'employe_id' => 'exists:employes,id'
            ]);

            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projet_id);

            // Vérifier si l'utilisateur est chef du projet
            $estChef = $projet->chefs()->where('employe_id', $user->id)->exists();

            // Vérifier si l'utilisateur est un administrateur
            if ($user instanceof Administrateur) {
                $employeUser = Employe::where('email', $user->email)->first();
                // Vérifier si l'utilisateur est chef du projet
                $estChef = $projet->chefs()->where('employe_id', $employeUser->id)->exists();
            }

            if (!$estChef) {
                return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de voir les tâches de ce projet'], 403);
            }

            // Vérifier que la tâche appartient bien à ce projet
            $tache = Tache::where('projet_id', $projet_id)->findOrFail($id);

            // Si un changement d'employé est demandé
            if ($request->has('employe_id')) {
                $employe = Employe::findOrFail($request->employe_id);

                // Vérifier que l'employé appartient au projet
                $isMembre = ($projet->membres()->where('employe_id', $employe->id)->exists());
                $isChef =  ($projet->chefs()->where('employe_id', $employe->id)->exists());
                if (!$isMembre && !$isChef) {
                    return response()->json(['error' => 'Cet employé n\'est ni un membre, ni un chef du projet'], 400);
                }

                // if ($request->assignable_type === 'membre_projet') {
                //     if (!$projet->membres()->where('employe_id', $employe->id)->exists()) {
                //         return response()->json(['error' => 'Cet employé n\'est pas un membre du projet'], 400);
                //     }
                // } elseif ($request->assignable_type === 'chef_projet') {
                //     if (!$projet->chefs()->where('employe_id', $employe->id)->exists()) {
                //         return response()->json(['error' => 'Cet employé n\'est pas un chef du projet'], 400);
                //     }
                // }

                // Associer l'employé à la tâche
                $tache->assignable()->associate($employe);
                $tache->save();
            }

            // Mettre à jour les autres informations de la tâche
            $tache->update($request->only(['titre', 'description', 'date_debut', 'date_fin']));

            return response()->json(['message' => 'Tâche mise à jour avec succès', 'tache' => $tache]);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors de mise à jour une tâche existante.',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Supprime une tâche.
     */
    public function destroy($projet_id, $id)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projet_id);
            // Vérifier si l'utilisateur est chef du projet
            $estChef = $projet->chefs()->where('employe_id', $user->id)->exists();

            // Vérifier si l'utilisateur est un administrateur
            if ($user instanceof Administrateur) {
                $employeUser = Employe::where('email', $user->email)->first();
                // Vérifier si l'utilisateur est chef du projet
                $estChef = $projet->chefs()->where('employe_id', $employeUser->id)->exists();
            }

            if (!$estChef) {
                return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de voir les tâches de ce projet'], 403);
            }

            // Vérifier que la tâche appartient bien à ce projet
            $tache = Tache::where('projet_id', $projet_id)->findOrFail($id);

            // Supprimer la tâche
            $tache->delete();

            return response()->json(['message' => 'Tâche supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors Supprime une tâche',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
