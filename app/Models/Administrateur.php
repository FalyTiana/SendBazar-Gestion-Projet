<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Administrateur extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['nom', 'email', 'telephone', 'poste', 'mot_de_passe', 'entreprise_id', 'email_verified_at'];
    protected $attributes = [
        'nom' => null,
        'telephone' => null,
        'poste' => null,
        'entreprise_id' => null,
        'email_verified_at' => null,
    ];

    protected $hidden = ['mot_de_passe', 'created_at', 'updated_at'];

    // Relation One-to-One avec Entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Mutator pour hacher le mot de passe automatiquement
    public function setMotDePasseAttribute($value)
    {
        $this->attributes['mot_de_passe'] = Hash::make($value);
    }

}
