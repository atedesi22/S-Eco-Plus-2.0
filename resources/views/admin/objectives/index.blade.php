@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300" x-data="{ openModal: false, openSanctionModal: false, activeObjectiveId: null }">

    <!-- En-tête -->
    <div class="flex flex-col gap-4 pb-5 border-b md:flex-row md:items-center md:justify-between border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-wide text-white">OBJECTIFS STRATÉGIQUES & SANCTIONS</h1>
            <p class="text-xs text-slate-400">Gérer le rendement du personnel, attribuer les cibles commerciales et consigner les mesures disciplinaires</p>
        </div>
        <button @click="openModal = true" class="px-4 py-2 text-xs font-bold tracking-wider uppercase transition bg-emerald-500 text-slate-950 rounded-xl hover:bg-emerald-400">
            <i class="mr-2 bi bi-plus-lg"></i> Fixer un Objectif
        </button>
    </div>

    <!-- Filtres -->
    <div class="flex items-center p-2 space-x-2 border bg-slate-950 rounded-xl border-slate-800 w-fit">
        <a href="{{ route('admin.objectives.index') }}" class="px-3 py-1 text-xs rounded-lg font-medium transition {{ !$status ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">Tous</a>
        <a href="{{ route('admin.objectives.index', ['status' => 'in_progress']) }}" class="px-3 py-1 text-xs rounded-lg font-medium transition {{ $status === 'in_progress' ? 'bg-amber-500/10 text-amber-400' : 'text-slate-400 hover:text-white' }}">En cours</a>
        <a href="{{ route('admin.objectives.index', ['status' => 'achieved']) }}" class="px-3 py-1 text-xs rounded-lg font-medium transition {{ $status === 'achieved' ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-400 hover:text-white' }}">Atteints</a>
        <a href="{{ route('admin.objectives.index', ['status' => 'failed']) }}" class="px-3 py-1 text-xs rounded-lg font-medium transition {{ $status === 'failed' ? 'bg-rose-500/10 text-rose-400' : 'text-slate-400 hover:text-white' }}">Échoués</a>
    </div>

    <!-- Table des Objectifs -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-950 border-b border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <th class="p-4">Collaborateur</th>
                    <th class="p-4">Objectif / Cible</th>
                    <th class="p-4">Période</th>
                    <th class="p-4">Progression</th>
                    <th class="p-4">Échéance</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-800">
                @forelse($objectives as $obj)
                    @php
                        $percent = $obj->target_value > 0 ? min(100, round(($obj->current_value / $obj->target_value) * 100)) : 0;
                    @endphp
                    <tr class="transition hover:bg-slate-950/40">
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $obj->user->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">ID: #{{ $obj->user_id }}</div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-200">{{ $obj->title }}</div>
                            <div class="font-mono text-xs text-slate-400">{{ number_format($obj->current_value) }} / {{ number_format($obj->target_value) }} ({{ $obj->type }})</div>
                        </td>
                        <td class="p-4">
                            <span class="text-[10px] font-bold uppercase tracking-wide bg-slate-800 px-2 py-0.5 rounded text-slate-400">{{ $obj->period }}</span>
                        </td>
                        <td class="w-1/5 p-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-full h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full transition-all bg-emerald-500" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="font-mono text-xs font-bold text-slate-400">{{ $percent }}%</span>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-xs">
                            {{ \Carbon\Carbon::parse($obj->end_date)->format('d/m/Y') }}
                        </td>
                        <td class="p-4">
                            @if($obj->status === 'in_progress')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-500/10 text-amber-400 uppercase">En cours</span>
                            @elseif($obj->status === 'achieved')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-400 uppercase">Atteint</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-rose-500/10 text-rose-400 uppercase">Échoué</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if($obj->status !== 'achieved')
                                <button @click="openSanctionModal = true; activeObjectiveId = {{ $obj->id }}" class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg transition">
                                    <i class="mr-1 bi bi-exclamation-triangle"></i> Sanctionner
                                </button>
                            @else
                                <span class="text-xs italic text-slate-500">Aucune action</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-xs italic text-center text-slate-500">Aucun objectif défini pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-800 bg-slate-950/20">
            {{ $objectives->links() }}
        </div>
    </div>

    <!-- MODAL 1 : CREATION OBJECTIF -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-xl" @click.away="openModal = false">
            <h3 class="text-base font-black tracking-wider text-white uppercase">Nouvel Objectif Métier</h3>
            <form action="{{ route('admin.objectives.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Assigner au personnel</label>
                    <select name="user_id" class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->getRoleNames()->first() ?? 'Agent' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Intitulé de l'objectif</label>
                    <input type="text" name="title" placeholder="Ex: Collecter 1 000 000 XAF de Tontines" required class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Type de Métrique</label>
                        <input type="text" name="type" placeholder="Ex: volume_tontine" required class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Valeur cible</label>
                        <input type="number" name="target_value" placeholder="1000000" required class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Fréquence / Période</label>
                    <select name="period" class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        <option value="day">Journalier</option>
                        <option value="week">Hebdomadaire</option>
                        <option value="month" selected>Mensuel</option>
                        <option value="quarter">Trimestriel</option>
                        <option value="semester">Semestriel</option>
                        <option value="year">Annuel</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date Début</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date Échéance</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex items-center justify-end pt-2 space-x-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-bold tracking-wider uppercase transition bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold tracking-wider uppercase transition bg-emerald-500 text-slate-950 rounded-xl hover:bg-emerald-400">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2 : ATTRIBUTION DE SANCTION -->
    <div x-show="openSanctionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-xl" @click.away="openSanctionModal = false">
            <h3 class="text-base font-black tracking-wider text-white uppercase text-rose-400">Émettre une mesure disciplinaire</h3>
            <form :action="'/admin/objectives/' + activeObjectiveId + '/sanction'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Motif de la sanction</label>
                    <textarea name="reason" placeholder="Spécifier le manquement ou l'infraction constatée..." required rows="3" class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-rose-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Gravité</label>
                        <select name="severity" class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-rose-500">
                            <option value="low">Faible (Avertissement)</option>
                            <option value="medium" selected>Modérée</option>
                            <option value="high">Critique (Mise à pied / Suspension)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pénalité Fin. (XAF)</label>
                        <input type="number" name="financial_penalty_amount" placeholder="0" class="w-full px-3 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-rose-500">
                    </div>
                </div>
                <div class="flex items-center justify-end pt-2 space-x-2">
                    <button type="button" @click="openSanctionModal = false; activeObjectiveId = null" class="px-4 py-2 text-xs font-bold tracking-wider uppercase transition bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">Fermer</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold tracking-wider text-white uppercase transition bg-rose-500 rounded-xl hover:bg-rose-400">Appliquer</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
