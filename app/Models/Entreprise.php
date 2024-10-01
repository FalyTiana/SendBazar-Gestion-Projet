<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    // Relation One-to-One avec Administrateur
    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }
}
