<div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-3">
    <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
        <div>
            <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Volume Épargne Agence</p>
            <h3 class="mt-2 text-2xl font-bold text-white">{{ number_format($totalBalance ?? 50000) }} XAF</h3>
        </div>
        <div class="flex items-center justify-center w-12 h-12 bg-emerald-500/10 rounded-xl text-emerald-400">
            <i class="text-xl bi bi-cash-stack"></i>
        </div>
    </div>

    <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
        <div>
            <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Fonds de Réserve (Garanties)</p>
            <h3 class="mt-2 text-2xl font-bold text-amber-400">{{ number_format($totalReserve ?? 1000) }} XAF</h3>
        </div>
        <div class="flex items-center justify-center w-12 h-12 bg-amber-500/10 rounded-xl text-amber-400">
            <i class="text-xl bi bi-shield-shaded"></i>
        </div>
    </div>
</div>

<div class="p-6 mt-6 border bg-slate-900 border-slate-800 rounded-2xl">
    <h5 class="mb-4 font-bold text-white">Guichet de Test - Retrait Express (Palier 25 000 XAF)</h5>

    <form action="#" method="POST" class="max-w-md space-y-4">
        @csrf
        <div>
            <label class="block mb-2 text-xs text-slate-400">Sélectionner le compte client du seeder</label>
            <select name="account_id" class="w-full px-4 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
                <option value="1">Mamadou Diallo (SEP-TONT-2026-889) - Dispo: 49 000 XAF</option>
            </select>
        </div>
        <div>
            <label class="block mb-2 text-xs text-slate-400">Montant du Retrait (XAF)</label>
            <input type="number" name="amount" placeholder="Ex: 10000" class="w-full px-4 py-2 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500">
        </div>
        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-xs font-bold px-4 py-2.5 rounded-xl transition">
            Valider le Retrait au Guichet
        </button>
    </form>
</div>
