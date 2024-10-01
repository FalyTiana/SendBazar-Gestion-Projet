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
