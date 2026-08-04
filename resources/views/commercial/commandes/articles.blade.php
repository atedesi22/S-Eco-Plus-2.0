@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-6 bg-slate-950 text-slate-100" x-data="orderModalData()">

    <!-- En-tête -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="bi bi-shop text-emerald-400"></i> Catalogue Produits Boutique
            </h1>
            <p class="text-xs text-slate-400">Proposez nos articles en vente au comptant ou en tontine échelonnée (60% / 40%).</p>
        </div>

        <form action="{{ route('commercial.articles.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un article..." class="px-3 py-2 text-xs text-white border outline-none bg-slate-900 border-slate-800 rounded-xl focus:border-emerald-500">
            <button type="submit" class="px-3 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 rounded-xl"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <!-- Grille des Produits -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @forelse($products as $product)
            <div class="flex flex-col justify-between p-4 space-y-3 transition border bg-slate-900/80 border-slate-800 rounded-2xl hover:border-slate-700">
                <div class="space-y-2">
                    <div class="relative w-full h-40 overflow-hidden border rounded-xl bg-slate-950 border-slate-800">
                        @if($product->primary_image)
                            <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
                        @else
                            <div class="flex items-center justify-center w-full h-full text-slate-700"><i class="text-4xl bi bi-box-seam"></i></div>
                        @endif
                        <span class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-950/80 backdrop-blur-sm text-slate-300 font-mono border border-slate-800">
                            Réf: {{ $product->reference }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-white line-clamp-1">{{ $product->name }}</h3>
                    <p class="text-xs text-slate-400 line-clamp-2">{{ $product->description ?? 'Aucune description disponible.' }}</p>
                </div>

                <div class="pt-2 space-y-3 border-t border-slate-800">
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Prix Comptant :</span>
                            <span class="font-mono font-bold text-white">{{ number_format($product->selling_price_cash, 0, ',', ' ') }} XAF</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Prix Échelonné :</span>
                            <span class="font-mono font-bold text-emerald-400">{{ number_format($product->selling_price_installment, 0, ',', ' ') }} XAF</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-amber-400 font-mono">
                            <span>Seuil Livraison (60%) :</span>
                            <span>{{ number_format(ceil($product->selling_price_installment * 0.60), 0, ',', ' ') }} XAF</span>
                        </div>
                    </div>

                    <button @click="openModal({{ json_encode($product) }})" class="flex items-center justify-center w-full gap-2 py-2 text-xs font-bold transition shadow-lg text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-emerald-500/10">
                        <i class="bi bi-pen-fill"></i> Remplir Protocole
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-xs italic text-center border border-dashed col-span-full text-slate-500 border-slate-800 rounded-2xl">
                Aucun produit disponible dans le catalogue pour le moment.
            </div>
        @endforelse
    </div>

    <!-- MODALE DU PROTOCOLE D'ACCORD ET SIGNATURE NUMÉRIQUE -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.outside="showModal = false" class="w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 bg-slate-900 border border-slate-800 rounded-2xl space-y-4">

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white">Protocole d'Accord & Prise de Commande</h3>
                    <p class="text-[11px] text-slate-400">Produit : <strong class="text-emerald-400" x-text="selectedProduct?.name"></strong></p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('commercial.commandes.store') }}" method="POST" @submit="submitForm($event)" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" :value="selectedProduct?.id">
                <input type="hidden" name="client_signature" x-ref="clientSigInput">
                <input type="hidden" name="agent_signature" x-ref="agentSigInput">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Client Bénéficiaire *</label>
                        <select name="user_id" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                            <option value="">-- Choisir un client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-300">Mode de Règlement *</label>
                        <select name="payment_type" x-model="paymentType" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                            <option value="installment">Échelonné (Collecte Tontine Article)</option>
                            <option value="cash">Paiement Comptant</option>
                        </select>
                    </div>
                </div>

                <!-- TERMES DU PROTOCOLE D'ACCORD -->
                <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl space-y-2 text-[11px] text-slate-300 leading-relaxed font-mono">
                    <p class="font-bold uppercase text-amber-400">Clauses du Protocole d'Accord :</p>
                    <ul class="pl-4 space-y-1 list-disc">
                        <li>Le souscripteur s'engage à effectuer des versements quotidiens/hebdomadaires réguliers.</li>
                        <li><strong>Règle des 60% :</strong> Dès que le total des souscriptions atteint <span class="font-bold text-emerald-400" x-text="formatMoney(calculateThreshold())"></span> XAF (60% de la valeur), une alerte est transmise pour valider la livraison physique.</li>
                        <li><strong>Reliquat 40% :</strong> Le client s'engage à solder les 40% restants selon le planning convenu post-livraison.</li>
                    </ul>
                </div>

                <!-- ZONE DE SIGNATURE NUMÉRIQUE (CANVAS HTML5) -->
                <div class="grid grid-cols-1 gap-4 pt-2 md:grid-cols-2">
                    <!-- Signature Client -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold text-slate-300">Signature Numérique du Client *</label>
                        <div class="relative p-1 border border-slate-800 bg-slate-950 rounded-xl">
                            <canvas id="clientCanvas" width="280" height="120" class="w-full border rounded-lg h-28 border-slate-800/60 touch-none bg-slate-900/50 cursor-crosshair"></canvas>
                            <button type="button" @click="clearCanvas('clientCanvas')" class="absolute top-2 right-2 text-[10px] px-2 py-0.5 bg-slate-800 text-slate-400 rounded hover:text-white">Effacer</button>
                        </div>
                    </div>

                    <!-- Signature Agent/Commercial -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold text-slate-300">Signature Numérique Commercial *</label>
                        <div class="relative p-1 border border-slate-800 bg-slate-950 rounded-xl">
                            <canvas id="agentCanvas" width="280" height="120" class="w-full border rounded-lg h-28 border-slate-800/60 touch-none bg-slate-900/50 cursor-crosshair"></canvas>
                            <button type="button" @click="clearCanvas('agentCanvas')" class="absolute top-2 right-2 text-[10px] px-2 py-0.5 bg-slate-800 text-slate-400 rounded hover:text-white">Effacer</button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold shadow-lg text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-emerald-500/10">Valider & Signer le Protocole</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- SCRIPT DE GESTION SIGNATURE CANVAS HTML5 -->
