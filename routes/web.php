<?php

// use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\ObjectiveController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ComptableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaireController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Toutes les routes sécurisées après connexion
Route::middleware(['auth'])->group(function () {

    // Le point d'entrée unique après le Login
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Espace dédié exclusivement aux clients
    Route::middleware(['role:Client'])->group(function () {
        Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
        // Opérations financières de base
        Route::post('/client/transaction', [ClientController::class, 'storeTransaction'])->name('client.transaction.store');

        // Souscription à une nouvelle tontine
        Route::post('/client/sub-account', [ClientController::class, 'storeSubAccount'])->name('client.subaccount.store');

    });

    // Espaces dédiés au personnel interne
    Route::middleware(['role:Comptable'])->group(function () {
        Route::get('/comptable/dashboard', [ComptableController::class, 'index'])->name('comptable.dashboard');
    });

    Route::middleware(['role:Secretaire'])->group(function () {
        Route::get('/secretaire/dashboard', [SecretaireController::class, 'index'])->name('secretaire.dashboard');
    });

    // Route::middleware(['role:SuperAdmin|PDG|DG|DAF'])->group(function () {
    //     Route::get('/structures', [StructureController::class, 'index'])->name('structures.index');
    // });
    // Route::middleware(['auth', 'role:SuperAdmin'])->prefix('admin')->name('admin.')->group(function () {

    // // Dashboard principal (admin.dashboard)
    // Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // // Catalogue des tontines (admin.tontines.index et admin.tontines.store)
    // Route::get('/tontine-plans', [SuperAdminController::class, 'tontinePlansIndex'])->name('tontines.index');
    // Route::post('/tontine-plans', [SuperAdminController::class, 'storeTontinePlan'])->name('tontines.store');

    // // Création du personnel / staff (admin.staff.store) <-- C'EST CETTE LINE QUI MANQUE OU EST MAL NOMMÉE
    // Route::post('/staff/create', [SuperAdminController::class, 'createStaffUser'])->name('staff.store');

    // // Traitement de création des structures physiques
    // Route::post('/structures', [StructureController::class, 'store'])->name('structures.store');
    // //  blend      // Affectation des directeurs aux structures
    // Route::post('/structures/assign-director', [StructureController::class, 'assignDirector'])->name('structures.assign-director');
// });

    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

        // Accessible uniquement au SuperAdmin pour la config de base
        Route::middleware(['role:SuperAdmin'])->group(function () {
            Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            // Fiche Profil Détaillée de l'utilisateur (Collaborateur ou Client)
            Route::get('/users/{id}/profile', [SuperAdminController::class, 'showProfile'])->name('users.profile');


            // 2. Gestion complète du Staff (Création, Affichage, État)
            Route::get('/staff', [SuperAdminController::class, 'staffIndex'])->name('staff.index');
            Route::post('/staff/create', [SuperAdminController::class, 'createStaffUser'])->name('staff.store');
            Route::patch('/staff/{id}/toggle', [SuperAdminController::class, 'toggleStaffStatus'])->name('staff.toggle');

            // 3. Gestion du Catalogue des Tontines
            Route::get('/tontine-plans', [SuperAdminController::class, 'tontinePlansIndex'])->name('tontines.index');
            Route::post('/tontine-plans', [SuperAdminController::class, 'storeTontinePlan'])->name('tontines.store');

            // 4. Structures Physiques (Directions & Agences)
            Route::post('/structures', [StructureController::class, 'store'])->name('structures.store');
            Route::post('/structures/assign-director', [StructureController::class, 'assignDirector'])->name('structures.assign-director');

            // 5. Gestion Globale des Clients
            Route::get('/clients', [SuperAdminController::class, 'clientsIndex'])->name('clients.index');
            Route::patch('/clients/{id}/status', [SuperAdminController::class, 'toggleClientStatus'])->name('clients.toggle');

            // 6. Gestion Globale de la Boutique / Produits
            Route::get('/shop', [SuperAdminController::class, 'shopIndex'])->name('shop.index');
            Route::post('/shop/products', [SuperAdminController::class, 'storeProduct'])->name('shop.products.store');

            // 7. Superviseur Financier (Toutes les transactions, gains & pertes)
            Route::get('/finances', [SuperAdminController::class, 'financesIndex'])->name('finances.index');
            Route::post('/finances/force-transaction', [SuperAdminController::class, 'forceTransaction'])->name('finances.force');

            // 8. Rapports de Performance du Personnel
            Route::get('/reports', [SuperAdminController::class, 'reportsIndex'])->name('reports.index');

        });

        // Accessible au SuperAdmin, PDG, DG et DAF (Synchro avec le niveau hiérarchique du Sidebar)
        Route::middleware(['role:SuperAdmin|PDG|DG|DAF'])->group(function () {
            Route::get('/structures', [StructureController::class, 'index'])->name('structures.index');
            // Liste et affichage des objectifs
            Route::get('/objectives', [ObjectiveController::class, 'index'])->name('objectives.index');

            // Création d'un nouvel objectif
            Route::post('/objectives', [ObjectiveController::class, 'store'])->name('objectives.store');

            // Application d'une sanction sur un objectif spécifique
            Route::post('/objectives/{id}/sanction', [ObjectiveController::class, 'storeSanction'])->name('objectives.sanction');
        });
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
