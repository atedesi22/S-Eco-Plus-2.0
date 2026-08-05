<!-- SIDEBAR COLLECTRICE -->
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <!-- BRAND / LOGO -->
    <div class="flex items-center gap-3 px-2 mb-8">
        <div class="flex items-center justify-center w-10 h-10 text-xl font-black text-white shadow-lg rounded-xl bg-gradient-to-tr from-emerald-600 to-amber-500 shadow-emerald-900/20">
            S
        </div>
        <div>
            <h1 class="text-base font-black tracking-wider text-white">S ECO PLUS <span class="text-xs text-amber-400">2.0</span></h1>
            <p class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold">Espace Collecte</p>
        </div>
    </div>

    <!-- USER PROFILES BRIEFER -->
    <div class="p-3 mb-6 border rounded-xl bg-slate-900/60 border-slate-800/80">
        <div class="flex items-center gap-3">
            <div class="relative">
                <img class="object-cover w-10 h-10 rounded-full ring-2 ring-emerald-500/50"
                     src="{{ auth()->user()->profile_photo ? asset('storage/'.auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=10b981&color=fff' }}"
                     alt="Photo Collectrice">
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-950 rounded-full"></span>
            </div>
            <div class="overflow-hidden">
                <h2 class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</h2>
                <span class="inline-block px-2 py-0.5 text-[9px] font-semibold text-emerald-400 bg-emerald-950/80 rounded-full border border-emerald-800/50">
                    Collectrice
                </span>
            </div>
        </div>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="flex-1 space-y-1">

        <!-- TABLEAU DE BORD -->
        <a href="{{ route('collector.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('collector.dashboard') ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'hover:bg-slate-900 hover:text-white' }}">
            <i class="text-base bi bi-grid-1x2-fill"></i>
            <span>Tableau de bord</span>
        </a>

        <!-- DIVISION : ACTION TERRAIN -->
        <div class="pt-4 pb-1 px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase">
            Saisie & Terrain
        </div>

        <!-- NOUVELLE COLLECTE (ACTION RAPIDE) -->
        <a href="{{ route('collector.collects.create') }}"
           class="flex items-center justify-between px-3 py-2.5 text-xs font-bold rounded-xl bg-gradient-to-r from-amber-500/10 to-emerald-500/10 text-amber-400 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-200">
            <div class="flex items-center gap-3">
                <i class="text-base bi bi-plus-circle-fill text-amber-400"></i>
                <span>Encaisser Tontine</span>
            </div>
            <span class="flex w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
        </a>

        <!-- MON PORTEFEUILLE CLIENTS -->
        <a href="{{ route('collector.clients.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('collector.clients.*') ? 'bg-slate-900 text-emerald-400 font-semibold border-l-2 border-emerald-500' : 'hover:bg-slate-900 hover:text-white' }}">
            <i class="text-base bi bi-people-fill"></i>
            <span>Mes Clients & Tontines</span>
        </a>

        <!-- HISTORIQUE DES COLLECTES -->
        <a href="{{ route('collector.collects.history') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('collector.collects.history') ? 'bg-slate-900 text-emerald-400 font-semibold border-l-2 border-emerald-500' : 'hover:bg-slate-900 hover:text-white' }}">
            <i class="text-base bi bi-receipt-cutoff"></i>
            <span>Historique des Passages</span>
        </a>

        <!-- DIVISION : GESTION DE CAISSE -->
        <div class="pt-4 pb-1 px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase">
            Caisse & Versement
        </div>

        <!-- VERSEMENTS EN CAISSE AGENCE -->
        <a href="{{ route('collector.cash-deposits.index') }}"
           class="flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('collector.cash-deposits.*') ? 'bg-slate-900 text-emerald-400 font-semibold border-l-2 border-emerald-500' : 'hover:bg-slate-900 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <i class="text-base bi bi-safe-fill"></i>
                <span>Versement Caisse</span>
            </div>
            <!-- Badge optionnel pour versements en attente -->
            @if(isset($pendingDepositsCount) && $pendingDepositsCount > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-500/20 text-amber-400 rounded-md border border-amber-500/30">
                    {{ $pendingDepositsCount }}
                </span>
            @endif
        </a>

        <!-- DIVISION : PERFORMANCE & COMMUNICATON -->
        <div class="pt-4 pb-1 px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase">
            Suivi & Synthèse
        </div>

        <!-- RAPPORT & SYNTHÈSE JOURNEE -->
        <a href="{{ route('collector.reports.daily') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('collector.reports.*') ? 'bg-slate-900 text-emerald-400 font-semibold border-l-2 border-emerald-500' : 'hover:bg-slate-900 hover:text-white' }}">
            <i class="text-base bi bi-file-earmark-bar-graph-fill"></i>
            <span>Rapport Journalier</span>
        </a>

        <!-- MESSAGERIE INTERNE -->
        <a href="{{ route('messages.index') }}"
           class="flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('messages.*') ? 'bg-slate-900 text-emerald-400 font-semibold border-l-2 border-emerald-500' : 'hover:bg-slate-900 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <i class="text-base bi bi-chat-left-text-fill"></i>
                <span>Messagerie</span>
            </div>
            @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-emerald-500 text-slate-950 rounded-full">
                    {{ $unreadMessagesCount }}
                </span>
            @endif
        </a>

    </nav>

    <!-- FOOTER / DÉCONNEXION -->
    <div class="pt-4 mt-auto border-t border-slate-800/80">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center w-full gap-3 px-3 py-2.5 text-xs font-medium text-rose-400 rounded-xl hover:bg-rose-500/10 hover:text-rose-300 transition-all duration-200">
                <i class="text-base bi bi-box-arrow-left"></i>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>

</aside>
