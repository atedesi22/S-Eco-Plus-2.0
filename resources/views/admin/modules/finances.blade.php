@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div>
        <h1 class="text-2xl font-black tracking-wide text-white">CONSOLE COMPTABLE & RÉSULTATS</h1>
        <p class="text-xs text-slate-400">Supervision en temps réel des flux monétaires, commissions capturées et volumes de trésorerie</p>
    </div>

    <!-- METRICS ANALYTIQUES GLOBALES -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Trésorerie Actuelle (En Caisse)</p>
            <h3 class="font-mono text-xl font-black text-emerald-400">{{ number_format($netVault, 0, ',', ' ') }} XAF</h3>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Volume des Dépôts</p>
            <h3 class="font-mono text-xl font-black text-white">{{ number_format($totalDeposits, 0, ',', ' ') }} XAF</h3>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl">
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Volume des Retraits</p>
            <h3 class="font-mono text-xl font-black text-rose-400">{{ number_format($totalWithdrawals, 0, ',', ' ') }} XAF</h3>
        </div>
        <div class="p-4 border bg-slate-900 border-slate-800 rounded-xl bg-gradient-to-br from-amber-500/5 to-transparent">
            <p class="text-[10px] text-amber-500 font-bold uppercase tracking-wider">Gains sur Frais (500 XAF / Palier)</p>
            <h3 class="font-mono text-xl font-black text-amber-400">{{ number_format($totalFeesCollected, 0, ',', ' ') }} XAF</h3>
        </div>
    </div>

    <!-- JOURNAL D'AUDIT FINANCIER ABSOLU -->
    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <h2 class="text-sm font-bold tracking-wider text-white uppercase">Grand Livre de Contrôle Système</h2>
            <span class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded font-mono">Flux Temps Réel</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Horodatage</th>
                        <th class="pb-3">Bénéficiaire Account</th>
                        <th class="pb-3">Flux</th>
                        <th class="pb-3">Montant Net</th>
                        <th class="pb-3">Frais Prélevés</th>
                        <th class="pb-3">Opérateur Terrain</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach($transactions as $tx)
                    <tr>
                        <td class="py-3 font-mono text-slate-500">{{ $tx->created_at->format('d/m H:i:s') }}</td>
                        <td class="py-3 font-medium text-white">
                            {{ $tx->account->user->name ?? 'N/A' }}
                            <span class="text-[10px] text-slate-500 block font-mono">#{{ $tx->account_id }}</span>
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $tx->type === 'deposit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                {{ $tx->type }}
                            </span>
                        </td>
                        <td class="py-3 font-mono font-bold text-slate-200">{{ number_format($tx->amount, 0, '.', ' ') }} XAF</td>
                        <td class="py-3 font-mono font-bold text-amber-400">{{ number_format($tx->fee, 0, '.', ' ') }} XAF</td>
                        <td class="py-3 italic text-slate-400">{{ $tx->performedBy->name ?? 'Système' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
