@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6 space-y-6 bg-slate-950 text-slate-100 font-sans" x-data="{ editModal: false, selectedProspect: null }">

    @if(session('success'))
        <div class="p-4 text-xs font-bold border text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-xl">
            <i class="bi bi-check-circle-fill mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-person-plus-fill text-emerald-400"></i> Prospection Terrain & Lead Management
            </h1>
            <p class="text-xs text-slate-400">Suivez vos prospects enregistrés sur le terrain et convertissez-les en clients.</p>
        </div>

        <a href="{{ route('commercial.prospects.create') }}" class="px-4 py-2.5 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl transition shadow-lg shadow-emerald-500/10 flex items-center gap-2 w-fit">
            <i class="bi bi-plus-circle-fill text-sm"></i> Nouveau Prospect
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Prospects</span>
            <div class="text-xl font-mono font-bold text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nouveaux</span>
            <div class="text-xl font-mono font-bold text-cyan-400 mt-1">{{ $stats['nouveaux'] }}</div>
        </div>
        <div class="p-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">En Discussion</span>
            <div class="text-xl font-mono font-bold text-amber-400 mt-1">{{ $stats['en_cours'] }}</div>
        </div>
        <div class="p-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Convertis</span>
            <div class="text-xl font-mono font-bold text-emerald-400 mt-1">{{ $stats['convertis'] }}</div>
        </div>
    </div>

    <div class="p-4 border bg-slate-900 border-slate-800 rounded-2xl flex flex-col md:flex-row gap-3 justify-between items-center">
        <form action="{{ route('commercial.prospects.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher nom, tel, secteur, lieu..." class="px-3 py-2 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl outline-none focus:border-emerald-500 flex-1">

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                <option value="">Tous les statuts</option>
                <option value="nouveau" {{ request('status') === 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                <option value="en_discussion" {{ request('status') === 'en_discussion' ? 'selected' : '' }}>En discussion</option>
                <option value="converti" {{ request('status') === 'converti' ? 'selected' : '' }}>Converti</option>
                <option value="abandonne" {{ request('status') === 'abandonne' ? 'selected' : '' }}>Abandonné</option>
            </select>

            <button type="submit" class="px-4 py-2 text-xs bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 font-semibold">
                Filtrer
            </button>
        </form>
    </div>

    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Prospect</th>
                        <th class="p-3">Activité / Localisation</th>
                        <th class="p-3">Intérêt & Budget</th>
                        <th class="p-3">Prochaine Relance</th>
                        <th class="p-3">Statut</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($prospects as $prospect)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-3">
                                <div class="font-bold text-white">{{ $prospect->full_name }}</div>
                                <div class="font-mono text-[11px] text-slate-400">{{ $prospect->phone }}</div>
                            </td>
                            <td class="p-3">
                                <div class="text-slate-200">{{ $prospect->activity_sector ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $prospect->location ?? 'Non précisé' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg border bg-slate-800 text-emerald-300 border-slate-700">
                                    {{ ucfirst($prospect->interest_type) }}
                                </span>
                                <div class="text-[11px] font-mono text-slate-400 mt-1">
                                    {{ number_format($prospect->estimated_budget, 0, ',', ' ') }} XAF
                                </div>
                            </td>
                            <td class="p-3 font-mono text-[11px]">
                                @if($prospect->next_contact_at)
                                    <span class="{{ $prospect->next_contact_at->isPast() ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                        {{ $prospect->next_contact_at->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="text-slate-500 italic">Non planifiée</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @switch($prospect->status)
                                    @case('nouveau')
                                        <span class="px-2.5 py-1 text-[10px] font-bold text-cyan-400 bg-cyan-950/40 border border-cyan-800/60 rounded-full">Nouveau</span>
                                        @break
                                    @case('en_discussion')
                                        <span class="px-2.5 py-1 text-[10px] font-bold text-amber-400 bg-amber-950/40 border border-amber-800/60 rounded-full">En discussion</span>
                                        @break
                                    @case('converti')
                                        <span class="px-2.5 py-1 text-[10px] font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/60 rounded-full">Converti</span>
                                        @break
                                    @default
                                        <span class="px-2.5 py-1 text-[10px] font-bold text-slate-400 bg-slate-800 border border-slate-700 rounded-full">Abandonné</span>
                                @endswitch
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <a href="tel:{{ $prospect->phone }}" class="px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-emerald-400 rounded-lg transition">
                                    <i class="bi bi-telephone-fill"></i>
                                </a>

                                <button @click="selectedProspect = {{ json_encode($prospect) }}; editModal = true" class="px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg transition">
                                    Suivi / Éditer
                                </button>

                                @if($prospect->status !== 'converti')
                                    <a href="{{ route('commercial.clients.create', ['from_prospect' => $prospect->id, 'name' => $prospect->full_name, 'phone' => $prospect->phone]) }}" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 rounded-lg transition">
                                        Convertir
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500 italic">Aucun prospect trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $prospects->links() }}
        </div>
    </div>

    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.outside="editModal = false" class="w-full max-w-lg p-6 bg-slate-900 border border-slate-800 rounded-2xl space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Suivi Prospect : <span x-text="selectedProspect?.full_name"></span></h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <template x-if="selectedProspect">
                <form :action="'/commercial/prospects/' + selectedProspect.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Statut de la prospection</label>
                        <select name="status" x-model="selectedProspect.status" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                            <option value="nouveau">Nouveau</option>
                            <option value="en_discussion">En discussion</option>
                            <option value="converti">Converti</option>
                            <option value="abandonne">Abandonné</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Date/Heure de Prochaine Relance</label>
                        <input type="datetime-local" name="next_contact_at" :value="selectedProspect.next_contact_at ? selectedProspect.next_contact_at.replace(' ', 'T').substring(0, 16) : ''" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Notes / Remarques Terrain</label>
                        <textarea name="notes" rows="3" x-model="selectedProspect.notes" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500" placeholder="Observation sur les attentes du prospect..."></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-xs text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl">Annuler</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl">Enregistrer</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
