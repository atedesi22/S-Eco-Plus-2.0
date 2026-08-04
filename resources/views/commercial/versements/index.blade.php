@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 space-y-6 font-sans md:p-6 bg-slate-950 text-slate-100" x-data="{ newDepositModal: false }">

    <!-- Notification -->
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
                <i class="bi bi-cash-coin text-emerald-400"></i> Versements en Caisse
            </h1>
            <p class="text-xs text-slate-400">Déclarez les cash collectés et suivez la validation par les caissiers d'agence.</p>
        </div>

        <button @click="newDepositModal = true" class="px-4 py-2.5 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl transition shadow-lg shadow-emerald-500/10 flex items-center gap-2 w-fit">
            <i class="text-sm bi bi-plus-circle-fill"></i> Nouveau Versement Caisse
        </button>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="p-5 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Validé Caisse</span>
            <div class="mt-1 font-mono text-2xl font-bold text-emerald-400">{{ number_format($stats['total_verse'], 0, ',', ' ') }} XAF</div>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">En Attente de Confirmation</span>
            <div class="mt-1 font-mono text-2xl font-bold text-amber-400">{{ number_format($stats['en_attente'], 0, ',', ' ') }} XAF</div>
            <span class="text-[10px] text-slate-500 font-mono">{{ $stats['nb_pending'] }} bordereau(x) non validé(s)</span>
        </div>

        <div class="flex items-center justify-between p-5 border bg-slate-900/60 border-slate-800/80 rounded-2xl">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Procédure</span>
                <p class="mt-1 text-xs text-slate-300">Remettez les espèces au guichet avec la référence attribuée.</p>
            </div>
            <i class="text-2xl bi bi-shield-check text-cyan-400"></i>
        </div>
    </div>

    <!-- Tableau des Versements -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Référence</th>
                        <th class="p-3">Type / Motif</th>
                        <th class="p-3">Montant</th>
                        <th class="p-3">Reçu / Pièce</th>
                        <th class="p-3">Statut Caisse</th>
                        <th class="p-3">Date Soumission</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($versements as $versement)
                        <tr class="transition hover:bg-slate-800/50">
                            <td class="p-3 font-mono font-bold text-white">{{ $versement->reference_code }}</td>
                            <td class="p-3">
                                @switch($versement->deposit_type)
                                    @case('frais_dossier')
                                        <span class="font-medium text-slate-200">Frais d'ouverture dossier</span>
                                        @break
                                    @case('vente_boutique')
                                        <span class="font-medium text-slate-200">Vente / Acompte Boutique</span>
                                        @break
                                    @case('collecte_globale')
                                        <span class="font-medium text-slate-200">Collecte Terrain Globale</span>
                                        @break
                                    @default
                                        <span class="font-medium text-slate-200">Autre Versement</span>
                                @endswitch
                            </td>
                            <td class="p-3 font-mono text-sm font-bold text-emerald-400">
                                {{ number_format($versement->amount, 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-3">
                                @if($versement->receipt_photo)
                                    <a href="{{ asset('storage/' . $versement->receipt_photo) }}" target="_blank" class="text-cyan-400 underline hover:text-cyan-300 text-[11px] font-mono">
                                        <i class="bi bi-image"></i> Voir Reçu
                                    </a>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Aucun reçu</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($versement->status === 'approved')
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/60 rounded-full flex items-center gap-1 w-fit">
                                        <i class="bi bi-check-circle-fill"></i> Validé ({{ $versement->cashier->name ?? 'Caisse' }})
                                    </span>
                                @elseif($versement->status === 'pending')
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-amber-400 bg-amber-950/40 border border-amber-800/60 rounded-full flex items-center gap-1 w-fit">
                                        <i class="bi bi-clock-history"></i> En attente caisse
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-rose-400 bg-rose-950/40 border border-rose-800/60 rounded-full flex items-center gap-1 w-fit">
                                        <i class="bi bi-x-circle-fill"></i> Rejeté
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-[11px] text-slate-400">
                                {{ $versement->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 italic text-center text-slate-500">Aucun bordereau de versement enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $versements->links() }}
        </div>
    </div>

    <!-- MODALE NOUVEAU VERSEMENT -->
    <div x-show="newDepositModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.outside="newDepositModal = false" class="w-full max-w-lg p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Déclarer un Versement en Caisse</h3>
                <button @click="newDepositModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('commercial.versements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Montant Déposé (XAF) *</label>
                    <input type="number" min="500" step="100" name="amount" required placeholder="Ex: 50000" class="w-full px-3 py-2 font-mono text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Motif / Nature des fonds *</label>
                    <select name="deposit_type" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                        <option value="frais_dossier">Frais d'ouverture / Dossier</option>
                        <option value="vente_boutique">Acompte / Vente Article Boutique</option>
                        <option value="collecte_globale">Versements issus de la tournée</option>
                        <option value="autre">Autre motif</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Photo du bordereau / Reçu signé (Optionnel)</label>
                    <input type="file" name="receipt_photo" accept="image/*" class="w-full text-xs border text-slate-400 border-slate-800 rounded-xl bg-slate-950 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700">
                </div>

                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-slate-300">Note explicative (Optionnel)</label>
                    <textarea name="notes" rows="2" placeholder="Détails supplémentaires..." class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="newDepositModal = false" class="px-4 py-2 text-xs text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold shadow-lg text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl shadow-emerald-500/10">Soumettre au Caissier</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
