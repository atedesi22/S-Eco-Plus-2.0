@extends('layouts.app')

@section('content')
<div x-data="{ openSettingsModal: false, openDeleteModal: false }" class="p-6 space-y-6 text-slate-300">

    <!-- En-tête & Boutons d'action -->
    <div class="flex flex-col justify-between gap-4 p-6 border md:flex-row md:items-center bg-slate-900 border-slate-800 rounded-2xl">
        <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-12 h-12 text-xl font-bold border rounded-xl bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-xs font-mono font-bold">{{ $zone->code }}</span>
                    <h1 class="text-xl font-bold text-white">{{ $zone->name }}</h1>
                </div>
                <p class="mt-1 text-xs text-slate-400">Agence : <strong class="text-white">{{ $zone->agency->name }}</strong></p>
            </div>
        </div>

        <!-- Boutons Paramètres & Suppression -->
        <div class="flex items-center space-x-3">
            <button @click="openSettingsModal = true" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold text-white transition border bg-slate-800 hover:bg-slate-700 rounded-xl border-slate-700">
                <i class="bi bi-gear-fill"></i>
                <span>Paramètres</span>
            </button>
            <button @click="openDeleteModal = true" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition border bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border-rose-500/30 rounded-xl">
                <i class="bi bi-trash-fill"></i>
                <span>Supprimer la zone</span>
            </button>
        </div>
    </div>

    <!-- Cartes de Synthèse & Effectifs -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <!-- Chef de Zone -->
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="block mb-1 text-xs text-slate-400">Chef de Zone / Commercial</span>
            <p class="text-base font-bold text-white">{{ $zone->manager->name ?? 'Non assigné' }}</p>
            <span class="text-[10px] text-emerald-400 font-mono">Superviseur Terrain</span>
        </div>

        <!-- Effectifs Agents -->
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="block mb-1 text-xs text-slate-400">Effectif Terrain</span>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-300"><strong class="font-mono text-white">{{ $commerciaux->count() }}</strong> Commerciaux</span>
                <span class="text-slate-300"><strong class="font-mono text-white">{{ $collectrices->count() }}</strong> Collectrices</span>
            </div>
        </div>

        <!-- Flux Total Dépôts -->
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="block mb-1 text-xs text-slate-400">Total Dépôts (Zone)</span>
            <p class="font-mono text-lg font-black text-emerald-400">+{{ number_format($statsGlobales->total_depots ?? 0, 0, ',', ' ') }} FCFA</p>
        </div>

        <!-- Flux Total Retraits -->
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="block mb-1 text-xs text-slate-400">Total Retraits (Zone)</span>
            <p class="font-mono text-lg font-black text-rose-400">-{{ number_format($statsGlobales->total_retraits ?? 0, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <!-- Tableau : Performances des Agents Terrain -->
    <div class="p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
        <h3 class="flex items-center space-x-2 text-base font-bold text-white">
            <i class="bi bi-people"></i>
            <span>Flux d'Activité par Agent de Terrain</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="font-mono text-xs uppercase border-b border-slate-800 text-slate-400">
                        <th class="px-4 py-3">Agent</th>
                        <th class="px-4 py-3">Rôle</th>
                        <th class="px-4 py-3 text-right">Dépôts Enregistrés</th>
                        <th class="px-4 py-3 text-right">Retraits Enregistrés</th>
                        <th class="px-4 py-3 text-right">Solde Net</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-800/60">
                    @forelse($agentsWithStats as $agent)
                        <tr class="transition hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-bold text-white">{{ $agent->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded {{ $agent->hasRole('Collectrice') ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                                    {{ $agent->getRoleNames()->first() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-right text-emerald-400">+{{ number_format($agent->total_depots, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 font-mono text-right text-rose-400">-{{ number_format($agent->total_retraits, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 font-mono font-bold text-right text-white">
                                {{ number_format($agent->total_depots - $agent->total_retraits, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-xs italic text-center text-slate-500">
                                Aucun agent de terrain affecté à cette zone pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODALE 1 : PARAMÈTRES (ÉDITION) -->
    <div x-show="openSettingsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="openSettingsModal = false" class="w-full max-w-lg p-6 space-y-6 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-base font-bold text-white">Modifier la Zone {{ $zone->code }}</h3>
                <button @click="openSettingsModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('admin.zones.update', $zone->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Code Zone</label>
                    <input type="text" name="code" value="{{ $zone->code }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Nom de la Zone</label>
                    <input type="text" name="name" value="{{ $zone->name }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Chef de Zone Responsable</label>
                    <select name="manager_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Aucun responsable --</option>
                        @foreach($potentialManagers as $manager)
                            <option value="{{ $manager->id }}" {{ $zone->manager_id == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end pt-4 space-x-3 border-t border-slate-800">
                    <button type="button" @click="openSettingsModal = false" class="px-4 py-2 text-xs font-bold bg-slate-800 text-slate-300 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-black bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALE 2 : CONFIRMATION DE SUPPRESSION -->
    <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="openDeleteModal = false" class="w-full max-w-md p-6 space-y-6 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="space-y-3 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto text-xl border rounded-full bg-rose-500/10 text-rose-500 border-rose-500/20">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h3 class="text-base font-bold text-white">Supprimer cette Zone ?</h3>
                <p class="text-xs text-slate-400">
                    Êtes-vous sûr de vouloir supprimer définitivement la zone <strong>{{ $zone->name }}</strong> ?
                    Les agents et clients rattachés à cette zone ne seront pas supprimés, mais ne seront plus sectorisés.
                </p>
            </div>

            <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" class="flex items-center justify-center space-x-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="openDeleteModal = false" class="w-1/2 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold">Annuler</button>
                <button type="submit" class="w-1/2 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-500/20">Supprimer</button>
            </form>
        </div>
    </div>

</div>
@endsection
