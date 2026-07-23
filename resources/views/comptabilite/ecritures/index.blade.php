@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openManualModal: false }">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Grand Livre des Écritures Comptables</h1>
            <p class="text-xs text-slate-400">Traçabilité complète, journaux des opérations et régularisations.</p>
        </div>
        <button
            @click="openManualModal = true"
            class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl"
        >
            <i class="bi bi-plus-circle-fill"></i>
            <span>Saisir une Écriture / Régularisation</span>
        </button>
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
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs text-slate-400">Total Dépôts (Période)</span>
            <p class="mt-1 text-xl font-bold text-emerald-400">{{ number_format($totauxPériode->total_depots, 0, ',', ' ') }} XAF</p>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs text-slate-400">Total Retraits (Période)</span>
            <p class="mt-1 text-xl font-bold text-rose-400">{{ number_format($totauxPériode->total_retraits, 0, ',', ' ') }} XAF</p>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs text-slate-400">Total Frais Générés</span>
            <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($totauxPériode->total_frais, 0, ',', ' ') }} XAF</p>
        </div>
    </div>

    <form method="GET" action="{{ route('comptabilite.ecritures.index') }}" class="grid items-end grid-cols-1 gap-3 p-4 border bg-slate-900 border-slate-800 rounded-2xl md:grid-cols-4">
        <div>
            <label class="block mb-1 text-[11px] font-medium text-slate-400">Date Début</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
        </div>
        <div>
            <label class="block mb-1 text-[11px] font-medium text-slate-400">Date Fin</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
        </div>
        <div>
            <label class="block mb-1 text-[11px] font-medium text-slate-400">Type de Transaction</label>
            <select name="type" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                <option value="">-- Tous les types --</option>
                <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Dépôts</option>
                <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Retraits</option>
                <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>Transferts / Arrêtés</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full py-2.5 text-xs font-bold text-slate-950 bg-emerald-500 hover:bg-emerald-600 rounded-xl transition">
                <i class="bi bi-filter"></i> Filtrer le Grand Livre
            </button>
        </div>
    </form>

    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Référence & Date</th>
                        <th class="px-6 py-3.5">Intitulé / Libellé</th>
                        <th class="px-6 py-3.5">Client / Compte</th>
                        <th class="px-6 py-3.5">Opérateur</th>
                        <th class="px-6 py-3.5 text-right">Débit (-)</th>
                        <th class="px-6 py-3.5 text-right">Crédit (+)</th>
                        <th class="px-6 py-3.5 text-right">Frais</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($transactions as $t)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-[11px] text-white">
                                <span class="block font-bold text-emerald-400">{{ $t->reference }}</span>
                                <span class="text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-300">
                                {{ $t->description ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $t->client_name ?? 'Compte Interne' }}
                            </td>
                            <td class="px-6 py-4 text-slate-400">
                                {{ $t->agent_name ?? 'Système' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-right text-rose-400">
                                {{ $t->type === 'withdrawal' ? number_format($t->amount, 0, ',', ' ') . ' XAF' : '-' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-right text-emerald-400">
                                {{ $t->type === 'deposit' ? number_format($t->amount, 0, ',', ' ') . ' XAF' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right text-amber-400">
                                {{ $t->fees > 0 ? number_format($t->fees, 0, ',', ' ') . ' XAF' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Aucune écriture comptable trouvée pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $transactions->links() }}
        </div>
    </div>

    <div x-show="openManualModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak>
        <div @click.away="openManualModal = false" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Saisie d'Écriture de Régularisation</h3>
                <button @click="openManualModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.ecritures.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">ID Utilisateur / Client</label>
                    <input type="number" name="user_id" required placeholder="Ex: 11" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Sens de l'écriture</label>
                    <select name="type" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="deposit">Crédit / Ajout Solde (Dépôt)</option>
                        <option value="withdrawal">Débit / Dédution Solde (Retrait)</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Montant (XAF)</label>
                    <input type="number" name="amount" min="1" required placeholder="Ex: 5000" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-400">Motif de la régularisation</label>
                    <textarea name="description" rows="2" required placeholder="Ex: Correction erreur de saisie du 22/07" class="w-full px-4 py-2.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl focus:border-emerald-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 text-xs font-bold transition text-slate-950 bg-emerald-500 hover:bg-emerald-600 rounded-xl">
                    Enregistrer l'Écriture au Grand Livre
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
