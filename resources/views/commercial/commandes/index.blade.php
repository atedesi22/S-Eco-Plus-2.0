@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-6 bg-slate-950 text-slate-100">

    @if(session('success'))
        <div class="flex items-center justify-between p-4 text-xs font-bold border text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-2xl">
            <span><i class="mr-2 bi bi-check-circle-fill"></i> {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <!-- En-tête -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="bi bi-cart-check-fill text-amber-400"></i> Suivi des Commandes & Échéanciers (60%/40%)
            </h1>
            <p class="text-xs text-slate-400">Suivez la progression des collectes d'articles et identifiez les éligibilités à la livraison.</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-3 p-4 border bg-slate-900 border-slate-800 rounded-2xl">
        <form action="{{ route('commercial.commandes.index') }}" method="GET" class="flex flex-wrap flex-1 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Numéro commande, client..." class="px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-amber-500">
                <option value="">Tous les statuts</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours de collecte</option>
                <option value="eligible_for_delivery" {{ request('status') == 'eligible_for_delivery' ? 'selected' : '' }}>Éligibles Livraison (60%)</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrées</option>
            </select>
        </form>
    </div>

    <!-- Tableau -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Commande / Client</th>
                        <th class="p-3">Article</th>
                        <th class="p-3">Montant Total</th>
                        <th class="p-3">Avancement Collecte (Seuil 60%)</th>
                        <th class="p-3">Statut / Alerte</th>
                        <th class="p-3 text-right">Fiche / Protocole</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($orders as $order)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="p-3">
                                <div class="font-mono font-bold text-white">{{ $order->order_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->client->name ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3 font-medium text-amber-300">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="p-3 font-mono font-bold text-white">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</td>

                            <!-- PROGRESSION ET BARRE VISUELLE DES 60% -->
                            <td class="p-3 space-y-1 min-w-[200px]">
                                <div class="flex justify-between text-[10px] font-mono">
                                    <span class="font-bold text-emerald-400">{{ number_format($order->paid_amount, 0, ',', ' ') }} XAF</span>
                                    <span class="text-slate-400">{{ $order->progress_percentage }}% (Objectif 60%: {{ number_format($order->threshold_60_amount, 0, ',', ' ') }} XAF)</span>
                                </div>
                                <div class="relative w-full h-2 overflow-hidden border rounded-full bg-slate-950 border-slate-800">
                                    <!-- Ligne repère du seuil des 60% -->
                                    <div class="absolute top-0 bottom-0 left-[60%] w-0.5 bg-amber-400 z-10"></div>
                                    <!-- Remplissage effectif -->
                                    <div class="h-full transition-all duration-500 bg-emerald-400" style="width: {{ $order->progress_percentage }}%"></div>
                                </div>
                            </td>

                            <!-- ALERTE SI ÉLIGIBLE À LA LIVRAISON (>= 60%) -->
                            <td class="p-3">
                                @if($order->isEligibleForDelivery() && $order->status !== 'delivered' && $order->status !== 'completed')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-500/40 animate-pulse flex items-center gap-1 w-fit">
                                        <i class="text-xs bi bi-truck"></i> ALERTE : ÉLIGIBLE LIVRAISON (60%)
                                    </span>
                                @elseif($order->status === 'delivered')
                                    <span class="px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-[10px] font-bold border border-cyan-500/30">
                                        Livrée (Solde 40% en cours)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold">
                                        Collecte en cours
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 text-right">
                                <a href="{{ route('commercial.commandes.show', $order->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition text-[11px] font-bold">
                                    Voir Protocole
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 italic text-center text-slate-500">Aucune commande enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
