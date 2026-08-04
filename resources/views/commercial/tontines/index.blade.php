@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-6 bg-slate-950 text-slate-100" x-data="{ subscribeModal: false, selectedType: null, defaultAmount: 1000 }">

    <!-- Flash Message -->
    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl">
            <span><i class="mr-2 bi bi-check-circle-fill"></i> {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <!-- En-tête -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="bi bi-pie-chart-fill text-amber-400"></i> Offres & Souscriptions Tontines
            </h1>
            <p class="text-xs text-slate-400">Proposez et souscrivez vos clients aux différents plans d'épargne et tontines disponibles.</p>
        </div>
    </div>

    <!-- CATALOGUE DES OFFRES TONTINE (Cartes d'offres) -->
    <div class="space-y-3">
        <h2 class="text-xs font-bold tracking-wider uppercase text-slate-400">Plans Tontine Disponibles au Catalogue</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @forelse($tontineTypes as $type)
                <div class="flex flex-col justify-between p-5 space-y-4 transition border bg-slate-900/80 border-slate-800 hover:border-amber-500/50 rounded-2xl">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between">
                            <h3 class="text-sm font-bold text-white">{{ $type->name }}</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                {{ $type->duration_days ?? '31' }} jours
                            </span>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-400">{{ $type->description ?? 'Plan de cotisation journalière standard pour commerçants et particuliers.' }}</p>
                    </div>

                    <div class="pt-3 space-y-3 border-t border-slate-800/80">
                        <div class="flex justify-between font-mono text-xs">
                            <span class="text-slate-500">Mise Recommandée :</span>
                            <strong class="text-emerald-400">{{ number_format($type->daily_amount ?? 1000, 0, ',', ' ') }} XAF/j</strong>
                        </div>

                        <button @click="selectedType = {{ json_encode($type) }}; defaultAmount = {{ $type->daily_amount ?? 1000 }}; subscribeModal = true" class="flex items-center justify-center w-full gap-2 py-2 text-xs font-bold transition text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-xl">
                            <i class="bi bi-plus-circle-fill"></i> Souscrire un Client
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6 text-xs italic text-center border border-dashed col-span-full text-slate-500 border-slate-800 rounded-2xl">
                    Aucun plan de tontine configuré dans le système.
                </div>
            @endforelse
        </div>
    </div>

    <!-- HISTORIQUE DES SOUSCRIPTIONS RECENTES INITIÉES -->
    <div class="pt-4 space-y-3">
        <h2 class="text-xs font-bold tracking-wider uppercase text-slate-400">Dernières Souscriptions Effectuées</h2>

        <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Client</th>
                        <th class="p-3">Intitulé Tontine</th>
                        <th class="p-3">Mise Journalière</th>
                        <th class="p-3">Date Souscription</th>
                        <th class="p-3 text-right">Fiche</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($subscriptions as $sub)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="p-3 font-bold text-white">
                                {{ $sub->account->user->name ?? 'N/A' }}
                                <div class="font-mono text-[10px] font-normal text-slate-500">{{ $sub->account->user->phone ?? '' }}</div>
                            </td>
                            <td class="p-3 font-medium text-amber-300">{{ $sub->name }}</td>
                            <td class="p-3 font-mono font-bold text-emerald-400">{{ number_format($sub->daily_amount, 0, ',', ' ') }} XAF</td>
                            <td class="p-3 font-mono text-slate-400">{{ $sub->created_at->format('d/m/Y') }}</td>
                            <td class="p-3 text-right">
                                @if(isset($sub->account->user->id))
                                    <a href="{{ route('commercial.clients.show', $sub->account->user->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold rounded-lg transition">
                                        Voir Client
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 italic text-center text-slate-500">Aucune souscription enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODALE DE SOUSCRIPTION D'UN CLIENT -->
    <div x-show="subscribeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.outside="subscribeModal = false" class="w-full max-w-lg p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Nouvelle Souscription Tontine</h3>
                <button @click="subscribeModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('commercial.tontines.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="tontine_type_id" :value="selectedType?.id">

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Sélectionner le Client *</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                        <option value="">-- Choisir un client dans votre portefeuille --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Plan Choisis</label>
                    <input type="text" readonly :value="selectedType?.name" class="w-full px-3 py-2 text-xs font-bold border outline-none text-amber-400 bg-slate-950/60 border-slate-800 rounded-xl">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Nom Personnalisé du Sous-compte (Optionnel)</label>
                    <input type="text" name="custom_name" placeholder="Ex: Tontine Achat Camionette" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Mise Journalière Convenue (XAF) *</label>
                    <input type="number" name="daily_amount" x-model="defaultAmount" min="100" step="100" required class="w-full px-3 py-2 text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="subscribeModal = false" class="px-4 py-2 text-xs text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-xl">Valider la Souscription</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
