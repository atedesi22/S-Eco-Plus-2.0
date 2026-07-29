@extends('layouts.app')

@section('content')
<div x-data="{
    search: '',
    statusFilter: 'all',
    selectedClient: null
}" class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Portefeuille Clients</h1>
            <p class="text-xs text-slate-400">Supervisez l'ensemble des comptes et des épargnes clients de votre agence.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Clients</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-people"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalClients, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Comptes Actifs</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-check-circle"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($activeClients, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Encours Total Épargne</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-400 bg-amber-500/10">
                    <i class="text-lg bi bi-wallet2"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalSavings, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 p-4 border sm:flex-row bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher par nom, téléphone ou CNI..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="flex items-center w-full gap-2 sm:w-auto">
            <select x-model="statusFilter" class="w-full px-4 py-2 text-sm text-white border sm:w-auto rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="all">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs / Suspendus</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
        <table class="w-full text-sm text-left border-collapse text-slate-300">
            <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="p-4">Client</th>
                    <th class="p-4">Téléphone</th>
                    <th class="p-4">Agent Affecté</th>
                    <th class="p-4 text-right">Solde Cumulé</th>
                    <th class="p-4 text-center">Statut</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="font-medium divide-y divide-slate-800/60">
                @forelse($clients as $client)
                @php
                    $totalBalance = $client->accounts->sum('balance');
                @endphp
                <tr x-show="(statusFilter === 'all' || '{{ $client->status ?? 'active' }}' === statusFilter) &&
                            (search === '' || '{{ strtolower($client->name) }}'.includes(search.toLowerCase()) || '{{ $client->phone }}'.includes(search))"
                    class="transition hover:bg-slate-800/40">

                    <td class="p-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center font-bold uppercase border rounded-full w-9 h-9 bg-slate-800 text-emerald-400 border-slate-700">
                                {{ substr($client->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-white">{{ $client->name }}</p>
                                <span class="font-mono text-xs text-slate-500">{{ $client->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="p-4 font-mono text-slate-300">
                        {{ $client->phone ?? 'N/A' }}
                    </td>

                    <td class="p-4 text-xs">
                        <span class="text-slate-400">{{ $client->collector->name ?? 'Non assigné' }}</span>
                    </td>

                    <td class="p-4 font-mono font-bold text-right text-emerald-400">
                        {{ number_format($totalBalance, 0, ',', ' ') }} XAF
                    </td>

                    <td class="p-4 text-center">
                        @if(($client->status ?? 'active') === 'active')
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Actif</span>
                        @else
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">Inactif</span>
                        @endif
                    </td>

                    <td class="p-4 text-right">
                        <button @click="selectedClient = {{ json_encode([
                                    'id' => $client->id,
                                    'name' => $client->name,
                                    'phone' => $client->phone ?? 'N/A',
                                    'email' => $client->email ?? 'N/A',
                                    'collector' => $client->collector->name ?? 'Non assigné',
                                    'accounts' => $client->accounts,
                                    'created_at' => $client->created_at ? $client->created_at->format('d/m/Y') : 'N/A'
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                                class="p-2 transition text-slate-400 hover:text-white"
                                title="Consulter dossier">
                            <i class="text-base bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-500">
                        Aucun client trouvé dans le portefeuille de cette agence.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="selectedClient" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="selectedClient = null" class="w-full max-w-2xl p-6 space-y-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

            <div class="flex items-start justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-12 h-12 text-lg font-bold uppercase border rounded-full text-emerald-400 bg-slate-800 border-slate-700">
                        <span x-text="selectedClient ? selectedClient.name.charAt(0) : ''"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white" x-text="selectedClient?.name"></h3>
                        <p class="font-mono text-xs text-slate-400" x-text="'Inscrit le : ' + selectedClient?.created_at"></p>
                    </div>
                </div>
                <button @click="selectedClient = null" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="grid grid-cols-1 gap-4 font-mono text-xs sm:grid-cols-2">
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Téléphone</span>
                    <span class="text-sm font-semibold text-white" x-text="selectedClient?.phone"></span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Agent / Collecteur Affecté</span>
                    <span class="text-sm font-semibold text-emerald-400" x-text="selectedClient?.collector"></span>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-bold tracking-wider uppercase text-slate-400">Comptes & Souscriptions</h4>
                <div class="space-y-2">
                    <template x-for="acc in selectedClient?.accounts" :key="acc.id">
                        <div class="flex items-center justify-between p-3 border rounded-xl bg-slate-950/40 border-slate-800/80">
                            <div>
                                <span class="text-xs font-bold text-white uppercase" x-text="acc.account_type || 'Tontine / Compte'"></span>
                                <span class="block text-[10px] text-slate-500 font-mono" x-text="'N° ' + acc.account_number"></span>
                            </div>
                            <span class="font-mono text-sm font-bold text-emerald-400" x-text="new Intl.NumberFormat('fr-FR').format(acc.balance) + ' XAF'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-800">
                <button @click="selectedClient = null" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Fermer</button>
            </div>
        </div>
    </div>

</div>
@endsection
