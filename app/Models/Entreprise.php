<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    // Relation One-to-Many avec Administrateur
    public function administrateurs()
    {
        return $this->hasMany(Administrateur::class);
    }
    // Relation One-to-Many avec Administrateur
    public function employes()
    {
        return $this->hasMany(employe::class);
    }

    // Une entreprise a plusieurs projets
    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class);
    }
}
