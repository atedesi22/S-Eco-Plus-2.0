@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'summary' }" class="space-y-6">

    <!-- EN-TÊTE & SÉLECTEUR DE DATE -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Rapport d'Activité Journalier</h1>
            <p class="text-xs text-slate-400">Synthèse consolidée des flux financiers, recrutements et livraisons de l'agence.</p>
        </div>

        <!-- Sélection de la date et Impression -->
        <form method="GET" action="{{ route('directeur.rapports.index') }}" class="flex items-center gap-3">
            <div class="relative">
                <i class="absolute text-slate-500 left-3 top-2.5 bi bi-calendar3"></i>
                <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}" onchange="this.form.submit()"
                       class="py-2 pr-4 font-mono text-xs font-bold text-white border rounded-xl bg-slate-900 border-slate-800 pl-9 focus:outline-none focus:border-emerald-500">
            </div>

            <button type="button" onclick="window.print()" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition text-slate-900 bg-emerald-400 rounded-xl hover:bg-emerald-300">
                <i class="bi bi-printer-fill"></i>
                <span>Imprimer</span>
            </button>
        </form>
    </div>

    <!-- CARTE DE SYNTHÈSE GLOBALE DU JOUR -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Encaissements Terrain -->
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Collecte Terrain</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-wallet2"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalFieldCollected, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <span class="text-[10px] text-slate-500 font-mono">{{ $paymentsToday->count() }} versements perçus</span>
        </div>

        <!-- Ventes Cash Boutique -->
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Ventes Comptant</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-shop"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalCashSales, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <span class="text-[10px] text-slate-500 font-mono">Encaissement 100% direct</span>
        </div>

        <!-- Livraisons 60% Valides -->
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Articles Livrés (60%)</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-400 bg-amber-500/10">
                    <i class="text-lg bi bi-box-seam"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-400">{{ $deliveriesCount }}</p>
            <span class="text-[10px] text-slate-500 font-mono">Délivrés ce {{ $selectedDate->format('d/m/Y') }}</span>
        </div>

        <!-- Recrutement Clients -->
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Nouveaux Clients</span>
                <div class="flex items-center justify-center w-8 h-8 text-purple-400 rounded-lg bg-purple-500/10">
                    <i class="text-lg bi bi-person-plus-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $newClientsCount }}</p>
            <span class="text-[10px] text-slate-500 font-mono">Adhésions enregistrées</span>
        </div>
    </div>

    <!-- BANNIÈRE RECETTE TOTALE DU JOUR -->
    <div class="flex items-center justify-between p-6 border bg-emerald-500/10 border-emerald-500/30 rounded-2xl">
        <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500 text-slate-950">
                <i class="text-2xl bi bi-cash-stack"></i>
            </div>
            <div>
                <span class="text-xs font-bold tracking-wider uppercase text-emerald-400">Total Encaissé en Agence</span>
                <h2 class="font-mono text-2xl font-extrabold text-white">{{ number_format($grandTotalCollected, 0, ',', ' ') }} XAF</h2>
            </div>
        </div>
        <div class="hidden font-mono text-xs text-right sm:block text-slate-400">
            <span>Date de clôture :</span>
            <p class="text-sm font-bold text-white">{{ $selectedDate->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <!-- ONGLETS NAVIGATION -->
    <div class="flex space-x-2 text-xs font-bold border-b border-slate-800">
        <button @click="activeTab = 'summary'"
                :class="activeTab === 'summary' ? 'border-b-2 border-emerald-400 text-emerald-400' : 'text-slate-400 hover:text-white'"
                class="px-4 pb-3 transition">
            Collecte par Agent
        </button>
        <button @click="activeTab = 'details'"
                :class="activeTab === 'details' ? 'border-b-2 border-emerald-400 text-emerald-400' : 'text-slate-400 hover:text-white'"
                class="px-4 pb-3 transition">
            Détails des Versements
        </button>
    </div>

    <!-- TAB 1 : PERFORMANCE COLLECTEURS SUR LA JOURNÉE -->
    <div x-show="activeTab === 'summary'" class="space-y-4">
        <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
            <table class="w-full text-sm text-left border-collapse text-slate-300">
                <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-4">Agent / Collecteur</th>
                        <th class="p-4 text-center">Nombre d'opérations</th>
                        <th class="p-4 text-right">Montant Versé</th>
                        <th class="p-4 text-right">Contribution</th>
                    </tr>
                </thead>
                <tbody class="font-medium divide-y divide-slate-800/60">
                    @forelse($collectorsPerformance as $perf)
                    @php
                        $share = $grandTotalCollected > 0 ? round(($perf['total_amount'] / $grandTotalCollected) * 100) : 0;
                    @endphp
                    <tr class="transition hover:bg-slate-800/40">
                        <td class="flex items-center p-4 space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full bg-slate-800">
                                {{ strtoupper(substr($perf['collector']->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">{{ $perf['collector']->name }}</p>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $perf['collector']->phone ?? 'N/A' }}</span>
                            </div>
                        </td>

                        <td class="p-4 font-mono text-center">
                            <span class="px-2.5 py-1 text-xs rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                                {{ $perf['transactions_count'] }} transactions
                            </span>
                        </td>

                        <td class="p-4 font-mono font-bold text-right text-emerald-400">
                            {{ number_format($perf['total_amount'], 0, ',', ' ') }} XAF
                        </td>

                        <td class="p-4 text-right w-36">
                            <div class="space-y-1">
                                <span class="text-[10px] text-slate-400 font-mono">{{ $share }}%</span>
                                <div class="w-full bg-slate-950 h-1.5 rounded-full overflow-hidden border border-slate-800">
                                    <div class="h-full bg-emerald-500" style="width: {{ $share }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            Aucune activité d'agent enregistrée pour cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2 : REGISTRE DÉTAILLÉ DES FLUX -->
    <div x-show="activeTab === 'details'" class="space-y-4">
        <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
            <table class="w-full text-sm text-left border-collapse text-slate-300">
                <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-4">Heure</th>
                        <th class="p-4">Commande & Client</th>
                        <th class="p-4">Encaissé Par</th>
                        <th class="p-4 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="font-medium divide-y divide-slate-800/60">
                    @forelse($paymentsToday as $payment)
                    <tr class="transition hover:bg-slate-800/40">
                        <td class="p-4 font-mono text-xs text-slate-400">
                            {{ $payment->created_at->format('H:i:s') }}
                        </td>
                        <td class="p-4">
                            <span class="font-mono text-xs font-bold text-emerald-400">#{{ $payment->order->order_number ?? 'N/A' }}</span>
                            <p class="text-xs text-white">{{ $payment->order->client->name ?? 'Client Inconnu' }}</p>
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-300">
                            {{ $payment->collector->name ?? 'Système/Caisse' }}
                        </td>
                        <td class="p-4 font-mono font-bold text-right text-emerald-400">
                            + {{ number_format($payment->amount, 0, ',', ' ') }} XAF
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            Aucun versement individuel enregistré à cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
