@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <!-- Header avec infos clés -->
    <div class="flex flex-col gap-4 pb-5 border-b md:flex-row md:items-center md:justify-between border-slate-800">
        <div class="flex items-center space-x-4">
            <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : 'https://placehold.co/80x80/0f172a/fff?text='.substr($user->name, 0, 2) }}"
                 class="object-cover w-16 h-16 border-2 shadow-xl rounded-2xl border-slate-800">
            <div>
                <h1 class="text-xl font-black tracking-wide text-white uppercase">{{ $user->name }}</h1>
                <div class="flex items-center mt-1 space-x-2">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">
                        {{ $user->getRoleNames()->first() ?? 'Aucun rôle' }}
                    </span>
                    <span class="font-mono text-xs text-slate-500">Tel: {{ $user->numero_de_telephone }}</span>
                </div>
            </div>
        </div>

        <!-- Actions Admin Critiques -->
        <div class="flex items-center space-x-3">
            <form action="{{ route('admin.staff.reset-password', $user->id) }}" method="POST" onsubmit="return confirm('Réinitialiser le mot de passe de cet agent à 0000 ?')">
                @csrf
                <button type="submit" class="px-4 py-2 text-xs font-bold transition border bg-rose-500/10 hover:bg-rose-500 hover:text-slate-950 text-rose-400 rounded-xl border-rose-500/30">
                    <i class="mr-1 bi bi-shield-lock-fill"></i> Restaurer Code (0000)
                </button>
            </form>
        </div>
    </div>

    <!-- Grille de Contenu Principal -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- COLONNE 1 & 2 : OBJECTIFS & RAPPORTS -->
        <div class="space-y-6 lg:col-span-2">
            <!-- SECTION OBJECTIFS -->
            <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold tracking-wider uppercase text-slate-400">Objectifs de Performance Allocataires</h3>
                    <button class="text-[10px] bg-emerald-500 text-slate-950 px-2 py-1 rounded font-bold hover:bg-emerald-600">Assigner</button>
                </div>

                <div class="space-y-4">
                    @forelse($user->objectives as $obj)
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="font-bold text-white">{{ $obj->title }}</span>
                            <span class="font-mono text-slate-400">{{ number_format($obj->current_value) }} / {{ number_format($obj->target_value) }}</span>
                        </div>
                        <!-- Barre de Progression -->
                        @php $percent = min(100, ($obj->current_value / max(1, $obj->target_value)) * 100); @endphp
                        <div class="w-full h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full transition-all bg-emerald-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <span>Échéance : {{ \Carbon\Carbon::parse($obj->end_date)->format('d/m/Y') }}</span>
                            <span class="uppercase font-bold {{ $obj->status == 'achieved' ? 'text-emerald-400' : 'text-amber-500' }}">{{ $obj->status }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="py-4 text-xs italic text-center text-slate-500">Aucun objectif assigné à cet agent.</p>
                    @endforelse
                </div>
            </div>

            <!-- HISTORIQUE DES RAPPORTS ENVOYÉS & VALIDÉS -->
            <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
                <h3 class="mb-4 text-xs font-bold tracking-wider uppercase text-slate-400">Flux des Rapports d'Activité Terrain</h3>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="font-bold border-b border-slate-800 text-slate-500">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Type / Titre</th>
                                <th class="pb-2 text-right">Statut Validation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-slate-400">
                            @forelse($user->reports as $report)
                            <tr>
                                <td class="py-2.5 font-mono text-slate-500">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2.5 font-medium text-white">{{ $report->title }}</td>
                                <td class="py-2.5 text-right">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $report->is_validated ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                        {{ $report->is_validated ? 'Validé' : 'En attente' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 italic text-center text-slate-500">Aucun rapport soumis par cet agent.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COLONNE 3 : DOSSIER DISCIPLINAIRE (SANCTIONS) -->
        <div class="space-y-6">
            <div class="p-5 border border-l-4 bg-slate-900 border-slate-800 rounded-xl border-l-rose-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold tracking-wider uppercase text-rose-400">Registre des Sanctions</h3>
                    <button class="text-[10px] bg-rose-500 text-slate-950 px-2 py-1 rounded font-bold hover:bg-rose-600">Sanctionner</button>
                </div>

                <div class="space-y-3">
                    @forelse($user->sanctions as $sanction)
                    <div class="p-3 space-y-1 text-xs border rounded-lg bg-slate-950 border-slate-800">
                        <div class="flex justify-between">
                            <span class="font-bold tracking-wide uppercase text-slate-200">{{ $sanction->severity }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ \Carbon\Carbon::parse($sanction->applied_at)->format('d/m/Y') }}</span>
                        </div>
                        <p class="text-slate-400 text-[11px] italic">"{{ $sanction->reason }}"</p>
                        @if($sanction->financial_penalty_amount > 0)
                        <div class="text-[10px] text-rose-400 font-mono font-bold pt-1">
                            Retenue : -{{ number_format($sanction->financial_penalty_amount) }} XAF
                        </div>
                        @endif
                    </div>
                    @empty
                    <p class="py-4 text-xs italic text-center text-slate-500">Dossier disciplinaire vierge. Excellent travail.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
