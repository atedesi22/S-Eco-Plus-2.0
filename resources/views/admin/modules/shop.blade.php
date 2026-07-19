@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-wide text-white">STOCKS & BOUTIQUE CENTRALE</h1>
            <p class="text-xs text-slate-400">Gestion des marchandises destinées aux tontines d'acquisition et ventes physiques</p>
        </div>
        <!-- Bouton d'action rapide pour le SuperAdmin -->
        <button onclick="document.getElementById('productModal').classList.remove('hidden')" class="px-4 py-2 text-xs font-bold transition shadow-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl shadow-emerald-500/10">
            + Référencer un Produit
        </button>
    </div>

    <!-- TABLEAU DES PRODUITS EN STOCK -->
    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Référence</th>
                        <th class="pb-3">Désignation</th>
                        <th class="pb-3">Prix d'Achat</th>
                        <th class="pb-3">Prix de Vente</th>
                        <th class="pb-3 text-center">Quantité Dispo</th>
                        <th class="pb-3 text-right">Alerte Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400">
                    @forelse($products as $product)
                    <tr class="{{ $product->stock <= $product->alert_threshold ? 'bg-rose-500/5' : '' }}">
                        <td class="py-3.5 font-mono font-bold text-slate-400">#{{ $product->reference }}</td>
                        <td class="py-3.5 text-white font-medium">{{ $product->name }}</td>
                        <td class="py-3.5 font-mono text-slate-400">{{ number_format($product->purchase_price, 0, '.', ' ') }} XAF</td>
                        <td class="py-3.5 font-mono font-bold text-emerald-400">{{ number_format($product->selling_price, 0, '.', ' ') }} XAF</td>
                        <td class="py-3.5 text-center font-bold font-mono text-white">{{ $product->stock }}</td>
                        <td class="py-3.5 text-right">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $product->stock <= $product->alert_threshold ? 'bg-rose-500/10 text-rose-400' : 'bg-slate-950 text-slate-500' }}">
                                {{ $product->stock <= $product->alert_threshold ? 'Seuil critique' : 'Ok' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 italic text-center text-slate-500">Aucun article disponible en boutique.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MINI MODAL DE CRÉATION DE PRODUIT (SIMPLE JAVASCRIPT HIDE/SHOW) -->
<div id="productModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold tracking-wider text-white uppercase">Ajouter un produit au stock</h3>
            <button onclick="document.getElementById('productModal').classList.add('hidden')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <form action="{{ route('admin.shop.products.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block mb-1 text-slate-400">Nom de l'article</label>
                <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-slate-400">Prix Achat (XAF)</label>
                    <input type="number" name="purchase_price" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block mb-1 text-slate-400">Prix Vente (XAF)</label>
                    <input type="number" name="selling_price" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>
            <div>
                <label class="block mb-1 text-slate-400">Stock Initial</label>
                <input type="number" name="stock" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white focus:outline-none focus:border-emerald-500">
            </div>
            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold p-3 rounded-lg transition uppercase tracking-wider text-[11px]">
                Enregistrer au Catalogue
            </button>
        </form>
    </div>
</div>
@endsection
