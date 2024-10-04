<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Projet;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function creerProjet(Request $request)

    {
        // Validation des données envoyées
        $request->validate([
            'titre' => 'required|string',
            'entreprise_id' => 'required|exists:entreprises,id',
            'chefs' => 'required|array|min:1|max:3', // Doit avoir entre 1 et 3 chefs
            'chefs.*' => 'exists:employes,id', // Vérifie que chaque chef est un employé existant
            'membres' => 'nullable|array',
            'membres.*' => 'exists:employes,id', // Vérifie que chaque membre est un employé existant
        ]);

        // Vérifier si tous les chefs sont bien des employés de l'entreprise
        $chefs = Employe::whereIn('id', $request->chefs)
            ->where('entreprise_id', $request->entreprise_id)
            ->get();

        if ($chefs->count() < count($request->chefs)) {
            return response()->json([
                'error' => 'Tous les chefs doivent être des employés de l\'entreprise'
            ], 400);
        }

        // Si des membres sont envoyés, vérifier qu'ils sont bien des employés de l'entreprise
        if ($request->has('membres')) {
            $membres = Employe::whereIn('id', $request->membres)
                ->where('entreprise_id', $request->entreprise_id)
                ->get();

            if ($membres->count() < count($request->membres)) {
                return response()->json([
                    'error' => 'Tous les membres doivent être des employés de l\'entreprise'
                ], 400);
            }
        }

        // Créer le projet
        $projet = Projet::create([
            'titre' => $request->titre,
            'date_debut' => $request->date_debut ?? null,
            'date_fin' => $request->date_fin ?? null,
            'description' => $request->description ?? null,
            'entreprise_id' => $request->entreprise_id,
        ]);

        // Associer les chefs de projet
        $projet->chefs()->attach($request->chefs);

        // Associer les membres de projet, s'ils existent
        if ($request->has('membres')) {
            $projet->membres()->attach($request->membres);
        }

        // Retourner la réponse de succès
        return response()->json([
            'message' => 'Projet créé avec succès',
            'projet' => $projet,
            'chefs' => $chefs,
            'membres' => $membres ?? null
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
