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

            <form action="{{ route('admin.structures.assign-director') }}" method="POST" class="space-y-4" x-data="{ targetType: 'regional_direction' }">
                @csrf

                <!-- 1. Sélection du Type de Structure -->
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Type de structure à pourvoir</label>
                    <select x-model="targetType" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="regional_direction">Direction Régionale</option>
                        <option value="agency">Agence</option>
                    </select>
                </div>

                <!-- 2. Sélection du Cadre Disponible -->
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Cadre Disponible</label>

                    <!-- Select DR (Actif UNIQUEMENT si Direction Régionale) -->
                    <div x-show="targetType === 'regional_direction'">
                        <select name="user_id"
                                :disabled="targetType !== 'regional_direction'"
                                required
                                class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                            <option value="">-- Choisir un Directeur Régional en attente --</option>
                            @foreach($availableRegionalDirectors as $drUser)
                                <option value="{{ $drUser->id }}">{{ $drUser->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Agence (Actif UNIQUEMENT si Agence) -->
                    <div x-show="targetType === 'agency'" x-cloak>
                        <select name="user_id"
                                :disabled="targetType !== 'agency'"
                                required
                                class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                            <option value="">-- Choisir un Directeur d'Agence en attente --</option>
                            @foreach($availableAgencyDirectors as $daUser)
                                <option value="{{ $daUser->id }}">{{ $daUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Sélection du Bureau Cible (Filtré dynamiquement selon le Type !) -->
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Bureau Cible</label>
                    <select name="structure_id" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Sélectionner l'infrastructure cible --</option>
                        @foreach($structures as $struct)
                            {{-- Affiche uniquement les structures qui correspondent au type sélectionné --}}
                            <option value="{{ $struct->id }}" x-show="targetType === '{{ $struct->type }}'">
                                {{ $struct->name }} ({{ $struct->type === 'regional_direction' ? 'Région' : 'Agence' }})
                            </option>
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
                        <!-- Nom de la Structure -->
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

                        <!-- Structure Parente -->
                        <td class="py-3 font-medium text-slate-300">
                            {{ $structure->parent->name ?? 'Direction Générale (Holding)' }}
                        </td>

                        <!-- Responsable / Directeur -->
                        <td class="py-3 italic">
                            @if($structure->director)
                                <span class="not-italic font-bold text-white">
                                    <i class="mr-1 bi bi-person-check-fill text-emerald-400"></i>{{ $structure->director->name }}
                                </span>
                            @else
                                <span class="text-rose-500/70 font-bold text-[10px] uppercase tracking-wide bg-rose-500/5 border border-rose-500/10 px-2 py-0.5 rounded">
                                    Poste Vacant
                                </span>
                            @endif
                        </td>

                        <!-- Nombre d'agents -->
                        <td class="py-3 font-mono font-bold text-center text-slate-300">
                            {{ $structure->users_count ?? $structure->users->count() }} agents
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

    <div x-data="{
        search: '',
        typeFilter: 'all',

        // Fonction de vérification si une agence ou ses zones correspondent à la recherche
        matchesAgency(agencyName, directorName, zones) {
            if (!this.search.trim()) return true;
            const q = this.search.toLowerCase();

            // Match sur le nom d'agence ou le directeur
            const agencyMatch = agencyName.toLowerCase().includes(q) || (directorName && directorName.toLowerCase().includes(q));
            if (agencyMatch) return true;

            // Match sur le code ou le nom d'une des zones rattachées
            return zones.some(z =>
                z.name.toLowerCase().includes(q) ||
                z.code.toLowerCase().includes(q) ||
                (z.manager_name && z.manager_name.toLowerCase().includes(q))
            );
        },

        matchesZone(zone) {
            if (!this.search.trim()) return true;
            const q = this.search.toLowerCase();
            return zone.name.toLowerCase().includes(q) ||
                zone.code.toLowerCase().includes(q) ||
                (zone.manager_name && zone.manager_name.toLowerCase().includes(q));
        }
    }" class="space-y-6">

        <!-- ========================================== -->
        <!-- BARRE DE RECHERCHE & FILTRES EN TEMPS RÉEL -->
        <!-- ========================================== -->
        <div class="flex flex-col items-center justify-between gap-4 p-4 border bg-slate-900 border-slate-800 rounded-2xl md:flex-row">

            <!-- Champ de Recherche Principal -->
            <div class="relative w-full md:w-1/2">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500">
                    <i class="text-sm bi bi-search"></i>
                </span>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Rechercher une agence, un code zone (ex: ZN-AKW), un chef de zone..."
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-10 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition"
                >
                <!-- Bouton pour effacer rapidement la recherche -->
                <button
                    x-show="search.length > 0"
                    @click="search = ''"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-white"
                >
                    <i class="text-xs bi bi-x-circle-fill"></i>
                </button>
            </div>

            <!-- Filtre par Type & Compteur Réactif -->
            <div class="flex items-center justify-between w-full space-x-3 md:w-auto md:justify-end">
                <!-- Select Type de Structure -->
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-slate-400">Type :</label>
                    <select
                        x-model="typeFilter"
                        class="px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500"
                    >
                        <option value="all">Toutes les structures</option>
                        <option value="regional_direction">Directions Régionales</option>
                        <option value="agency">Agences Uniquement</option>
                    </select>
                </div>

                <!-- Badge d'information -->
                <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-mono rounded-lg">
                    Filtre actif
                </span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- LISTE DES AGENCES ET LEURS ZONES DE COLLECTE -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 gap-6">
            @foreach($structures as $agency)
                @php
                    // Préparation des données JSON des zones pour Alpine.js
                    $zonesData = $agency->zones->map(function($z) {
                        return [
                            'id' => $z->id,
                            'name' => $z->name,
                            'code' => $z->code,
                            'manager_name' => $z->manager->name ?? ''
                        ];
                    });
                @endphp

                <!-- Carte Agence (Masquée si elle ne correspond pas au filtre) -->
                <div
                    x-show="(typeFilter === 'all' || typeFilter === '{{ $agency->type }}') && matchesAgency('{{ addslashes($agency->name) }}', '{{ addslashes($agency->director->name ?? '') }}', {{ json_encode($zonesData) }})"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="p-6 space-y-6 border bg-slate-900 border-slate-800 rounded-2xl"
                >
                    <!-- En-tête Agence -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 font-bold border rounded-xl bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                <i class="text-xl bi bi-bank"></i>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white">{{ $agency->name }}</h3>
                                    <span class="px-2 py-0.5 bg-slate-800 text-slate-400 rounded text-[10px] uppercase font-mono font-bold">
                                        {{ $agency->type === 'regional_direction' ? 'Dir. Régionale' : 'Agence' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">Directeur : <strong class="text-slate-200">{{ $agency->director->name ?? 'Non assigné' }}</strong></p>
                            </div>
                        </div>

                        @hasanyrole('SuperAdmin|Directeur Agence')

                            <!-- Bouton Créer une zone -->
                            <button @click="openZoneModal = true; selectedAgency = {{ $agency->id }}" class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                                <i class="bi bi-plus-lg"></i>
                                <span>Nouvelle Zone</span>
                            </button>
                        @endhasanyrole
                    </div>

                    <!-- Grille des Zones de cette Agence -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @forelse($agency->zones as $zone)
                            <!-- Carte Zone (Mise en valeur si elle matche directement la recherche) -->
                            <div
                                x-show="matchesZone({ name: '{{ addslashes($zone->name) }}', code: '{{ addslashes($zone->code) }}', manager_name: '{{ addslashes($zone->manager->name ?? '') }}' })"
                                class="p-4 space-y-3 transition border bg-slate-950 border-slate-800/80 rounded-xl hover:border-emerald-500/40 group"
                            >
                                <a href="{{ route('admin.zones.show', $zone->id) }}" class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 bg-slate-800 text-slate-300 group-hover:bg-emerald-500/20 group-hover:text-emerald-400 rounded text-[10px] font-mono font-bold transition">
                                            {{ $zone->code }}
                                        </span>
                                        <h4 class="text-sm font-bold text-white transition group-hover:text-emerald-400">{{ $zone->name }}</h4>
                                    </div>
                                    <i class="text-xs transition bi bi-chevron-right text-slate-500 group-hover:text-emerald-400"></i>
                                </a>

                                <!-- Infos synthétiques -->
                                <div class="text-xs space-y-1.5 text-slate-400">
                                    <p class="flex justify-between">
                                        <span>Chef de zone :</span>
                                        <strong class="text-white">{{ $zone->manager->name ?? 'Non assigné' }}</strong>
                                    </p>
                                    <p class="flex justify-between">
                                        <span>Agents terrain :</span>
                                        <strong class="font-mono text-emerald-400">{{ $zone->agents->count() }} agents</strong>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-xs italic text-center border border-dashed col-span-full text-slate-500 bg-slate-950/40 rounded-xl border-slate-800">
                                Aucune zone de collecte configurée.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
