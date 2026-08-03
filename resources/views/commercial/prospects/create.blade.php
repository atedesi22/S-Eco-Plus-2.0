@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-8 space-y-6 bg-slate-950 text-slate-100 font-sans">

    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-white">Nouveau Prospect Terrain</h1>
            <p class="text-xs text-slate-400">Enregistrez un prospect rencontré lors de vos descentes terrain.</p>
        </div>
        <a href="{{ route('commercial.prospects.index') }}" class="px-4 py-2 text-xs font-bold text-slate-400 bg-slate-900 border border-slate-800 rounded-xl hover:bg-slate-800 transition">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <form action="{{ route('commercial.prospects.store') }}" method="POST" class="max-w-2xl space-y-6">
        @csrf

        <div class="p-6 border bg-slate-900/60 border-slate-800 rounded-2xl space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Nom Complet *</label>
                    <input type="text" name="full_name" required value="{{ old('full_name') }}" placeholder="Ex: Paul Atangana" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Téléphone *</label>
                    <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="Ex: 670000000" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Secteur d'Activité</label>
                    <input type="text" name="activity_sector" value="{{ old('activity_sector') }}" placeholder="Ex: Commerce de vêtements, Vendeur de fruits" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Localisation / Emplacement</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="Ex: Marché Mokolo, Allée 3" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Produit recherché / Intérêt *</label>
                    <select name="interest_type" required class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                        <option value="tontine">Tontine / Sous-compte</option>
                        <option value="epargne">Épargne Simple</option>
                        <option value="article">Achat d'Article / Électroménager</option>
                        <option value="credit">Demande de Crédit</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Budget Estimé / Cotisation visée (XAF)</label>
                    <input type="number" name="estimated_budget" value="{{ old('estimated_budget', 1000) }}" min="0" step="500" class="w-full px-3 py-2 text-xs font-bold text-emerald-400 border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Zone Commerciale</label>
                    <select name="zone_id" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                        <option value="">Zone par défaut</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Date de Relance Planifiée</label>
                    <input type="datetime-local" name="next_contact_at" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block mb-1 text-[11px] font-semibold text-slate-300">Remarques / Observations</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 text-xs text-white border bg-slate-950 border-slate-800 rounded-xl outline-none focus:border-emerald-500" placeholder="Notes de votre premier échange..."></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl transition shadow-lg shadow-emerald-500/10">
                Enregistrer le Prospect
            </button>
        </div>
    </form>
</div>
@endsection
