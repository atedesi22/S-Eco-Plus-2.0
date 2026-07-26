@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col justify-between gap-4 p-4 border md:flex-row md:items-center bg-slate-900/60 border-slate-800 rounded-2xl backdrop-blur-xl">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-white">Tableau de Bord Direction Agence</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-bold uppercase tracking-wider">
                    Supervision Général
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400">Vue synthétique des liquidités, flux financiers, caisses et activités terrain du jour.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-mono text-slate-300 bg-slate-950 px-3.5 py-2 rounded-xl border border-slate-800 flex items-center gap-2 shadow-inner">
                <i class="text-indigo-400 bi bi-calendar-event"></i> {{ now()->translatedFormat('d F Y') }}
            </span>
            <a href="{{ route('directeur.validations.index') }}" class="relative flex items-center gap-2 px-4 py-2 text-xs font-bold text-white transition bg-indigo-600 shadow-lg hover:bg-indigo-500 rounded-xl shadow-indigo-600/30 group">
                <i class="text-sm transition bi bi-shield-check group-hover:scale-110"></i> Validations
                @if($validationsEnAttente->count() > 0)
                    <span class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold animate-pulse">
                        {{ $validationsEnAttente->count() }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="relative p-5 transition border bg-slate-900/90 border-slate-800 rounded-2xl hover:border-emerald-500/30">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-400">Épargne Globale Clients</span>
                    <p class="mt-2 text-2xl font-black tracking-tight text-emerald-400">
                        {{ number_format($totalEpargneClients, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
                    </p>
                </div>
                <div class="flex items-center justify-center text-xl border shadow-inner w-11 h-11 rounded-xl bg-emerald-500/10 border-emerald-500/20 text-emerald-400">
                    <i class="bi bi-piggy-bank"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-1">
                <i class="bi bi-people text-slate-500"></i> <strong class="text-white">{{ number_format($totalClients) }}</strong> clients inscrits
            </p>
        </div>

        <div class="p-5 transition border bg-slate-900/90 border-slate-800 rounded-2xl hover:border-blue-500/30">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-400">En Caisses & Coffres</span>
                    <p class="mt-2 text-2xl font-black tracking-tight text-white">
                        {{ number_format($liquiditeCaisses, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
                    </p>
                </div>
                <div class="flex items-center justify-center text-xl text-blue-400 border shadow-inner w-11 h-11 rounded-xl bg-blue-500/10 border-blue-500/20">
                    <i class="bi bi-safe2"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-1">
                <i class="bi bi-shield-lock text-slate-500"></i> Disponibilité physique agence
            </p>
        </div>

        <div class="p-5 transition border bg-slate-900/90 border-slate-800 rounded-2xl hover:border-emerald-500/30">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-400">Collecte & Dépôts (Aujourd'hui)</span>
                    <p class="mt-2 text-2xl font-black tracking-tight text-emerald-400">
                        +{{ number_format($depotsAujourdhui, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
                    </p>
                </div>
                <div class="flex items-center justify-center text-xl border shadow-inner w-11 h-11 rounded-xl bg-emerald-500/10 border-emerald-500/20 text-emerald-400">
                    <i class="bi bi-arrow-down-left-circle"></i>
                </div>
            </div>
            <p class="text-[11px] text-emerald-400 mt-3 font-semibold flex items-center gap-1">
                <i class="bi bi-graph-up-arrow"></i> Frais perçus : {{ number_format($fraisAujourdhui, 0, ',', ' ') }} XAF
            </p>
        </div>

        <div class="p-5 transition border bg-slate-900/90 border-slate-800 rounded-2xl hover:border-rose-500/30">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-400">Décaissements (Aujourd'hui)</span>
                    <p class="mt-2 text-2xl font-black tracking-tight text-rose-400">
                        -{{ number_format($retraitsAujourdhui, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
                    </p>
                </div>
                <div class="flex items-center justify-center text-xl border shadow-inner w-11 h-11 rounded-xl bg-rose-500/10 border-rose-500/20 text-rose-400">
                    <i class="bi bi-arrow-up-right-circle"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-1">
                <i class="bi bi-person-badge text-slate-500"></i> Staff actif : <strong class="text-white">{{ $totalAgents }} agents</strong>
            </p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="p-5 space-y-4 border lg:col-span-2 bg-slate-900/90 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider text-white uppercase">
                    <i class="text-sm text-indigo-400 bi bi-bank"></i> Caisses physiques & Coffres-Forts
                </h2>
                <span class="text-[11px] font-mono text-slate-400">{{ $caisses->count() }} guichet(s) / coffre(s)</span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse($caisses as $caisse)
                    @php
                        $maxLimit = $caisse->max_limit ?? 5000000;
                        $percentage = $maxLimit > 0 ? min(100, round(($caisse->current_balance / $maxLimit) * 100)) : 0;
                    @endphp
                    <div class="p-4 space-y-3 transition border bg-slate-950/80 border-slate-800/80 rounded-xl hover:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="bi {{ $caisse->type === 'coffre_fort' ? 'bi-shield-lock text-amber-400' : 'bi-display text-indigo-400' }}"></i>
                                <span class="text-xs font-bold text-white">{{ $caisse->name }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border
                                {{ ($caisse->status ?? 'closed') === 'open' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                {{ ($caisse->status ?? 'closed') === 'open' ? 'Ouverte' : 'Fermée' }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $percentage >= 90 ? 'bg-rose-500' : ($percentage >= 70 ? 'bg-amber-500' : 'bg-indigo-500') }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-end justify-between pt-1 text-xs">
                            <div>
                                <p class="text-[10px] text-slate-500">Agent :</p>
                                <p class="font-semibold text-slate-300 truncate max-w-[120px]">{{ $caisse->agent_name ?? 'Non assignée' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-500">Solde Actuel :</p>
                                <p class="font-bold text-emerald-400">{{ number_format($caisse->current_balance ?? 0, 0, ',', ' ') }} <span class="text-[10px] text-slate-500">XAF</span></p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-6 text-xs text-center text-slate-500">Aucune caisse enregistrée dans cette agence.</div>
                @endforelse
            </div>
        </div>

        <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider text-white uppercase">
                    <i class="text-sm bi bi-shield-exclamation text-amber-400"></i> Validations Urgentes
                </h2>
                <a href="{{ route('directeur.validations.index') }}" class="text-[10px] font-bold text-indigo-400 hover:underline">Voir tout</a>
            </div>

            <div class="space-y-3">
                @forelse($validationsEnAttente as $val)
                    <div class="p-3 space-y-2 transition border bg-slate-950/80 border-slate-800/80 rounded-xl hover:border-amber-500/30">
                        <div class="flex items-start justify-between">
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-[9px] font-bold border border-amber-500/20 uppercase">
                                {{ str_replace('_', ' ', $val->type ?? 'Autorisation') }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ \Carbon\Carbon::parse($val->created_at)->format('H:i') }}</span>
                        </div>
                        <p class="text-xs font-medium text-slate-300 line-clamp-2">{{ $val->description ?? 'Demande d\'approbation requise' }}</p>
                        <div class="flex justify-between items-center text-[10px] text-slate-500 pt-1 border-t border-slate-900">
                            <span>Par : <strong class="text-slate-300">{{ $val->requester_name }}</strong></span>
                            <a href="{{ route('directeur.validations.index') }}" class="flex items-center gap-1 font-bold text-indigo-400 hover:text-indigo-300">
                                Trancher <i class="bi bi-chevron-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 space-y-2 text-xs text-center text-slate-500">
                        <i class="block text-2xl bi bi-check-circle text-emerald-400"></i>
                        <p class="font-medium text-slate-400">Aucune demande en attente</p>
                        <p class="text-[10px] text-slate-600">Toutes les opérations sensibles ont été traitées.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
            <div class="pb-3 border-b border-slate-800">
                <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider text-white uppercase">
                    <i class="text-sm bi bi-geo-alt-fill text-emerald-400"></i> Collecte par Zone
                </h2>
            </div>

            <div class="space-y-3">
                @forelse($zonesPerformance as $zone)
                    <div class="p-3 space-y-2 border bg-slate-950/80 border-slate-800/80 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">{{ $zone->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                                {{ $zone->total_clients }} client(s)
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-1 text-xs">
                            <span class="text-slate-500">Total du jour :</span>
                            <span class="font-extrabold text-emerald-400">{{ number_format($zone->collecte_jour, 0, ',', ' ') }} XAF</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-xs text-center text-slate-500">Aucune zone de collecte configurée.</div>
                @endforelse
            </div>
        </div>

        <div class="p-5 space-y-4 border lg:col-span-2 bg-slate-900/90 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider text-white uppercase">
                    <i class="text-sm text-indigo-400 bi bi-activity"></i> Flux Financier Récent
                </h2>
                <span class="text-[10px] text-slate-500">8 dernières opérations</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-400">
                    <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-3 py-2.5">Heure</th>
                            <th class="px-3 py-2.5">Client & Compte</th>
                            <th class="px-3 py-2.5">Agent / Caissier</th>
                            <th class="px-3 py-2.5">Type</th>
                            <th class="px-3 py-2.5 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentTransactions as $tx)
                            <tr class="transition hover:bg-slate-800/40">
                                <td class="px-3 py-3 font-mono text-[11px] text-slate-400">
                                    {{ \Carbon\Carbon::parse($tx->created_at)->format('H:i:s') }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-bold text-white">{{ $tx->client_name }}</div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-semibold uppercase
                                        {{ str_contains(strtolower($tx->account_type), 'scolaire') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400' }}">
                                        {{ str_replace('_', ' ', $tx->account_type) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-slate-300">{{ $tx->agent_name }}</td>
                                <td class="px-3 py-3">
                                    @if($tx->type === 'deposit')
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold text-[10px] border border-emerald-500/20">DÉPÔT</span>
                                    @elseif($tx->type === 'withdrawal')
                                        <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-bold text-[10px] border border-rose-500/20">RETRAIT</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px] border border-amber-500/20">{{ strtoupper($tx->type) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-black text-sm {{ $tx->type === 'deposit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $tx->type === 'deposit' ? '+' : '-' }} {{ number_format($tx->amount, 0, ',', ' ') }} <span class="text-[10px] font-normal text-slate-500">XAF</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune transaction enregistrée aujourd'hui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
