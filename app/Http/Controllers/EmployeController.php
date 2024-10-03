<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use App\Models\Employe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

            // Vérification que l'administrateur est bien l'administrateur de l'entreprise
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
}
