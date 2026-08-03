@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-8 bg-slate-950 text-slate-100">

    <!-- En-tête -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white">Ouverture de Compte Client</h1>
            <p class="text-xs text-slate-400">Enregistrement client et souscription au sous-compte tontine</p>
        </div>
        <a href="{{ route('commercial.clients.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-bold transition border text-slate-400 bg-slate-900 border-slate-800 rounded-xl hover:bg-slate-800">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 text-xs font-semibold border text-rose-400 bg-rose-950/40 border-rose-800/60 rounded-2xl">
            <ul class="space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('commercial.clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Informatoins Personnelles (2 colonnes) -->
            <div class="space-y-6 lg:col-span-2">
                <div class="p-6 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
                    <h2 class="flex items-center gap-2 pb-3 text-sm font-bold text-white border-b border-slate-800">
                        <i class="bi bi-person-vcard text-emerald-400"></i> Informations Personnelles Client
                    </h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Nom Complet *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Jean Paul Kouam" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Numéro de Téléphone *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="Ex: 699000111" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Adresse Email (Optionnelle)</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="client@exemple.com" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Mot de Passe par défaut *</label>
                            <input type="password" name="password" required value="password123" class="w-full px-3 py-2 font-mono text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Photo de profil / Pièce d'identité (Optionnelle)</label>
                        <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs border text-slate-400 border-slate-800 rounded-xl bg-slate-950 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700">
                    </div>
                </div>

                <!-- Attribution Tontine Initiale (Table SubAccount) -->
                <div class="p-6 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
                    <h2 class="flex items-center gap-2 pb-3 text-sm font-bold text-white border-b border-slate-800">
                        <i class="bi bi-pie-chart-fill text-amber-400"></i> Activation du Sous-compte Tontine (SubAccount)
                    </h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Sélection du modèle de tontine -->
                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Modèle / Type de Tontine *</label>
                            <select name="tontine_type_id" id="tontine_type_select" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                                <option value="" data-amount="">-- Choisir un type de tontine --</option>
                                @foreach($tontineTypes as $type)
                                    @php
                                        // Récupération sécurisée du montant par défaut
                                        $amount = $type->default_amount ?? $type->daily_amount ?? null;
                                    @endphp
                                    <option value="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-amount="{{ $amount ?? 1000 }}"
                                            {{ old('tontine_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                        @if(null !== $amount)
                                            ({{ number_format($amount, 0, ',', ' ') }} XAF/jour)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nom / Intitulé du sous-compte généré ou personnalisé -->
                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Intitulé du Sous-compte (SubAccount)</label>
                            <input type="text" name="tontine_name" id="tontine_name" value="{{ old('tontine_name') }}" placeholder="Ex: Tontine Journalière Marché" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        </div>

                        <!-- Cotisation journalière pré-remplie mais modifiable -->
                        <div>
                            <label class="block mb-1 text-[11px] font-semibold text-slate-300">Somme Déposée / Dépôt Initial (XAF) *</label>
                            <input type="number"
                                min="1000"
                                step="100"
                                name="initial_deposit"
                                value="{{ old('initial_deposit', 0) }}"
                                placeholder="Ex: 5000"
                                required
                                class="w-full px-3 py-2 text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                            <p class="mt-1 text-[10px] text-slate-500">Ce montant servira de solde de départ pour la tontine.</p>
                        </div>
                    </div>
                </div>

                <!-- Script d'auto-remplissage selon la tontine sélectionnée -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const select = document.getElementById('tontine_type_select');
                        const nameInput = document.getElementById('tontine_name');
                        const amountInput = document.getElementById('daily_amount');

                        select.addEventListener('change', function () {
                            const selectedOption = this.options[this.selectedIndex];
                            const defaultAmount = selectedOption.getAttribute('data-amount');
                            const defaultName = selectedOption.getAttribute('data-name');

                            if (defaultName) {
                                if (!nameInput.value || nameInput.dataset.autoFilled === 'true') {
                                    nameInput.value = defaultName;
                                    nameInput.dataset.autoFilled = 'true';
                                }
                            }

                            if (defaultAmount) {
                                amountInput.value = defaultAmount;
                            }
                        });

                        nameInput.addEventListener('input', function() {
                            this.dataset.autoFilled = 'false';
                        });
                    });
                </script>
            </div>

            <!-- Affectation & Zone -->
            <div class="p-6 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl h-fit">
                <h2 class="flex items-center gap-2 pb-3 text-sm font-bold text-white border-b border-slate-800">
                    <i class="bi bi-geo-alt text-cyan-400"></i> Zone & Collectrice
                </h2>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Collectrice Assignée</label>
                    <select name="collector_id" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="">-- Sélectionner la collectrice --</option>
                        @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}" {{ old('collector_id') == $collector->id ? 'selected' : '' }}>
                                {{ $collector->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Agence attribuée</label>
                    <!-- Champ affiché en lecture seule -->
                    <input type="text"
                        value="{{ $commercial->structure->name ?? 'Aucune agence rattachée' }}"
                        disabled
                        class="w-full px-3 py-2 text-xs border cursor-not-allowed text-slate-400 bg-slate-900 border-slate-800 rounded-xl">

                    <!-- Champ caché envoyé au formulaire -->
                    <input type="hidden" name="agency_id" value="{{ $commercial->structure_id }}">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Zone Commerciale</label>
                    <select name="zone_id" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="">-- Zone par défaut --</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Statut initial *</label>
                    <select name="status" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="active" selected>Actif</option>
                        <option value="pending">En attente</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl transition shadow-lg shadow-emerald-500/10 flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Créer Compte & Sous-compte
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
