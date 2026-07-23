<!-- Modale Alpine.js -->
<div x-show="openZoneModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
     x-cloak>

    <!-- Carte de la Modale -->
    <div @click.away="openZoneModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="w-full max-w-lg overflow-hidden border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

        <!-- En-tête de la Modale -->
        <div class="flex items-center justify-between p-5 border-b border-slate-800 bg-slate-950/40">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold border rounded-lg bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-wider text-white uppercase">Créer une nouvelle Zone</h3>
                    <p class="text-[11px] text-slate-400">Rattachement géographique à l'agence</p>
                </div>
            </div>
            <button @click="openZoneModal = false" class="transition text-slate-400 hover:text-white">
                <i class="text-xl bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('admin.zones.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Récupération dynamique de l'ID de l'agence sélectionnée -->
            <input type="hidden" name="structure_id" :value="selectedAgencyId">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Champ Code -->
                <div>
                    <label class="block mb-1 text-xs font-bold tracking-wider uppercase text-slate-300">Code Zone <span class="text-emerald-400">*</span></label>
                    <input type="text"
                           name="code"
                           required
                           placeholder="ex: ZN-AKW-01"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50 font-mono">
                </div>

                <!-- Champ Nom -->
                <div>
                    <label class="block mb-1 text-xs font-bold tracking-wider uppercase text-slate-300">Nom du Secteur <span class="text-emerald-400">*</span></label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="ex: Marché Central"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50">
                </div>
            </div>

            <!-- Sélection du Chef Commercial / Responsible Zone (Manager) -->
            <div>
                <label class="block mb-1 text-xs font-bold tracking-wider uppercase text-slate-300">Chef de Zone / Commercial (Manager)</label>
                <select name="manager_id"
                        class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500/50">
                    <option value="">-- Aucun Chef de Zone Assigné --</option>
                    @foreach($commercials ?? [] as $commercial)
                        <option value="{{ $commercial->id }}">{{ $commercial->name }} ({{ $commercial->getRoleNames()->first() ?? 'Chef Commercial' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Description -->
            <div>
                <label class="block mb-1 text-xs font-bold tracking-wider uppercase text-slate-300">Description / Périmètre</label>
                <textarea name="description"
                          rows="3"
                          placeholder="Précisez les limites de la zone (ex: Du carrefour X au marché Y)..."
                          class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50"></textarea>
            </div>

            <!-- Statut (Actif par défaut) -->
            <input type="hidden" name="is_active" value="1">

            <!-- Actions de bas de modale -->
            <div class="flex items-center justify-end pt-4 space-x-3 border-t border-slate-800">
                <button type="button"
                        @click="openZoneModal = false"
                        class="px-4 py-2 text-xs font-bold tracking-wider uppercase transition bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">
                    Annuler
                </button>
                <button type="submit"
                        class="flex items-center px-5 py-2 space-x-2 text-xs font-black tracking-wider uppercase transition shadow-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl shadow-emerald-500/20">
                    <i class="text-base bi bi-check-lg"></i>
                    <span>Enregistrer la Zone</span>
                </button>
            </div>
        </form>

    </div>
</div>
