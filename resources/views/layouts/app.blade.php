<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S ECO PLUS 2.0 - Administration Interne</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2 family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>

<!-- Initialisation globale d'Alpine sur le body pour que le bouton header ET leaside partagent la même variable -->
<body class="flex min-h-screen antialiased bg-slate-950 text-slate-100" x-data="{ sidebarOpen: false }">

    <aside
    x-data="{ sidebarOpen: false }"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <span class="text-sm font-bold tracking-wider text-emerald-400">S ECO Internes</span>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <a href="{{ route('admin.dashboard') }}"
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Tableau de bord</span>
            </a>

            @hasanyrole('SuperAdmin|PDG|DG|DAF')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Ressources Humaines</div>

            <a href="{{ route('admin.staff.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.staff.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people-fill text-slate-500"></i>
                <span>Registre Personnel</span>
            </a>
            @endhasanyrole

            @role('SuperAdmin')
            <a href="{{ route('admin.tontines.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.tontines.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-layers-half text-slate-500"></i>
                <span>Catalogue Tontines</span>
            </a>
            @endrole

            @hasanyrole('SuperAdmin|PDG|DG|DAF')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pilotage & Management</div>

            <a href="{{ route('admin.structures.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.structures.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-bank"></i>
                <span>Flux des Agences</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-safe2 text-slate-500"></i>
                <span>Coffre-Fort Central</span>
            </a>

            <a href="{{ route('admin.objectives.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.objectives.*') || request()->routeIs('admin.sanctions.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-shield-check text-slate-500"></i>
                <span>Objectifs & Sanctions</span>
            </a>

            <a href="{{ route('admin.reports.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-graph-up-arrow text-slate-500"></i>
                <span>Rapports Performance</span>
            </a>
            @endhasanyrole

            @hasanyrole('SuperAdmin|PDG|DG|DAF|Comptable|Caissier|Secretaire')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Gestion Financière</div>

            <a href="{{ route('admin.finances.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.finances.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cash-stack text-slate-500"></i>
                <span>Console Comptable</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-cash-coin text-slate-500"></i>
                <span>Gestion des Caisses</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-calculator text-slate-500"></i>
                <span>Grand Livre / Écritures</span>
            </a>
            @endhasanyrole

            @hasanyrole('SuperAdmin|PDG|DG|Collectrice|Commercial|Chef Commercial|Secretaire|Comptable')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Opérations & Terrain</div>

            <a href="{{ route('admin.clients.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.clients.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people text-slate-500"></i>
                <span>Gestion des Clients</span>
            </a>

            <a href="{{ route('admin.shop.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.shop.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart4 text-slate-500"></i>
                <span>Boutique & Stocks</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-journal-check text-slate-500"></i>
                <span>Collecte de Tontines</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-phone text-slate-500"></i>
                <span>Suivi des Terminaux (POS)</span>
            </a>
            @endhasanyrole

            @hasanyrole('SuperAdmin|PDG|DG|AnalysteCredit|DAF|Comptable|Secretaire')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Crédits & Micro-Prêts</div>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-file-earmark-medical text-slate-500"></i>
                <span>Demandes de Crédit</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-calendar-range text-slate-500"></i>
                <span>Suivi des Échéanciers</span>
            </a>
            @endhasanyrole

            @hasanyrole('SuperAdmin|Auditeur')
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Sécurité & Conformité</div>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-shield-lock text-slate-500"></i>
                <span>Journaux d'Audit (Logs)</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-gear text-slate-500"></i>
                <span>Droits & Rôles Spatie</span>
            </a>
            @endhasanyrole
        </nav>
    </div>

    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div>
            <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-emerald-400 px-1.5 py-0.5 bg-emerald-500/10 rounded">
                {{ Auth::user()->getRoleNames()->first() ?? 'Personnel SI' }}
            </span>
        </div>
    </div>
</aside>

    <!-- Zone principale -->
    <main class="flex flex-col flex-1 min-w-0 overflow-y-auto">

        <header class="flex items-center justify-between h-16 px-6 border-b border-slate-800 bg-slate-900/40 backdrop-blur">
            <div class="flex items-center space-x-4">
                <!-- Ouverture Mobile (Bouton d'activation) -->
                <button @click="sidebarOpen = true" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <i class="text-2xl bi bi-list"></i>
                </button>
                <h3 class="text-lg font-bold text-white">Console Administration Métier</h3>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center space-x-2 text-xs font-semibold transition text-slate-400 hover:text-red-400">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </header>

        <!-- Injection du contenu des dashboards spécifiques -->
        <div class="p-6 space-y-6">
            @yield('content')
        </div>
    </main>

    <!-- Overlay d'assombrissement mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 md:hidden" x-cloak></div>

</body>
</html>
