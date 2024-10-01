<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdministrateursController extends Controller
{
    public function updateProfile(Request $request)
    {
        // On s'assure que l'utilisateur est authentifié via Sanctum
        $administrateur = Auth::user();
        if (!$administrateur instanceof Administrateur) {
            return response()->json(['error' => 'Utilisateur non autorisé'], 403);
        }

        // Validation des informations fournies
        $request->validate([
            'nom' => 'nullable|sometimes|string|max:255',
            'email' => 'nullable|sometimes|email|unique:administrateurs,email,' . $administrateur->id, // Vérification que l'email est unique mais exclure l'utilisateur actuel
            'telephone' => 'nullable|sometimes|string',
            'poste' => 'nullable|sometimes|string',
        ]);

        // Mise à jour des informations
        if ($request->has('nom')) {
            $administrateur->nom = $request->nom;
        }
        if ($request->has('email')) {
            $administrateur->email = $request->email;
        }
        if ($request->has('telephone')) {
            $administrateur->telephone = $request->telephone;
        }
        if ($request->has('poste')) {
            $administrateur->poste = $request->poste;
        }

        // Sauvegarder les changements
        $administrateur->save();

        // Retourner une réponse
        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'administrateur' => $administrateur
        ]);
    }

    // Méthode pour changer le mot de passe de l'administrateur
    public function changePassword(Request $request)
    {
        // Récupérer l'administrateur authentifié via le garde 'admin'
        $administrateur = Auth::user();

        // Vérifier que l'administrateur est bien une instance du modèle
        if (!$administrateur instanceof Administrateur) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        // Validation des champs
        $request->validate([
            'ancien_mot_de_passe' => 'required|string',
            'nouveau_mot_de_passe' => 'required|string',
        ]);

        // Vérifier que l'ancien mot de passe est correct
        if (!Hash::check($request->ancien_mot_de_passe, $administrateur->mot_de_passe)) {
            return response()->json(['message' => 'Ancien mot de passe incorrect'], 401);
        }

        // Mettre à jour avec le nouveau mot de passe
        $administrateur->mot_de_passe = Hash::make($request->nouveau_mot_de_passe);
        $administrateur->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
    }


    public function getEntreprise()
    {
        // Récupérer l'administrateur authentifié
        $administrateur = Auth::user();

        // Vérifier si l'administrateur est bien lié à une entreprise
        if (!$administrateur->entreprise) {
            return response()->json(['error' => 'Aucune entreprise associée à cet administrateur'], 404);
        }

        // Retourner les informations de l'entreprise
        return response()->json([
            'message' => 'Informations de l\'entreprise récupérées avec succès',
            'entreprise' => $administrateur->entreprise
        ]);
    }

    public function getProfile()
    {
        // Récupérer l'administrateur authentifié
        $administrateur = Auth::user();

        // Retourner les informations de l'administrateur
        return response()->json([
            'message' => 'Informations de l\'administrateur récupérées avec succès',
            'administrateur' => $administrateur
        ]);
    }
}
