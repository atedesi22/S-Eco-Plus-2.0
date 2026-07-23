@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Analyse des Flux Financiers : {{ $agency->name }}</h1>
            <p class="text-xs text-slate-400">Ventilation géographique de la collecte et suivi de la rentabilité des zones.</p>
        </div>

        <form method="GET" action="{{ route('comptabilite.flux.index') }}" class="flex items-center p-2 space-x-2 border bg-slate-900 rounded-xl border-slate-800">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-lg focus:border-emerald-500">
            <span class="text-xs text-slate-500">à</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-lg focus:border-emerald-500">
            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-slate-950 bg-emerald-500 hover:bg-emerald-600 rounded-lg transition">
                <i class="bi bi-arrow-repeat"></i>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Flux Entrants (Collecte Global)</span>
                <div class="p-2 bg-emerald-500/10 rounded-xl text-emerald-400"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-400">{{ number_format($totalAgenceDepots, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Flux Sortants (Retraits Global)</span>
                <div class="p-2 bg-rose-500/10 rounded-xl text-rose-400"><i class="bi bi-graph-down-arrow"></i></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-400">{{ number_format($totalAgenceRetraits, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Flux Net Agence (Trésorerie Net)</span>
                <div class="p-2 text-blue-400 bg-blue-500/10 rounded-xl"><i class="bi bi-piggy-bank"></i></div>
            </div>
            <p class="text-2xl font-bold {{ $totalAgenceNet >= 0 ? 'text-white' : 'text-amber-400' }} mt-2">
                {{ number_format($totalAgenceNet, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($zonesStats as $zone)
            <div class="p-5 space-y-4 transition border bg-slate-900 border-slate-800 rounded-2xl hover:border-slate-700">

                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-white">{{ $zone->name }}</h3>
                        <span class="text-[10px] text-slate-500">{{ $zone->nb_agents }} Agent(s) terrain rattaché(s)</span>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-800 text-slate-300">
                        {{ $zone->nb_transactions }} ops
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Dépôts collectés :</span>
                        <span class="font-bold text-emerald-400">+ {{ number_format($zone->total_depots, 0, ',', ' ') }} XAF</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Retraits exécutés :</span>
                        <span class="font-bold text-rose-400">- {{ number_format($zone->total_retraits, 0, ',', ' ') }} XAF</span>
                    </div>

                    <div class="p-2.5 bg-slate-950 rounded-xl flex justify-between items-center border border-slate-800">
                        <span class="text-[11px] font-medium text-slate-300">Solde Net Zone :</span>
                        <span class="font-bold {{ $zone->flux_net >= 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ number_format($zone->flux_net, 0, ',', ' ') }} XAF
                        </span>
                    </div>
                </div>

            </div>
        @empty
            <div class="p-8 text-center border col-span-full text-slate-500 bg-slate-900 border-slate-800 rounded-2xl">
                Aucune zone configurée pour cette agence.
            </div>
        @endforelse
    </div>

    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="p-4 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Top Agents Terrain de l'Agence</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Agent</th>
                        <th class="px-6 py-3.5">Zone Affectée</th>
                        <th class="px-6 py-3.5 text-right">Montant Collecté</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($topAgents as $agent)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-bold text-white">
                                {{ $agent->name }}
                                <span class="block text-[10px] text-slate-500 font-normal">{{ $agent->phone }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 font-medium rounded-lg bg-slate-800 text-slate-300">
                                    {{ $agent->zone->name ?? 'Non assigné' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-right text-emerald-400">
                                {{ number_format($agent->total_collecte, 0, ',', ' ') }} XAF
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-slate-500">
                                Aucun agent trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
