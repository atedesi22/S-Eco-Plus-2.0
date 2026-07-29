@extends('layouts.app')

@section('content')
<div x-data="{
    showModal: false,
    assignmentType: 'agent',
    filterType: 'all',
    search: ''
}" class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Objectifs & Performance</h1>
            <p class="text-xs text-slate-400">Suivez la progression des objectifs individuels et d'équipe au sein de l'agence.</p>
        </div>
        <button @click="showModal = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition text-slate-950 rounded-xl bg-emerald-500 hover:bg-emerald-400">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nouvel Objectif</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Objectifs</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-target"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $totalObjectives }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Objectifs Atteints</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-emerald-400 bg-emerald-500/10">
                    <i class="text-lg bi bi-check-all"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $achievedCount }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">En Cours</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <i class="text-lg bi bi-hourglass-split"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $inProgressCount }}</p>
        </div>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 p-4 border sm:flex-row bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher par titre ou agent..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="flex items-center w-full gap-2 sm:w-auto">
            <select x-model="filterType" class="w-full px-4 py-2 text-sm text-white border sm:w-auto rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="all">Tous les types</option>
                <option value="collecte_amount">Collecte Financière</option>
                <option value="new_accounts">Création de Comptes</option>
                <option value="product_sales">Vente Boutique</option>
                <option value="credit_recovery">Recouvrement Crédit</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($objectives as $obj)
        @php
            $current = $obj->current_value;
            $target = $obj->target_value;
            $percentage = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
            $isMoney = in_array($obj->type, ['collecte_amount', 'credit_recovery']);
        @endphp
        <div x-show="(filterType === 'all' || '{{ $obj->type }}' === filterType) &&
                    (search === '' || '{{ strtolower($obj->title) }}'.includes(search.toLowerCase()) || '{{ strtolower($obj->user->name ?? $obj->role_name ?? '') }}'.includes(search.toLowerCase()))"
             class="p-5 space-y-4 transition border border-slate-800 bg-slate-900/60 rounded-2xl hover:border-slate-700">

            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400 px-2 py-0.5 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                        {{ strtoupper($obj->period) }}
                    </span>
                    <h3 class="mt-2 text-base font-bold text-white">{{ $obj->title }}</h3>
                </div>

                <form action="{{ route('directeur.performance.objectives.destroy', $obj) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="transition text-slate-500 hover:text-rose-400" title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>

            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <i class="bi bi-person-badge"></i>
                <span>Atribué à : </span>
                <strong class="text-slate-200">
                    {{ $obj->user->name ?? 'Équipe ' . ucfirst($obj->role_name) }}
                </strong>
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between font-mono text-xs">
                    <span class="text-slate-400">Progression</span>
                    <span class="font-bold text-white">{{ $percentage }}%</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-950 overflow-hidden border border-slate-800">
                    <div class="h-full transition-all duration-500 rounded-full {{ $percentage >= 100 ? 'bg-emerald-400' : 'bg-emerald-500' }}"
                         style="width: {{ $percentage }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400 font-mono pt-1">
                    <span>Actuel: <strong class="text-white">{{ $isMoney ? number_format($current, 0, ',', ' ') . ' XAF' : $current }}</strong></span>
                    <span>Cible: <strong class="text-emerald-400">{{ $isMoney ? number_format($target, 0, ',', ' ') . ' XAF' : $target }}</strong></span>
                </div>
            </div>

            <div class="pt-2 text-[10px] border-t border-slate-800/80 flex justify-between text-slate-500 font-mono">
                <span>Du {{ \Carbon\Carbon::parse($obj->start_date)->format('d/m/Y') }}</span>
                <span>Au {{ \Carbon\Carbon::parse($obj->end_date)->format('d/m/Y') }}</span>
            </div>
        </div>
        @empty
        <div class="p-8 text-center border col-span-full text-slate-500 border-slate-800 rounded-2xl bg-slate-900/40">
            Aucun objectif défini actuellement pour votre agence.
        </div>
        @endforelse
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-lg p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Assigner un Nouvel Objectif</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('directeur.performance.objectives.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Intitulé de l'objectif</label>
                    <input type="text" name="title" required placeholder="Ex: Collecte Mensuelle Tontine"
                           class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Type d'attribution</label>
                        <select x-model="assignmentType" name="assignment_type" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            <option value="agent">Agent individuel</option>
                            <option value="role">Équipe / Rôle</option>
                        </select>
                    </div>

                    <div x-show="assignmentType === 'agent'">
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Sélectionner l'agent</label>
                        <select name="user_id" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="assignmentType === 'role'" x-cloak>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Sélectionner le rôle</label>
                        <select name="role_name" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Type d'indicateur</label>
                        <select name="type" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            <option value="collecte_amount">Montant de collecte (XAF)</option>
                            <option value="new_accounts">Ouverture de comptes</option>
                            <option value="product_sales">Ventes Boutique</option>
                            <option value="credit_recovery">Recouvrement Crédits</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Valeur Cible</label>
                        <input type="number" name="target_value" required min="1" placeholder="Ex: 5000000"
                               class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Période</label>
                        <select name="period" required class="w-full px-3 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            <option value="daily">Journalier</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly" selected>Mensuel</option>
                            <option value="quarterly">Trimestriel</option>
                            <option value="yearly">Annuel</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Date Début</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Date Fin</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">Enregistrer L'Objectif</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
