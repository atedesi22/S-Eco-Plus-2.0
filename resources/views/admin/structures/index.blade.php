@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">

    <!-- EN-TÊTE -->
    <div>
        <h1 class="text-2xl font-black tracking-wide text-white">MAILLAGE DES BUREAUX ET STRUCTURES</h1>
        <p class="text-xs text-slate-400">Déploiement géographique des Directions Régionales et affectation des lignes de commandement</p>
    </div>

    @if(auth()->user()->hasRole('SuperAdmin'))
    <!-- COMPOSANTS DE CONFIGURATION (Uniquement pour le SuperAdmin) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- FORMULAIRE 1 : INSERER UNE NOUVELLE DIRECTON OU AGENCE -->
        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl" x-data="{ type: 'regional_direction' }">
            <div class="flex items-center pb-3 space-x-2 border-b border-slate-800">
                <i class="text-lg bi bi-plus-circle text-emerald-400"></i>
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">Créer un Pôle Physique</h2>
            </div>

            <form action="{{ route('admin.structures.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nom Localisé de l'entité</label>
                    <input type="text" name="name" placeholder="Ex: DR Littoral ou Agence de Bafoussam" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Rang Structurel</label>
                    <select name="type" x-model="type" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="regional_direction">Direction Régionale (Niveau 1)</option>
                        <option value="agency">Agence Opérationnelle (Niveau 2)</option>
                    </select>
                </div>

                <!-- Apparaît uniquement si c'est une agence opérationnelle -->
                <div x-show="type === 'agency'" x-transition>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Direction Régionale de Rattachement</label>
                    <select name="parent_id" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Sélectionner le pôle régional --</option>
                        @foreach($regionalDirections as $rd)
                            <option value="{{ $rd->id }}">{{ $rd->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 rounded-lg transition duration-200">
                    Déployer la structure
                </button>
            </form>
        </div>

        <!-- FORMULAIRE 2 : ATTRIBUER UN DIRECTEUR A UNE ENTITÉ EXISTANTE -->
        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl" x-data="{ targetType: 'regional_direction' }">
            <div class="flex items-center pb-3 space-x-2 border-b border-slate-800">
                <i class="text-lg text-indigo-400 bi bi-person-badge"></i>
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">Affecter un Commandement (Directeur)</h2>
            </div>

            <form action="{{ route('admin.structures.assign-director') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Type de structure à pourvoir</label>
                    <select x-model="targetType" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="regional_direction">Direction Régionale</option>
                        <option value="agency">Agence</option>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Cadre Disponible</label>

                    <!-- Select DR -->
                    <select name="user_id" x-show="targetType === 'regional_direction'" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Choisir un Directeur Régional en attente --</option>
                        @foreach($availableRegionalDirectors as $drUser)
                            <option value="{{ $drUser->id }}">{{ $drUser->name }}</option>
                        @endforeach
                    </select>

                    <!-- Select Agence -->
                    <select name="user_id" x-show="targetType === 'agency'" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500" x-cloak>
                        <option value="">-- Choisir un Directeur d'Agence en attente --</option>
                        @foreach($availableAgencyDirectors as $daUser)
                            <option value="{{ $daUser->id }}">{{ $daUser->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Bureau Cible</label>
                    <select name="structure_id" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Sélectionner l'infrastructure cible --</option>
                        @foreach($structures as $struct)
                            <option value="{{ $struct->id }}">{{ $struct->name }} ({{ $struct->type === 'regional_direction' ? 'Région' : 'Agence' }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 rounded-lg transition duration-200">
                    Signer le décret d'affectation
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- LISTE GLOBALE ET MAPPING DES ORGANES PHYSIQUES -->
    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <h2 class="text-sm font-bold tracking-wider text-white uppercase">État Actuel de l'Infrastructure S Eco Plus</h2>
            <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded font-mono">Hierarchical Architecture Matrix</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Type / Libellé</th>
                        <th class="pb-3">Rattachement Supérieur</th>
                        <th class="pb-3">Commandement (Directeur)</th>
                        <th class="pb-3 text-center">Effectif Total Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($structures as $structure)
                    <tr>
                        <td class="flex items-center py-3 space-x-2 font-bold text-white">
                            @if($structure->type === 'regional_direction')
                                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                <span class="text-sky-400 font-mono text-[10px]">[DR]</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span class="text-amber-400 font-mono text-[10px]">[AG]</span>
                            @endif
                            <span>{{ $structure->name }}</span>
                        </td>
                        <td class="py-3 font-medium">
                            {{ $structure->parent ? $structure->parent->name : 'Direction Générale (Holding)' }}
                        </td>
                        <td class="py-3 italic">
                            @php
                                // Trouver le manager ayant le rôle adéquat rattaché à cette structure
                                $manager = $structure->users->filter(function($u) use ($structure) {
                                    return $structure->type === 'regional_direction'
                                        ? $u->hasRole('Directeur Regional')
                                        : $u->hasRole('Directeur Agence');
                                })->first();
                            @endphp

                            @if($manager)
                                <span class="not-italic font-bold text-white"><i class="mr-1 bi bi-person-check-fill text-emerald-400"></i>{{ $manager->name }}</span>
                            @else
                                <span class="text-rose-500/70 font-bold text-[10px] uppercase tracking-wide bg-rose-500/5 border border-rose-500/10 px-2 py-0.5 rounded">Poste Vacant</span>
                            @endif
                        </td>
                        <td class="py-3 font-mono font-bold text-center text-slate-300">
                            {{ $structure->users->count() }} agents
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 italic text-center text-slate-600">
                            Aucune entité physique (DR ou Agence) configurée pour le moment.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
