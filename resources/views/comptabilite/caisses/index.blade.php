@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, selectedAgent: null }">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Gestion des Caisses Terrain & Agents</h1>
            <p class="text-xs text-slate-400">Contrôle des flux d'espèces, arrêtés de caisse et déchargements.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 text-xs font-semibold border rounded-xl bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="p-4 text-xs font-semibold border rounded-xl bg-amber-500/10 text-amber-400 border-amber-500/20">
            {{ session('warning') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Total Encaissements Terrain</span>
                <div class="p-2 bg-emerald-500/10 rounded-xl text-emerald-400"><i class="bi bi-download"></i></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalEspecesCollectees, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Cash Actuel en Circulation (Terrain)</span>
                <div class="p-2 bg-amber-500/10 rounded-xl text-amber-400"><i class="bi bi-wallet2"></i></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-400">{{ number_format($totalSoldeEnCirculation, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Agents Actifs</span>
                <div class="p-2 text-blue-400 bg-blue-500/10 rounded-xl"><i class="bi bi-people"></i></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $agentsCaisses->count() }} <span class="text-xs font-normal text-slate-400">Agents</span></p>
        </div>
    </div>

    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="flex items-center justify-between p-4 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Caisses Individuelles des Agents</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Agent</th>
                        <th class="px-6 py-3.5">Rôle / Zone</th>
                        <th class="px-6 py-3.5 text-right">Encaissements</th>
                        <th class="px-6 py-3.5 text-right">Décaissements</th>
                        <th class="px-6 py-3.5 text-right">Solde Théorique</th>
                        <th class="px-6 py-3.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($agentsCaisses as $agent)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-bold text-white">
                                {{ $agent->name }}
                                <span class="block text-[10px] text-slate-500 font-normal">{{ $agent->phone }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg bg-slate-800 text-slate-300 font-medium text-[10px]">
                                    {{ $agent->roles->first()->name ?? 'Agent' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-right text-emerald-400">
                                {{ number_format($agent->total_encaisse, 0, ',', ' ') }} XAF
                            </td>
                            <td class="px-6 py-4 font-semibold text-right text-rose-400">
                                {{ number_format($agent->total_decaisse, 0, ',', ' ') }} XAF
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold px-2.5 py-1 rounded-lg {{ $agent->solde_theorique > 0 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400' }}">
                                    {{ number_format($agent->solde_theorique, 0, ',', ' ') }} XAF
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    @click="selectedAgent = {{ json_encode($agent) }}; openModal = true"
                                    class="px-3 py-1.5 text-xs font-bold transition bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl"
                                >
                                    Décharger / Arrêté
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                Aucun agent trouvé pour cette agence.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak>
        <div @click.away="openModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Arrêté & Déchargement Caisse</h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.caisses.arrete') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="agent_id" :value="selectedAgent ? selectedAgent.id : ''">

                <div>
                    <span class="block mb-1 text-xs text-slate-400">Agent concerné</span>
                    <p class="text-sm font-bold text-white" x-text="selectedAgent ? selectedAgent.name : ''"></p>
                </div>

                <div class="flex items-center justify-between p-3 border bg-slate-950 border-slate-800 rounded-xl">
                    <span class="text-xs text-slate-400">Solde Théorique attendu :</span>
                    <span class="text-sm font-bold text-amber-400" x-text="selectedAgent ? new Intl.NumberFormat('fr-FR').format(selectedAgent.solde_theorique) + ' XAF' : '0 XAF'"></span>
                </div>

                <div>
                    <label class="block mb-2 text-xs font-medium text-slate-400">Montant Physiquement Reçu (Cash)</label>
                    <input
                        type="number"
                        name="amount_declare"
                        placeholder="Ex: 50000"
                        required
                        class="w-full px-4 py-2.5 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500"
                    >
                </div>

                <button type="submit" class="w-full px-4 py-3 text-xs font-bold transition bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl">
                    Valider le Déchargement vers le Coffre
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
