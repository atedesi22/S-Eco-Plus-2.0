@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openAddTontineModal: false, openStatusModal: false }">

    <!-- En-tête Client -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 text-lg font-bold border rounded-2xl bg-emerald-500/10 border-emerald-500/20 text-emerald-400">
                {{ strtoupper(substr($client->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white">{{ $client->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase
                        {{ $client->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                        {{ $client->status === 'active' ? 'Compte Valide' : 'Compte Inactif / Suspendu' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">Client inscrit le {{ $client->created_at->format('d/m/Y à H:i') }} | Tél : <span class="font-mono font-bold text-white">{{ $client->phone }}</span></p>
            </div>
        </div>

        <!-- Boutons d'actions / Gestion du statut -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="openAddTontineModal = true" class="px-3 py-2 text-xs font-bold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl transition flex items-center gap-1.5">
                <i class="bi bi-plus-circle-fill"></i> Ajouter une Tontine
            </button>

            <button @click="openStatusModal = true" class="px-3 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="bi bi-shield-lock-fill"></i> Modifier État / Actions
            </button>
        </div>
    </div>

    <!-- Alertes -->
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

    <!-- Grille Informations de Contexte & Territoire -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <!-- Carte Profil & Territoire -->
        <div class="p-5 space-y-3 border bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="pb-2 text-xs font-bold tracking-wider uppercase border-b text-slate-400 border-slate-800">Informations Territoriales</h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Agence :</span>
                    <span class="font-bold text-white">{{ $client->structure->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Zone d'appartenance :</span>
                    <span class="font-bold text-emerald-400">{{ $client->zone->name ?? 'Non assignée' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Agent Créateur :</span>
                    <span class="font-bold text-white">{{ $creator->name ?? 'Système / Inconnu' }}</span>
                </div>
            </div>
        </div>

        <!-- Collectrices Affectées à la Zone -->
        <div class="p-5 space-y-3 border bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="pb-2 text-xs font-bold tracking-wider uppercase border-b text-slate-400 border-slate-800">Collectrices Affectées (Zone)</h3>

            <div class="space-y-2">
                @forelse($collectrices as $col)
                    <div class="flex items-center justify-between p-2 text-xs border bg-slate-950 border-slate-800 rounded-xl">
                        <span class="font-bold text-white">{{ $col->name }}</span>
                        <span class="font-mono text-[10px] text-slate-400">{{ $col->phone }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucune collectrice affectée à cette zone.</p>
                @endforelse
            </div>
        </div>

        <!-- Panier / Boutique Électroménager -->
        <div class="p-5 space-y-3 border bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="flex justify-between pb-2 text-xs font-bold tracking-wider uppercase border-b text-slate-400 border-slate-800">
                <span>Panier Boutique (Électroménager)</span>
                <i class="bi bi-cart-check text-emerald-400"></i>
            </h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Achats Effectués :</span>
                    <span class="font-bold text-white">{{ $boutiqueTransactions->count() }} article(s)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Réglé :</span>
                    <span class="font-bold text-emerald-400">{{ number_format($boutiqueTransactions->sum('amount'), 0, ',', ' ') }} XAF</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Section Tontines Souscrites -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-white">Tontines Actives & Portefeuilles ({{ $client->accounts->count() }})</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach($client->accounts as $acc)
                <div class="p-4 space-y-3 transition border bg-slate-900 border-slate-800 rounded-2xl hover:border-slate-700">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-emerald-400 text-[10px] uppercase font-bold">
                            Tontine {{ $acc->type }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $acc->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                            {{ $acc->status }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[10px] text-slate-500 font-mono">N° {{ $acc->account_number }}</p>
                        <p class="text-xl font-bold text-white">{{ number_format($acc->balance, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
                    </div>

                    <div class="p-2 bg-slate-950 rounded-xl flex justify-between items-center text-[11px] border border-slate-800">
                        <span class="text-slate-400">Fond de Caisse Réserver :</span>
                        <span class="font-bold text-amber-400">1 000 XAF</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section Historique Général des Transactions -->
    <div class="p-4 space-y-4 overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">

        <div class="flex flex-col justify-between gap-3 pb-3 border-b md:flex-row md:items-center border-slate-800">
            <h2 class="text-sm font-bold text-white">Historique de toutes les Transactions</h2>

            <!-- Filtre de Date et Type -->
            <form method="GET" action="{{ route('comptabilite.clients.show', $client->id) }}" class="flex flex-wrap items-center gap-2">
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1 text-xs text-white bg-slate-950 border border-slate-800 rounded-lg">
                <span class="text-xs text-slate-500">à</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1 text-xs text-white bg-slate-950 border border-slate-800 rounded-lg">

                <select name="tx_type" class="px-2.5 py-1 text-xs text-white bg-slate-950 border border-slate-800 rounded-lg">
                    <option value="">Tous les Types</option>
                    <option value="deposit" {{ $txType === 'deposit' ? 'selected' : '' }}>Dépôts</option>
                    <option value="withdrawal" {{ $txType === 'withdrawal' ? 'selected' : '' }}>Retraits</option>
                    <option value="product_payment" {{ $txType === 'product_payment' ? 'selected' : '' }}>Achat Boutique</option>
                    <option value="account_maintenance_fee" {{ $txType === 'account_maintenance_fee' ? 'selected' : '' }}>Frais Tenue Compte</option>
                </select>

                <button type="submit" class="px-3 py-1 text-xs font-bold rounded-lg text-slate-950 bg-emerald-500 hover:bg-emerald-400">
                    <i class="bi bi-funnel"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Réf / Tontine</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Opérateur</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($transactions as $tx)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-4 py-3 text-slate-300 font-mono text-[11px]">
                                {{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-mono">
                                <span class="block font-bold text-white">{{ $tx->reference }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Tontine {{ $tx->account_type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($tx->type === 'deposit')
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold text-[10px]">DÉPÔT</span>
                                @elseif($tx->type === 'withdrawal')
                                    <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-bold text-[10px]">RETRAIT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px]">{{ strtoupper($tx->type) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $tx->agent_name }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $tx->type === 'deposit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->type === 'deposit' ? '+' : '-' }} {{ number_format($tx->amount, 0, ',', ' ') }} XAF
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Aucune transaction enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- MODAL AJOUT D'UNE NOUVELLE TONTINE -->
    <div x-show="openAddTontineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Souscrire une Nouvelle Tontine</h3>
                <button @click="openAddTontineModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.add-tontine', $client->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Type de Tontine *</label>
                    <select name="type" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl">
                        @foreach($tontineTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Dépôt Initial d'Activation (Min 1 000 XAF) *</label>
                    <input type="number" min="1000" name="initial_deposit" value="1000" required class="w-full px-3 py-2 text-xs font-bold border text-emerald-400 bg-slate-950 border-slate-800 rounded-xl">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddTontineModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-emerald-500 rounded-xl">Activer Tontine</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL GESTION DU STATUT / BLOCAGE / GEL / SUSPENSION -->
    <div x-show="openStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Changer l'État du Compte Client</h3>
                <button @click="openStatusModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.update-status', $client->id) }}" method="POST" class="space-y-3">
                @csrf
                <p class="text-xs text-slate-400">Choisissez l'action à appliquer sur le compte de <strong class="text-white">{{ $client->name }}</strong> :</p>

                <div class="space-y-2">
                    <label class="flex items-center justify-between p-3 border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="status" value="active" {{ $client->status === 'active' ? 'checked' : '' }} class="text-emerald-500">
                            <span class="text-xs font-bold text-emerald-400">Activer le Compte</span>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="status" value="blocked" class="text-amber-500">
                            <span class="text-xs font-bold text-amber-400">Bloquer Temporairement</span>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="status" value="suspended" class="text-rose-500">
                            <span class="text-xs font-bold text-rose-400">Suspendre le Client</span>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="status" value="frozen" class="text-cyan-500">
                            <span class="text-xs font-bold text-cyan-400">Geler les Tontines</span>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="status" value="closed" class="text-slate-500">
                            <span class="text-xs font-bold text-slate-400">Clôturer / Supprimer</span>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="openStatusModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl">Appliquer la Modification</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
