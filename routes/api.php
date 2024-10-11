<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AdministrateursController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ProjetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->post('entreprises', [EntrepriseController::class, 'createEntreprise']);
Route::middleware('auth:sanctum')->post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('invitation', [AuthController::class, 'invitation']);

Route::middleware('auth:sanctum')->get('entreprises', [EntrepriseController::class, 'getAllEntreprises']);
Route::middleware('auth:sanctum')->delete('entreprises/{id}', [EntrepriseController::class, 'deleteEntrepriseById']);
Route::middleware('auth:sanctum')->put('entreprises/{id}', [EntrepriseController::class, 'updateEntreprise']);

Route::middleware('auth:sanctum')->put('administrateurs/profile', [AdministrateursController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('administrateurs/entreprise', [AdministrateursController::class, 'getEntreprise']);
Route::middleware('auth:sanctum')->get('administrateurs/profile', [AdministrateursController::class, 'getProfile']);
Route::middleware('auth:sanctum')->post('administrateurs/change-password', [AdministrateursController::class, 'changePassword']);

Route::middleware('auth:sanctum')->get('/entreprises/{id_entreprise}/employes', [EmployeController::class, 'getAll']);
Route::middleware('auth:sanctum')->delete('employes/{id}', [EmployeController::class, 'deleteEmployeById']);
Route::middleware('auth:sanctum')->put('employes/profile', [EmployeController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->post('employes/change-password', [EmployeController::class, 'changePassword']);

Route::middleware('auth:sanctum')->post('projets',[ProjetController::class, 'creerProjet']);
Route::middleware('auth:sanctum')->get('entreprises/{id_entreprise}/projets', [ProjetController::class, 'getAll']);
Route::middleware('auth:sanctum')->get('entreprises/projets/{id}/projets-membre', [ProjetController::class, 'getProjetsMembre']);
Route::middleware('auth:sanctum')->get('entreprises/projets/{id}/projets-chefs', [ProjetController::class, 'getProjetsChefs']);
Route::middleware('auth:sanctum')->delete('entreprises/projets/{projet_id}', [ProjetController::class, 'supprimerProjet']);
Route::middleware('auth:sanctum')->put('entreprises/projets/{projet_id}', [ProjetController::class, 'ajouterMembres']);
Route::middleware('auth:sanctum')->put('entreprises/projets/chefs/{projet_id}', [ProjetController::class, 'ajouterChef']);

Route::middleware('auth:sanctum')->get('entreprises/projets/{id}/projets-chefs/{id_projet}', [ProjetController::class, 'getProjetChef']);
Route::middleware('auth:sanctum')->put('entreprises/projets/{id_employe}/{id}', [ProjetController::class, 'modifierProjet']);
Route::middleware('auth:sanctum')->put('entreprises/projet/{projet_id}/membre-retire', [ProjetController::class, 'retirerMembre']);
Route::middleware('auth:sanctum')->put('entreprises/projet/{projet_id}/chef-retire', [ProjetController::class, 'retirerChef']);