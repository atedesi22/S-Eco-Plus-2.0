@extends('layouts.app')

@section('content')
<div class="min-h-screen p-6 space-y-6 bg-slate-950 text-slate-100">

    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 font-mono text-xl font-bold text-white">
                <i class="bi bi-trophy-fill text-amber-400"></i> Mes Objectifs & Primes
            </h1>
            <p class="text-xs text-slate-400">Suivi en temps réel de votre progression et calcul dynamique des gratifications</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($objectives as $objective)
            @php
                $pct = $objective->progress_percentage;
                $bonus = $objective->calculated_bonus;
            @endphp
            <div class="relative p-5 space-y-4 overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">
                        {{ strtoupper($objective->period) }}
                    </span>
                    @if($pct >= 100)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            Objectif Atteint (100%)
                        </span>
                    @elseif($pct >= 70)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            Palier Éligible (40% Prime)
                        </span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30">
                            Non Éligible (<70%)
                        </span>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white">{{ $objective->title }}</h3>
                    <p class="font-mono text-xs text-slate-400">Cible : {{ number_format($objective->adjusted_target) }} {{ $objective->type === 'new_accounts' ? 'Comptes' : 'XAF' }}</p>
                </div>

                <div class="space-y-1">
                    <div class="flex justify-between font-mono text-xs">
                        <span class="text-slate-400">Réalisé : {{ number_format($objective->current_value) }}</span>
                        <span class="font-bold {{ $pct >= 70 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-slate-950 h-3 rounded-full overflow-hidden border border-slate-800 p-0.5 relative">
                        <div class="absolute top-0 bottom-0 left-[70%] w-0.5 bg-amber-400 z-10"></div>
                        <div class="h-full rounded-full transition-all duration-500 {{ $pct >= 100 ? 'bg-emerald-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 border bg-slate-950 border-slate-800 rounded-xl">
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Prime Estimée</span>
                        <span class="text-xs text-slate-500">Base : {{ number_format($objective->base_bonus) }} XAF</span>
                    </div>
                    <span class="font-mono text-base font-bold text-amber-400">+ {{ number_format($bonus, 0, ',', ' ') }} XAF</span>
                </div>
            </div>
        @empty
            <div class="p-8 italic text-center border col-span-full text-slate-500 bg-slate-900/50 border-slate-800 rounded-2xl">
                Aucun objectif assigné pour le moment.
            </div>
        @endforelse
    </div>

</div>
@endsection
