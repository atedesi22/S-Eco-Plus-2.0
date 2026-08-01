@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Audits des Stocks & Ventes Articles</h1>
            <p class="text-xs text-slate-400">Suivi valorisé des stocks boutique, des ventes comptant et des contrats échelonnés (60%/40%).</p>
        </div>

        <button type="button" onclick="window.print()" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition text-slate-900 bg-emerald-400 rounded-xl hover:bg-emerald-300 w-fit">
            <i class="bi bi-printer-fill"></i>
            <span>Exporter l'Invenaire</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Unités Stock</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-emerald-400 bg-emerald-500/10">
                    <i class="text-lg bi bi-box-seam"></i>
                </div>
            </div>
            <p class="mt-2 font-mono text-2xl font-bold text-white">{{ number_format($totalStockItems, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">articles</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Valeur Stock (Vente)</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-tags-fill"></i>
                </div>
            </div>
            <p class="mt-2 font-mono text-2xl font-bold text-blue-400">{{ number_format($stockValueSelling, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Ventes Comptant</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-400 bg-amber-500/10">
                    <i class="text-lg bi bi-cash-stack"></i>
                </div>
            </div>
            <p class="mt-2 font-mono text-2xl font-bold text-amber-400">{{ number_format($totalSalesCash, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Recouvré / Échelonné</span>
                <div class="flex items-center justify-center w-8 h-8 text-purple-400 rounded-lg bg-purple-500/10">
                    <i class="text-lg bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <p class="mt-2 font-mono text-2xl font-bold text-purple-400">{{ number_format($totalCollectedInstallments, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-400 mt-1">Sur {{ number_format($totalSalesInstallment, 0, ',', ' ') }} XAF de contrats engagés</p>
        </div>
    </div>

    <div x-data="{ activeTab: 'stock' }" class="space-y-4">

        <div class="flex pb-2 space-x-2 border-b border-slate-800">
            <button @click="activeTab = 'stock'"
                    :class="activeTab === 'stock' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500' : 'text-slate-400 border-transparent hover:text-white'"
                    class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition border rounded-xl">
                <i class="bi bi-boxes"></i>
                <span>Inventaire & Valorisation Stock</span>
            </button>
            <button @click="activeTab = 'orders'"
                    :class="activeTab === 'orders' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500' : 'text-slate-400 border-transparent hover:text-white'"
                    class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition border rounded-xl">
                <i class="bi bi-receipt"></i>
                <span>Audit des Ventes & Contrats</span>
            </button>
        </div>

        <div x-show="activeTab === 'stock'" class="space-y-4">

            <div class="p-4 border bg-slate-900/80 border-slate-800 rounded-2xl">
                <form method="GET" action="{{ route('comptabilite.boutique.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="relative">
                        <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom d'article, code..."
                               class="w-full py-2 text-xs font-medium text-white border rounded-xl bg-slate-950 border-slate-800 pl-9 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <select name="stock_status" class="w-full px-3 py-2 text-xs font-medium border text-slate-300 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                            <option value="">Tous les états de stock</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stock critique / Seuil d'alerte</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Rupture de stock</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="w-full py-2 text-xs font-bold transition rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">
                            Filtrer
                        </button>
                        <a href="{{ route('comptabilite.boutique.index') }}" class="flex items-center justify-center px-3 py-2 text-xs border rounded-xl border-slate-800 text-slate-400 hover:text-white">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
                <table class="w-full text-sm text-left border-collapse text-slate-300">
                    <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="p-4">Réf. Article</th>
                            <th class="p-4">Désignation</th>
                            <th class="p-4 text-center">Quantité Stock</th>
                            <th class="p-4 text-right">Prix Comptant</th>
                            <th class="p-4 text-right">Prix Échelonné</th>
                            <th class="p-4 text-right">Valeur Vente Totale</th>
                            <th class="p-4 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="font-medium divide-y divide-slate-800/60">
                        @forelse($articles as $article)
                        <tr class="transition hover:bg-slate-800/40">
                            <td class="p-4 font-mono text-xs font-bold text-emerald-400">
                                {{ $article->code ?? 'ART-'.$article->id }}
                            </td>
                            <td class="p-4 text-xs font-bold text-white">
                                {{ $article->name }}
                            </td>
                            <td class="p-4 font-mono text-sm font-bold text-center text-white">
                                {{ $article->stock }}
                            </td>
                            <td class="p-4 font-mono text-xs text-right text-slate-300">
                                {{ number_format($article->cash_price, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 font-mono text-xs text-right text-slate-300">
                                {{ number_format($article->installment_price ?? $article->cash_price, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 font-mono text-xs font-bold text-right text-emerald-400">
                                {{ number_format($article->stock * $article->cash_price, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 text-center">
                                @if($article->stock <= 0)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Rupture
                                    </span>
                                @elseif($article->stock <= ($article->min_stock_threshold ?? 5))
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Alerte Stock
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        En Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                Aucun article trouvé dans le stock de l'agence.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $articles->links() }}
            </div>
        </div>

        <div x-show="activeTab === 'orders'" class="space-y-4">
            <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
                <table class="w-full text-sm text-left border-collapse text-slate-300">
                    <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Client</th>
                            <th class="p-4">Article</th>
                            <th class="p-4">Type de Paiement</th>
                            <th class="p-4 text-right">Montant Total</th>
                            <th class="p-4 text-right">Montant Payé</th>
                            <th class="p-4 text-right">Reste à Payer</th>
                            <th class="p-4 text-center">Statut Livré</th>
                        </tr>
                    </thead>
                    <tbody class="font-medium divide-y divide-slate-800/60">
                        @forelse($recentOrders as $order)
                        <tr class="transition hover:bg-slate-800/40">
                            <td class="p-4 font-mono text-xs text-slate-400">
                                {{ $order->created_at->format('d/m/Y') }}
                            </td>
                            <td class="p-4 text-xs font-bold text-white">
                                {{ $order->user->name ?? 'Client Inconnu' }}
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                {{ $order->article->name ?? 'N/A' }}
                            </td>
                            <td class="p-4 text-xs">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $order->payment_type === 'cash' ? 'bg-amber-500/10 text-amber-400' : 'bg-purple-500/10 text-purple-400' }}">
                                    {{ $order->payment_type === 'cash' ? 'Comptant' : 'Échelonné (60/40)' }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-xs font-bold text-right text-white">
                                {{ number_format($order->total_amount, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 font-mono text-xs font-bold text-right text-emerald-400">
                                {{ number_format($order->paid_amount, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 font-mono text-xs font-bold text-right text-rose-400">
                                {{ number_format(max(0, $order->total_amount - $order->paid_amount), 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-4 text-center">
                                @if($order->is_delivered)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-emerald-500/10 text-emerald-400">
                                        Livré
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-500/10 text-amber-400">
                                        En Attente (Seuil 60%)
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                Aucune transaction ou contrat d'article enregistré.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
