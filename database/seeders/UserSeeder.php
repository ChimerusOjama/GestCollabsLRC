<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Administrateur;
use App\Models\Manager;
use App\Models\Collaborateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        User::query()->delete();
        DB::table('administrateurs')->truncate();
        DB::table('managers')->truncate();
        DB::table('collaborateurs')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1. Créer un administrateur
        $admin = Administrateur::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone' => '+33123456789',
            'department' => 'Administration',
        ]);

        $adminUser = User::create([
            'email' => 'admin@lrcgroup.com',
            'password' => bcrypt('Admin123!'),
            'email_verified_at' => now(),
            'userable_type' => Administrateur::class,
            'userable_id' => $admin->id,
        ]);

        // 2. Créer un manager RH
        $rhManager = Manager::create([
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'phone' => '+33123456790',
            'department' => 'RH',
            'level' => 'senior',
        ]);

        $rhManagerUser = User::create([
            'email' => 'marie.dupont@lrcgroup.com',
            'password' => bcrypt('Manager123!'),
            'email_verified_at' => now(),
            'userable_type' => Manager::class,
            'userable_id' => $rhManager->id,
        ]);

        // 3. Créer un manager IT
        $itManager = Manager::create([
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'phone' => '+33123456791',
            'department' => 'IT',
            'level' => 'director',
        ]);

        $itManagerUser = User::create([
            'email' => 'pierre.martin@lrcgroup.com',
            'password' => bcrypt('Manager123!'),
            'email_verified_at' => now(),
            'userable_type' => Manager::class,
            'userable_id' => $itManager->id,
        ]);

        // 4. Créer des collaborateurs
        $collab1 = Collaborateur::create([
            'matricule' => 'EMP001',
            'first_name' => 'Jean',
            'last_name' => 'Petit',
            'email' => 'jean.petit@lrcgroup.com',
            'phone' => '+33123456792',
            'address' => '123 Rue de Paris, 75001 Paris',
            'date_of_birth' => '1990-05-15',
            'department' => 'RH',
            'poste' => 'Assistant RH',
            'date_embauche' => '2022-01-15',
            'statut' => 'actif',
            'salaire' => 32000.00,
            'competences' => json_encode(['Gestion administrative', 'Recrutement', 'Paie']),
            'notes' => 'Excellent employé',
            'manager_id' => $rhManager->id,
        ]);

        $collab1User = User::create([
            'email' => 'jean.petit@lrcgroup.com',
            'password' => bcrypt('Collaborateur123!'),
            'email_verified_at' => now(),
            'userable_type' => Collaborateur::class,
            'userable_id' => $collab1->id,
        ]);

        $collab2 = Collaborateur::create([
            'matricule' => 'EMP002',
            'first_name' => 'Alice',
            'last_name' => 'Durand',
            'email' => 'alice.durand@lrcgroup.com',
            'phone' => '+33123456793',
            'address' => '456 Avenue IT, 69000 Lyon',
            'date_of_birth' => '1988-08-20',
            'department' => 'IT',
            'poste' => 'Développeuse Full Stack',
            'date_embauche' => '2021-06-01',
            'statut' => 'actif',
            'salaire' => 45000.00,
            'competences' => json_encode(['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'MySQL']),
            'notes' => 'Développeuse principale',
            'manager_id' => $itManager->id,
        ]);

        $collab2User = User::create([
            'email' => 'alice.durand@lrcgroup.com',
            'password' => bcrypt('Collaborateur123!'),
            'email_verified_at' => now(),
            'userable_type' => Collaborateur::class,
            'userable_id' => $collab2->id,
        ]);

        $this->command->info('✅ 5 utilisateurs créés avec succès !');
        $this->command->info('👑 Administrateur: admin@lrcgroup.com / Admin123!');
        $this->command->info('👔 Managers: 2');
        $this->command->info('👥 Collaborateurs: 2');
    }
}