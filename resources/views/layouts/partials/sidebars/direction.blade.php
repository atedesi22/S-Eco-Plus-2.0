<aside
    x-data="{ sidebarOpen: false }"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
    x-cloak>

    <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
        <!-- Logo & Titre de l'Espace -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <div>
                    <span class="block text-xs font-bold tracking-wider text-emerald-400">S ECO Plus</span>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">Direction Générale</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <!-- 1. VUE GÉNÉRALE -->
            <a href="{{ route('admin.dashboard') }}"
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="text-base bi bi-speedometer2"></i>
                <span>Tableau de bord</span>
            </a>

            <!-- 2. STRATÉGIE & RÉSEAU -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pilotage & Réseau</div>

            <a href="{{ route('admin.structures.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.structures.*') || request()->routeIs('admin.zones.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-bank text-slate-500"></i>
                <span>Agences & Secteurs</span>
            </a>

            <a href="{{ route('admin.tontines.index') }}" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.tontines.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-layers-half text-slate-500"></i>
                <span>Catalogue Tontines</span>
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


            <!-- 3. RESSOURCES HUMAINES -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Ressources Humaines</div>

            <a href="{{ route('admin.staff.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.staff.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people-fill text-slate-500"></i>
                <span>Registre du Personnel</span>
            </a>

            <!-- 4. APERÇU FINANCIER & OPÉRATIONNEL -->
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Supervision Opérationnelle</div>

            <a href="{{ route('admin.finances.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.finances.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cash-stack text-slate-500"></i>
                <span>Console Financière</span>
            </a>

            <a href="{{ route('admin.clients.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.clients.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people text-slate-500"></i>
                <span>Portefeuille Clients</span>
            </a>

            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-cash-coin text-slate-500"></i>
                <span>Gestion des Caisses</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-calculator text-slate-500"></i>
                <span>Grand Livre / Écritures</span>
            </a>

            <a href="{{ route('admin.shop.index') }}"
            class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl {{ request()->routeIs('admin.shop.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-cart4 text-slate-500"></i>
                <span>Boutique & Stocks</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Crédits & Micro-Prêts</div>
            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-file-earmark-medical text-slate-500"></i>
                <span>Demandes de Crédit</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                <i class="bi bi-calendar-range text-slate-500"></i>
                <span>Suivi des Échéanciers</span>
            </a>

            <!-- 5. PARAMÈTRES AVANCÉS (Réservé SuperAdmin / Audit) -->
            @role('SuperAdmin')
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Configuration Système</div>



                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-phone text-slate-500"></i>
                    <span>Suivi des Terminaux (POS)</span>
                </a>

                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-shield-lock text-slate-500"></i>
                    <span>Journaux d'Audit</span>
                </a>

                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-gear text-slate-500"></i>
                    <span>Droits & Rôles Spatie</span>
                </a>
            @endrole
        </nav>
    </div>

    <!-- Profil de l'utilisateur connecté -->
    <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/50">
        <div>
            <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
            <span class="text-[9px] uppercase font-bold text-emerald-400 px-1.5 py-0.5 bg-emerald-500/10 rounded">
                {{ Auth::user()->getRoleNames()->first() ?? 'Direction' }}
            </span>
        </div>
    </div>
</aside>


<!-- Sidebar (aside) -->
    {{-- <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 transition-transform duration-300 ease-in-out transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static"
        x-cloak>

        <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-80px)]">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                    <span class="text-sm font-bold tracking-wider text-emerald-400">S ECO Internes</span>
                </div>
                <!-- Fermeture sur Mobile -->
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                    <i class="text-xl bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="space-y-1">
                <a href="#" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm font-medium">
                    <i class="text-base bi bi-speedometer2"></i>
                    <span>Tableau de bord</span>
                </a>

                @hasanyrole('SuperAdmin|PDG|DG|DAF')
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pilotage & Management</div>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-bank text-slate-500"></i>
                    <span>Flux des Agences</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-safe2 text-slate-500"></i>
                    <span>Coffre-Fort Central</span>
                </a>
                @endhasanyrole

                @hasanyrole('SuperAdmin|DAF|Comptable|Caissier|Secretaire')
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Gestion Financière</div>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-cash-coin text-slate-500"></i>
                    <span>Gestion des Caisses</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-calculator text-slate-500"></i>
                    <span>Grand Livre / Écritures</span>
                </a>
                @endhasanyrole

                @hasanyrole('SuperAdmin|Collectrice|Commercial|Chef Commercial|Secretaire|Comptable')
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Opérations & Terrain</div>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-person-plus text-slate-500"></i>
                    <span>Enregistrer un Client</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-journal-check text-slate-500"></i>
                    <span>Collecte de Tontines</span>
                </a>

                @endhasanyrole

                @hasanyrole('SuperAdmin|AnalysteCredit|DG|DAF|Comptable|Secretaire')

                @endhasanyrole

                @hasanyrole('SuperAdmin|Auditeur')
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">Sécurité & Conformité</div>
                <a href="#" class="flex items-center px-4 py-2 space-x-3 text-sm transition rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white">
                    <i class="bi bi-shield-lock text-slate-500"></i>
                    <span>Journaux d'Audit (Logs)</span>
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
    </aside> --}}
