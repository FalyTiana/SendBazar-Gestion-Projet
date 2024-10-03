<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use App\Models\AdministrateurSupeur;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Méthode d'inscription avec envoi de code de vérification
    // public function register(Request $request)
    // {
    //     // Validation des données
    //     $request->validate([
    //         'email' => 'required|email|unique:administrateurs',
    //         'mot_de_passe' => 'required',
    //     ]);

    //     // Créer un nouvel administrateur
    //     $administrateur = Administrateur::create([
    //         'email' => $request->email,
    //         'mot_de_passe' => $request->mot_de_passe, // Le mutator va hacher automatiquement
    //         'nom' => $request->nom ?? null,
    //         'telephone' => $request->telephone ?? null,
    //         'poste' => $request->poste ?? null,
    //         'entreprise_id' => $request->entreprise_id,
    //     ]);

    //     // Générer un code de vérification aléatoire
    //     $verificationCode = Str::random(6);

    //     // Enregistrer le code dans un champ temporaire (ou dans une table séparée si nécessaire)
    //     $administrateur->verification_code = $verificationCode;
    //     $administrateur->save();

    //     // Envoyer le code à l'email de l'utilisateur
    //     Mail::raw("Votre code de vérification est : $verificationCode", function ($message) use ($request) {
    //         $message->to($request->email)
    //             ->subject('Code de vérification de votre compte');
    //     });

    //     return response()->json(['message' => 'Un code de vérification a été envoyé à votre adresse e-mail.']);
    // }

    public function register(Request $request)
    {
        try {

            // On s'assure que l'utilisateur est authentifié via Sanctum
            $administrateurSupeur = Auth::user();
            if (!$administrateurSupeur instanceof AdministrateurSupeur) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }
            // Validation des données
            $request->validate([
                'nom' => 'required',
                'email' => 'required|email|unique:administrateurs', // Vérification de l'unicité de l'email
            ]);

            // Vérifier si l'entreprise a déjà un administrateur
            $existingAdmin = Administrateur::where('entreprise_id', $request->entreprise_id)->first();
            if ($existingAdmin) {
                return response()->json(['message' => 'Cette entreprise a déjà un administrateur.'], 400);
            }

            // Générer un mot de passe aléatoire
            $mot_de_passe = Str::random(8);

            // Créer un nouvel administrateur
            $administrateur = Administrateur::create([
                'email' => $request->email,
                'mot_de_passe' => $mot_de_passe, // Le mutator va hacher automatiquement
                'nom' => $request->nom ?? null,
                'telephone' => $request->telephone ?? null,
                'poste' => $request->poste ?? null,
                'entreprise_id' => $request->entreprise_id,
            ]);

            // Enregistrer l'administrateur
            $administrateur->save();

            // Envoyer le mot de passe par email à l'administrateur
            Mail::raw(
                "Bonjour {$request->nom},
    
    Votre compte administrateur a été créé avec succès.
    
    Voici vos informations de connexion :
    
    Email : {$request->email}
    Mot de passe : {$mot_de_passe}
    
    Nous vous recommandons de changer votre mot de passe dès votre première connexion.
    
    Merci de faire partie de notre équipe !
    
    Cordialement,
    L'équipe de gestion",
                function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('Création de votre compte administrateur');
                }
            );

            // Réponse en cas de succès
            return response()->json([
                'message' => 'Le compte a été créé avec succès.'
                // 'message' => 'Le compte a été créé avec succès. Un email contenant less informations de connexion a été envoyé à l\'adresse.'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gérer les erreurs de validation
            return response()->json([
                'message' => 'Erreur de validation des données.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Gérer les autres erreurs, comme l'email déjà utilisé
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du compte. Veuillez réessayer plus tard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Méthode de vérification du code et génération de token JWT
    // public function verifyEmail(Request $request)
    // {
    //     // Validation du code
    //     $request->validate([
    //         'email' => 'required|email',
    //         'verification_code' => 'required',
    //     ]);

    //     // Trouver l'utilisateur via l'email
    //     $administrateur = Administrateur::where('email', $request->email)->first();

    //     // Vérifier si le code est correct
    //     if ($administrateur && $administrateur->verification_code === $request->verification_code) {
    //         // Code correct, générer un token JWT
    //         // $token = $administrateur->createToken('auth_token')->plainTextToken;

    //         // Supprimer le code de vérification
    //         $administrateur->verification_code = null;
    //         $administrateur->email_verified_at = now();
    //         $administrateur->save();

    //         return response()->json([
    //             'message' => 'Email vérifié avec succès',
    //         ]);
    //     } else {
    //         return response()->json(['message' => 'Code de vérification incorrect'], 401);
    //     }
    // }

    public function login(Request $request)
    {
        // Valider l'email et le mot de passe
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required',
        ]);

        // Trouver l'utilisateur par email
        $administrateurSupeur = AdministrateurSupeur::where('email', $request->email)->first();
        $administrateur = Administrateur::where('email', $request->email)->first();

        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($administrateurSupeur && Hash::check($request->mot_de_passe, $administrateurSupeur->mot_de_passe)) {
            // Si tout est correct, générer un token
            $token = $administrateurSupeur->createToken('auth_token', ['role' => 'adminSuper'])->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'rôle' => 'adminSuper',
                'administrateur' => $administrateurSupeur
            ]);
        } elseif ($administrateur && Hash::check($request->mot_de_passe, $administrateur->mot_de_passe)) {
            // Si tout est correct, générer un token
            $token = $administrateur->createToken('auth_token', ['role' => 'admin'])->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'rôle' => 'admin',
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

    public function invitation(Request $request)
    {
        try {

            // On s'assure que l'utilisateur est authentifié via Sanctum
            $administrateur = Auth::user();
            if (!$administrateur instanceof Administrateur) {
                return response()->json(['error' => 'Utilisateur non autorisé'], 403);
            }

            // Vérification que l'administrateur est bien l'administrateur de l'entreprise
            if ($administrateur->entreprise_id !== $request->entreprise_id) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à inviter des employés pour cette entreprise'], 403);
            }

            // Validation des données
            $request->validate([
                'nom' => 'required',
                'email' => 'required|email|unique:administrateurs', // Vérification de l'unicité de l'email
            ]);


            // Générer un mot de passe aléatoire
            $mot_de_passe = Str::random(8);

            // Créer un nouvel employé
            $employe = Employe::create([
                'email' => $request->email,
                'mot_de_passe' => $mot_de_passe, // Le mutator va hacher automatiquement
                'nom' => $request->nom ?? null,
                'telephone' => $request->telephone ?? null,
                'poste' => $request->poste ?? null,
                'entreprise_id' => $request->entreprise_id,
            ]);

            // Enregistrer l'employé
            $employe->save();

            // Envoyer le mot de passe par email à l'employé
            Mail::raw(
                "Bonjour {$request->nom},

Nous sommes heureux de vous informer que votre compte employé au sein de l'entreprise {$administrateur->entreprise->nom} a été créé avec succès.

Voici vos informations de connexion :

- **Email** : {$request->email}
- **Mot de passe temporaire** : {$mot_de_passe}

Nous vous recommandons vivement de changer ce mot de passe lors de votre première connexion pour garantir la sécurité de votre compte.

Bienvenue dans notre équipe chez {$administrateur->entreprise->nom} ! Nous sommes ravis de vous compter parmi nous et espérons que vous apprécierez cette nouvelle aventure professionnelle.

Cordialement,

L'équipe de gestion de {$administrateur->entreprise->nom}",
                function ($message) use ($request, $administrateur) {
                    $message->to($request->email)
                        ->subject('Invitation à rejoindre l\'équipe de ' . $administrateur->entreprise->nom);
                }
            );

            // Réponse en cas de succès
            return response()->json([
                // 'message' => 'Le compte a été créé avec succès. Un email contenant les informations de connexion a été envoyé à l\'adresse.'
                'message' => 'Le compte a été créé avec succès.'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gérer les erreurs de validation
            return response()->json([
                'message' => 'Erreur de validation des données.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Gérer les autres erreurs, comme l'email déjà utilisé
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du compte. Veuillez réessayer plus tard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
