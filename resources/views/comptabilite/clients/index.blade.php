@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openNewModal: false }">

    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Gestion des Comptes Clients & Tontines</h1>
            <p class="text-xs text-slate-400">Recherche, souscription de tontines et suivi des portefeuilles clients.</p>
        </div>

        <!-- Bouton Nouveau Client -->
        <button @click="openNewModal = true" class="flex items-center gap-2 px-4 py-2 text-xs font-bold transition shadow-lg text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-emerald-500/10">
            <i class="text-sm bi bi-person-plus-fill"></i> Nouveau Client
        </button>
    </div>

    <!-- Messages Flash -->
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

    <!-- Cartes Macro -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs font-medium text-slate-400">Clients Actifs dans l'Agence</span>
            <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totalClientsActifs) }} <span class="text-xs font-normal text-slate-400">membres</span></p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs font-medium text-slate-400">Épargne Globale Cumulée</span>
            <p class="mt-1 text-2xl font-bold text-emerald-400">{{ number_format($totalEpargneGlobal, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <form method="GET" action="{{ route('comptabilite.clients.index') }}" class="grid grid-cols-1 gap-3 p-4 border bg-slate-900 border-slate-800 rounded-2xl md:grid-cols-3">
        <div>
            <label class="block mb-1 text-[11px] text-slate-400">Rechercher Client</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nom, Téléphone, Email..." class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
        </div>
        <div>
            <label class="block mb-1 text-[11px] text-slate-400">Type de Tontine</label>
            <select name="account_type" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                <option value="">-- Toutes les Tontines --</option>
                @foreach($tontineTypes as $key => $label)
                    <option value="{{ $key }}" {{ $accountType === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full py-2.5 text-xs font-bold text-slate-950 bg-emerald-500 hover:bg-emerald-600 rounded-xl transition">
                <i class="bi bi-search"></i> Filtrer
            </button>
        </div>
    </form>

    <!-- Tableau des Clients -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Client</th>
                        <th class="px-6 py-3.5">Zone</th>
                        <th class="px-6 py-3.5">Tontines Souscrites</th>
                        <th class="px-6 py-3.5 text-right">Solde Total</th>
                        <th class="px-6 py-3.5 text-center">Statut</th>
                        <th class="px-6 py-3.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($clients as $client)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-bold text-white">
                                {{ $client->name }}
                                <span class="block text-[10px] text-slate-500 font-normal">{{ $client->phone }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-800 text-slate-300 rounded-md font-medium text-[11px]">
                                    {{ $client->zone->name ?? 'Non assignée' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($client->accounts as $acc)
                                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-emerald-400 text-[10px] uppercase font-bold">
                                            {{ $acc->type }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-right text-emerald-400">
                                {{ number_format($client->accounts->sum('balance'), 0, ',', ' ') }} XAF
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($client->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">Actif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold">Suspendu</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('comptabilite.clients.show', $client->id) }}" class="px-3 py-1.5 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 rounded-lg border border-slate-700 transition">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">Aucun client trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- MODAL CRÉATION NOUVEAU CLIENT -->
    <div x-show="openNewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="flex items-center gap-2 text-base font-bold text-white">
                    <i class="bi bi-person-plus text-emerald-500"></i> Création d'un Nouveau Client
                </h3>
                <button @click="openNewModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('comptabilite.clients.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Infos Personnelles -->
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Nom Complet du Client *</label>
                        <input type="text" name="name" required placeholder="Ex: Jean Pascal" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Numéro Téléphone *</label>
                        <input type="text" name="phone" required placeholder="Ex: 699001122" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Adresse Email (Optionnel)</label>
                        <input type="email" name="email" placeholder="client@gmail.com" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Zone d'Abonnement *</label>
                        <select name="zone_id" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                            <option value="">-- Choisir la Zone --</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section Tontines & Versements Obligatoires -->
                <div class="pt-4 space-y-3 border-t border-slate-800">
                    <h4 class="text-xs font-bold text-white">Sélection des Tontines & Versement Initial Obligatoire (Min 1 000 XAF/tontine)</h4>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($tontineTypes as $key => $label)
                            <div class="p-3 space-y-2 border bg-slate-950 border-slate-800 rounded-xl" x-data="{ checked: false }">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="tontines[]" value="{{ $key }}" x-model="checked" class="rounded bg-slate-900 border-slate-800 text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-xs font-bold text-white">{{ $label }}</span>
                                </label>

                                <div x-show="checked" class="pt-1">
                                    <label class="block text-[10px] text-slate-400">Dépôt Initial (Min 1 000 XAF) :</label>
                                    <input type="number" min="1000" name="deposits[{{ $key }}]" value="1000" :disabled="!checked" class="w-full px-2 py-1 text-xs font-bold border rounded-lg text-emerald-400 bg-slate-900 border-slate-800">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="openNewModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl">Créer le Client & Activer Tontines</button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
