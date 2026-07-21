@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div>
        <h1 class="text-2xl font-black tracking-wide text-white">REGISTRE GLOBAL DU PERSONNEL INTERNE</h1>
        <p class="text-xs text-slate-400">Contrôle absolu sur les comptes d'accès, les privilèges et l'état d'activité du staff</p>
    </div>

    <!-- TABLEAU DE CONTRÔLE -->
    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Agent</th>
                        <th class="pb-3">Rôle Assigné</th>
                        <th class="pb-3">Affectation Physique</th>
                        <th class="pb-3">Supérieur Hiérarchique</th>
                        <th class="pb-3 text-center">État du Compte</th>
                        <th class="pb-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400">
                    @foreach($staff as $agent)
                    <tr>
                        <td class="py-3.5">
                            <div class="font-bold text-white">{{ $agent->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $agent->email }}</div>
                        </td>
                        <td class="py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-950 text-indigo-400 border border-indigo-500/25">
                                {{ $agent->roles->first()?->name ?? 'Aucun rôle' }}
                            </span>
                        </td>

                        <td class="py-3.5 font-medium text-slate-300">
                            {{ $agent->structure ? $agent->structure->name : 'Non assigné (Siège Central)' }}
                        </td>
                        <td class="py-3.5 italic text-slate-500">
                            {{ $agent->superior ? $agent->superior->name : 'Racine (Aucun)' }}
                        </td>
                        <td class="py-3.5 text-center">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $agent->is_active ?? true ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                {{ $agent->is_active ?? true ? 'Actif' : 'Suspendu' }}
                            </span>
                        </td>
                        <td class="py-3.5 text-right">
                            <form action="{{ route('admin.staff.toggle', $agent->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[10px] font-bold px-2 py-1 rounded transition
                                    {{ $agent->is_active ?? true ? 'bg-rose-500/10 text-rose-400 hover:bg-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }}">
                                    {{ $agent->is_active ?? true ? 'Suspendre' : 'Réactiver' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
