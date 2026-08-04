@extends('layouts.app')

@section('content')
<div class="min-h-screen p-6 space-y-6 bg-slate-950 text-slate-100">

    @if(session('success'))
        <div class="p-4 border bg-emerald-500/10 border-emerald-500/30 text-emerald-400 rounded-xl">
            <i class="mr-2 bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 border bg-rose-500/10 border-rose-500/30 text-rose-400 rounded-xl">
            <i class="mr-2 bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col items-start justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 font-mono text-xl font-bold text-white">
                <i class="text-teal-400 bi bi-file-earmark-bar-graph"></i> Rapport Journalier d'Activité
            </h1>
            <p class="text-xs text-slate-400">Synthèse automatique du {{ now()->format('d/m/Y') }}</p>
        </div>

        <div class="flex items-center gap-3 p-3 border bg-slate-900 border-amber-500/30 rounded-xl">
            <i class="text-xl bi bi-clock-history text-amber-400 animate-pulse"></i>
            <div>
                <span class="block text-[10px] uppercase font-bold text-slate-400">Envoi Automatique dans :</span>
                <span id="countdown" class="font-mono text-base font-bold text-amber-400">--:--:--</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <span class="text-xs font-bold uppercase text-slate-400">Nouveaux Comptes Client</span>
            <span class="block mt-1 font-mono text-2xl font-bold text-emerald-400">{{ $dailyStats['accounts_count'] }}</span>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <span class="text-xs font-bold uppercase text-slate-400">Prospects Recrutés</span>
            <span class="block mt-1 font-mono text-2xl font-bold text-purple-400">{{ $dailyStats['prospects_count'] }}</span>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <span class="text-xs font-bold uppercase text-slate-400">Volume Collecté</span>
            <span class="block mt-1 font-mono text-2xl font-bold text-cyan-400">{{ number_format($dailyStats['total_collected'], 0, ',', ' ') }} XAF</span>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <span class="text-xs font-bold uppercase text-slate-400">Ventes d'Articles</span>
            <span class="block mt-1 font-mono text-2xl font-bold text-amber-400">{{ $dailyStats['orders_count'] }}</span>
        </div>
    </div>

    <form action="{{ route('commercial.rapports.send') }}" method="POST" class="p-6 space-y-6 border bg-slate-900 border-slate-800 rounded-2xl">
        @csrf

        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h2 class="flex items-center gap-2 text-sm font-bold uppercase text-slate-300">
                <i class="text-teal-400 bi bi-pencil-square"></i> Observations & Compléments Terrain
            </h2>
            <span class="text-xs text-slate-500">Formulaire avant envoi</span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="zone" class="block mb-1 text-xs font-semibold uppercase text-slate-400">Zone Couverte</label>
                <input type="text" id="zone" name="zone" value="{{ old('zone', $dailyStats['zone_name']) }}"
                    class="w-full px-3 py-2 text-sm border rounded-xl bg-slate-950 border-slate-800 text-slate-200 focus:outline-none focus:border-teal-500" required>
            </div>

            <div>
                <label for="prospects_count" class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prospects du jour</label>
                <input type="number" id="prospects_count" name="prospects_count" value="{{ old('prospects_count', $dailyStats['prospects_count']) }}"
                    class="w-full px-3 py-2 text-sm border rounded-xl bg-slate-950 border-slate-800 text-slate-200 focus:outline-none focus:border-teal-500" min="0" required>
            </div>
        </div>

        <div>
            <label for="observations" class="block mb-1 text-xs font-semibold uppercase text-slate-400">Notes / Observations du Commercial</label>
            <textarea id="observations" name="observations" rows="3"
                placeholder="Renseignez ici vos remarques sur le marché, difficultés rencontrées, opportunités..."
                class="w-full p-3 text-sm border rounded-xl bg-slate-950 border-slate-800 text-slate-200 focus:outline-none focus:border-teal-500">{{ old('observations') }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-slate-400">
                <i class="mr-1 text-teal-400 bi bi-info-circle"></i>
                Ce rapport sera transmis automatiquement à 23:59:59 si vous ne le soumettez pas manuellement.
            </p>
            <button type="submit" class="flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white transition bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg shadow-emerald-900/20">
                <i class="bi bi-send-fill"></i> Transmettre le Rapport
            </button>
        </div>
    </form>

</div>

<script>
    function updateCountdown() {
        const now = new Date();
        const midnight = new Date();
        midnight.setHours(23, 59, 59, 999);

        const diff = midnight - now;

        if (diff <= 0) {
            document.getElementById('countdown').innerText = "En cours d'envoi...";
            return;
        }

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('countdown').innerText =
            String(hours).padStart(2, '0') + ":" +
            String(minutes).padStart(2, '0') + ":" +
            String(seconds).padStart(2, '0');
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>
@endsection
