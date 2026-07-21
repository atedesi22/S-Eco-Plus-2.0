<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Structure;
use App\Models\User;
use App\Models\Zone;
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

// Mot de passe universel pour les tests
        $defaultPassword = Hash::make('password123');

        // =========================================================================
        // 2. CRÉATION DU SUPERADMIN (Indépendant de toute structure)
        // =========================================================================
        // $superAdmin = User::create([
        //     'name' => 'Super Administrateur',
        //     'phone' => '677000000',
        //     'email' => 'admin@secoplus.cm',
        //     'password' => $defaultPassword,
        // ]);
        // $superAdmin->assignRole('SuperAdmin');

        // =========================================================================
        // 3. STRUCTURES : DIRECTION GÉNÉRALE & DIRECTIONS RÉGIONALES
        // =========================================================================

        // A. Direction Générale (Siège)
        $dirGenerale = Structure::create([
            'name' => 'Direction Générale S ECO PLUS',
            'type' => 'regional_direction',
            'parent_id' => null,
        ]);

        // B. Direction Régionale (ex: Littoral)
        $dirLittoral = Structure::create([
            'name' => 'Direction Régionale du Littoral',
            'type' => 'regional_direction',
            'parent_id' => $dirGenerale->id,
        ]);

        // C. Agence Physique (ex: Akwa)
        $agenceAkwa = Structure::create([
            'name' => 'Agence de Douala - Akwa',
            'type' => 'agency',
            'parent_id' => $dirLittoral->id,
        ]);

        // =========================================================================
        // 4. PERSONNEL HAUTE DIRECTION & CADRES (Connexion par EMAIL)
        // =========================================================================

        // PDG
        $pdg = User::create([
            'name' => 'Président Directeur Général',
            'email' => 'pdg@secoplus.cm',
            'phone' => '+237690000001',
            'password' => Hash::make('password123'),
            'structure_id' => $dirGenerale->id,
        ]);
        $pdg->assignRole('PDG');

        // DG
        $dg = User::create([
            'name' => 'Directeur Général',
            'email' => 'dg@secoplus.cm',
            'phone' => '+237690000002',
            'password' => Hash::make('password123'),
            'structure_id' => $dirGenerale->id,
        ]);
        $dg->assignRole('DG');

        // On affecte le DG comme directeur de la Direction Générale
        $dirGenerale->update(['director_id' => $dg->id]);

        // DAF
        $daf = User::create([
            'name' => 'Directeur Administratif & Financier',
            'email' => 'daf@secoplus.cm',
            'phone' => '+237690000003',
            'password' => Hash::make('password123'),
            'structure_id' => $dirGenerale->id,
        ]);
        $daf->assignRole('DAF');

        // Directeur Agence Akwa
        $dirAkwa = User::create([
            'name' => 'Directeur Agence Akwa',
            'email' => 'dir.akwa@secoplus.cm',
            'phone' => '+237690000004',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
        ]);
        $dirAkwa->assignRole('Directeur Agence'); // Ou rôle de chef d'agence
        $agenceAkwa->update(['director_id' => $dirAkwa->id]);

        // Comptable Agence
        $comptable = User::create([
            'name' => 'Comptable Akwa',
            'email' => 'comptable.akwa@secoplus.cm',
            'phone' => '+237690000005',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
        ]);
        $comptable->assignRole('Comptable');

        // Secrétaire Agence
        $secretaire = User::create([
            'name' => 'Secrétaire Akwa',
            'email' => 'secretaire.akwa@secoplus.cm',
            'phone' => '+237690000006',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
        ]);
        $secretaire->assignRole('Secretaire');

        // =========================================================================
        // 5. CRÉATION DES ZONES DE COLLECTE (SECTEURS TERRAIN)
        // =========================================================================

        // Chef Commercial / Chef de Zone Akwa Central
        $chefZoneAkwa = User::create([
            'name' => 'Chef Zone Marché Central',
            'email' => 'chef.marche@secoplus.cm',
            'phone' => '+237690000007',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
        ]);
        $chefZoneAkwa->assignRole('Chef Commercial');

        // Zone 1 : Marché Central Akwa
        $zoneMarche = Zone::create([
            'code' => 'ZN-AKW-01',
            'name' => 'Secteur Marché Central',
            'description' => 'Zone commerciale à forte densité - Tontine journalière',
            'structure_id' => $agenceAkwa->id,
            'manager_id' => $chefZoneAkwa->id,
        ]);
        $chefZoneAkwa->update(['zone_id' => $zoneMarche->id]);

        // =========================================================================
        // 6. ÉQUIPE TERRAIN (Connexion par EMAIL)
        // =========================================================================

        // Collectrice sur la Zone Marché Central
        $collectrice = User::create([
            'name' => 'Collectrice Marie',
            'email' => 'marie.collecte@secoplus.cm',
            'phone' => '+237690000008',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
            'zone_id' => $zoneMarche->id,
        ]);
        $collectrice->assignRole('Collectrice');

        // Commercial sur la Zone Marché Central
        $commercial = User::create([
            'name' => 'Commercial Paul',
            'email' => 'paul.commercial@secoplus.cm',
            'phone' => '+237690000009',
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
            'zone_id' => $zoneMarche->id,
        ]);
        $commercial->assignRole('Commercial');

        // =========================================================================
        // 7. CLIENTS TERRAIN (Connexion exclusivement par NUMÉRO DE TÉLÉPHONE)
        // =========================================================================

        $client1 = User::create([
            'name' => 'Mama Jeanne (Commerçante)',
            'email' => null, // Optionnel ou vide pour un client
            'phone' => '670112233', // Utilisé pour le login Client
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
            'zone_id' => $zoneMarche->id,
        ]);
        $client1->assignRole('Client');

        $client2 = User::create([
            'name' => 'Papa Thomas (Boutiquier)',
            'email' => null,
            'phone' => '690445566', // Utilisé pour le login Client
            'password' => Hash::make('password123'),
            'structure_id' => $agenceAkwa->id,
            'zone_id' => $zoneMarche->id,
        ]);
        $client2->assignRole('Client');
    }
}
