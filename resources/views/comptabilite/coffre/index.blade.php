@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openMouvementModal: false, actionType: 'vault_deposit' }">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Gestion du Coffre-Fort Agence</h1>
            <p class="text-xs text-slate-400">Suivi du fond de caisse principal, versements banque et dotations agents.</p>
        </div>
        <div class="flex space-x-2">
            <button
                @click="actionType = 'vault_deposit'; openMouvementModal = true"
                class="px-3.5 py-2 text-xs font-bold transition bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl flex items-center space-x-1.5"
            >
                <i class="bi bi-box-arrow-in-down"></i>
                <span>Approvisionner Coffre</span>
            </button>
            <button
                @click="actionType = 'agent_dotation'; openMouvementModal = true"
                class="px-3.5 py-2 text-xs font-bold transition bg-blue-500 hover:bg-blue-600 text-white rounded-xl flex items-center space-x-1.5"
            >
                <i class="bi bi-wallet2"></i>
                <span>Dotation Agent</span>
            </button>
            <button
                @click="actionType = 'vault_withdrawal'; openMouvementModal = true"
                class="px-3.5 py-2 text-xs font-bold transition bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl flex items-center space-x-1.5"
            >
                <i class="bi bi-bank"></i>
                <span>Verser en Banque</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 text-xs font-semibold border rounded-xl bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 text-xs font-semibold border rounded-xl bg-rose-500/10 text-rose-400 border-rose-500/20">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="relative p-5 overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Solde Réel Coffre-Fort</span>
                <div class="p-2 bg-emerald-500/10 rounded-xl text-emerald-400"><i class="text-lg bi bi-safe2-fill"></i></div>
            </div>
            <p class="mt-2 text-2xl font-black text-emerald-400">{{ number_format($soldeCoffre, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Total Alimentations & Déchargements</span>
                <div class="p-2 text-blue-400 bg-blue-500/10 rounded-xl"><i class="bi bg-arrow-down-left"></i></div>
            </div>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format($totalEntrees, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Total Sorties (Dotations + Banque)</span>
                <div class="p-2 bg-rose-500/10 rounded-xl text-rose-400"><i class="bi bi-arrow-up-right"></i></div>
            </div>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format($totalSorties, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>
    </div>

    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="p-4 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Journal des Mouvements de Trésorerie du Coffre</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Référence & Date</th>
                        <th class="px-6 py-3.5">Type Mouvement</th>
                        <th class="px-6 py-3.5">Description / Motif</th>
                        <th class="px-6 py-3.5">Effectué par</th>
                        <th class="px-6 py-3.5 text-right">Montant (XAF)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($mouvementsCoffre as $m)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-[11px] text-white">
                                <span class="block font-bold text-emerald-400">{{ $m->reference }}</span>
                                <span class="text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($m->type === 'vault_deposit')
                                    <span class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-bold text-[10px]">Approvisionnement</span>
                                @elseif($m->type === 'transfer')
                                    <span class="px-2 py-1 rounded-lg bg-blue-500/10 text-blue-400 font-bold text-[10px]">Déchargement Caisse</span>
                                @elseif($m->type === 'agent_dotation')
                                    <span class="px-2 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 font-bold text-[10px]">Dotation Agent</span>
                                @else
                                    <span class="px-2 py-1 rounded-lg bg-amber-500/10 text-amber-400 font-bold text-[10px]">Dépôt Banque</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-300">
                                {{ $m->description }}
                            </td>
                            <td class="px-6 py-4 text-slate-400">
                                {{ $m->agent_name ?? 'Comptable' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-sm {{ in_array($m->type, ['vault_deposit', 'transfer']) ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ in_array($m->type, ['vault_deposit', 'transfer']) ? '+' : '-' }} {{ number_format($m->amount, 0, ',', ' ') }} XAF
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                Aucun mouvement de coffre enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $mouvementsCoffre->links() }}
        </div>
    </div>

    <div x-show="openMouvementModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak>
        <div @click.away="openMouvementModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white" x-text="
                    actionType === 'vault_deposit' ? 'Approvisionner le Coffre-Fort' :
                    (actionType === 'agent_dotation' ? 'Dotation de Caisse Agent' : 'Saisie Dépôt Banque')
                "></h3>
                <button @click="openMouvementModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.coffre.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action_type" :value="actionType">

                <div x-show="actionType === 'agent_dotation'">
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Agent bénéficiaire</label>
                    <select name="target_agent_id" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="">-- Sélectionner l'agent --</option>
                        @foreach($agentsAgence as $ag)
                            <option value="{{ $ag->id }}">{{ $ag->name }} ({{ $ag->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Montant (XAF)</label>
                    <input type="number" name="amount" min="1000" required placeholder="Ex: 500000" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Description / Justificatif</label>
                    <input type="text" name="description" required placeholder="Ex: Alimentation Banque BICEC / Fond de roulement" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <button type="submit" class="w-full py-3 text-xs font-bold transition text-slate-950 bg-emerald-500 hover:bg-emerald-600 rounded-xl">
                    Valider le Mouvement de Coffre
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
