@extends('layouts.app')

@section('content')
<div x-data="{
    search: '',
    showCreateModal: false,
    selectedProduct: null,
    editProduct: null
}" class="space-y-6">

    @if(session('success'))
        <div class="p-4 text-sm font-medium border text-emerald-400 rounded-xl bg-emerald-500/10 border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Articles Boutique & Catalogue</h1>
            <p class="text-xs text-slate-400">Gérez le catalogue des équipements et produits disponibles en vente au comptant ou par tontine.</p>
        </div>
        <button @click="showCreateModal = true" class="flex items-center justify-center px-4 py-2.5 space-x-2 text-sm font-bold text-slate-950 bg-emerald-500 rounded-xl hover:bg-emerald-400 transition">
            <i class="bi bi-plus-lg"></i>
            <span>Nouvel Article</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Références Catalogue</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-box-seam"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalProducts, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Unités en Stock</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-layers"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalStock, 0, ',', ' ') }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Alertes Stock Bas</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-400 bg-amber-500/10">
                    <i class="text-lg bi bi-exclamation-triangle"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($lowStockCount, 0, ',', ' ') }}</p>
        </div>
    </div>

    <div class="p-4 border bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher un article ou référence..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($products as $product)
        <div x-show="search === '' || '{{ strtolower($product->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($product->reference) }}'.includes(search.toLowerCase())"
             class="flex flex-col justify-between overflow-hidden transition border bg-slate-900/60 border-slate-800 rounded-2xl hover:border-slate-700">

            <div>
                <div class="relative flex items-center justify-center h-48 border-b bg-slate-950 border-slate-800">
                    @if($product->primary_image)
                        <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
                    @else
                        <i class="text-4xl bi bi-image text-slate-700"></i>
                    @endif

                    <span class="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-bold font-mono rounded-full {{ $product->stock <= $product->alert_threshold ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                        Stock: {{ $product->stock }}
                    </span>
                </div>

                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-slate-500">{{ $product->reference }}</span>
                        @if($product->is_available)
                            <span class="text-[10px] font-bold text-emerald-400 uppercase">Disponible</span>
                        @else
                            <span class="text-[10px] font-bold text-rose-400 uppercase">Indisponible</span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-white">{{ $product->name }}</h3>
                    <p class="text-xs text-slate-400 line-clamp-2">{{ $product->description ?? 'Aucune description disponible.' }}</p>

                    <div class="pt-3 border-t border-slate-800/80 space-y-1.5 font-mono text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Comptant :</span>
                            <span class="font-bold text-white">{{ number_format($product->selling_price_cash, 0, ',', ' ') }} XAF</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Échelonné / Tontine :</span>
                            <span class="font-bold text-emerald-400">{{ number_format($product->selling_price_installment, 0, ',', ' ') }} XAF</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 border-t border-slate-800 bg-slate-950/40">
                <button @click='selectedProduct = @json($product, JSON_HEX_APOS | JSON_HEX_QUOT)' class="flex items-center space-x-1 text-xs font-semibold text-slate-300 hover:text-white">
                    <i class="bi bi-eye"></i>
                    <span>Détails</span>
                </button>

                <div class="flex items-center space-x-2">
                    <button @click='editProduct = @json($product, JSON_HEX_APOS | JSON_HEX_QUOT)' class="p-1.5 text-slate-400 hover:text-amber-400 transition" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('directeur.articles.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 transition" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center border col-span-full bg-slate-900/40 border-slate-800 rounded-2xl text-slate-500">
            Aucun article enregistré dans la boutique pour le moment.
        </div>
        @endforelse
    </div>

    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showCreateModal = false" class="w-full max-w-2xl p-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Ajouter un nouvel Article</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('directeur.articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Nom de l'Article *</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix d'Achat (XAF) *</label>
                        <input type="number" name="purchase_price" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix Comptant (XAF) *</label>
                        <input type="number" name="selling_price_cash" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix Échelonné (XAF) *</label>
                        <input type="number" name="selling_price_installment" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Stock Initial *</label>
                        <input type="number" name="stock" value="1" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Seuil d'Alerte Stock *</label>
                        <input type="number" name="alert_threshold" value="5" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Image Principale</label>
                    <input type="file" name="primary_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Photos de Galerie (Optionnel, jusqu'à 3 images)</label>
                    <input type="file" name="gallery_images[]" multiple accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                </div>

                <div class="flex justify-end pt-4 space-x-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-slate-950 bg-emerald-500 rounded-xl hover:bg-emerald-400">Enregistrer l'Article</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editProduct" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="editProduct = null" class="w-full max-w-2xl p-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Modifier l'Article</h3>
                <button @click="editProduct = null" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form :action="'/directeur/articles/' + editProduct?.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Nom de l'Article *</label>
                    <input type="text" name="name" :value="editProduct?.name" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Description</label>
                    <textarea name="description" rows="3" :value="editProduct?.description" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix d'Achat (XAF)</label>
                        <input type="number" name="purchase_price" :value="editProduct?.purchase_price" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix Comptant (XAF)</label>
                        <input type="number" name="selling_price_cash" :value="editProduct?.selling_price_cash" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Prix Échelonné (XAF)</label>
                        <input type="number" name="selling_price_installment" :value="editProduct?.selling_price_installment" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Stock</label>
                        <input type="number" name="stock" :value="editProduct?.stock" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Seuil d'Alerte</label>
                        <input type="number" name="alert_threshold" :value="editProduct?.alert_threshold" required min="0" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold uppercase text-slate-400">Remplacer l'image principale</label>
                    <input type="file" name="primary_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                </div>

                <div class="flex items-center pt-2 space-x-2">
                    <input type="checkbox" name="is_available" id="is_available" :checked="editProduct?.is_available" value="1" class="rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-0">
                    <label for="is_available" class="text-xs font-semibold text-slate-300">Article disponible à la vente</label>
                </div>

                <div class="flex justify-end pt-4 space-x-3 border-t border-slate-800">
                    <button type="button" @click="editProduct = null" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-slate-950 bg-amber-500 rounded-xl hover:bg-amber-400">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="selectedProduct" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="selectedProduct = null" class="w-full max-w-2xl p-6 space-y-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div>
                    <h3 class="text-lg font-bold text-white" x-text="selectedProduct?.name"></h3>
                    <p class="font-mono text-xs text-slate-400" x-text="'Réf : ' + selectedProduct?.reference"></p>
                </div>
                <button @click="selectedProduct = null" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <p class="text-xs text-slate-300" x-text="selectedProduct?.description || 'Aucune description disponible'"></p>

            <div class="space-y-2">
                <h4 class="text-xs font-bold tracking-wider uppercase text-slate-400">Photos de l'article</h4>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="(img, idx) in selectedProduct?.gallery_images || []" :key="idx">
                        <div class="h-24 overflow-hidden border rounded-xl bg-slate-950 border-slate-800">
                            <img :src="'/storage/' + img" class="object-cover w-full h-full">
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-800">
                <button @click="selectedProduct = null" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Fermer</button>
            </div>
        </div>
    </div>

</div>
@endsection
