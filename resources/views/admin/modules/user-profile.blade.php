@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">

    <!-- En-tête / Retour -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-12 h-12 text-xl font-black border rounded-xl bg-emerald-500/10 border-emerald-500/30 text-emerald-400">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-xl font-black tracking-wide text-white uppercase">{{ $user->name }}</h1>
                <p class="text-xs text-slate-400">Fiche d'audit interne et vue d'ensemble du collaborateur</p>
            </div>
        </div>
        <a href="javascript:history.back()" class="px-3 py-1.5 text-xs font-bold uppercase tracking-wider bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition">
            <i class="mr-2 bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <!-- Grille Principale -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Colonne Gauche : Infos Carte d'identité -->
        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl h-fit">
            <h3 class="pb-2 text-xs font-bold tracking-widest uppercase border-b text-emerald-400 border-slate-800">Informations Générales</h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="block font-semibold uppercase text-slate-500">Téléphone / Identifiant</span>
                    <span class="font-mono text-sm text-white">{{ $user->phone ?? 'Numero non renseigné' }} / {{ $user->email ?? 'Email non renseignée' }}</span>
                </div>
                <div>
                    <span class="block font-semibold uppercase text-slate-500">Rôle Système</span>
                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded font-bold uppercase text-[10px] inline-block mt-1">
                        {{ $user->getRoleNames()->first() ?? 'Aucun rôle assigné' }}
                    </span>
                </div>
                <div>
                    <span class="block font-semibold uppercase text-slate-500">Membre depuis le</span>
                    <span class="font-mono text-slate-300">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Colonne Droite : Suivi & KPI Managériaux -->
        <div class="space-y-6 lg:col-span-2">

            <!-- BLOC 1 : Objectifs et Rendement -->
            @if(!$user->hasRole('client'))
                <!-- SECTION PERSONNEL INTERNE : Objectifs & Performance -->
                <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
                    <h3 class="flex items-center mb-4 text-xs font-bold tracking-widest uppercase text-slate-400">
                        <i class="mr-2 bi bi-graph-up-arrow text-emerald-400"></i> Performance & Objectifs Assignés
                    </h3>

                    <div class="space-y-4">
                        @forelse($user->objectives as $obj)
                            @php
                                $percent = $obj->target_value > 0 ? min(100, round(($obj->current_value / $obj->target_value) * 100)) : 0;
                            @endphp
                            <div class="p-4 space-y-2 border bg-slate-950 rounded-xl border-slate-800">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-white">{{ $obj->title }}</h4>
                                        <p class="text-[11px] text-slate-500 font-mono uppercase">
                                            {{ $obj->role_name ? 'Objectif de groupe : ' . $obj->role_name : 'Objectif individuel' }}
                                        </p>
                                    </div>
                                    <span class="font-mono text-xs font-bold text-emerald-400">{{ number_format($obj->current_value) }} / {{ number_format($obj->target_value) }}</span>
                                </div>

                                <div class="flex items-center pt-1 space-x-3">
                                    <div class="w-full h-2 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full transition-all bg-emerald-500" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="font-mono text-xs font-bold text-slate-400">{{ $percent }}%</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs italic text-slate-500">Aucun objectif assigné à ce membre du personnel.</p>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- SECTION CLIENT : Suivi Tontine Emprunt & Solvabilité -->
                <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl">
                    <h3 class="flex items-center text-xs font-bold tracking-widest uppercase text-amber-400">
                        <i class="mr-2 bi bi-wallet2"></i> État des Engagements & Tontine Emprunt
                    </h3>
                    <!-- Détails de la Tontine Emprunt si active -->
                </div>
            @endif

            <!-- BLOC 2 : Dossier Disciplinaire (Sanctions) -->
            <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
                <h3 class="flex items-center mb-4 text-xs font-bold tracking-widest uppercase text-rose-400">
                    <i class="mr-2 bi bi-exclamation-triangle"></i> Historique des Sanctions / Pénalités
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] uppercase font-bold text-slate-500">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Motif / Justification</th>
                                <th class="pb-2">Gravité</th>
                                <th class="pb-2 text-right">Amende Fin.</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-800/60">
                            @forelse($user->sanctions as $sanction)
                                <tr class="hover:bg-slate-950/20">
                                    <td class="py-3 font-mono text-slate-400">{{ \Carbon\Carbon::parse($sanction->applied_at)->format('d/m/Y') }}</td>
                                    <td class="py-3 pr-4">
                                        <div class="font-medium text-slate-200">{{ $sanction->reason }}</div>
                                        @if($sanction->objective)
                                            <div class="text-[10px] text-slate-500 italic">Lié à l'objectif : {{ $sanction->objective->title }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($sanction->severity === 'high')
                                            <span class="text-rose-400 font-bold bg-rose-500/10 px-1.5 py-0.5 rounded text-[10px]">Critique</span>
                                        @elseif($sanction->severity === 'medium')
                                            <span class="text-amber-400 font-bold bg-amber-500/10 px-1.5 py-0.5 rounded text-[10px]">Modérée</span>
                                        @else
                                            <span class="text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded text-[10px]">Faible</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-mono font-bold text-right text-rose-400">
                                        {{ number_format($sanction->financial_penalty_amount) }} XAF
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 italic text-center text-slate-500">Dossier vierge. Aucune sanction enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BLOC 3 : Flux Financiers Récents -->
            <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
                <h3 class="flex items-center mb-4 text-xs font-bold tracking-widest uppercase text-slate-400">
                    <i class="mr-2 text-blue-400 bi bi-cash-stack"></i> Activité Financière Récente
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] uppercase font-bold text-slate-500">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Type</th>
                                <th class="pb-2 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="font-mono text-xs divide-y divide-slate-800/60">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-slate-950/20">
                                    <td class="py-2.5 text-slate-400">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2.5 text-slate-200 uppercase text-[11px]">{{ str_replace('_', ' ', $tx->type) }}</td>
                                    <td class="py-2.5 text-right font-bold {{ in_array($tx->type, ['frais_dossier','interets','commission','vente_cash']) ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ number_format($tx->amount, 0, '.', ' ') }} XAF
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 font-sans italic text-center text-slate-500">Aucun flux financier récent enregistré pour ce compte.</td>
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
