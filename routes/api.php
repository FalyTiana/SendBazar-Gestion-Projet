<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AdministrateursController;
use App\Http\Controllers\EmployeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

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
Route::middleware('auth:sanctum')->get('logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/entreprises/{id_entreprise}/employes', [EmployeController::class, 'getAll']);
Route::middleware('auth:sanctum')->delete('employes/{id}', [EmployeController::class, 'deleteEmployeById']);
Route::middleware('auth:sanctum')->put('employes/profile', [EmployeController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->post('employes/change-password', [EmployeController::class, 'changePassword']);

