@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-6 bg-slate-950 text-slate-100">

    <!-- En-tête / Actions -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div class="flex items-center gap-3">
            <a href="{{ route('commercial.commandes.index') }}" class="p-2 transition border bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white rounded-xl">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-mono text-xl font-bold text-white">{{ $order->order_number }}</h1>
                    @if($order->isEligibleForDelivery() && $order->status !== 'delivered' && $order->status !== 'completed')
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-500/40 animate-pulse">
                            <i class="bi bi-truck"></i> Éligible à la livraison (60% atteints)
                        </span>
                    @elseif($order->status === 'delivered')
                        <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 text-[10px] font-bold border border-cyan-500/30">
                            Article Livré (Reliquat 40% en cours)
                        </span>
                    @elseif($order->status === 'completed')
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold border border-emerald-500/30">
                            Commande entièrement soldée
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold">
                            En cours de collecte
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">Date d'enregistrement : {{ $order->created_at }}</p>
            </div>
        </div>
{{-- {{ $order->created_at->format('d/m/Y à H:i') }} --}}
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="flex items-center gap-2 px-3 py-2 text-xs font-bold transition border bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-300 rounded-xl">
                <i class="bi bi-printer"></i> Imprimer Protocole & Reçu
            </button>
        </div>
    </div>

    <!-- Layout Grille Principal -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Colonne Gauche (2 cols) : Détails Article, Progression 60/40, Versements -->
        <div class="space-y-6 lg:col-span-2">

            <!-- 1. Carte Progression Visuelle 60% / 40% -->
            <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                        <i class="bi bi-pie-chart-fill text-emerald-400"></i> Statut de Financement Article (60% / 40%)
                    </h2>
                    <span class="font-mono text-xs font-bold text-emerald-400">{{ $order->progress_percentage }}% au total</span>
                </div>

                <!-- Barre de progression relative aux 60% et 100% -->
                <div class="space-y-2">
                    <div class="relative w-full h-4 bg-slate-950 rounded-full overflow-hidden border border-slate-800 p-0.5">
                        <!-- Repère 60% -->
                        <div class="absolute top-0 bottom-0 left-[60%] w-0.5 bg-amber-400 z-20 shadow-[0_0_8px_#fbbf24]"></div>
                        <!-- Barre remplie -->
                        <div class="h-full transition-all duration-700 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: {{ min(100, $order->progress_percentage) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-mono text-slate-400">
                        <span>0 XAF</span>
                        <span class="font-bold text-amber-400">Seuil Livraison (60%): {{ number_format($order->threshold_60_amount, 0, ',', ' ') }} XAF</span>
                        <span>Total (100%): {{ number_format($order->total_amount, 0, ',', ' ') }} XAF</span>
                    </div>
                </div>

                <!-- Metrics clés -->
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3 border bg-slate-950 border-slate-800/80 rounded-xl">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Déjà Payé</span>
                        <span class="font-mono text-sm font-bold text-emerald-400">{{ number_format($order->paid_amount, 0, ',', ' ') }} XAF</span>
                    </div>
                    <div class="p-3 border bg-slate-950 border-slate-800/80 rounded-xl">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Reste pour Livraison (60%)</span>
                        @php
                            $remaining60 = max(0, $order->threshold_60_amount - $order->paid_amount);
                        @endphp
                        <span class="text-sm font-bold {{ $remaining60 == 0 ? 'text-emerald-400' : 'text-amber-400' }} font-mono">
                            {{ number_format($remaining60, 0, ',', ' ') }} XAF
                        </span>
                    </div>
                    <div class="p-3 border bg-slate-950 border-slate-800/80 rounded-xl">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Reliquat Post-Livraison (40%)</span>
                        <span class="font-mono text-sm font-bold text-cyan-400">
                            {{ number_format(max(0, $order->total_amount - $order->paid_amount), 0, ',', ' ') }} XAF
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Informations Article & Transaction -->
            <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-box-seam-fill text-amber-400"></i> Détails de l'Article Commandé
                </h2>

                <div class="flex flex-col items-start gap-4 p-3 border sm:flex-row sm:items-center bg-slate-950 border-slate-800 rounded-xl">
                    <div class="flex items-center justify-center flex-shrink-0 w-16 h-16 overflow-hidden border rounded-lg bg-slate-900 border-slate-800">
                        @if($order->product && $order->product->primary_image)
                            <img src="{{ asset('storage/' . $order->product->primary_image) }}" alt="{{ $order->product->name }}" class="object-cover w-full h-full">
                        @else
                            <i class="text-2xl bi bi-box-seam text-slate-600"></i>
                        @endif
                    </div>
                    <div class="flex-1 space-y-1">
                        <h3 class="text-sm font-bold text-white">{{ $order->product->name ?? 'Article non défini' }}</h3>
                        <p class="text-xs text-slate-400">Réf: <span class="font-mono text-slate-300">{{ $order->product->reference ?? 'N/A' }}</span> | Mode : <span class="font-bold capitalize text-emerald-400">{{ $order->payment_type === 'installment' ? 'Échelonné (Tontine)' : 'Comptant' }}</span></p>
                    </div>
                    <div class="font-mono text-right">
                        <span class="block text-xs text-slate-400">Prix unitaire</span>
                        <span class="text-sm font-bold text-white">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</span>
                    </div>
                </div>
            </div>

            <!-- 3. Historique des Versements / Collectes -->
            <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                        <i class="bi bi-receipt text-cyan-400"></i> Historique des Encaissements (Tontine Article)
                    </h2>
                    <span class="text-xs text-slate-400">{{ $order->payments->count() }} versement(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/40">
                                <th class="p-2.5">Date & Heure</th>
                                <th class="p-2.5">Montant Encaissé</th>
                                <th class="p-2.5">Agent Encaisseur</th>
                                <th class="p-2.5 text-right">Référence</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                            @forelse($order->payments as $payment)
                                <tr class="transition hover:bg-slate-800/40">
                                    <td class="p-2.5 font-mono text-slate-400">{{ $payment->created_at->format('d/m/Y à H:i') }}</td>
                                    <td class="p-2.5 font-mono font-bold text-emerald-400">+ {{ number_format($payment->amount, 0, ',', ' ') }} XAF</td>
                                    <td class="p-2.5">{{ $payment->collector->name ?? 'Système / Direct' }}</td>
                                    <td class="p-2.5 font-mono text-right text-slate-500">PAY-{{ $payment->id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 italic text-center text-slate-500">Aucun versement enregistré pour cette commande pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Colonne Droite (1 col) : Informations Client & PROTOCOLE D'ACCORD (Signatures) -->
        <div class="space-y-6">

            <!-- Informations Client -->
            <div class="p-5 space-y-3 border bg-slate-900/90 border-slate-800 rounded-2xl">
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    <i class="bi bi-person-circle text-emerald-400"></i> Bénéficiaire / Client
                </h2>
                <div class="p-3 space-y-2 text-xs border bg-slate-950 border-slate-800 rounded-xl">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nom & Prénom :</span>
                        <span class="font-bold text-white">{{ $order->client->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Téléphone :</span>
                        <span class="font-mono text-emerald-400">{{ $order->client->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Adresse / Ville :</span>
                        <span class="text-slate-300">{{ $order->client->address ?? 'Non spécifiée' }}</span>
                    </div>
                </div>
            </div>

            <!-- PROTOCOLE D'ACCORD & SIGNATURES NUMÉRIQUES (Valable juridiquement) -->
            <div class="p-5 space-y-4 border bg-slate-900/90 border-slate-800 rounded-2xl">
                <div class="pb-2 border-b border-slate-800">
                    <h2 class="flex items-center gap-2 font-mono text-sm font-bold uppercase text-amber-400">
                        <i class="bi bi-file-earmark-check-fill"></i> Protocole d'Accord Signé
                    </h2>
                    <p class="text-[10px] text-slate-400">Signé le {{ $order->signed_at ? \Carbon\Carbon::parse($order->signed_at)->format('d/m/Y à H:i') : 'N/A' }}</p>
                </div>

                <!-- Termes contractuels -->
                <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-[11px] text-slate-300 space-y-2 font-mono leading-relaxed">
                    <p>{{ $order->protocol_terms }}</p>
                </div>

                <!-- Zone d'affichage des 2 Signatures Manuscrites Numériques -->
                <div class="pt-2 space-y-3">
                    <!-- Signature Client -->
                    <div class="space-y-1">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Signature Numérique du Client :</span>
                        <div class="flex items-center justify-center h-24 p-2 border bg-slate-950 border-slate-800 rounded-xl">
                            @if($order->client_signature)
                                <img src="{{ $order->client_signature }}" alt="Signature Client" class="object-contain max-h-full filter invert">
                            @else
                                <span class="text-slate-600 italic text-[11px]">Non signée</span>
                            @endif
                        </div>
                    </div>

                    <!-- Signature Commercial / Agent -->
                    <div class="space-y-1">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Signature Numérique Commercial :</span>
                        <div class="flex items-center justify-center h-24 p-2 border bg-slate-950 border-slate-800 rounded-xl">
                            @if($order->agent_signature)
                                <img src="{{ $order->agent_signature }}" alt="Signature Commercial" class="object-contain max-h-full filter invert">
                            @else
                                <span class="text-slate-600 italic text-[11px]">Non signée</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

{{-- @extends('layouts.app')

@section('content')
<div class="max-w-6xl p-4 mx-auto space-y-6 md:p-6">

    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('commercial.commandes.index') }}" class="p-2 transition border text-slate-400 hover:text-white bg-slate-900 border-slate-800 rounded-xl">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-white">Commande N° {{ $order->order_number }}</h1>
            </div>
            <p class="mt-1 text-xs text-slate-400 pl-11">Enregistrée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if($order->status === 'in_progress')
                <span class="px-3 py-1 text-xs font-semibold border rounded-full text-amber-400 bg-amber-400/10 border-amber-400/20">En Cours d'Épargne</span>
            @elseif($order->status === 'completed')
                <span class="px-3 py-1 text-xs font-semibold border rounded-full text-emerald-400 bg-emerald-400/10 border-emerald-400/20">Livrée / Solidée</span>
            @else
                <span class="px-3 py-1 text-xs font-semibold rounded-full text-slate-400 bg-slate-800">{{ ucfirst($order->status) }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl md:col-span-2">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xs font-semibold tracking-wider uppercase text-slate-400">Avancement du Règlement</h3>
                    <p class="mt-1 text-2xl font-bold text-white">{{ number_format($order->paid_amount, 0, ',', ' ') }} <span class="text-xs text-slate-400">/ {{ number_format($order->total_amount, 0, ',', ' ') }} XAF</span></p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Objectif 60% (Livraison) :</span>
                    <p class="text-sm font-bold text-emerald-400">{{ number_format($order->threshold_60_amount, 0, ',', ' ') }} XAF</p>
                </div>
            </div>

            @php
                $percentTotal = min(100, round(($order->paid_amount / max(1, $order->total_amount)) * 100));
                $percent60 = min(100, round(($order->paid_amount / max(1, $order->threshold_60_amount)) * 100));
            @endphp

            <div class="space-y-2">
                <div class="flex justify-between text-xs font-semibold">
                    <span class="text-slate-400">Progression globale</span>
                    <span class="text-emerald-400">{{ $percentTotal }}%</span>
                </div>
                <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                    <div class="h-full transition-all duration-500 rounded-full bg-gradient-to-r from-emerald-600 to-emerald-400" style="width: {{ $percentTotal }}%"></div>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 text-xs border bg-slate-950/60 border-slate-800/80 rounded-xl">
                <span class="text-slate-400">Statut Éligibilité Livraison :</span>
                @if($order->paid_amount >= $order->threshold_60_amount)
                    <span class="flex items-center gap-1 font-bold text-emerald-400"><i class="bi bi-check-circle-fill"></i> Éligible (Seuil de 60% atteint)</span>
                @else
                    <span class="font-medium text-amber-400">Reste {{ number_format($order->threshold_60_amount - $order->paid_amount, 0, ',', ' ') }} XAF avant livraison</span>
                @endif
            </div>
        </div>

        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="text-xs font-semibold tracking-wider uppercase text-slate-400">Compte & Client</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center font-bold w-9 h-9 rounded-xl bg-slate-800 text-emerald-400">
                        {{ strtoupper(substr($order->client->name ?? 'C', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ $order->client->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400">{{ $order->client->phone ?? 'Pas de numéro' }}</p>
                    </div>
                </div>

                <div class="pt-2 space-y-1 text-xs border-t border-slate-800/80">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Sous-compte lié :</span>
                        <span class="font-semibold text-emerald-400">{{ $subAccount->name ?? 'Tontine Électroménager' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Solde du Sous-compte :</span>
                        <span class="font-mono text-white">{{ number_format($subAccount->balance ?? $order->paid_amount, 0, ',', ' ') }} XAF</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
        <h3 class="text-xs font-semibold tracking-wider uppercase text-slate-400">Article Souscrit</h3>

        <div class="flex flex-col justify-between gap-4 p-4 border sm:flex-row sm:items-center bg-slate-950 border-slate-800 rounded-xl">
            <div class="flex items-center gap-4">
                @if($order->product?->image_url)
                    <img src="{{ asset($order->product->image_url) }}" class="object-cover border rounded-lg w-14 h-14 border-slate-800">
                @else
                    <div class="flex items-center justify-center rounded-lg w-14 h-14 bg-slate-800 text-slate-500"><i class="text-xl bi bi-box-seam"></i></div>
                @endif
                <div>
                    <h4 class="text-sm font-bold text-white">{{ $order->product->name ?? 'Article non spécifié' }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($order->product->description ?? '', 80) }}</p>
                </div>
            </div>
            <div class="pt-2 text-right border-t sm:border-t-0 sm:pt-0 border-slate-800">
                <span class="text-xs text-slate-400">Type de paiement :</span>
                <p class="text-xs font-bold text-white uppercase">{{ $order->payment_type === 'installment' ? 'Échelonné (Tontine)' : 'Comptant' }}</p>
            </div>
        </div>
    </div>

    <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
        <h3 class="text-xs font-semibold tracking-wider uppercase text-slate-400">Protocole de Vente & Signatures Numériques</h3>

        <div class="p-4 font-mono text-xs leading-relaxed border bg-slate-950 border-slate-800/80 rounded-xl text-slate-300">
            {{ $order->protocol_terms ?? "Protocole valant engagement d'achat échelonné souscrit par le client. Rattaché aux conditions de la microfinance." }}
        </div>

        <div class="grid grid-cols-1 gap-4 pt-2 md:grid-cols-2">

            <div class="p-4 space-y-2 border bg-slate-950 border-slate-800 rounded-xl">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-300">Signature du Client</span>
                    <span class="text-[10px] text-slate-500">{{ $order->signed_at ? \Carbon\Carbon::parse($order->signed_at)->format('d/m/Y H:i') : '' }}</span>
                </div>
                <div class="flex items-center justify-center h-32 p-2 overflow-hidden border rounded-lg bg-slate-900/60 border-slate-800">
                    @if(!empty($order->client_signature))
                        <img src="{{ $order->client_signature }}" alt="Signature Client" class="object-contain max-w-full max-h-full filter invert opacity-90">
                    @else
                        <span class="text-xs italic text-slate-500">Aucune signature enregistrée</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400 text-center">{{ $order->client->name ?? 'Le Client' }}</p>
            </div>

            <div class="p-4 space-y-2 border bg-slate-950 border-slate-800 rounded-xl">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-300">Signature de l'Agent Commercial</span>
                    <span class="text-[10px] text-slate-500">{{ $order->signed_at ? \Carbon\Carbon::parse($order->signed_at)->format('d/m/Y H:i') : '' }}</span>
                </div>
                <div class="flex items-center justify-center h-32 p-2 overflow-hidden border rounded-lg bg-slate-900/60 border-slate-800">
                    @if(!empty($order->agent_signature))
                        <img src="{{ $order->agent_signature }}" alt="Signature Commercial" class="object-contain max-w-full max-h-full filter invert opacity-90">
                    @else
                        <span class="text-xs italic text-slate-500">Aucune signature enregistrée</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-400 text-center">{{ $order->collector->name ?? Auth::user()->name }}</p>
            </div>

        </div>
    </div>

</div>
@endsection --}}
