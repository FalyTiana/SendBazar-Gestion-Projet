<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'date_debut', 'date_fin', 'description', 'entreprise_id'];

    // Un projet appartient à une entreprise
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Un projet a plusieurs chefs de projet
    public function chefs(): BelongsToMany
    {
        return $this->belongsToMany(Employe::class, 'chef_projet');
    }

    // Un projet a plusieurs membres
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Employe::class, 'membre_projet');
    }

    // Fonction pour obtenir toutes les relations définies
    public function loadAllRelations()
    {
        $relations = array_keys($this->getRelations());

        return $this->with($relations);
    }

    // Event for deleting related pivot entries
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($projet) {
            // Detach all related chefs and membres
            $projet->chefs()->detach();
            $projet->membres()->detach();
        });
    }
}
