@extends('layouts.app')

@section('content')

<div x-data="{
        openAddTontineModal: false,
        openResetPasswordModal: false,
        openFreezeAccountModal: false,
        searchQuery: '',
        dateFrom: '',
        dateTo: '',
        typeFilter: 'all',

        // Fonction de filtrage côté client pour recherche instantanée
        filterTransaction(date, type, desc, ref) {
            let matchQuery = !this.searchQuery || desc.toLowerCase().includes(this.searchQuery.toLowerCase()) || ref.toLowerCase().includes(this.searchQuery.toLowerCase());
            let matchType = this.typeFilter === 'all' || type === this.typeFilter;
            let matchDateFrom = !this.dateFrom || date >= this.dateFrom;
            let matchDateTo = !this.dateTo || date <= this.dateTo;
            return matchQuery && matchType && matchDateFrom && matchDateTo;
        }
    }" class="min-h-screen p-4 space-y-6 font-sans md:p-8 bg-slate-950 text-slate-100">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border shadow-lg text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl backdrop-blur-md">
            <span class="flex items-center gap-2">
                <i class="text-sm bi bi-check-circle-fill text-emerald-400"></i> {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center justify-between p-4 text-xs font-bold text-red-400 border shadow-lg bg-red-950/40 border-red-800/60 rounded-2xl backdrop-blur-md">
            <span class="flex items-center gap-2">
                <i class="text-sm text-red-400 bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <!-- Navigation Header -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800/80">
        <div>
            <a href="{{ route('comptabilite.clients.index') }}" class="inline-flex items-center gap-2 mb-2 text-xs font-medium transition-colors text-slate-400 hover:text-emerald-400">
                <i class="text-sm bi bi-arrow-left"></i> Retour à la liste des clients
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black tracking-tight text-white">{{ $client->name }}</h1>
                @php
                    $statusColor = match($client->status ?? 'active') {
                        'active' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                        'suspended' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                        'frozen' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                        'blocked' => 'bg-red-500/10 text-red-400 border-red-500/30',
                        default => 'bg-slate-800 text-slate-400 border-slate-700'
                    };
                    $statusLabel = match($client->status ?? 'active') {
                        'active' => 'Compte Actif',
                        'suspended' => 'Suspendu',
                        'frozen' => 'Gelé',
                        'blocked' => 'Bloqué',
                        default => 'Inactif'
                    };
                @endphp
                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase border rounded-full {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400">Code Client: <span class="font-mono font-bold text-slate-200">{{ $client->code ?? 'CLI-'.str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</span></p>
        </div>

        <!-- Boutons d'Action Principaux -->
        <div class="flex items-center gap-3">
            <button @click="openAddTontineModal = true" class="px-4 py-2.5 text-xs font-bold transition-all text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-lg shadow-emerald-500/10 flex items-center gap-2">
                <i class="text-sm bi bi-plus-circle-fill"></i> Nouvelle Tontine
            </button>

            <!-- Dropdown Options d'Administration -->
            <div x-data="{ openOptions: false }" class="relative">
                <button @click="openOptions = !openOptions" @click.away="openOptions = false" class="p-2.5 text-xs font-bold text-slate-300 bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl transition">
                    <i class="text-sm bi bi-three-dots-vertical"></i>
                </button>

                <div x-show="openOptions" x-cloak class="absolute right-0 z-30 w-56 p-1 mt-2 space-y-1 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl backdrop-blur-xl">
                    <button @click="openResetPasswordModal = true; openOptions = false" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition">
                        <i class="bi bi-key text-amber-400"></i> Réinitialiser mot de passe
                    </button>
                    <button @click="openFreezeAccountModal = true; openOptions = false" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition">
                        <i class="text-blue-400 bi bi-snow2"></i> Geler / Suspendre compte
                    </button>
                    <div class="my-1 border-t border-slate-800"></div>
                    <form action="{{ route('comptabilite.clients.toggle-block', $client->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier le statut de ce compte ?')">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-red-400 hover:bg-red-950/40 rounded-xl transition">
                            <i class="bi bi-shield-lock"></i> {{ ($client->status ?? '') === 'blocked' ? 'Débloquer le compte' : 'Bloquer définitivement' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille d'Informations du Client & Attribution -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Carte 1: Profil Informations -->
        <div class="p-5 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/60">
                <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                    <i class="bi bi-person-vcard text-emerald-400"></i> Informations Personnelles
                </span>
                <span class="text-[10px] text-slate-500">Inscrit le {{ $client->created_at ? $client->created_at->format('d/m/Y') : 'N/A' }}</span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Téléphone:</span>
                    <span class="font-mono font-bold text-slate-200">{{ $client->phone ?? 'Non renseigné' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Email:</span>
                    <span class="font-medium text-slate-200">{{ $client->email ?? 'Non renseigné' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">CNI / NUI:</span>
                    <span class="font-mono text-slate-200">{{ $client->cni_number ?? $client->identity_card ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Adresse:</span>
                    <span class="font-medium text-slate-200">{{ $client->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Carte 2: Attribution, Collectrice & Zone -->
        <div class="p-5 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/60">
                <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                    <i class="bi bi-diagram-3 text-cyan-400"></i> Zone & Attributions
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <!-- Zone -->
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/50">
                    <span class="text-slate-400">Secteur / Zone:</span>
                    <span class="font-bold text-cyan-400">{{ $client->zone->name ?? $client->sector ?? 'Non attribuée' }}</span>
                </div>
                <!-- Collectrice attitrée -->
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/50">
                    <div>
                        <span class="block text-[10px] text-slate-500">Collectrice Dédiée</span>
                        <span class="font-bold text-slate-200">{{ $client->collector->name ?? $client->zone->collector->name ?? 'Aucune attribuée' }}</span>
                    </div>
                    <i class="text-lg bi bi-person-badge text-slate-400"></i>
                </div>
                <!-- Agent Créateur -->
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/50">
                    <div>
                        <span class="block text-[10px] text-slate-500">Créateur du Compte</span>
                        <span class="font-bold text-slate-200">{{ $client->creator->name ?? 'Système / Auto-inscription' }}</span>
                    </div>
                    <i class="text-lg bi bi-person-plus text-slate-400"></i>
                </div>
            </div>
        </div>

        <!-- Carte 3: Aperçu des Soldes -->
        <div class="flex flex-col justify-between p-5 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800/60">
                    <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                        <i class="bi bi-wallet2 text-emerald-400"></i> Position Financière
                    </span>
                    <span class="text-[10px] text-emerald-400 font-bold">XAF</span>
                </div>

                <div class="mt-4">
                    <span class="text-[11px] text-slate-400">Solde Global Cumulé</span>
                    <p class="text-3xl font-black text-emerald-400 tracking-tight mt-0.5">
                        {{ number_format($client->accounts ? $client->accounts->sum('balance') : 0, 0, ',', ' ') }} <span class="text-sm font-normal text-slate-400">XAF</span>
                    </p>
                </div>
            </div>

            <!-- Badges Sous-Comptes -->
            <div class="pt-3 border-t border-slate-800/60">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Tontines Actives</span>
                <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                    @php
                        $hasSubAccounts = $client->accounts && $client->accounts->flatMap->subAccounts->isNotEmpty();
                    @endphp

                    @if($hasSubAccounts)
                        @foreach($client->accounts as $acc)
                            @foreach($acc->subAccounts as $sub)
                                <span class="px-2.5 py-1 text-[10px] font-bold bg-slate-800/90 text-emerald-300 border border-slate-700/80 rounded-lg flex items-center gap-1.5">
                                    <i class="bi bi-pie-chart-fill text-[9px] text-emerald-400"></i>
                                    {{ $sub->name }}: <span class="font-mono text-white">{{ number_format($sub->balance, 0, ',', ' ') }}</span>
                                </span>
                            @endforeach
                        @endforeach
                    @else
                        <span class="text-[11px] text-slate-500 italic">Aucune tontine active actuellement.</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Section Historique des Transactions & Filtres -->
    <div class="p-6 space-y-6 border bg-slate-900/60 border-slate-800/80 rounded-2xl">

        <!-- Barre d'outils / Filtres -->
        <div class="flex flex-col justify-between gap-4 pb-4 border-b lg:flex-row lg:items-center border-slate-800/80">
            <div>
                <h2 class="flex items-center gap-2 text-base font-bold text-white">
                    <i class="bi bi-clock-history text-emerald-400"></i> Historique des Transactions
                </h2>
                <p class="text-xs text-slate-400">Consultez et filtrez les mouvements financiers de ce client.</p>
            </div>

            <!-- Formulaire de recherche et filtres AlpineJS -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Recherche texte -->
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-2.5 text-xs text-slate-500"></i>
                    <input type="text" x-model="searchQuery" placeholder="Référence, libellé..." class="w-40 py-2 pl-8 pr-3 text-xs text-white border outline-none sm:w-48 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500/50">
                </div>

                <!-- Filtre par type -->
                <select x-model="typeFilter" class="px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500/50">
                    <option value="all">Tous les types</option>
                    <option value="deposit">Dépôts (+)</option>
                    <option value="withdrawal">Retraits (-)</option>
                    <option value="tontine">Cotisations Tontine</option>
                </select>

                <!-- Filtre Date Début & Fin -->
                <div class="flex items-center gap-1 px-2 py-1 border bg-slate-950 border-slate-800 rounded-xl">
                    <input type="date" x-model="dateFrom" class="p-1 text-xs bg-transparent outline-none text-slate-300">
                    <span class="text-xs text-slate-600">à</span>
                    <input type="date" x-model="dateTo" class="p-1 text-xs bg-transparent outline-none text-slate-300">
                </div>

                <!-- Reset Filtres -->
                <button @click="searchQuery = ''; dateFrom = ''; dateTo = ''; typeFilter = 'all'" class="p-2 text-xs text-slate-400 hover:text-white bg-slate-800 rounded-xl" title="Réinitialiser">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>

        <!-- Tableau des Transactions -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <th class="px-3 pb-3">Date</th>
                        <th class="px-3 pb-3">Référence</th>
                        <th class="px-3 pb-3">Type / Description</th>
                        <th class="px-3 pb-3">Compte / Tontine</th>
                        <th class="px-3 pb-3 text-right">Montant</th>
                        <th class="px-3 pb-3 text-center">Statut</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800/50">
                    @forelse($transactions ?? [] as $trx)
                        <tr x-show="filterTransaction('{{ $trx->created_at->format('Y-m-d') }}', '{{ $trx->type }}', '{{ $trx->description }}', '{{ $trx->reference }}')" class="transition-colors hover:bg-slate-800/30">
                            <td class="px-3 py-3 font-mono text-slate-400">
                                {{ $trx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-3 font-mono font-bold text-emerald-400">
                                {{ $trx->reference ?? 'TRX-'.$trx->id }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-medium text-white">{{ $trx->description ?? 'Opération financière' }}</span>
                                <span class="text-[10px] text-slate-500">Par: {{ $trx->operator->name ?? 'Système' }}</span>
                            </td>
                            <td class="px-3 py-3 text-slate-300">
                                {{ $trx->subAccount->name ?? $trx->account->type ?? 'Compte Principal' }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-sm {{ $trx->type === 'withdrawal' ? 'text-red-400' : 'text-emerald-400' }}">
                                {{ $trx->type === 'withdrawal' ? '-' : '+' }}{{ number_format($trx->amount, 0, ',', ' ') }} XAF
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ ucfirst($trx->status ?? 'Succès') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 italic text-center text-slate-500">
                                Aucune transaction enregistrée pour ce client.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Laravel si applicable -->
        @if(isset($transactions) && method_exists($transactions, 'links'))
            <div class="pt-4 border-t border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- ================= MODALES ================= -->

    <!-- Modale 1: Souscrire une Tontine -->
    <div x-show="openAddTontineModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="openAddTontineModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-pie-chart text-emerald-400"></i> Souscrire une Nouvelle Tontine
                </h3>
                <button @click="openAddTontineModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.add-tontine', $client->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Type de Tontine *</label>
                    <select name="tontine_plan_id" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="" disabled selected>-- Sélectionner une tontine --</option>

                        @forelse($tontineTypes ?? [] as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @empty
                            <option value="" disabled>Aucun type de tontine disponible</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Dépôt Initial (Min 1 000 XAF) *</label>
                    <input type="number" min="1000" name="initial_deposit" value="1000" required class="w-full px-3 py-2 text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddTontineModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-emerald-400 rounded-xl hover:bg-emerald-300">Activer Tontine</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale 2: Réinitialiser le mot de passe -->
    <div x-show="openResetPasswordModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="openResetPasswordModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-key text-amber-400"></i> Réinitialiser le mot de passe
                </h3>
                <button @click="openResetPasswordModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.reset-password', $client->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Nouveau mot de passe *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 caractères" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirmation" required placeholder="Répéter le mot de passe" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openResetPasswordModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-amber-400 rounded-xl hover:bg-amber-300">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale 3: Geler / Suspendre temporairement le compte -->
    <div x-show="openFreezeAccountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="openFreezeAccountModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="text-blue-400 bi bi-snow2"></i> Gel / Suspension du compte
                </h3>
                <button @click="openFreezeAccountModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.freeze', $client->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Type de restriction *</label>
                    <select name="status" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-blue-500">
                        <option value="frozen">Geler temporairement (Aucun retrait autorisé)</option>
                        <option value="suspended">Suspendre (Blocage total)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Date Début *</label>
                        <input type="date" name="freeze_start_at" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Date Fin *</label>
                        <input type="date" name="freeze_end_at" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Motif de la restriction</label>
                    <textarea name="reason" rows="2" placeholder="Ex: Inactivité prolongée, suspicion de fraude..." class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openFreezeAccountModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-500">Appliquer la restriction</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
