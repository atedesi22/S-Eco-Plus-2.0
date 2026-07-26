@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Validations & Autorisations</h1>
            <p class="text-xs text-slate-400">Demandes de dérogation, plafonds et opérations soumises à approbation.</p>
        </div>
        <a href="{{ route('directeur.dashboard') }}" class="flex items-center gap-1 text-xs text-slate-400 hover:text-white">
            &larr; Retour au Dashboard
        </a>
    </div>

    <!-- Filtres -->
    <div class="flex gap-2">
        <a href="{{ route('directeur.validations.index', ['status' => 'pending']) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-bold border {{ request('status', 'pending') === 'pending' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-slate-900 text-slate-400 border-slate-800' }}">
            En Attente
        </a>
        <a href="{{ route('directeur.validations.index', ['status' => 'approved']) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-bold border {{ request('status') === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-900 text-slate-400 border-slate-800' }}">
            Approuvées
        </a>
        <a href="{{ route('directeur.validations.index', ['status' => 'rejected']) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-bold border {{ request('status') === 'rejected' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-slate-900 text-slate-400 border-slate-800' }}">
            Rejetées
        </a>
    </div>

    <!-- Liste -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <table class="w-full text-xs text-left text-slate-400">
            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Date / Heure</th>
                    <th class="px-4 py-3">Demandeur</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">Montant</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($validations as $val)
                    <tr class="transition hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-mono text-[11px] text-slate-400">
                            {{ \Carbon\Carbon::parse($val->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 font-bold text-white">{{ $val->requester_name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px] uppercase">
                                {{ str_replace('_', ' ', $val->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $val->description }}</td>
                        <td class="px-4 py-3 font-bold text-right text-white">
                            {{ $val->amount ? number_format($val->amount, 0, ',', ' ') . ' XAF' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($val->status === 'pending')
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px]">EN ATTENTE</span>
                            @elseif($val->status === 'approved')
                                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold text-[10px]">APPROUVÉ</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-bold text-[10px]">REJETÉ</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">
                            Aucune demande de validation trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $validations->links() }}
    </div>
</div>
@endsection
