<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Réinitialiser le cache des rôles et permissions (Évite les bugs au seed)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Définition de quelques permissions clés
        $permissions = [
            'bypass approvals',      // Autorisation totale (SuperAdmin)
            'validate operations',   // Double validation des actions critiques
            'view global balances',  // Voir les soldes de tout le pays/réseau
            'view agency balances',  // Voir les soldes de son agence affiliée
            'manage inventory',      // Saisie des stocks et présences (Secrétaire)
            'collect money',         // Encaisser l'argent des tontines (Collectrice)
            'create accounts',       // Ouvrir des comptes clients (Commercial)
            'configure goals',       // Fixer des objectifs au personnel
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 3. Création de tous les Rôles de la hiérarchie S Eco Plus
        $superAdminRole = Role::create(['name' => 'SuperAdmin']);
        $pdgRole        = Role::create(['name' => 'PDG']);
        $dgRole         = Role::create(['name' => 'DG']);
        $dafRole        = Role::create(['name' => 'DAF']);
        $domRole        = Role::create(['name' => 'DOM']);
        $dirRole        = Role::create(['name' => 'Directeur Regional']);
        $daRole         = Role::create(['name' => 'Directeur Agence']);
        $comptableRole  = Role::create(['name' => 'Comptable']);
        $caissierRole    = Role::firstOrCreate(['name' => 'Caissier']);   // Add Caissier
        $caissiereRole   = Role::firstOrCreate(['name' => 'Caissière']);  // Add Caissière par précaution
        $secretaireRole = Role::create(['name' => 'Secretaire']);
        $chefComRole    = Role::create(['name' => 'Chef Commercial']);
        $collectriceRole= Role::create(['name' => 'Collectrice']);
        $commercialRole = Role::create(['name' => 'Commercial']);
        $clientRole     = Role::create(['name' => 'Client']);

        // 4. Attribution basique des permissions aux rôles
        $superAdminRole->givePermissionTo(Permission::all());
        $pdgRole->givePermissionTo(['view global balances', 'validate operations']);
        $dgRole->givePermissionTo(['view global balances', 'validate operations']);
        $dafRole->givePermissionTo(['view global balances', 'configure goals']);
        $domRole->givePermissionTo(['manage inventory', 'configure goals']);

        $daRole->givePermissionTo(['view agency balances', 'validate operations']);
        $comptableRole->givePermissionTo(['view agency balances']);
        $caissierRole->givePermissionTo(['collect money', 'view agency balances']);
        $secretaireRole->givePermissionTo(['manage inventory']);

        $collectriceRole->givePermissionTo(['collect money']);
        $commercialRole->givePermissionTo(['create accounts']);

        // 5. Création de VOTRE compte SuperAdmin par défaut pour le développement
        $superAdminUser = User::updateOrCreate(
            ['email' => 'di@secoplus.com'],
            [
                'name' => 'Directeur Informatique',
                'phone' => '677000000', // Ajout d'un numéro de test (Format Cameroun)
                'password' => Hash::make('password!'),
                'email_verified_at' => now(),
            ]);

        // Attribuer le rôle de SuperAdmin à cet utilisateur
        $superAdminUser->assignRole($superAdminRole);
    }
}
