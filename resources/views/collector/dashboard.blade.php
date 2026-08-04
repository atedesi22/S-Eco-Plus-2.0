@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 md:p-6 bg-slate-950 text-slate-100">

    <!-- HEADER PROFILE & QUICK STATUS -->
    <div class="flex flex-col justify-between gap-4 p-5 border sm:flex-row sm:items-center bg-slate-900/80 border-slate-800 rounded-2xl">
        <div class="flex items-center gap-4">
            <div class="relative">
                <img class="object-cover w-12 h-12 rounded-full ring-2 ring-amber-400"
                     src="{{ auth()->user()->profile_photo ? asset('storage/'.auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=f59e0b&color=fff' }}"
                     alt="Avatar">
                <span class="absolute bottom-0 right-0 w-3 h-3 border-2 rounded-full bg-emerald-500 border-slate-900"></span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white">Bonjour, {{ auth()->user()->name }} 👋</h1>
                <p class="text-xs text-slate-400">Prêt pour votre tournée de collecte du jour !</p>
            </div>
        </div>

        <!-- ACTION ENCAISSER RAPIDE -->
        <a href="{{ route('collector.collects.create') }}"
           class="flex items-center justify-center gap-2 px-5 py-3 text-sm font-black transition-all transform shadow-lg bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-amber-950 rounded-xl shadow-amber-500/20 active:scale-95">
            <i class="text-lg bi bi-plus-circle-fill"></i>
            <span>ENCAISSER TONTINE</span>
        </a>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        <!-- Collecté aujourd'hui -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-xs font-semibold tracking-wider uppercase text-slate-400">
                <span>Collecté aujourd'hui</span>
                <i class="text-base bi bi-wallet-fill text-emerald-400"></i>
            </div>
            <div class="font-mono text-xl font-black text-emerald-400">
                {{ number_format($todayCollected, 0, ',', ' ') }} <span class="font-sans text-xs">XAF</span>
            </div>
            <p class="text-[10px] text-slate-500">{{ $todayPassages }} cotisations encaissées</p>
        </div>

        <!-- En espèces sur soi -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-xs font-semibold tracking-wider uppercase text-slate-400">
                <span>Espèces sur soi</span>
                <i class="text-base bi bi-cash-stack text-amber-400"></i>
            </div>
            <div class="font-mono text-xl font-black text-amber-400">
                {{ number_format($cashHandheld, 0, ',', ' ') }} <span class="font-sans text-xs">XAF</span>
            </div>
            <p class="text-[10px] text-slate-500">À verser à la caissière</p>
        </div>

        <!-- Portefeuille Clients -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-xs font-semibold tracking-wider uppercase text-slate-400">
                <span>Mes Clients</span>
                <i class="text-base bi bi-people-fill text-cyan-400"></i>
            </div>
            <div class="font-mono text-xl font-black text-white">
                {{ $assignedClientsCount }}
            </div>
            <p class="text-[10px] text-slate-500">Clients en portefeuille</p>
        </div>

        <!-- Versements Caisse -->
        <div class="p-4 space-y-2 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div class="flex items-center justify-between text-xs font-semibold tracking-wider uppercase text-slate-400">
                <span>Remise Caisse</span>
                <i class="text-base text-indigo-400 bi bi-safe-fill"></i>
            </div>
            <a href="{{ route('collector.cash-deposits.index') }}" class="inline-flex items-center gap-1 pt-1 text-xs font-bold text-indigo-400 hover:underline">
                Effectuer une remise <i class="bi bi-arrow-right"></i>
            </a>
            <p class="text-[10px] text-slate-500">Demander validation caissière</p>
        </div>

    </div>

    <!-- OBJECTIF DU MOIS & BARRE DE PROGRESSION -->
    <div class="p-5 space-y-3 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
        @php
            $percentage = min(100, round(($monthlyCollected / max(1, $monthlyTarget)) * 100));
        @endphp
        <div class="flex items-center justify-between">
            <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-slate-400">
                <i class="bi bi-trophy-fill text-amber-400"></i> Objectif de Collecte Mensuel
            </span>
            <span class="font-mono text-xs font-bold text-emerald-400">
                {{ number_format($monthlyCollected, 0, ',', ' ') }} / {{ number_format($monthlyTarget, 0, ',', ' ') }} XAF
            </span>
        </div>

        <div class="w-full h-3.5 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
            <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-r from-emerald-500 via-cyan-400 to-indigo-500" style="width: {{ $percentage }}%"></div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Progression : <strong class="font-mono text-white">{{ $percentage }}%</strong></span>
            <span>Reste à collecter : <strong class="font-mono text-amber-400">{{ number_format(max(0, $monthlyTarget - $monthlyCollected), 0, ',', ' ') }} XAF</strong></span>
        </div>
    </div>

    <!-- DERNIERS PASSAGES / COLLECTES -->
    <div class="p-5 space-y-4 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
        <div class="flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                <i class="bi bi-clock-history text-amber-400"></i> Derniers Encaissements
            </h3>
            <a href="{{ route('collector.collects.history') }}" class="text-xs font-bold text-emerald-400 hover:underline">
                Voir tout
            </a>
        </div>

        <div class="space-y-2">
            @forelse($recentTransactions as $trx)
                <div class="flex items-center justify-between p-3 border bg-slate-950/70 border-slate-800/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 border rounded-lg bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                            <i class="bi bi-arrow-down-left"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white">{{ $trx->account->user->name ?? 'Client' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $trx->subAccount->name ?? 'Tontine' }} • {{ $trx->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono text-xs font-bold text-emerald-400">+{{ number_format($trx->amount, 0, ',', ' ') }} XAF</div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">Succès</span>
                    </div>
                </div>
            @empty
                <div class="py-6 text-xs text-center text-slate-500">
                    Aucun encaissement effectué aujourd'hui.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
