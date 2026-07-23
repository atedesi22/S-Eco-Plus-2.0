<aside
    x-data="{ sidebarOpen: false }"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold bg-blue-500 rounded-lg text-slate-950">S</div>
                <div>
                    <span class="block text-xs font-bold tracking-wider text-blue-400">S ECO Plus</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Espace Secrétariat</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <a href="{{ route('secretaire.dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('secretaire.dashboard') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Accueil Secrétariat</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Accueil & Enregistrements</div>

            <a href="{{ route('admin.clients.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.clients.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-person-plus text-slate-500"></i>
                <span>Ouverture de Comptes</span>
            </a>

            <a href="{{ route('admin.shop.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.shop.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart4 text-slate-500"></i>
                <span>Ventes Boutique / Matériel</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Dossiers & Demandes</div>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-file-earmark-medical text-slate-500"></i>
                <span>Réception Demandes Crédit</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-journal-check text-slate-500"></i>
                <span>Versements Tontine</span>
            </a>
        </nav>
    </div>

    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div>
            <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-blue-400 px-1.5 py-0.5 bg-blue-500/10 rounded">
                Secrétariat
            </span>
        </div>
    </div>
</aside>
