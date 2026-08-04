@extends('layouts.app')

@section('content')
<div x-data="{
    openQuickCollectModal: false,
    accountsPeriodFilter: 'month',
    historyFilter: 'all'
}" class="min-h-screen p-4 space-y-6 font-sans md:p-8 bg-slate-950 text-slate-100">

    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border shadow-lg text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl backdrop-blur-md">
            <span class="flex items-center gap-2">
                <i class="text-sm bi bi-check-circle-fill text-emerald-400"></i> {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800/80">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white">Espace Commercial</h1>
            <p class="text-xs text-slate-400">Ravi de vous revoir, <span class="font-bold text-emerald-400">{{ Auth::user()->name }}</span>. Voici vos performances de suivi.</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- <button @click="openQuickCollectModal = true" class="px-4 py-2.5 text-xs font-bold transition-all text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-lg shadow-emerald-500/10 flex items-center gap-2">
                <i class="text-sm bi bi-cash-stack"></i> Encaisser une Cotisation
            </button> --}}
            <a href="{{ route('commercial.clients.create') }}" class="px-4 py-2.5 text-xs font-bold text-slate-200 bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="text-sm bi bi-person-plus-fill text-cyan-400"></i> Nouveau Client
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="flex flex-col justify-between p-5 space-y-3 border lg:col-span-2 bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                        <i class="bi bi-trophy-fill text-amber-400"></i>
                        {{ $kpis['objective_title'] ?? 'Objectif de Création de Comptes' }}
                    </span>
                    <span class="font-mono text-xs font-bold text-emerald-400">
                        {{ $kpis['created_accounts'] ?? 0 }} / {{ $kpis['target_accounts'] ?? 50 }} Comptes
                    </span>
                </div>

                @php
                    $target = $kpis['target_accounts'] ?? 50;
                    $created = $kpis['created_accounts'] ?? 0;
                    $percentage = min(100, round(($created / max(1, $target)) * 100));
                @endphp

                <div class="w-full h-3.5 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                    <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-r from-emerald-500 via-cyan-400 to-indigo-500"
                        style="width: {{ $percentage }}%">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 text-xs border-t text-slate-400 border-slate-800/50">
                <span>Progression : <strong class="font-mono text-white">{{ $percentage }}%</strong></span>

                <span>
                    Reste à réaliser :
                    <strong class="font-mono text-amber-400">
                        {{ max(0, $target - $created) }} {{ Str::plural('compte', max(0, $target - $created)) }}
                    </strong>
                </span>

                @if(($kpis['base_bonus'] ?? 0) > 0)
                    <span class="hidden sm:inline">
                        Prime accumulée :
                        <strong class="font-mono text-emerald-400">
                            {{ number_format($kpis['estimated_prime'] ?? 0, 0, ',', ' ') }} XAF
                        </strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-col justify-between p-5 border bg-gradient-to-br from-indigo-950/40 via-slate-900/80 to-slate-900/60 border-indigo-500/30 rounded-2xl">
            <div class="flex items-center justify-between text-indigo-300">
                <span class="text-xs font-bold tracking-wider uppercase">Prime & Commissions</span>
                <i class="text-xl text-indigo-400 bi bi-award-fill"></i>
            </div>
            <div class="my-2">
                <p class="font-mono text-3xl font-black tracking-tight text-indigo-400">
                    {{ number_format($kpis['estimated_commission'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
                </p>
                <p class="text-[10px] text-slate-400 mt-1">Estimation calculée sur les souscriptions et la collecte active du mois.</p>
            </div>
            <div class="text-[11px] text-indigo-300/80 flex items-center justify-between pt-2 border-t border-indigo-800/30">
                <span>Statut : <strong class="text-emerald-400">En cours d'acquisition</strong></span>
                <i class="bi bi-graph-up-arrow text-emerald-400"></i>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="p-4 space-y-3 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">Comptes Créés</span>
                <i class="text-lg bi bi-person-vcard text-cyan-400"></i>
            </div>

            <div class="flex items-baseline justify-between">
                <p x-show="accountsPeriodFilter === 'today'" class="font-mono text-2xl font-black tracking-tight text-cyan-400">
                    {{ $kpis['created_accounts_today'] ?? 0 }}
                </p>
                <p x-show="accountsPeriodFilter === 'month'" class="font-mono text-2xl font-black tracking-tight text-cyan-400">
                    {{ $kpis['created_accounts_month'] ?? 0 }}
                </p>
                <p x-show="accountsPeriodFilter === 'year'" class="font-mono text-2xl font-black tracking-tight text-cyan-400">
                    {{ $kpis['created_accounts_year'] ?? 0 }}
                </p>
                <p x-show="accountsPeriodFilter === 'all'" class="font-mono text-2xl font-black tracking-tight text-cyan-400">
                    {{ $kpis['created_accounts_total'] ?? 0 }}
                </p>

                <select x-model="accountsPeriodFilter" class="px-2 py-1 text-[10px] font-semibold text-slate-300 border bg-slate-950 border-slate-800 rounded-lg outline-none focus:border-cyan-500">
                    <option value="today">Aujourd'hui</option>
                    <option value="month" selected>Ce mois</option>
                    <option value="year">Cette année</option>
                    <option value="all">Tout</option>
                </select>
            </div>
            <span class="text-[10px] text-slate-500 block">Nouveaux dossiers clients enregistrés</span>
        </div>

        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">Collecte du Jour</span>
                <i class="text-lg bi bi-wallet2 text-emerald-400"></i>
            </div>
            <p class="font-mono text-2xl font-black tracking-tight text-emerald-400">
                {{ number_format($kpis['today_collected'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
            </p>
            <span class="text-[10px] text-slate-500 block">En espèces en votre possession</span>
        </div>

        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">À Verser en Caisse</span>
                <i class="text-lg bi bi-safe text-amber-400"></i>
            </div>
            <p class="font-mono text-2xl font-black tracking-tight text-amber-400">
                {{ number_format($kpis['pending_cash'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
            </p>
            <span class="text-[10px] text-slate-500 block">Non encore validé par le caissier</span>
        </div>

        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">Portefeuille Actif</span>
                <i class="text-lg text-indigo-400 bi bi-people-fill"></i>
            </div>
            <p class="font-mono text-2xl font-black tracking-tight text-indigo-400">
                {{ $kpis['total_active_clients'] ?? 0 }}
            </p>
            <span class="text-[10px] text-slate-500 block">Clients suivis dans votre zone</span>
        </div>

    </div>

    <div class="p-6 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
        <div class="flex flex-col gap-3 pb-3 border-b sm:flex-row sm:items-center sm:justify-between border-slate-800/80">
            <div>
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-clock-history text-cyan-400"></i> 10 Dernières Créations de Comptes
                </h2>
                <p class="text-[11px] text-slate-400">Dossiers récents soumis ou ouverts dans le système.</p>
            </div>

            <div class="flex items-center gap-1 p-1 border bg-slate-950 border-slate-800 rounded-xl text-[11px]">
                <button @click="historyFilter = 'all'" :class="historyFilter === 'all' ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1 transition rounded-lg">
                    Tous
                </button>
                <button @click="historyFilter = 'active'" :class="historyFilter === 'active' ? 'bg-emerald-950/80 text-emerald-400 font-bold border border-emerald-800/50' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1 transition rounded-lg">
                    Actifs
                </button>
                <button @click="historyFilter = 'pending'" :class="historyFilter === 'pending' ? 'bg-amber-950/80 text-amber-400 font-bold border border-amber-800/50' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1 transition rounded-lg">
                    En Attente
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <th class="px-3 py-2">Date & Heure</th>
                        <th class="px-3 py-2">Nom du Client</th>
                        <th class="px-3 py-2">Téléphone</th>
                        <th class="px-3 py-2">Type / Formule</th>
                        <th class="px-3 py-2 text-center">Statut du Compte</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800/50">
                    @forelse($latestCreatedAccounts ?? [] as $account)
                        <tr x-show="historyFilter === 'all' || (historyFilter === 'active' && '{{ $account->status }}' === 'active') || (historyFilter === 'pending' && '{{ $account->status }}' === 'pending')"
                            class="transition-colors hover:bg-slate-800/30">
                            <td class="px-3 py-3 font-mono text-slate-400">
                                {{ $account->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-3 font-bold text-white">
                                {{ $account->user->name ?? $account->client_name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-3 font-mono text-slate-300">
                                {{ $account->user->phone ?? $account->phone ?? '-' }}
                            </td>
                            <td class="px-3 py-3 text-slate-300">
                                <span class="px-2 py-0.5 rounded-md bg-slate-950 border border-slate-800 text-[10px] font-semibold text-cyan-300">
                                    {{ $account->product_name ?? 'Tontine' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if(($account->status ?? 'active') === 'active')
                                    <span class="px-2.5 py-1 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Actif</span>
                                @else
                                    <span class="px-2.5 py-1 text-[9px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">En Attente Validation</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <a href="{{ route('commercial.clients.show', $account->id) }}" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition inline-flex items-center">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 italic text-center text-slate-500">
                                Aucun compte créé récemment dans votre profil.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openQuickCollectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="openQuickCollectModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-cash-stack text-emerald-400"></i> Encaissement Cotisation Terrain
                </h3>
                <button @click="openQuickCollectModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('commercial.versements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Sélectionner le Client *</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="" disabled selected>-- Recherche Client --</option>
                        @foreach($myClients ?? [] as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Montant Reçu (XAF) *</label>
                    <input type="number" min="500" step="500" name="amount" required placeholder="Ex: 1000" class="w-full px-3 py-2 text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Note / Observation (Facultatif)</label>
                    <input type="text" name="observation" placeholder="Ex: Cotisation du jour" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openQuickCollectModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-emerald-400 rounded-xl hover:bg-emerald-300">Valider l'encaissement</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