<script>
function orderModalData() {
    return {
        showModal: false,
        selectedProduct: null,
        paymentType: 'installment',
        clientPad: null,
        agentPad: null,

        openModal(product) {
            this.selectedProduct = product;
            this.showModal = true;
            this.$nextTick(() => {
                this.initCanvases();
            });
        },

        calculateThreshold() {
            if(!this.selectedProduct) return 0;
            const price = this.paymentType === 'cash' ? this.selectedProduct.selling_price_cash : this.selectedProduct.selling_price_installment;
            return Math.ceil(price * 0.60);
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount);
        },

        initCanvases() {
            this.setupCanvas('clientCanvas');
            this.setupCanvas('agentCanvas');
        },

        setupCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let isDrawing = false;

            ctx.strokeStyle = '#34d399'; // Emerald color
            ctx.lineWidth = 2;

            const getPos = (e) => {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return { x: clientX - rect.left, y: clientY - rect.top };
            };

            const startDrawing = (e) => { isDrawing = true; const pos = getPos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); };
            const draw = (e) => { if (!isDrawing) return; const pos = getPos(e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); };
            const stopDrawing = () => { isDrawing = false; };

            canvas.onmousedown = startDrawing;
            canvas.onmousemove = draw;
            canvas.onmouseup = stopDrawing;

            canvas.ontouchstart = startDrawing;
            canvas.ontouchmove = draw;
            canvas.ontouchend = stopDrawing;
        },

        clearCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        },

        submitForm(e) {
            const clientCanvas = document.getElementById('clientCanvas');
            const agentCanvas = document.getElementById('agentCanvas');

            this.$refs.clientSigInput.value = clientCanvas.toDataURL();
            this.$refs.agentSigInput.value = agentCanvas.toDataURL();
        }
    }
}
</script>
@endsection
