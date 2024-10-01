<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Méthode d'inscription avec envoi de code de vérification
    public function register(Request $request)
    {
        // Validation des données
        $request->validate([
            'email' => 'required|email|unique:administrateurs',
            'mot_de_passe' => 'required',
        ]);

        // Créer un nouvel administrateur
        $administrateur = Administrateur::create([
            'email' => $request->email,
            'mot_de_passe' => $request->mot_de_passe, // Le mutator va hacher automatiquement
            'nom' => $request->nom ?? null,
            'telephone' => $request->telephone ?? null,
            'poste' => $request->poste ?? null,
            'entreprise_id' => $request->entreprise_id,
        ]);

        // Générer un code de vérification aléatoire
        $verificationCode = Str::random(6);

        // Enregistrer le code dans un champ temporaire (ou dans une table séparée si nécessaire)
        $administrateur->verification_code = $verificationCode;
        $administrateur->save();

        // Envoyer le code à l'email de l'utilisateur
        Mail::raw("Votre code de vérification est : $verificationCode", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Code de vérification de votre compte');
        });

        return response()->json(['message' => 'Un code de vérification a été envoyé à votre adresse e-mail.']);
    }

    // Méthode de vérification du code et génération de token JWT
    public function verifyEmail(Request $request)
    {
        // Validation du code
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required',
        ]);

        // Trouver l'utilisateur via l'email
        $administrateur = Administrateur::where('email', $request->email)->first();

        // Vérifier si le code est correct
        if ($administrateur && $administrateur->verification_code === $request->verification_code) {
            // Code correct, générer un token JWT
            // $token = $administrateur->createToken('auth_token')->plainTextToken;

            // Supprimer le code de vérification
            $administrateur->verification_code = null;
            $administrateur->email_verified_at = now();
            $administrateur->save();

            return response()->json([
                'message' => 'Email vérifié avec succès',
            ]);
        } else {
            return response()->json(['message' => 'Code de vérification incorrect'], 401);
        }
    }

    public function login(Request $request)
    {
        // Valider l'email et le mot de passe
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required',
        ]);

        // Trouver l'utilisateur par email
        $administrateur = Administrateur::where('email', $request->email)->first();

        // Vérifier si l'utilisateur existe, si l'email est vérifié et si le mot de passe est correct
        if ($administrateur && Hash::check($request->mot_de_passe, $administrateur->mot_de_passe)) {
            if (!$administrateur->email_verified_at) {
                // Si l'email n'est pas vérifié, renvoyer une erreur
                return response()->json(['message' => 'Votre email n\'a pas été vérifié.'], 403);
            }

            // Si tout est correct, générer un token
            $token = $administrateur->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'administrateur' => $administrateur
            ]);
        } else {
            // Si les informations ne sont pas correctes, renvoyer une erreur
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }
    }

    // Méthode de déconnexion
    public function logout(Request $request)
    {
        // Récupérer l'administrateur authentifié
        $administrateur = Auth::user();

        if ($administrateur) {
            // Révoquer le token actuel utilisé par l'utilisateur
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Déconnexion réussie',
            ]);
        }

        return response()->json(['message' => 'Non authentifié'], 401);
    }
}
