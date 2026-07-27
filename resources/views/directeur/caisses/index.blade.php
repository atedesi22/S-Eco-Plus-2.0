@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, transferModal: false }">

    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-xl font-bold text-white">Gestion des Caisses & Coffres-Forts</h1>
            <p class="text-xs text-slate-400">Supervision des liquidités physiques, guichets et transferts de l'agence.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="transferModal = true" class="px-3.5 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="bi bi-arrow-left-right text-emerald-400"></i> Virement Inter-Caisse
            </button>
            <button @click="modalOpen = true" class="px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Ajouter Caisse / Guichet
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 text-xs font-bold border bg-emerald-500/10 border-emerald-500/20 rounded-xl text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 text-xs font-bold border bg-rose-500/10 border-rose-500/20 rounded-xl text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 space-y-1 text-xs font-bold border bg-rose-500/10 border-rose-500/20 rounded-xl text-rose-400">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs font-medium text-slate-400">Trésorerie Coffre-Fort</span>
            <p class="mt-2 text-2xl font-bold text-emerald-400">{{ number_format($soldeTotalCoffres, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 mt-2">Réserve principale de l'agence</p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs font-medium text-slate-400">Liquidité Guichets / Caisses</span>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($soldeTotalGuichets, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 mt-2">Fonds de roulement en guichets</p>
        </div>

        <div class="p-5 border bg-slate-900 border-slate-800 rounded-2xl">
            <span class="text-xs font-medium text-slate-400">Total Liquidité Agence</span>
            <p class="mt-2 text-2xl font-bold text-indigo-400">{{ number_format($soldeTotalCoffres + $soldeTotalGuichets, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 mt-2">{{ $caisses->count() }} points d'encaisse actifs</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($caisses as $caisse)
            <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border
                            {{ $caisse->type === 'coffre_fort' ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                            {{ str_replace('_', ' ', $caisse->type) }}
                        </span>
                        <h3 class="mt-1 text-base font-bold text-white">{{ $caisse->name }}</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border
                        {{ $caisse->status === 'open' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                        {{ $caisse->status === 'open' ? 'Ouverte' : 'Fermée' }}
                    </span>
                </div>

                <div class="space-y-1">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Solde actuel / Ouverture</p>
                    <p class="text-xl font-bold text-emerald-400">{{ number_format($caisse->current_balance, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
                    <div class="flex justify-between text-[10px] text-slate-500 pt-1">
                        <span>Solde initial : {{ number_format($caisse->opening_balance, 0, ',', ' ') }} XAF</span>
                        <span>Plafond : {{ number_format($caisse->max_limit, 0, ',', ' ') }} XAF</span>
                    </div>
                </div>

                <form action="{{ route('directeur.caisses.assign', $caisse->id) }}" method="POST" class="pt-3 space-y-2 border-t border-slate-800">
                    @csrf
                    <label class="text-[10px] text-slate-400 block font-semibold">Caissier Affecté :</label>
                    <div class="flex gap-2">
                        <select name="assigned_to" class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-2.5 py-1.5 focus:outline-none focus:border-emerald-500">
                            <option value="">-- Non assigné --</option>
                            @foreach($caissiers as $agent)
                                <option value="{{ $agent->id }}" {{ $caisse->assigned_to == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-white rounded-xl border border-slate-700 transition">
                            OK
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="col-span-3 p-8 text-xs text-center border text-slate-500 bg-slate-900 border-slate-800 rounded-2xl">
                Aucune caisse enregistrée pour le moment.
            </div>
        @endforelse
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Créer un nouveau Point d'Encaisse</h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('directeur.caisses.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Nom du Guichet / Coffre</label>
                    <input type="text" name="name" required placeholder="Ex: Guichet 03 / Coffre Principal" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Type de Caisse</label>
                    <select name="type" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        <option value="guichet">Guichet Caissier</option>
                        <option value="coffre_fort">Coffre-Fort Principal</option>
                        <option value="virtuelle">Caisse Virtuelle / Mobile</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Assigner à un caissier (Optionnel)</label>
                    <select name="assigned_to" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        <option value="">-- Aucun --</option>
                        @foreach($caissiers as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 text-xs text-slate-400">Solde Initial (XAF)</label>
                        <input type="number" name="opening_balance" value="0" min="0" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block mb-1 text-xs text-slate-400">Plafond Max (XAF)</label>
                        <input type="number" name="max_limit" value="5000000" min="0" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">État de la Caisse</label>
                    <select name="status" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        <option value="open">Ouverte</option>
                        <option value="closed" selected>Fermée</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white transition bg-emerald-600 hover:bg-emerald-500 rounded-xl">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="transferModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm" x-cloak>
        <div class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Virement Inter-Caisses / Approvisionnement</h3>
                <button @click="transferModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('directeur.caisses.transfer') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs text-slate-400">Caisse Source (Débit)</label>
                    <select name="from_caisse_id" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Solde: {{ number_format($c->current_balance, 0, ',', ' ') }} XAF)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Caisse Destination (Crédit)</label>
                    <select name="to_caisse_id" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-xs text-slate-400">Montant du virement (XAF)</label>
                    <input type="number" name="amount" min="1000" placeholder="Ex: 500000" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="transferModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white transition bg-indigo-600 hover:bg-indigo-500 rounded-xl">Exécuter le Virement</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
