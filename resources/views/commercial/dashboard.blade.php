@extends('layouts.app')

@section('content')
<div x-data="{ openQuickCollectModal: false }" class="min-h-screen p-4 space-y-6 font-sans md:p-8 bg-slate-950 text-slate-100">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border shadow-lg text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl backdrop-blur-md">
            <span class="flex items-center gap-2">
                <i class="text-sm bi bi-check-circle-fill text-emerald-400"></i> {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <!-- Header & Actions Rapides -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800/80">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white">Espace Commercial</h1>
            <p class="text-xs text-slate-400">Ravi de vous revoir, <span class="font-bold text-emerald-400">{{ Auth::user()->name }}</span>. Voici vos performances du jour.</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="openQuickCollectModal = true" class="px-4 py-2.5 text-xs font-bold transition-all text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-lg shadow-emerald-500/10 flex items-center gap-2">
                <i class="text-sm bi bi-cash-stack"></i> Encaisser une Cotisation
            </button>
            <a href="{{ route('commercial.clients.create') }}" class="px-4 py-2.5 text-xs font-bold text-slate-200 bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="text-sm bi bi-person-plus-fill text-cyan-400"></i> Nouveau Client
            </a>
        </div>
    </div>

    <!-- Carte Objectif Mensuel (Barre de Progression) -->
    <div class="p-5 space-y-3 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
        <div class="flex items-center justify-between">
            <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                <i class="bi bi-trophy-fill text-amber-400"></i> Objectif de Collecte Mensuel
            </span>
            <span class="font-mono text-xs font-bold text-emerald-400">
                {{ number_format($kpis['monthly_collected'] ?? 0, 0, ',', ' ') }} / {{ number_format($kpis['monthly_target'] ?? 1000000, 0, ',', ' ') }} XAF
            </span>
        </div>
        @php
            $target = $kpis['monthly_target'] ?? 1000000;
            $collected = $kpis['monthly_collected'] ?? 0;
            $percentage = min(100, round(($collected / max(1, $target)) * 100));
        @endphp
        <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
            <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-400" style="width: {{ $percentage }}%"></div>
        </div>
        <div class="flex justify-between items-center text-[11px] text-slate-400">
            <span>Progression: <strong class="font-mono text-white">{{ $percentage }}%</strong></span>
            <span>Reste à réaliser: <strong class="font-mono text-amber-400">{{ number_format(max(0, $target - $collected), 0, ',', ' ') }} XAF</strong></span>
        </div>
    </div>

    <!-- KPIs Statistiques du Jour -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Collecte du Jour -->
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

        <!-- Versements en Attente -->
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

        <!-- Nouveaux Clients / Prospects -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">Nouveaux Clients</span>
                <i class="text-lg bi bi-people text-cyan-400"></i>
            </div>
            <p class="font-mono text-2xl font-black tracking-tight text-cyan-400">
                {{ $kpis['new_clients_count'] ?? 0 }}
            </p>
            <span class="text-[10px] text-slate-500 block">Inscrits ce mois-ci</span>
        </div>

        <!-- Commission Estimée -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider">Commission estimée</span>
                <i class="text-lg text-indigo-400 bi bi-graph-up-arrow"></i>
            </div>
            <p class="font-mono text-2xl font-black tracking-tight text-indigo-400">
                {{ number_format($kpis['estimated_commission'] ?? 0, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
            </p>
            <span class="text-[10px] text-slate-500 block">Basé sur vos cotisations</span>
        </div>
    </div>

    <!-- Grille Principale: Dernières Collectes & Relances du jour -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Tableau des Dernières Collectes du Commercial -->
        <div class="p-6 space-y-4 border lg:col-span-2 bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-clock-history text-emerald-400"></i> Mes Dernières Encaissements
                </h2>
                <a href="{{ route('commercial.collectes.index') }}" class="text-xs font-medium text-emerald-400 hover:underline">Voir tout</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <th class="px-2 pb-2">Heure</th>
                            <th class="px-2 pb-2">Client</th>
                            <th class="px-2 pb-2">Tontine</th>
                            <th class="px-2 pb-2 text-right">Montant</th>
                            <th class="px-2 pb-2 text-center">Statut Caisse</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentCollects ?? [] as $collect)
                            <tr class="transition-colors hover:bg-slate-800/30">
                                <td class="py-2.5 px-2 font-mono text-slate-400">
                                    {{ $collect->created_at->format('H:i') }}
                                </td>
                                <td class="py-2.5 px-2 font-bold text-white">
                                    {{ $collect->account->user->name ?? 'Client inconnu' }}
                                </td>
                                <td class="py-2.5 px-2 text-slate-300">
                                    {{ $collect->subAccount->name ?? 'Tontine' }}
                                </td>
                                <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-400">
                                    +{{ number_format($collect->amount, 0, ',', ' ') }} XAF
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    @if(($collect->is_transferred ?? false) == true)
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Versé</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">En possession</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 italic text-center text-slate-500">
                                    Aucune collecte enregistrée aujourd'hui.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Relances & Prospects à contacter -->
        <div class="p-6 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-telephone-outbound text-cyan-400"></i> Relances du jour
                </h2>
                <span class="text-[10px] text-slate-500">Zone: {{ Auth::user()->zone->name ?? 'Attribuée' }}</span>
            </div>

            <div class="space-y-3">
                @forelse($pendingProspects ?? [] as $prospect)
                    <div class="flex items-center justify-between p-3 border bg-slate-950/60 border-slate-800 rounded-xl">
                        <div>
                            <p class="text-xs font-bold text-white">{{ $prospect->name }}</p>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $prospect->phone }}</span>
                        </div>
                        <a href="tel:{{ $prospect->phone }}" class="p-2 text-xs border rounded-lg text-emerald-400 bg-emerald-950/40 border-emerald-800/60 hover:bg-emerald-900/60">
                            <i class="bi bi-telephone-fill"></i>
                        </a>
                    </div>
                @empty
                    <p class="py-4 text-xs italic text-center text-slate-500">Aucune relance prévue aujourd'hui.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODALE : Saisie Rapide d'une Cotisation Terrain -->
    <div x-show="openQuickCollectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="openQuickCollectModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-cash-stack text-emerald-400"></i> Encaissement Cotisation Terrain
                </h3>
                <button @click="openQuickCollectModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('commercial.collectes.store') }}" method="POST" class="space-y-4">
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
