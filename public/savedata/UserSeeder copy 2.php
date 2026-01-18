<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Services\UserFactory;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrateur
        UserFactory::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@lrcgroup.com',
            'password' => 'Admin123!',
            'phone' => '+33123456789',
            'department' => 'Administration',
        ], 'administrateur');

        // 2. Managers
        $rhManager = UserFactory::create([
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'email' => 'marie.dupont@lrcgroup.com',
            'password' => 'Manager123!',
            'department' => 'RH',
            'phone' => '+33123456790',
            'level' => 'senior',
        ], 'manager');

        $itManager = UserFactory::create([
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'email' => 'pierre.martin@lrcgroup.com',
            'password' => 'Manager123!',
            'department' => 'IT',
            'phone' => '+33123456791',
            'level' => 'director',
        ], 'manager');

        // 3. Collaborateurs
        UserFactory::create([
            'matricule' => 'EMP001',
            'first_name' => 'Jean',
            'last_name' => 'Petit',
            'email' => 'jean.petit@lrcgroup.com',
            'password' => 'Collaborateur123!',
            'department' => 'RH',
            'poste' => 'Assistant RH',
            'date_embauche' => '2022-01-15',
            'phone' => '+33123456792',
            'manager_id' => $rhManager->profil->id,
        ], 'collaborateur');

        UserFactory::create([
            'matricule' => 'EMP002',
            'first_name' => 'Alice',
            'last_name' => 'Durand',
            'email' => 'alice.durand@lrcgroup.com',
            'password' => 'Collaborateur123!',
            'department' => 'IT',
            'poste' => 'Développeuse',
            'date_embauche' => '2021-06-01',
            'phone' => '+33123456793',
            'manager_id' => $itManager->profil->id,
        ], 'collaborateur');

        $this->command->info('✅ Utilisateurs créés avec succès !');
        $this->command->info('👑 Administrateur: admin@lrcgroup.com / Admin123!');
        $this->command->info('👔 Managers: 2');
        $this->command->info('👥 Collaborateurs: 2');
    }
}