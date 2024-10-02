<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class AdministrateurSupeur extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'administrateur_supeur';

    protected $fillable = ['name', 'email', 'mot_de_passe'];

    protected $hidden = ['mot_de_passe', 'remember_token'];

    // Mutator pour hacher le mot de passe automatiquement
    public function setMotDePasseAttribute($value)
    {
        $this->attributes['mot_de_passe'] = Hash::make($value);
    }
}
