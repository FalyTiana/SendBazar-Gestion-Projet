<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use App\Models\Employe;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeController extends Controller
{
    /**
     * Récupérer tous les employés d'une entreprise donnée
     */
    public function getAll($id_entreprise)
    {
        try {
            // On s'assure que l'utilisateur est authentifié via Sanctum
            $user = Auth::user();
            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérifier si l'utilisateur est un administrateur ou un employé
            if (!($user instanceof Administrateur || $user instanceof Employe)) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Vérification que l'utilisateur est bien l'administrateur ou employé de l'entreprise
            if ($user->entreprise_id != $id_entreprise) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à acceder à la list des employés pour cette entreprise'], 403);
            }
            // Récupérer tous les employés appartenant à l'entreprise donnée
            $employes = Employe::where('entreprise_id', $id_entreprise)->get();

            // Vérifier si l'entreprise a des employés
            if ($employes->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun employé trouvé pour cette entreprise.',
                ], 404);
            }

            // Ajouter un rôle à chaque employé (admin ou employé)
            $employes = $employes->map(function ($employe) {
                // Vérifier si l'email de l'employé correspond à celui d'un administrateur
                $admin = Administrateur::where('email', $employe->email)->first();
                if ($admin) {
                    // Si l'employé est lié à un administrateur
                    $employe->role = 'admin';
                } else {
                    // Pour tous les autres employés
                    $employe->role = 'employe';
                }
                return $employe;
            });


            // Retourner la liste des employés
            return response()->json([
                'message' => 'Liste des employés récupérée avec succès.',
                'data' => $employes,
            ], 200);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des employés.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteEmployeById($id)
    {
        try {
            // On s'assure que l'utilisateur est authentifié via Sanctum
            $user = Auth::user();
            // Vérifier que l'utilisateur est bien authentifié
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérifier si l'utilisateur est un administrateur ou un employé
            if (!($user instanceof Administrateur || $user instanceof Employe)) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Rechercher l'entreprise par son ID
            $entreprise = Entreprise::find($user->entreprise_id);

            // Vérifier si l'entreprise existe
            if (!$entreprise) {
                return response()->json([
                    'message' => 'Entreprise non trouvée'
                ], 404); // Retourner une réponse 404 si l'entreprise n'existe pas
            }

            // Rechercher l'employé par son ID
            $employe = Employe::find($id);

            if (!$employe) {
                return response()->json(['error' => 'Employé non trouvé'], 404); // Employé non trouvé
            }

            // verifier si l'administrateur et un employé appartien a la même entrepeise
            if ($user instanceof Administrateur) {
                if ($user->entreprise_id != $employe->entreprise_id) {
                    return response()->json(['error' => 'Utilisateur non autorisé'], 403);
                }
            }

            // Vérifier si l'email de l'employé correspond à celui d'un administrateur
            $admin = Administrateur::where('email', $employe->email)->first();

            if ($admin) {
                // Si un administrateur avec cet email existe, empêcher la suppression
                return response()->json(['error' => 'La suppression d\'un administrateur est interdite'], 403);
            }

            // Supprimer l'employe
            $employe->delete();

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Employe supprimés avec succès'
            ], 200); // Retourner une réponse 200 en cas de succès
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // On s'assure que l'utilisateur est authentifié via Sanctum
            $employe = Auth::user();
            if (!$employe instanceof Employe) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Validation des informations fournies
            $request->validate([
                'nom' => 'nullable|sometimes|string|max:255',
                'email' => 'nullable|sometimes|email|unique:administrateurs,email,' . $employe->id, // Vérification que l'email est unique mais exclure l'utilisateur actuel
                'telephone' => 'nullable|sometimes|string',
                'poste' => 'nullable|sometimes|string',
            ]);

            // Mise à jour des informations
            if ($request->has('nom')) {
                $employe->nom = $request->nom;
            }
            if ($request->has('email')) {
                $employe->email = $request->email;
            }
            if ($request->has('telephone')) {
                $employe->telephone = $request->telephone;
            }
            if ($request->has('poste')) {
                $employe->poste = $request->poste;
            }

            // Sauvegarder les changements
            $employe->save();

            // Vérifier si l'email de l'employé correspond à celui d'un administrateur
            $admin = Administrateur::where('email', $employe->email)->first();
            if ($admin) {
                // Mise à jour des informations
                if ($request->has('nom')) {
                    $admin->nom = $request->nom;
                }
                if ($request->has('email')) {
                    $admin->email = $request->email;
                }
                if ($request->has('telephone')) {
                    $admin->telephone = $request->telephone;
                }
                if ($request->has('poste')) {
                    $admin->poste = $request->poste;
                }

                // Sauvegarder les changements
                $admin->save();
            }

            // Retourner une réponse
            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'administrateur' => $employe
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Méthode pour changer le mot de passe de l'employé
    public function changePassword(Request $request)
    {
        try { // Récupérer l'employe authentifié via le garde 'employé'
            $employe = Auth::user();

            // Vérifier que l'employé est bien une instance du modèle
            if (!$employe instanceof Employe) {
                return response()->json(['message' => 'Employé non trouvé'], 404);
            }

            // Validation des champs
            $request->validate([
                'ancien_mot_de_passe' => 'required|string',
                'nouveau_mot_de_passe' => 'required|string',
            ]);

            // Vérifier que l'ancien mot de passe est correct
            if (!Hash::check($request->ancien_mot_de_passe, $employe->mot_de_passe)) {
                return response()->json(['message' => 'Ancien mot de passe incorrect'], 401);
            }

            // Mettre à jour avec le nouveau mot de passe
            $employe->mot_de_passe = $request->nouveau_mot_de_passe;
            $employe->save();

            // Vérifier si l'email de l'employé correspond à celui d'un administrateur
            $admin = Administrateur::where('email', $employe->email)->first();

            if ($admin) {
                // Mettre à jour avec le nouveau mot de passe
                $admin->mot_de_passe = $request->nouveau_mot_de_passe;

                // Sauvegarder les changements
                $admin->save();
            }

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
