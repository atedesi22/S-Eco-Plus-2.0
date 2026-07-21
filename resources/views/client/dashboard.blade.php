
<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S ECO PLUS - Mon Espace Client</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex flex-col justify-between min-h-screen pb-24 antialiased bg-slate-950 text-slate-100"
      x-data="{ currentTab: 'home', showTxModal: false, txType: 'depot', showTontineModal: false }">

    <header class="sticky top-0 z-40 flex items-center justify-between h-16 px-6 border-b border-slate-800 bg-slate-900/80 backdrop-blur">
        <div class="flex items-center space-x-3">
            <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
            <span class="text-sm font-bold tracking-wide text-white">S ECO PLUS</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-red-400 flex items-center space-x-2 bg-slate-800/50 px-3 py-1.5 rounded-xl border border-slate-700/50 transition">
                <i class="bi bi-box-arrow-right"></i>
                <span class="hidden sm:inline">Quitter</span>
            </button>
        </form>
    </header>

    <div class="w-full max-w-4xl px-4 mx-auto mt-4">
        @if(session('success'))
            <div class="flex items-center gap-2 p-4 text-xs font-semibold border bg-emerald-500/10 border-emerald-500/30 text-emerald-400 rounded-xl">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2 p-4 text-xs font-semibold text-red-400 border bg-red-500/10 border-red-500/30 rounded-xl">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif
    </div>

    <main class="flex-1 w-full max-w-4xl p-4 mx-auto space-y-6 sm:p-6">

        <!-- ================= ONGLET ACCUEIL ================= -->
        <div x-show="currentTab === 'home'" class="space-y-6" x-transition>
            <div class="flex items-center justify-between p-6 border bg-gradient-to-r from-slate-900 to-slate-900/40 border-slate-800 rounded-2xl">
                <div>
                    <h3 class="text-xl font-bold text-white">Ravi de vous revoir, {{ $client->name }}</h3>
                    <p class="mt-1 text-xs text-slate-400">Consultez l'évolution de votre épargne en toute sécurité.</p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 border rounded-full bg-slate-800 border-slate-700 text-emerald-400">
                    <i class="text-xl bi bi-person-circle"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative p-6 overflow-hidden border bg-gradient-to-br from-emerald-500/10 to-slate-900/50 border-emerald-500/20 rounded-2xl">
                    <p class="text-xs font-bold tracking-wider uppercase text-emerald-400">Solde Total Épargné</p>
                    <h2 class="mt-2 text-3xl font-black text-white">{{ number_format($account->balance) }} XAF</h2>
                    <p class="text-[10px] text-slate-500 mt-2">Numéro de compte : {{ $account->account_number }}</p>
                </div>

                <div class="relative p-6 overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
                    <p class="text-xs font-bold tracking-wider uppercase text-slate-400">Disponible pour Retrait</p>
                    @php
                        $balance = $account->balance;
                        $reserve = $account->reserve_fund;
                        $disponible = max($balance - $reserve, 0);
                    @endphp
                    <h2 class="mt-2 text-3xl font-black text-white">{{ number_format($disponible) }} XAF</h2>
                    <p class="text-[10px] text-amber-400/80 mt-2 flex items-center gap-1">
                        <i class="bi bi-info-circle-fill"></i> Fond de réserve obligatoire ({{ number_format($reserve) }} XAF) bloqué.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 border bg-slate-900 border-slate-800 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center rounded-lg w-9 h-9 bg-emerald-500/10 text-emerald-400">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Tontine Principale (Courante)</p>
                            <p class="text-[10px] text-slate-500">Compte N°{{ $account->account_number ?? $account->id }}-MAIN</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-emerald-400">+{{ number_format($account->balance) }} XAF</p>
                    </div>
                </div>

                @forelse($subAccounts as $sub)
                    <div class="flex items-center justify-between p-4 border bg-slate-900 border-slate-800 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-{{ $sub->color }}-500/10 text-{{ $sub->color }}-400 flex items-center justify-center">
                                <i class="bi bi-pie-chart"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">{{ $sub->name }}</p>
                                <p class="text-[10px] text-slate-500">Sous-compte N°{{ $account->id }}-{{ $sub->code }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-{{ $sub->color }}-400">+{{ number_format($sub->balance) }} XAF</p>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            <div class="p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <h4 class="mb-4 text-sm font-bold text-white">Historique des mouvements</h4>
                @if(count($transactions) > 0)
                    <div class="space-y-3">
                        @foreach($transactions as $trx)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/40 border border-slate-800/60">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs {{ $trx->type === 'depot' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                        <i class="bi {{ $trx->type === 'depot' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-200">{{ $trx->title }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <p class="text-xs font-bold {{ $trx->type === 'depot' ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $trx->type === 'depot' ? '+' : '-' }} {{ number_format($trx->amount) }} XAF
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-xs text-center text-slate-500">
                        <i class="block mb-2 text-3xl bi bi-journal-text text-slate-600"></i>
                        Aucun mouvement récent sur votre compte.
                    </div>
                @endif
            </div>
        </div>

        <!-- ================= ONGLET FACTURES & TRANSACTIONS ================= -->
        <div x-show="currentTab === 'factures'" class="space-y-6" x-transition>
            <div class="p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <h3 class="text-lg font-bold text-white">Factures & Opérations Express</h3>
                <p class="mt-1 text-xs text-slate-400">Initiez vos demandes de mouvements financiers ou réglez vos factures.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <button @click="txType = 'deposit'; showTxModal = true" class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl transition rounded-full bg-emerald-500/10 text-emerald-400 group-hover:scale-110"><i class="bi bi-arrow-down-left-circle"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Demande de Dépôt</span>
                </button>
                <button @click="txType = 'withdrawal'; showTxModal = true" class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl text-red-400 transition rounded-full bg-red-500/10 group-hover:scale-110"><i class="bi bi-arrow-up-right-circle"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Faire un Retrait</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl text-yellow-400 transition rounded-full bg-yellow-500/10 group-hover:scale-110"><i class="bi bi-lightning-charge"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Électricité (Eneo)</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl text-blue-400 transition rounded-full bg-blue-500/10 group-hover:scale-110"><i class="bi bi-droplet"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Facture d'Eau</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl text-purple-400 transition rounded-full bg-purple-500/10 group-hover:scale-110"><i class="bi bi-mortarboard"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Frais de Scolarité</span>
                </button>
                <button class="flex flex-col items-center justify-center gap-2 p-4 text-center transition border bg-slate-900 border-slate-800 hover:border-emerald-500/50 rounded-xl group">
                    <div class="flex items-center justify-center w-12 h-12 text-xl text-pink-400 transition rounded-full bg-pink-500/10 group-hover:scale-110"><i class="bi bi-phone-vibrate"></i></div>
                    <span class="text-xs font-semibold text-slate-200">Achat Crédit / Data</span>
                </button>
            </div>
        </div>

        <!-- ================= ONGLET SHOP / CATALOGUE ================= -->
        <div x-show="currentTab === 'shop'" class="space-y-6" x-transition>
            <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <div>
                    <h3 class="text-lg font-bold text-white">Boutique Électroménager & Tontines</h3>
                    <p class="mt-1 text-xs text-slate-400">Souscrivez à un produit et payez-le petit à petit via votre système de tontine.</p>
                </div>
                <button class="relative p-2.5 bg-slate-800 border border-slate-700 rounded-xl text-slate-300 hover:text-emerald-400 transition">
                    <i class="text-lg bi bi-bag"></i>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 text-[9px] font-bold text-slate-950 rounded-full flex items-center justify-center">2</span>
                </button>
            </div>

            <!-- Grille Produits -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Produit 1 -->
                <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                    <div class="relative flex items-center justify-center h-40 border-b bg-slate-950 border-slate-800/60">
                        <i class="text-5xl transition duration-300 bi bi-tv text-slate-700 group-hover:scale-110"></i>
                        <span class="absolute top-3 left-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] uppercase font-bold px-2 py-0.5 rounded">Tontine Dispo</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <h5 class="text-sm font-bold text-white">Smart TV LED 43" Digital</h5>
                            <p class="text-[11px] text-slate-400 mt-0.5">Éligible au paiement quotidien ou hebdomadaire.</p>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase">Prix Total</p>
                                <p class="text-sm font-black text-white">185,000 XAF</p>
                            </div>
                            <button class="px-3 py-1.5 bg-emerald-500 text-slate-950 text-xs font-bold rounded-xl hover:bg-emerald-400 transition flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Choisir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Produit 2 -->
                <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                    <div class="relative flex items-center justify-center h-40 border-b bg-slate-950 border-slate-800/60">
                        <i class="text-5xl transition duration-300 bi bi-smartwatch text-slate-700 group-hover:scale-110"></i>
                        <span class="absolute top-3 left-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] uppercase font-bold px-2 py-0.5 rounded">Tontine Dispo</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <h5 class="text-sm font-bold text-white">Réfrigérateur Combiné 220L</h5>
                            <p class="text-[11px] text-slate-400 mt-0.5">Idéal pour les ménages, garantie 12 mois constructeur.</p>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase">Prix Total</p>
                                <p class="text-sm font-black text-white">240,000 XAF</p>
                            </div>
                            <button class="px-3 py-1.5 bg-emerald-500 text-slate-950 text-xs font-bold rounded-xl hover:bg-emerald-400 transition flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Choisir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ONGLET TONTINES (DÉTAILLÉ) ================= -->
        <div x-show="currentTab === 'tontines'" class="space-y-6" x-transition>
            <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <div>
                    <h3 class="text-lg font-bold text-white">Mes Cotisations & Tontines</h3>
                    <p class="mt-1 text-xs text-slate-400">Suivez l'état de vos différents sous-comptes projets.</p>
                </div>
                <button @click="showTontineModal = true" class="px-3 py-1.5 bg-emerald-500 text-slate-950 rounded-xl text-xs font-bold hover:bg-emerald-400 transition">
                    + Nouvelle Tontine
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                @php
                    // Par exemple, l'objectif du compte principal peut être infini ou fixe
                    $mainTarget = 1000000; // Ou une valeur de ton choix
                    $mainProgress = min(round(($account->balance / $mainTarget) * 100), 100);
                @endphp
                <div class="p-4 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 text-xl rounded-xl bg-emerald-500/10 text-emerald-400">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Tontine Principale</h4>
                                <span class="text-[10px] px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-full font-semibold">
                                    Solde de base sécurisé
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-white">{{ number_format($account->balance) }} XAF</p>
                            <p class="text-[10px] text-slate-500">Actif</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] text-slate-400">
                            <span>Progression globale</span>
                            <span>{{ $mainProgress }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $mainProgress }}%"></div>
                        </div>
                    </div>
                </div>


                @foreach($subAccounts as $sub)
                    @php
                        $target = $sub->target_amount ?? 100000;
                        $progress = min(round(($sub->balance / $target) * 100), 100);
                    @endphp
                    <div class="p-4 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-{{ $sub->color }}-500/10 text-{{ $sub->color }}-400 flex items-center justify-center text-xl">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">{{ $sub->name }}</h4>
                                    <span class="text-[10px] px-2 py-0.5 bg-{{ $sub->color }}-500/10 text-{{ $sub->color }}-400 rounded-full font-semibold">
                                        Objectif : {{ number_format($target) }} XAF
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-white">{{ number_format($sub->balance) }} XAF</p>
                                <p class="text-[10px] text-slate-500">{{ ucfirst($sub->status) }}</p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[10px] text-slate-400">
                                <span>Progression</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-{{ $sub->color }}-500 h-full rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        <!-- ================= ONGLET PROFIL ================= -->
        <div x-show="currentTab === 'profile'" class="space-y-6" x-transition>
            <div class="p-6 space-y-3 text-center border bg-slate-900 border-slate-800 rounded-2xl">
                <div class="flex items-center justify-center w-20 h-20 mx-auto text-3xl border-2 rounded-full bg-slate-800 border-emerald-500 text-emerald-400">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">{{ $client->name }}</h3>
                    <p class="text-xs text-slate-400">Client Adhérent S ECO PLUS</p>
                </div>
                <div class="pt-2">
                    <span class="text-[10px] font-bold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 uppercase px-3 py-1 rounded-full tracking-wider">Compte Certifié</span>
                </div>
            </div>

            <div class="border divide-y bg-slate-900 border-slate-800 rounded-2xl divide-slate-800/60">
                <div class="flex justify-between p-4 text-xs"><span class="text-slate-400">Identifiant Unique</span><span class="font-semibold text-white">#SECO-{{ $client->id ?? '0000' }}</span></div>
                <div class="flex justify-between p-4 text-xs"><span class="text-slate-400">Adresse Email</span><span class="font-semibold text-white">{{ $client->email }}</span></div>
                <div class="flex justify-between p-4 text-xs"><span class="text-slate-400">Agence de Rattachement</span><span class="font-semibold text-emerald-400">Agence Fille Centrale</span></div>
                <div class="flex justify-between p-4 text-xs"><span class="text-slate-400">Sécurité</span><span class="font-semibold text-slate-300">Chiffrement de bout en bout (SSL)</span></div>
            </div>
        </div>


    </main>

    <div x-show="showTxModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-transition>
        <div @click.away="showTxModal = false" class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white" x-text="txType === 'deposit' ? 'Nouvelle demande de Dépôt' : 'Nouvelle demande de Retrait'"></h3>
                <button @click="showTxModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('client.transaction.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">

                <input type="hidden" name="type" :value="txType">

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400" x-text="txType === 'deposit' ? 'Destination des fonds' : 'Source du retrait'"></label>
                    <select name="sub_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">Compte Principal (Solde général)</option>
                        @foreach($subAccounts as $sub)
                            <option value="{{ $sub->id }}">
                                {{ $sub->name }} (Code: {{ $sub->code }} - Actuel: {{ number_format($sub->balance) }} XAF)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400">Montant de l'opération (XAF)</label>
                    <input type="number" name="amount" required min="500" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500" placeholder="Ex: 5000">
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400">Motif / Description</label>
                    <input type="text" name="description" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500" placeholder="Ex: Cotisation de la semaine, Urgence...">
                </div>

                <button type="submit" class="w-full py-3 text-xs font-bold transition bg-emerald-500 text-slate-950 rounded-xl hover:bg-emerald-400">
                    Confirmer l'opération
                </button>
            </form>
        </div>
    </div>

    <div x-show="showTontineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-transition>
        <div @click.away="showTontineModal = false" class="w-full max-w-md p-6 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white">Souscrire à une Tontine / Sous-compte</h3>
                <button @click="showTontineModal = false" class="text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('client.subaccount.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400">Choix du type de tontine</label>
                    <select name="tontine_plan_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($tontinePlans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400">Nom du projet / Intitulé</label>
                    <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500" placeholder="Ex: Mon Achat Moto, Fonds de commerce...">
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-400">Montant Objectif Ciblé (XAF)</label>
                    <input type="number" name="target_amount" required min="500" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500" placeholder="Ex: 500000">
                </div>

                <button type="submit" class="w-full py-3 text-xs font-bold transition bg-emerald-500 text-slate-950 rounded-xl hover:bg-emerald-400">
                    Créer le sous-compte
                </button>
            </form>
        </div>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 z-50 max-w-lg px-2 py-2 mx-auto border-t shadow-2xl bg-slate-900/90 backdrop-blur-md border-slate-800 sm:rounded-t-2xl">
        <div class="flex items-center justify-around">

            <!-- Accueil -->
            <button @click="currentTab = 'home'" :class="currentTab === 'home' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400'" class="flex flex-col items-center justify-center flex-1 py-2 transition rounded-xl">
                <i class="text-xl bi bi-house-door"></i>
                <span class="text-[10px] font-medium mt-1">Accueil</span>
            </button>

            <!-- Factures / Transactions -->
            <button @click="currentTab = 'factures'" :class="currentTab === 'factures' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400'" class="flex flex-col items-center justify-center flex-1 py-2 transition rounded-xl">
                <i class="text-xl bi bi-receipt"></i>
                <span class="text-[10px] font-medium mt-1">Factures</span>
            </button>

            <!-- Boutique (Shop) -->
            <button @click="currentTab = 'shop'" :class="currentTab === 'shop' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-slate-200'" class="flex flex-col items-center justify-center flex-1 py-2 transition duration-200 rounded-xl">
                <i class="text-xl bi" :class="currentTab === 'shop' ? 'bi-bag-dash-fill' : 'bi-bag-dash'"></i>
                <span class="text-[10px] font-medium mt-1">Shop</span>
            </button>

            <!-- Tontines -->
            <button @click="currentTab = 'tontines'" :class="currentTab === 'tontines' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400'" class="flex flex-col items-center justify-center flex-1 py-2 transition rounded-xl">
                <i class="text-xl bi bi-pie-chart"></i>
                <span class="text-[10px] font-medium mt-1">Tontines</span>
            </button>

            <!-- Profil -->
            <button @click="currentTab = 'profile'" :class="currentTab === 'profile' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-slate-200'" class="flex flex-col items-center justify-center flex-1 py-2 transition duration-200 rounded-xl">
                <i class="text-xl bi" :class="currentTab === 'profile' ? 'bi-person-fill' : 'bi-person'"></i>
                <span class="text-[10px] font-medium mt-1">Profil</span>
            </button>
        </div>
    </nav>


    @if(session()->has('show_emprunt_warning'))
        @php $alert = session('show_emprunt_warning'); @endphp
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border {{ $alert['is_overdue'] ? 'border-rose-500' : 'border-amber-500' }} p-6 rounded-xl max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center space-x-3 text-white">
                    <i class="bi {{ $alert['is_overdue'] ? 'bi-shield-slash-fill text-rose-500' : 'bi-exclamation-triangle-fill text-amber-500' }} text-2xl"></i>
                    <h3 class="text-sm font-black tracking-wider uppercase">
                        {{ $alert['is_overdue'] ? 'Remboursement Impératif' : 'Rappel d\'Échéance' }}
                    </h3>
                </div>
                <p class="text-xs leading-relaxed text-slate-300">
                    @if($alert['is_overdue'])
                        <span class="font-bold text-rose-400">Attention :</span> Votre date limite de remboursement du <span class="font-mono text-white">{{ $alert['deadline'] }}</span> est dépassée. Toutes vos prochaines opérations seront automatiquement prélevées pour solder votre dette.
                    @else
                        Vous avez une pénalité en cours. Il vous reste <span class="font-mono font-bold text-white">{{ number_format($alert['amount_left']) }} XAF</span> à cotiser dans votre <span class="underline">Tontine Emprunt</span> avant le <span class="font-mono text-white">{{ $alert['deadline'] }}</span>.
                    @endif
                </p>
                <div class="flex justify-end">
                    <button @click="open = false" class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider {{ $alert['is_overdue'] ? 'bg-rose-600 hover:bg-rose-500 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-300' }} rounded-lg transition">
                        J'ai compris
                    </button>
                </div>
            </div>
        </div>
    @endif

</body>
</html>
