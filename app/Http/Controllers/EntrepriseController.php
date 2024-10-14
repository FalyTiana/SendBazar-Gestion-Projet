<?php

namespace App\Http\Controllers;

use App\Models\AdministrateurSupeur;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EntrepriseController extends Controller
{
    // Méthode pour créer une entreprise
    public function createEntreprise(Request $request)
    {

        try { // On s'assure que l'utilisateur est authentifié via Sanctum
            $administrateurSupeur = Auth::user();
            if (!$administrateurSupeur instanceof AdministrateurSupeur) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Validation (nom non obligatoire)
            $request->validate([
                'nom' => 'nullable|string|max:255' // Aucune validation obligatoire pour le nom
            ]);

            // Créer l'entreprise
            $entreprise = Entreprise::create([
                'nom' => $request->nom ?? null, // Si aucun nom n'est fourni, il sera null
            ]);

            // Retourner l'ID de l'entreprise nouvellement créée
            return response()->json([
                'message' => 'Entreprise créée avec succès',
                'entreprise_id' => $entreprise->id
            ]);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors de la création de l\'entreprise.',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création de l\'entreprise.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllEntreprises()
    {

        try {
            // On s'assure que l'utilisateur est authentifié via Sanctum
            $administrateurSupeur = Auth::user();
            if (!$administrateurSupeur instanceof AdministrateurSupeur) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Récupérer les entreprises qui ont des administrateurs associés
            $entreprises = Entreprise::whereHas('administrateurs')->with('administrateurs')->get();

            // Retourner les entreprises et leurs administrateurs dans une réponse JSON
            return response()->json([
                'message' => 'Liste des entreprises récupérée avec succès',
                'entreprises' => $entreprises
            ]);
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors de la récupération des entreprise.',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des entreprise.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEntreprise(Request $request, $id)
    {
        try {
            // Récupérer l'utilisateur authentifié (administrateur)
            $administrateur = Auth::user();

            // Trouver l'entreprise via l'ID passé dans l'URL
            $entreprise = Entreprise::findOrFail($id);

            // Vérifier que l'administrateur est bien lié à cette entreprise
            if ($entreprise->id != $administrateur->entreprise_id) {
                return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier cette entreprise'], 403);
            }

            // Validation des données de l'entreprise
            $request->validate([
                'nom' => 'nullable|string|max:255',
                // Ajouter d'autres champs à valider si nécessaire
            ]);

            // Mise à jour des informations de l'entreprise
            if ($request->has('nom')) {
                $entreprise->nom = $request->nom;
            }

            // Sauvegarder les changements
            $entreprise->save();

            // Retourner une réponse
            return response()->json([
                'message' => 'Informations de l\'entreprise mises à jour avec succès',
                'entreprise' => $entreprise
            ]);
        } catch (\Exception $e) {

            Log::error(
                'Une erreur est survenue lors de la modification de l\'entreprise.',
                $e->getMessage()
            );
            // Gérer les erreurs
            return response()->json([
                'message' => 'Une erreur est survenue lors de la modification de l\'entreprise.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteEntrepriseById($id)
    {
        try {
            // On s'assure que l'utilisateur est authentifié via Sanctum
            $administrateurSupeur = Auth::user();
            if (!$administrateurSupeur instanceof AdministrateurSupeur) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Rechercher l'entreprise par son ID
            $entreprise = Entreprise::find($id);

            // Vérifier si l'entreprise existe
            if (!$entreprise) {
                return response()->json([
                    'message' => 'Entreprise non trouvée'
                ], 404); // Retourner une réponse 404 si l'entreprise n'existe pas
            }

            // Supprimer les administrateurs associés
            $entreprise->administrateurs()->delete(); // Notez l'utilisation de "administrateurs" ici

            // Supprimer les employes associés
            $entreprise->employes()->delete();

            // Supprimer l'entreprise
            $entreprise->delete();

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Entreprise et administrateurs supprimés avec succès'
            ], 200); // Retourner une réponse 200 en cas de succès
        } catch (\Exception $e) {
            Log::error(
                'Une erreur est survenue lors de la suppression de entreprise.',
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
