<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Employe extends Model
{
    use HasApiTokens,HasFactory;

    protected $fillable = ['nom', 'email', 'telephone', 'poste', 'mot_de_passe', 'entreprise_id'];
    protected $attributes = [
        'nom' => null,
        'telephone' => null,
        'poste' => null,
        'entreprise_id' => null,
    ];

    protected $hidden = ['mot_de_passe', 'created_at', 'updated_at'];

    // Relation One-to-One avec Entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function setMotDePasseAttribute($value)
    // Mutator pour hacher le mot de passe automatiquement
    {
        $this->attributes['mot_de_passe'] = Hash::make($value);
    }
    // Un employé peut être chef de plusieurs projets
    public function projetsCommeChef(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'chef_projet');
    }

    // Un employé peut être membre de plusieurs projets
    public function projetsCommeMembre(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'membre_projet');
    }

}
