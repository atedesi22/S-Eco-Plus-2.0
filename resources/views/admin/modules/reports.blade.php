@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div>
        <h1 class="text-2xl font-black tracking-wide text-white">AUDIT DES PERFORMANCES DU PERSONNEL</h1>
        <p class="text-xs text-slate-400">Analyse de la productivité, des volumes d'écritures générés et de l'activité sur le terrain ce mois-ci</p>
    </div>

    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Collaborateur</th>
                        <th class="pb-3">Poste Occupé</th>
                        <th class="pb-3 text-center">Opérations Traitées (Mois en cours)</th>
                        <th class="pb-3 text-right">Statut Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400">
                    @foreach($staffPerformances as $perf)
                    <tr>
                        <td class="py-3 font-bold text-white">{{ $perf->name }}</td>
                        <td class="py-3">
                            <span class="text-[10px] text-slate-400 font-mono">{{ $perf->roles->first()?->name }}</span>
                        </td>
                        <td class="py-3 font-mono font-bold text-center text-emerald-400">
                            {{ $perf->transactions_as_validator_count }} actions
                        </td>
                        <td class="py-3 text-right">
                            <span class="text-[10px] bg-slate-950 text-slate-500 px-2 py-1 rounded border border-slate-800 font-mono">
                                Conforme
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
