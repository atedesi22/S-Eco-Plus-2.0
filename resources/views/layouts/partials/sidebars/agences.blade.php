<aside
    x-data="{ sidebarOpen: false }"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <!-- Logo & Titre de l'Espace Directeur -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <div>
                    <span class="block text-xs font-bold tracking-wider text-emerald-400">S ECO Plus</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Direction Agence</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <!-- 1. VUE GÉNÉRALE -->
            <a href="{{ route('directeur.dashboard') }}"
               class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('directeur.dashboard') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Tableau de bord</span>
            </a>

            <!-- 2. SUPERVISION & OPÉRATIONS -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Supervision Agence</div>

            <a href="{{ route('directeur.validations.index') }}"
               class="flex items-center justify-between px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('directeur.validations.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center space-x-3">
                    <i class="bi bi-shield-check text-slate-500"></i>
                    <span>Validations & Accord</span>
                </div>
            </a>

            <a href="{{ route('directeur.caisses.index') }}" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.caisses.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-safe2 text-slate-500"></i>
                <span>Caisses & Coffres</span>
            </a>

            <a href="{{ route('directeur.zones.index', ['agencyId' => Auth::user()->agency_id]) }}"
                class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.zones.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-geo-alt-fill text-slate-500"></i>
                <span>Zones de Collecte</span>
            </a>

            <!-- 3. GESTION DES ÉQUIPES -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Ressources Humaines</div>

            <a href="{{ route('directeur.personnel.index') }}"
                class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.personnel.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people-fill text-slate-500"></i>
                <span>Personnel de l'Agence</span>
            </a>

            <a href="{{ route('directeur.performance.index') }}"
               class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.performance.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-award text-slate-500"></i>
                <span>Objectifs & Performance</span>
            </a>

            <!-- 4. PORTEFEUILLE CLIENTS & BOUTIQUE -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Clients & Commerce</div>

            <a href="{{ route('directeur.clients.index') }}"
                class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.clients.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-person-lines-fill text-slate-500"></i>
                <span>Portefeuille Clients</span>
            </a>

            <a href="{{ route('directeur.articles.index') }}"
                class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.articles.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-shop text-slate-500"></i>
                <span>Articles Boutique</span>
            </a>

            <a href="{{ route('directeur.commandes.index') }}"
                class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('directeur.commandes.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart-check text-slate-500"></i>
                <span>Commandes Clients</span>
            </a>

            <!-- 5. RAPPORTS & AUDIT -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Rapports & Activité</div>

            <a href="#"
               class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-graph-up-arrow text-slate-500"></i>
                <span>Rapports Journaliers</span>
            </a>

            <a href="#"
               class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-clock-history text-slate-500"></i>
                <span>Journal des Flux</span>
            </a>
        </nav>
    </div>

    <!-- Profil du Directeur connecté -->
    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div>
            <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-emerald-400 px-1.5 py-0.5 bg-emerald-500/10 rounded">
                {{ Auth::user()->getRoleNames()->first() ?? 'Directeur Agence' }}
            </span>
        </div>
    </div>
</aside>
