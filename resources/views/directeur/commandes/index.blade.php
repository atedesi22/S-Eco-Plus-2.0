@extends('layouts.app')

@section('content')
<div x-data="{
    search: '',
    statusFilter: 'all',
    selectedOrder: null,
    remindModalOrder: null,
    reminderNote: ''
}" class="space-y-6">

    <!-- MESSAGES DE NOTIFICATION -->
    @if(session('success'))
        <div class="p-4 text-sm font-medium border text-emerald-400 rounded-xl bg-emerald-500/10 border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 text-sm font-medium border text-rose-400 rounded-xl bg-rose-500/10 border-rose-500/20">
            {{ session('error') }}
        </div>
    @endif

    <!-- EN-TÊTE -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Commandes & Échéanciers Clients</h1>
            <p class="text-xs text-slate-400">Contrôlez le franchissement du seuil de 60%, validez les livraisons et donnez des instructions de relance aux agents.</p>
        </div>
    </div>

    <!-- CARTE STATISTIQUES (KPIs) -->
    <div class="grid gap-2 lg:grid-cols-4 md:grid-cols-4 sm:grid-cols-2">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Commandes</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-cart"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalOrders, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Éligibles Livraison (60%)</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-400 bg-amber-500/10">
                    <i class="text-lg bi bi-truck"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-400">{{ number_format($pendingDeliveries, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Retards Recouvrement</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-rose-400 bg-rose-500/10">
                    <i class="text-lg bi bi-alarm"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-400">{{ number_format($overdueOrders, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Encaissements Totaux</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-wallet2"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalCollected, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>
    </div>

    <!-- RECHERCHE & FILTRES -->
    <div class="flex flex-col items-center justify-between gap-4 p-4 border sm:flex-row bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher par N° commande, client..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="flex items-center w-full gap-2 sm:w-auto">
            <select x-model="statusFilter" class="w-full px-4 py-2 text-sm text-white border sm:w-auto rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="all">Tous les statuts</option>
                <option value="in_progress">Cotisation en cours (< 60%)</option>
                <option value="eligible_for_delivery">Attente Accord Livraison (>= 60%)</option>
                <option value="delivered">Livrés (Recouvrement 40%)</option>
                <option value="completed">Soldés / Clôturés</option>
            </select>
        </div>
    </div>

    <!-- TABLEAU DES COMMANDES -->
    <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
        <table class="w-full text-sm text-left border-collapse text-slate-300">
            <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="p-4">Commande & Client</th>
                    <th class="p-4">Article</th>
                    <th class="p-4">Progression (60% / 100%)</th>
                    <th class="p-4 text-center">Statut</th>
                    <th class="p-4">Agent Référent</th>
                    <th class="p-4 text-right">Actions Directeur</th>
                </tr>
            </thead>
            <tbody class="font-medium divide-y divide-slate-800/60">
                @forelse($orders as $order)
                @php
                    $percentage = $order->total_amount > 0 ? min(100, round(($order->paid_amount / $order->total_amount) * 100)) : 0;
                    $isEligible = $order->paid_amount >= $order->threshold_60_amount && !$order->delivered_approved_by_director;
                @endphp
                <tr x-show="(statusFilter === 'all' || '{{ $order->status }}' === statusFilter) &&
                            (search === '' || '{{ strtolower($order->order_number) }}'.includes(search.toLowerCase()) || '{{ strtolower($order->client->name ?? '') }}'.includes(search.toLowerCase()))"
                    class="transition hover:bg-slate-800/40">

                    <td class="p-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-emerald-400">#{{ $order->order_number }}</span>
                            <p class="font-bold text-white">{{ $order->client->name ?? 'Client inconnu' }}</p>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $order->client->phone ?? '' }}</span>
                        </div>
                    </td>

                    <td class="p-4">
                        <p class="text-xs font-bold text-white">{{ $order->product->name ?? 'N/A' }}</p>
                        <span class="text-[10px] font-mono text-slate-400">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</span>
                    </td>

                    <td class="p-4 w-52">
                        <div class="space-y-1">
                            <div class="flex justify-between text-[10px] font-mono">
                                <span class="text-slate-400">{{ number_format($order->paid_amount, 0, ',', ' ') }} XAF</span>
                                <span class="font-bold text-white">{{ $percentage }}%</span>
                            </div>
                            <div class="relative w-full h-2 overflow-hidden border rounded-full bg-slate-950 border-slate-800">
                                <!-- Marqueur de la ligne 60% -->
                                <div class="absolute top-0 bottom-0 left-[60%] w-0.5 bg-amber-400 z-10" title="Seuil de livraison (60%)"></div>
                                <div class="h-full {{ $percentage >= 60 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="flex justify-between text-[9px] text-slate-500 font-mono">
                                <span>0%</span>
                                <span class="font-bold text-amber-400">Seuil 60%</span>
                                <span>100%</span>
                            </div>
                        </div>
                    </td>

                    <td class="p-4 text-center">
                        @if($order->status === 'completed')
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Soldé (100%)</span>
                        @elseif($order->delivered_approved_by_director)
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">Livré (Recouvrement)</span>
                        @elseif($isEligible)
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">Accord requis (>= 60%)</span>
                        @else
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-slate-800 text-slate-400">Cotisation en cours</span>
                        @endif
                    </td>

                    <td class="p-4 font-mono text-xs text-slate-300">
                        {{ $order->collector->name ?? 'Non assigné' }}
                    </td>

                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <!-- Action : Valider Livraison si Seuil 60% atteint -->
                            @if($isEligible)
                                <form action="{{ route('directeur.commandes.approve-delivery', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-slate-950 bg-amber-400 rounded-lg hover:bg-amber-300 transition flex items-center space-x-1" title="Accorder la livraison">
                                        <i class="bi bi-box-seam"></i>
                                        <span>Valider Livraison</span>
                                    </button>
                                </form>
                            @endif

                            <!-- Action : Rappeler le collecteur pour suivi -->
                            <button @click='remindModalOrder = @json($order, JSON_HEX_APOS | JSON_HEX_QUOT)' class="p-2 transition text-slate-400 hover:text-amber-400" title="Rappeler l'agent référent">
                                <i class="bi bi-bell"></i>
                            </button>

                            <button @click='selectedOrder = @json($order->load("payments"), JSON_HEX_APOS | JSON_HEX_QUOT)' class="p-2 transition text-slate-400 hover:text-white" title="Détails de l'échéancier">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-500">
                        Aucune commande enregistrée dans l'agence.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODALE : ORDRE DE RAPPEL À L'AGENT -->
    <div x-show="remindModalOrder" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="remindModalOrder = null" class="w-full max-w-md p-6 space-y-4 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="flex items-center space-x-2 text-base font-bold text-white">
                    <i class="bi bi-bell text-amber-400"></i>
                    <span>Rappel Collecteur / Agent</span>
                </h3>
                <button @click="remindModalOrder = null" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <p class="text-xs text-slate-300">
                Transmettre une instruction directe à <strong class="text-emerald-400" x-text="remindModalOrder?.collector?.name || 'l\'agent'"></strong> pour intensifier la collecte auprès du client <strong class="text-white" x-text="remindModalOrder?.client?.name"></strong>.
            </p>

            <form :action="'/directeur/commandes/' + remindModalOrder?.id + '/remind-agent'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Instruction particulière</label>
                    <textarea name="note" x-model="reminderNote" rows="3" placeholder="Ex: Client en retard de 10 jours sur sa tranche hebdomadaire. Passer à son domicile d'ici demain." class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="flex justify-end pt-2 space-x-3">
                    <button type="button" @click="remindModalOrder = null" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-slate-950 bg-amber-400 rounded-xl hover:bg-amber-300">Envoyer l'Alerte Agent</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALE : DÉTAILS DE L'ÉCHÉANCIER ET HISTORIQUE -->
    <div x-show="selectedOrder" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="selectedOrder = null" class="w-full max-w-2xl p-6 space-y-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div>
                    <h3 class="text-lg font-bold text-white" x-text="'Commande #' + selectedOrder?.order_number"></h3>
                    <p class="text-xs text-slate-400" x-text="'Client : ' + selectedOrder?.client?.name"></p>
                </div>
                <button @click="selectedOrder = null" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <!-- Résumé Financier -->
            <div class="grid grid-cols-3 gap-3 font-mono text-xs">
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="text-slate-500 block uppercase font-sans text-[10px]">Prix Total</span>
                    <span class="font-bold text-white" x-text="new Intl.NumberFormat('fr-FR').format(selectedOrder?.total_amount || 0) + ' XAF'"></span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="text-slate-500 block uppercase font-sans text-[10px]">Total Encaissement</span>
                    <span class="font-bold text-emerald-400" x-text="new Intl.NumberFormat('fr-FR').format(selectedOrder?.paid_amount || 0) + ' XAF'"></span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="text-slate-500 block uppercase font-sans text-[10px]">Reste à Recouvrer</span>
                    <span class="font-bold text-rose-400" x-text="new Intl.NumberFormat('fr-FR').format((selectedOrder?.total_amount || 0) - (selectedOrder?.paid_amount || 0)) + ' XAF'"></span>
                </div>
            </div>

            <!-- Historique des Versements -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold tracking-wider uppercase text-slate-400">Historique des tranches perçues</h4>
                <div class="space-y-2 overflow-y-auto max-h-48">
                    <template x-for="p in selectedOrder?.payments" :key="p.id">
                        <div class="flex items-center justify-between p-3 text-xs border rounded-xl bg-slate-950/40 border-slate-800/80">
                            <span class="font-mono text-slate-400" x-text="p.created_at"></span>
                            <span class="font-mono font-bold text-emerald-400" x-text="'+ ' + new Intl.NumberFormat('fr-FR').format(p.amount) + ' XAF'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-800">
                <button @click="selectedOrder = null" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Fermer</button>
            </div>
        </div>
    </div>

</div>
@endsection
