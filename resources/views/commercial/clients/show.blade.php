@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-8 space-y-6 bg-slate-950 text-slate-100 font-sans">

    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl">
            <span class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div class="flex items-center space-x-4">
            <a href="{{ route('commercial.clients.index') }}" class="p-2.5 text-xs text-slate-400 bg-slate-900 border border-slate-800 rounded-xl hover:bg-slate-800 transition">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                    {{ $client->name }}
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $client->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </h1>
                <p class="text-xs text-slate-400">Membre depuis le <span class="text-slate-200 font-mono">{{ $client->created_at->format('d/m/Y') }}</span> • Enregistré par <span class="text-emerald-400">{{ $client->creator->name ?? 'Commercial' }}</span></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="tel:{{ $client->phone }}" class="px-4 py-2.5 text-xs font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/60 hover:bg-emerald-900/40 rounded-xl transition flex items-center gap-2">
                <i class="bi bi-telephone-fill"></i> Appeler ({{ $client->phone }})
            </a>
        </div>
    </div>

    <div class="p-4 border bg-slate-900/50 border-slate-800 rounded-xl flex items-center gap-3 text-xs text-slate-400">
        <i class="bi bi-info-circle-fill text-cyan-400 text-lg"></i>
        <span>Vue d'ensemble en **lecture seule**. Pour effectuer un versement ou un retrait de fond, veuillez diriger le client vers la caisse d'agence.</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="space-y-6">
            <div class="p-6 border bg-slate-900/60 border-slate-800/80 rounded-2xl space-y-4">
                <div class="flex items-center space-x-4">
                    @if($client->profile_photo)
                        <img src="{{ asset('storage/' . $client->profile_photo) }}" alt="{{ $client->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-700">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center font-black text-emerald-400 text-xl">
                            {{ strtoupper(substr($client->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-sm font-bold text-white">{{ $client->name }}</h2>
                        <p class="text-xs font-mono text-slate-400">{{ $client->phone }}</p>
                        <p class="text-[11px] text-slate-500">{{ $client->email ?? 'Aucun email' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Agence :</span>
                        <strong class="text-white">{{ $client->agency->name ?? 'Non spécifiée' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Zone Commerciale :</span>
                        <strong class="text-white">{{ $client->zone->name ?? 'Non spécifiée' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Collectrice Assignée :</span>
                        <strong class="text-emerald-400">{{ $client->collector->name ?? 'Aucune' }}</strong>
                    </div>
                </div>
            </div>

            <div class="p-6 border bg-slate-900/60 border-slate-800/80 rounded-2xl space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                    <i class="bi bi-wallet2 text-emerald-400"></i> Synthèse Financière
                </h3>

                @php
                    $mainAccount = $client->accounts->first();
                    $totalMainBalance = $mainAccount ? $mainAccount->balance : 0;
                    $totalSubAccountsBalance = $client->accounts->flatMap->subAccounts->sum('balance');
                @endphp

                <div class="space-y-3">
                    <div class="p-3 bg-slate-950 border border-slate-800/80 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Compte Épargne Principal</span>
                        <span class="text-xl font-mono font-bold text-emerald-400">{{ number_format($totalMainBalance, 0, ',', ' ') }} XAF</span>
                    </div>

                    <div class="p-3 bg-slate-950 border border-slate-800/80 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Cumulé Tontines</span>
                        <span class="text-xl font-mono font-bold text-cyan-400">{{ number_format($totalSubAccountsBalance, 0, ',', ' ') }} XAF</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">

            <div class="p-6 border bg-slate-900/60 border-slate-800/80 rounded-2xl space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                    <i class="bi bi-pie-chart-fill text-amber-400"></i> Sous-comptes & Tontines Actives
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($client->accounts->flatMap->subAccounts as $subAccount)
                        <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-xs font-bold text-white">{{ $subAccount->name }}</h4>
                                    <span class="text-[10px] text-slate-400">Mise: <strong class="text-amber-400 font-mono">{{ number_format($subAccount->daily_amount, 0, ',', ' ') }} XAF/jour</strong></span>
                                </div>
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ ucfirst($subAccount->status) }}
                                </span>
                            </div>

                            <div class="pt-2 border-t border-slate-800/60 flex justify-between items-end">
                                <span class="text-[10px] text-slate-500">Solde Actuel :</span>
                                <span class="text-base font-mono font-bold text-emerald-400">{{ number_format($subAccount->balance, 0, ',', ' ') }} XAF</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-slate-500 italic text-xs">
                            Aucune tontine ou sous-compte actif pour ce client.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="p-6 border bg-slate-900/60 border-slate-800/80 rounded-2xl space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                    <i class="bi bi-clock-history text-cyan-400"></i> Dernières Opérations
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-2 px-2">Date</th>
                                <th class="pb-2 px-2">Type</th>
                                <th class="pb-2 px-2">Destination / Tontine</th>
                                <th class="pb-2 px-2 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                            @forelse($recentTransactions ?? [] as $transaction)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="py-2.5 px-2 font-mono text-slate-400 text-[11px]">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2.5 px-2">
                                        @if($transaction->type === 'deposit')
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Dépôt</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">Retrait</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-2">
                                        {{ $transaction->subAccount->name ?? 'Compte Principal' }}
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-mono font-bold {{ $transaction->type === 'deposit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} XAF
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-500 italic text-xs">
                                        Aucune transaction récente enregistrée pour ce client.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
