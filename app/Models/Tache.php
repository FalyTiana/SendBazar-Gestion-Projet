<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Tache extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'date_debut', 'date_fin', 'projet_id', 'assignable_id'];

    // Relation polymorphique
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    // Un projet appartient à une tâche
    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }
}
