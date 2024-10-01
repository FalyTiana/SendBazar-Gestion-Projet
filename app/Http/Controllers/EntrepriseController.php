<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntrepriseController extends Controller
{
    // Méthode pour créer une entreprise
    public function createEntreprise(Request $request)
    {
        // Validation (nom non obligatoire)
        $request->validate([
            'nom' => 'nullable|string|max:255'// Aucune validation obligatoire pour le nom
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
    }

    public function getAllEntreprises()
    {
        // Récupérer toutes les entreprises
        $entreprises = Entreprise::all();

        // Retourner les entreprises dans une réponse JSON
        return response()->json([
            'message' => 'Liste des entreprises récupérée avec succès',
            'entreprises' => $entreprises
        ]);
    }

    public function updateEntreprise(Request $request, $id)
    {
        // Récupérer l'utilisateur authentifié (administrateur)
        $administrateur = Auth::user();

        // Trouver l'entreprise via l'ID passé dans l'URL
        $entreprise = Entreprise::findOrFail($id);

        // Vérifier que l'administrateur est bien lié à cette entreprise
        if ($entreprise->id !== $administrateur->entreprise_id) {
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
    }
}
