@extends('layouts.app')

@section('content')
<div x-data="{
    search: '',
    showCreateModal: false,
    showEditModal: false,
    showAssignModal: false,
    selectedZone: null,
    editData: { id: null, name: '', description: '', monthly_target: '' },
    assignData: { zone_id: null, manager_id: '' }
}" class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Zones de Collecte</h1>
            <p class="text-xs text-slate-400">Gérez les secteurs de collecte de l'agence et suivez les performances des agents sur le terrain.</p>
        </div>
        <button @click="showCreateModal = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition text-slate-950 rounded-xl bg-emerald-500 hover:bg-emerald-400">
            <i class="bi bi-geo-fill"></i>
            <span>Créer une Zone</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Zones</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-map"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ count($zones) }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Collecté Aujourd'hui</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-cash-stack"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalCollectedToday, 0, ',', ' ') }} <span class="text-xs text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Agents Affectés</span>
                <div class="flex items-center justify-center w-8 h-8 text-indigo-400 rounded-lg bg-indigo-500/10">
                    <i class="text-lg bi bi-person-badge"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">
                {{ $zones->where('manager', '!=', 'Non assigné')->count() }} / {{ count($zones) }}
            </p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Objectif Moyen</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <i class="text-lg bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">
                {{ round($zones->avg('completion_rate'), 1) }} %
            </p>
        </div>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 p-4 border sm:flex-row bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher une zone ou un agent..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>
    </div>

    <div class="overflow-hidden border border-slate-800 bg-slate-900/50 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-300">
                <thead class="font-mono text-xs uppercase border-b bg-slate-950 text-slate-400 border-slate-800">
                    <tr>
                        <th class="p-4">Zone</th>
                        <th class="p-4">Chef / Agent Affecté</th>
                        <th class="p-4">Clients Actifs</th>
                        <th class="p-4">Collecté Aujourd'hui</th>
                        <th class="p-4">Objectif Mensuel</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($zones as $zone)
                    <tr x-show="search === '' || '{{ strtolower($zone['name']) }}'.includes(search.toLowerCase()) || '{{ strtolower($zone['manager']) }}'.includes(search.toLowerCase())"
                        class="transition hover:bg-slate-800/40">
                        <td class="p-4 font-bold text-white">
                            <div class="flex items-center space-x-2">
                                <i class="bi bi-geo-alt-fill text-emerald-400"></i>
                                <div>
                                    <span class="block">{{ $zone['name'] }}</span>
                                    @if(!empty($zone['description']))
                                        <span class="block text-xs font-normal text-slate-500">{{ $zone['description'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($zone['manager'] !== 'Non assigné')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-200 border border-slate-700">
                                    <i class="bi bi-person-fill text-emerald-400 mr-1.5"></i> {{ $zone['manager'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    Non assigné
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-semibold text-slate-300">
                            {{ number_format($zone['active_clients'], 0, ',', ' ') }} clients
                        </td>
                        <td class="p-4 font-bold text-emerald-400">
                            {{ number_format($zone['collected_today'], 0, ',', ' ') }} XAF
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span class="text-slate-300">{{ number_format($zone['monthly_target'], 0, ',', ' ') }} XAF</span>
                                <span class="text-[10px] font-mono text-emerald-400">{{ $zone['completion_rate'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500"
                                    style="width: {{ min($zone['completion_rate'], 100) }}%"></div>
                            </div>
                        </td>
                        <td class="p-4 space-x-2 text-right">
                            <button @click="assignData.zone_id = {{ $zone['id'] }}; showAssignModal = true"
                                    class="p-2 transition text-slate-400 hover:text-emerald-400" title="Affecter un Agent">
                                <i class="text-base bi bi-person-plus"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">
                            Aucune zone de collecte trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 border shadow-xl bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="mb-4 text-lg font-bold text-white">Créer une nouvelle zone</h3>
            <form action="{{ route('directeur.zones.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Nom de la Zone</label>
                    <input type="text" name="name" required placeholder="ex: Zone Marché Central A"
                           class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Description</label>
                    <textarea name="description" rows="2" placeholder="Informations complémentaires ou secteur géographique..."
                              class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Chef de Zone / Collectrice (Optionnel)</label>
                    <select name="manager_id" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Aucun pour le moment --</option>
                        @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}">{{ $collector->name }} ({{ $collector->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Objectif Mensuel (XAF)</label>
                    <input type="number" name="monthly_target" step="1000" min="0" placeholder="5000000"
                           class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 border shadow-xl bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="mb-4 text-lg font-bold text-white">Affecter un Agent de Collecte</h3>
            <form action="{{ route('directeur.zones.assign') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="zone_id" :value="assignData.zone_id">
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Choisir l'agent / collectrice</label>
                    <select name="manager_id" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Sélectionner un Agent --</option>
                        @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}">{{ $collector->name }} ({{ $collector->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">Affecter</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
