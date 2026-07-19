<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Agency;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\TontinePlanSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
        RoleAndPermissionSeeder::class,
    ]);

    // $superAdminUser = User::updateOrCreate(
    // ['email' => 'di@secoplus.com'],
    // [
    //     'name' => 'Directeur Informatique',
    //     'phone' => '677000000', // Ajout d'un numéro de test (Format Cameroun)
    //     'password' => Hash::make('password!'),
    //     'email_verified_at' => now(),
    // ]

    // $superAdminUser->assignRole($superAdminRole);
// );
    $this->call(TontinePlanSeeder::class);

// 1. Création des Agences (Hiérarchie)
        $agenceMere = Agency::create([
            'name' => 'S ECO PLUS - Direction Générale',
            'code' => 'DG-HQ-00',
            'location' => 'Douala, Akwa',
            'parent_id' => null
        ]);

        $agenceFille = Agency::create([
            'name' => 'S ECO PLUS - Agence Marché Central',
            'code' => 'AG-MC-01',
            'location' => 'Yaoundé',
            'parent_id' => $agenceMere->id
        ]);

        // 2. Création du Personnel (Mot de passe: password)
        $secretaire = User::create([
            'name' => 'Awa Ndiaye',
            'email' => 'secretaire@secoplus.com',
            'password' => Hash::make('password'),
            'agency_id' => $agenceFille->id,
        ]);
        // ATTRIBUTION DU RÔLE SPATIE
        $secretaire->assignRole('Secretaire');

        $comptable = User::create([
            'name' => 'Jean Tchakounté',
            'email' => 'comptable@secoplus.com',
            'password' => Hash::make('password'),
            'agency_id' => $agenceFille->id,
        ]);
        $comptable->assignRole('Comptable');

        // 3. Création d'un Client de test
        $client = User::create([
            'name' => 'Mamadou Diallo',
            'email' => 'client.test@secoplus.com',
            'phone' => '677000001',
            'password' => Hash::make('password'),
            'agency_id' => $agenceFille->id,
        ]);
        $client->assignRole('Client');

        // 4. Création du Compte Epargne/Tontine Simple pour le client
        Account::create([
            'user_id' => $client->id,
            'account_number' => 'SEP-TONT-2026-889',
            'type' => 'simple',
            'balance' => 500000.00, // 50 000 XAF pour tester les retraits et les paliers
            'reserve_fund' => 1000.00, // Le fond obligatoire non disponible
            'status' => 'active'
        ]);
    }
}
