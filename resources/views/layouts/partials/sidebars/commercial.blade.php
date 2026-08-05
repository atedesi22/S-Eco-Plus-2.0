<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <!-- Logo & Titre de l'Espace Commercial -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <div>
                    <span class="block text-xs font-bold tracking-wider text-emerald-400">S ECO Plus</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Espace Commercial</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <!-- 1. VUE GÉNÉRALE -->
            <a href="{{ route('commercial.dashboard') }}"
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('commercial.dashboard') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Tableau de bord</span>
            </a>

            <!-- 2. PROSPECTION & CLIENTS -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Prospection & Clients</div>

            <a href="{{ route('commercial.clients.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.clients.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people-fill text-slate-500"></i>
                <span>Mon Portefeuille Clients</span>
            </a>

            <a href="{{ route('commercial.prospects.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.prospects.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-person-plus-fill text-slate-500"></i>
                <span>Prospection Terrain</span>
            </a>

            <a href="{{ route('commercial.clients.create') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.clients.create') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-person-vcard-fill text-slate-500"></i>
                <span>Ouverture de Compte</span>
            </a>

            <!-- 3. TONTINES & OPÉRATIONS DE COLLECTE -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Activité Tontine</div>

            <a href="{{ route('commercial.tontines.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.tontines.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-pie-chart-fill text-slate-500"></i>
                <span>Souscriptions Tontine</span>
            </a>

            {{-- <a href="{{ route('commercial.collectes.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.collectes.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-wallet2 text-slate-500"></i>
                <span>Saisie des Collectes</span>
            </a> --}}

            <a href="{{ route('commercial.versements.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.versements.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cash-coin text-slate-500"></i>
                <span>Versement en Caisse</span>
            </a>

            <!-- 4. VENTES & COMMANDES ARTICLES -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Ventes & Boutique</div>

            <a href="{{ route('commercial.articles.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.articles.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-shop text-slate-500"></i>
                <span>Catalogue Produits</span>
            </a>

            <a href="{{ route('commercial.commandes.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.commandes.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart-check-fill text-slate-500"></i>
                <span>Prise de Commandes</span>
            </a>

            <!-- 5. SUIVI DE PERFORMANCE & STATS -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Mes Performances</div>

            <a href="{{ route('commercial.objectifs.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.objectifs.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-trophy-fill text-slate-500"></i>
                <span>Mes Objectifs & Primes</span>
            </a>

            <a href="{{ route('commercial.rapports.index') }}"
            class="flex items-center space-x-3 px-4 py-2 text-sm transition rounded-xl {{ request()->routeIs('commercial.rapports.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-file-earmark-bar-graph text-slate-500"></i>
                <span>Rapport Journalier</span>
            </a>
        </nav>
    </div>

    <!-- Profil du Commercial connecté -->
    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div class="truncate">
            <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-emerald-400 px-1.5 py-0.5 bg-emerald-500/10 rounded">
                {{ Auth::user()->getRoleNames()->first() ?? 'Commercial Terrain' }}
            </span>
        </div>
        <i class="text-lg bi bi-person-badge text-slate-400"></i>
    </div>
</aside>
