@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Journal des Flux & Traçabilité</h1>
            <p class="text-xs text-slate-400">Registre général des entrées et sorties de fonds de l'agence.</p>
        </div>

        <button type="button" onclick="window.print()" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition text-slate-900 bg-emerald-400 rounded-xl hover:bg-emerald-300 w-fit">
            <i class="bi bi-printer-fill"></i>
            <span>Exporter le Journal</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Entrées (Crédit)</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-emerald-400 bg-emerald-500/10">
                    <i class="text-lg bi bi-arrow-down-left-circle-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-400">+ {{ number_format($totalCredits, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Sorties (Débit)</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-rose-400 bg-rose-500/10">
                    <i class="text-lg bi bi-arrow-up-right-circle-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-400">- {{ number_format($totalDebits, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span></p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Flux Net Période</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $netCashFlow >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                    <i class="text-lg bi bi-cash-coin"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold font-mono {{ $netCashFlow >= 0 ? 'text-white' : 'text-rose-400' }}">
                {{ $netCashFlow >= 0 ? '+' : '' }}{{ number_format($netCashFlow, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">XAF</span>
            </p>
        </div>
    </div>

    <div class="p-4 border bg-slate-900/80 border-slate-800 rounded-2xl">
        <form method="GET" action="{{ route('directeur.flux.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative">
                <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Réf, Client, Opérateur..."
                       class="w-full py-2 text-xs font-medium text-white border rounded-xl bg-slate-950 border-slate-800 pl-9 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <select name="type" class="w-full px-3 py-2 text-xs font-medium border text-slate-300 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Tous les sens (Entrées/Sorties)</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Entrées (Crédit)</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Sorties (Débit)</option>
                </select>
            </div>

            <div>
                <select name="category" class="w-full px-3 py-2 text-xs font-medium border text-slate-300 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Toutes les catégories</option>
                    <option value="tontine_deposit" {{ request('category') === 'tontine_deposit' ? 'selected' : '' }}>Dépôt Tontine / Épargne</option>
                    <option value="order_installment" {{ request('category') === 'order_installment' ? 'selected' : '' }}>Tranche Article (60%/40%)</option>
                    <option value="cash_sale" {{ request('category') === 'cash_sale' ? 'selected' : '' }}>Vente Comptant</option>
                    <option value="client_withdrawal" {{ request('category') === 'client_withdrawal' ? 'selected' : '' }}>Retrait Client</option>
                    <option value="cash_in" {{ request('category') === 'cash_in' ? 'selected' : '' }}>Approvisionnement Caisse</option>
                    <option value="cash_out" {{ request('category') === 'cash_out' ? 'selected' : '' }}>Décaissement / Charges</option>
                </select>
            </div>

            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 font-mono text-xs border text-slate-300 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="w-full py-2 text-xs font-bold transition rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">
                    Filtrer
                </button>
                <a href="{{ route('directeur.flux.index') }}" class="flex items-center justify-center px-3 py-2 text-xs border rounded-xl border-slate-800 text-slate-400 hover:text-white">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/60">
        <table class="w-full text-sm text-left border-collapse text-slate-300">
            <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="p-4">Date & Heure</th>
                    <th class="p-4">Réf. Transaction</th>
                    <th class="p-4">Catégorie / Motif</th>
                    <th class="p-4">Tiers / Client</th>
                    <th class="p-4">Opérateur</th>
                    <th class="p-4 text-right">Montant</th>
                </tr>
            </thead>
            <tbody class="font-medium divide-y divide-slate-800/60">
                @forelse($transactions as $trx)
                <tr class="transition hover:bg-slate-800/40">
                    <td class="p-4 font-mono text-xs text-slate-400">
                        <span class="block font-bold text-white">{{ $trx->created_at->format('d/m/Y') }}</span>
                        {{ $trx->created_at->format('H:i:s') }}
                    </td>

                    <td class="p-4 font-mono text-xs font-bold text-emerald-400">
                        {{ $trx->transaction_number }}
                    </td>

                    <td class="p-4">
                        <span class="inline-block px-2 py-0.5 text-[10px] uppercase font-bold rounded {{ $trx->type === 'credit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                            {{ str_replace('_', ' ', $trx->category) }}
                        </span>
                        <p class="mt-1 text-xs text-slate-400 line-clamp-1">{{ $trx->description ?? 'Aucune remarque' }}</p>
                    </td>

                    <td class="p-4 text-xs text-white">
                        {{ $trx->user->name ?? 'N/A' }}
                    </td>

                    <td class="p-4 font-mono text-xs text-slate-400">
                        {{ $trx->operator->name ?? 'Système' }}
                    </td>

                    <td class="p-4 text-right font-mono font-bold text-sm {{ $trx->type === 'credit' ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $trx->type === 'credit' ? '+' : '-' }} {{ number_format($trx->amount, 0, ',', ' ') }} XAF
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-500">
                        Aucun mouvement financier trouvé dans le journal pour ces critères.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>

</div>
@endsection
