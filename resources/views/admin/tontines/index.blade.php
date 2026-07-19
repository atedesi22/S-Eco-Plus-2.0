@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-wide text-white">CATALOGUE CENTRAL DES TONTINES</h1>
            <p class="text-xs text-slate-400">Configuration des produits d'épargne collectés par les équipes de terrain</p>
        </div>
    </div>

    <!-- GRILLE DES PLANS ACTIFS -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach($plans as $plan)
        <div class="flex flex-col justify-between p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
                        @if($plan->default_color == 'emerald') bg-emerald-500/10 text-emerald-400 border border-emerald-500/25
                        @elseif($plan->default_color == 'indigo') bg-indigo-500/10 text-indigo-400 border border-indigo-500/25
                        @elseif($plan->default_color == 'amber') bg-amber-500/10 text-amber-400 border border-amber-500/25
                        @else bg-slate-800 text-slate-400 @endif">
                        {{ $plan->default_color }} plan
                    </span>
                    <span class="w-2 h-2 rounded-full {{ $plan->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                </div>
                <h3 class="text-lg font-black tracking-wide text-white uppercase">{{ $plan->name }}</h3>
                <p class="text-xs leading-relaxed text-slate-400">{{ $plan->description ?? 'Aucune description fournie.' }}</p>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-between items-center text-[11px] text-slate-500">
                <span>Créé le {{ $plan->created_at->format('d/m/Y') }}</span>
                <span class="font-mono text-slate-400">ID: #{{ $plan->id }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
