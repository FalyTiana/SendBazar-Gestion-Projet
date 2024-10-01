<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AdministrateursController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('entreprises', [EntrepriseController::class, 'createEntreprise']);

Route::post('register', [AuthController::class, 'register']);
Route::post('verify-email', [AuthController::class, 'verifyEmail']);
Route::post('login', [AuthController::class, 'login']);

// Route::get('entreprises', [EntrepriseController::class, 'getAllEntreprises']);

Route::middleware('auth:sanctum')->put('entreprises/{id}', [EntrepriseController::class, 'updateEntreprise']);
Route::middleware('auth:sanctum')->put('administrateurs/profile', [AdministrateursController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('administrateurs/entreprise', [AdministrateursController::class, 'getEntreprise']);
Route::middleware('auth:sanctum')->get('administrateurs/profile', [AdministrateursController::class, 'getProfile']);
Route::middleware('auth:sanctum')->post('administrateurs/change-password', [AdministrateursController::class, 'changePassword']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
