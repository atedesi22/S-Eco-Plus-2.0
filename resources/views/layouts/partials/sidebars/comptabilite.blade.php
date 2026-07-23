<aside
    x-data="{ sidebarOpen: false }"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <!-- Logo & Titre Espace -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <div>
                    <span class="block text-xs font-bold tracking-wider text-emerald-400">S ECO Plus</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Espace Comptabilité</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <!-- Dashboard / Accueil Comptable -->
            <a href="{{ route('comptabilite.dashboard') }}"
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('comptabilite.dashboard') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Console Comptable ({{ Auth::user()->agency->name ?? 'Mon Agence' }})</span>
            </a>

            <!-- GESTION FINANCIÈRE & COMPTABILITÉ -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Finance & Trésorerie</div>

            <a href="{{ route('comptabilite.caisses.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.caisses.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cash-coin text-slate-500"></i>
                <span>Gestion des Caisses</span>
            </a>

            <a href="{{ route('comptabilite.ecritures.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.ecritures.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-calculator text-slate-500"></i>
                <span>Grand Livre & Écritures</span>
            </a>

            <a href="{{ route('comptabilite.coffre.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.coffre.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-safe2 text-slate-500"></i>
                <span>Coffre-Fort Agence</span>
            </a>

            <!-- CONTRÔLE DES AGENCES & TERRAIN -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Contrôle des Flux</div>

            <a href="{{ route('comptabilite.flux.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.flux.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-bank"></i>
                <span>Flux Agence & Zones</span>
            </a>

            <a href="{{ route('comptabilite.clients.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.clients.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people text-slate-500"></i>
                <span>Comptes Clients Agence</span>
            </a>

            <a href="{{ route('comptabilite.boutique.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.boutique.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart4 text-slate-500"></i>
                <span>Stocks & Ventes Agence</span>
            </a>

            <!-- ÉCHÉANCIERS & CRÉDITS -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Portefeuille Crédits</div>

            <a href="{{ route('comptabilite.echeanciers.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.echeanciers.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-calendar-range text-slate-500"></i>
                <span>Suivi des Échéanciers</span>
            </a>

            <a href="{{ route('comptabilite.rapports.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('comptabilite.rapports.*') ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-graph-up-arrow text-slate-500"></i>
                <span>États Financiers / Rapports</span>
            </a>
        </nav>
    </div>

    <!-- User connecté -->
    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div>
            <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-emerald-400 px-1.5 py-0.5 bg-emerald-500/10 rounded">
                Comptabilité
            </span>
        </div>
    </div>
</aside>
