
@extends('layouts.app')

@section('content')
    {{-- Message de bienvenue commun à tout le personnel --}}
    <div class="p-6 border bg-slate-900 rounded-2xl border-slate-800">
        <h4 class="text-xl font-bold text-white">Bienvenue, {{ Auth::user()->name }}</h4>
        <p class="mt-1 text-xs text-slate-400">
            Console connectée sur l'agence :
            <span class="font-semibold text-emerald-400">{{ Auth::user()->agency->name ?? 'Direction Générale' }}</span>
        </p>
    </div>

    {{-- Aiguillage dynamique selon le rôle réel avec Spatie --}}
    @role('Comptable')
        @include('dashboard._comptable')
    @endrole

    @role('Secretaire')
        @include('dashboard._secretaire')
    @endrole

    {{-- Si c'est un administrateur ou un rôle de direction générale --}}
    @hasanyrole('SuperAdmin|PDG|DG')
        <div class="p-6 mt-6 border bg-slate-900 border-slate-800 rounded-2xl">
            <h5 class="font-bold text-white">Vue Supervision Générale</h5>
            <p class="mt-1 text-xs text-slate-400">Sélectionnez un module dans la barre latérale pour auditer les flux financiers.</p>
        </div>
    @endhasanyrole
@endsection
