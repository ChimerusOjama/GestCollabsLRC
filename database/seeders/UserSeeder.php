<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Administrator
        UserService::createUser([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@lrcgroup.com',
            'password' => 'Admin123!',
            'phone' => '+33123456789',
            'address' => 'Siège social LRC Group, Paris',
            'date_of_birth' => '1985-05-15',
        ], 'administrator');

        // 2. Create Managers
        $managers = [
            [
                'first_name' => 'Marie',
                'last_name' => 'Dupont',
                'email' => 'marie.dupont@lrcgroup.com',
                'password' => 'Manager123!',
                'department' => 'RH',
                'phone' => '+33123456790',
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Martin',
                'email' => 'pierre.martin@lrcgroup.com',
                'password' => 'Manager123!',
                'department' => 'IT',
                'phone' => '+33123456791',
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@lrcgroup.com',
                'password' => 'Manager123!',
                'department' => 'Finance',
                'phone' => '+33123456792',
            ],
        ];

        $createdManagers = [];
        foreach ($managers as $managerData) {
            $manager = UserService::createUser($managerData, 'manager');
            $createdManagers[$managerData['department']] = $manager;
        }

        // 3. Create Collaborators
        $collaborators = [
            ['Jean', 'Petit', 'jean.petit@lrcgroup.com', 'RH'],
            ['Alice', 'Durand', 'alice.durand@lrcgroup.com', 'IT'],
            ['Thomas', 'Leroy', 'thomas.leroy@lrcgroup.com', 'IT'],
            ['Julie', 'Moreau', 'julie.moreau@lrcgroup.com', 'Finance'],
            ['Nicolas', 'Simon', 'nicolas.simon@lrcgroup.com', 'RH'],
            ['Laura', 'Michel', 'laura.michel@lrcgroup.com', 'IT'],
            ['David', 'Lefebvre', 'david.lefebvre@lrcgroup.com', 'Finance'],
            ['Sarah', 'Garcia', 'sarah.garcia@lrcgroup.com', 'RH'],
            ['Kevin', 'Robert', 'kevin.robert@lrcgroup.com', 'IT'],
            ['Chloé', 'Richard', 'chloe.richard@lrcgroup.com', 'Finance'],
        ];

        foreach ($collaborators as $collab) {
            UserService::createUser([
                'first_name' => $collab[0],
                'last_name' => $collab[1],
                'email' => $collab[2],
                'password' => 'Collaborator123!',
                'department' => $collab[3],
                'manager_id' => $createdManagers[$collab[3]]->id ?? null,
                'phone' => $this->generatePhoneNumber(),
                'address' => $this->generateAddress(),
                'date_of_birth' => $this->generateBirthDate(20, 40),
            ], 'collaborator');
        }

        $this->command->info('✅ Utilisateurs créés avec succès !');
        $this->command->info('👑 Administrateur: admin@lrcgroup.com / Admin123!');
        $this->command->info('👔 Managers: ' . count($managers));
        $this->command->info('👥 Collaborateurs: ' . count($collaborators));
    }

    // Helper methods...
    private function generatePhoneNumber(): string
    {
        $prefixes = ['+331', '+332', '+333', '+334', '+335'];
        return $prefixes[array_rand($prefixes)] . rand(10000000, 99999999);
    }

    private function generateAddress(): string
    {
        $streets = ['Rue de Paris', 'Avenue des Champs-Élysées', 'Boulevard Saint-Germain'];
        $cities = ['Paris', 'Lyon', 'Marseille'];
        
        return rand(1, 300) . ' ' . 
               $streets[array_rand($streets)] . ', ' . 
               rand(75000, 75999) . ' ' . 
               $cities[array_rand($cities)];
    }

    private function generateBirthDate(int $minAge, int $maxAge): string
    {
        $year = now()->year - rand($minAge, $maxAge);
        $month = rand(1, 12);
        $day = rand(1, 28);
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}