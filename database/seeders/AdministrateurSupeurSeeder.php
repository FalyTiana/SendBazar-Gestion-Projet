<?php

namespace Database\Seeders;

use App\Models\AdministrateurSupeur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministrateurSupeurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $admins = [
            ['name' => 'Admin156', 'email' => 'admin156@example.com', 'mot_de_passe' => ('password1')],
            ['name' => 'Admin285', 'email' => 'admin285@example.com', 'mot_de_passe' => ('password2')],
            ['name' => 'Admin395', 'email' => 'admin395@example.com', 'mot_de_passe' => ('password3')],
        ];

        foreach ($admins as $admin) {
            AdministrateurSupeur::create($admin);
        }
    }
}
