<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S ECO PLUS 2.0 - Épargne & Électroménager</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Animation personnalisée pour la pièce qui rebondit et plonge */
        @keyframes coin-drop {
            0% { transform: translateY(-80px) rotate(0deg); opacity: 0; }
            30% { transform: translateY(0) rotate(180deg); opacity: 1; }
            50% { transform: translateY(-20px) rotate(270deg); }
            75% { transform: translateY(0) rotate(360deg); }
            100% { transform: translateY(25px) scale(0.6); opacity: 0; }
        }
        /* Animation légère pour le portefeuille qui "gobe" la pièce */
        @keyframes wallet-pulse {
            0%, 100% { transform: scale(1); }
            60% { transform: scale(1.1); }
        }
        .animate-coin { animation: coin-drop 4s infinite ease-in-out; }
        .animate-wallet { animation: wallet-pulse 4s infinite ease-in-out; }
    </style>
</head>
<body class="antialiased bg-slate-950 text-slate-100" x-data="{ loading: true }" x-init="window.addEventListener('load', () => { setTimeout(() => loading = false, 2500) })">

    <div x-show="loading"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950">

        <div class="relative flex flex-col items-center justify-center w-32 h-40">
            <div class="absolute top-0 z-10 flex items-center justify-center w-8 h-8 border rounded-full shadow-lg bg-gradient-to-r from-amber-400 to-yellow-500 border-amber-300 animate-coin">
                <i class="text-xs font-bold bi bi-currency-exchange text-amber-950"></i>
            </div>

            <div class="absolute text-6xl bottom-6 text-emerald-400 animate-wallet">
                <i class="shadow-2xl bi bi-wallet2"></i>
            </div>
        </div>

        <div class="mt-4 space-y-1 text-center">
            <h2 class="text-xl font-bold tracking-wider text-white">S ECO PLUS 2.0</h2>
            <p class="text-xs tracking-widest uppercase text-slate-400 animate-pulse">Sécurisation de votre avenir...</p>
        </div>
    </div>


    <div x-show="!loading" x-transition:enter="transition ease-out duration-500" class="flex flex-col min-h-screen">

        <nav class="sticky top-0 z-40 flex items-center justify-between h-16 px-6 border-b border-slate-900 bg-slate-950/80 backdrop-blur">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold rounded-lg bg-emerald-500 text-slate-950">S</div>
                <span class="text-lg font-bold tracking-wider text-white">S ECO <span class="text-emerald-400">PLUS</span></span>
            </div>
            <div class="items-center hidden space-x-8 text-sm font-medium md:flex text-slate-400">
                <a href="#epargne" class="transition hover:text-emerald-400">Solutions Épargne</a>
                <a href="#electro" class="transition hover:text-cyan-400">Électroménager</a>
                <a href="#propos" class="transition hover:text-white">À propos</a>
            </div>
            <div>
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold transition border bg-slate-900 border-slate-800 rounded-xl hover:bg-slate-800">
                    <i class="bi bi-shield-lock-fill mr-1.5 text-emerald-400"></i> Accès Sécurisé
                </a>
            </div>
        </nav>

        <header class="relative px-6 py-24 overflow-hidden border-b border-slate-900 bg-gradient-to-b from-slate-900/50 via-slate-950 to-slate-950">
            <div class="relative z-10 max-w-5xl mx-auto space-y-6 text-center">
                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-[11px] font-bold uppercase tracking-widest rounded-full border border-emerald-500/20">
                    Microfinance & Équipement Moderne
                </span>
                <h1 class="max-w-3xl mx-auto text-4xl font-extrabold leading-tight tracking-tight text-white md:text-6xl">
                    Financez votre quotidien, réalisez vos <span class="text-transparent bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text">grands projets</span>.
                </h1>
                <p class="max-w-xl mx-auto text-base text-slate-400">
                    Une épargne flexible et sécurisée via nos tontines numériques, couplée à un accès direct aux meilleurs équipements électroménagers pour votre foyer.
                </p>
                <div class="flex flex-col items-center justify-center gap-4 pt-4 sm:flex-row">
                    <a href="#epargne" class="w-full px-6 py-3 text-sm font-bold transition shadow-lg sm:w-auto bg-emerald-500 hover:bg-emerald-600 text-slate-950 rounded-xl shadow-emerald-500/10">
                        <i class="mr-2 bi bi-piggy-bank-fill"></i> Commencer à Épargner
                    </a>
                    <a href="#electro" class="w-full px-6 py-3 text-sm font-semibold transition border sm:w-auto bg-slate-900 border-slate-800 hover:bg-slate-800 rounded-xl">
                        Voir le Catalogue Électroménager <i class="ml-1 bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-500/5 blur-[120px] rounded-full pointer-events-none"></div>
        </header>

        <section id="epargne" class="w-full max-w-6xl px-6 py-20 mx-auto">
            <div class="mb-12 space-y-2 text-center">
                <h2 class="text-2xl font-bold text-white md:text-3xl">Nos Solutions d'Épargne & Tontines</h2>
                <p class="max-w-md mx-auto text-xs text-slate-400">7 sous-comptes spécifiques conçus pour structurer votre croissance financière en toute sérénité.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="p-6 space-y-4 transition border bg-slate-900/60 border-slate-800/80 rounded-2xl hover:border-emerald-500/30">
                    <div class="flex items-center justify-center w-10 h-10 text-xl rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="bi bi-calendar3-event"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Tontine Quotidienne</h3>
                    <p class="text-xs leading-relaxed text-slate-400">Une collecte régulière gérée par nos agents de terrain certifiés pour sécuriser votre flux de trésorerie journalier.</p>
                </div>
                <div class="p-6 space-y-4 transition border bg-slate-900/60 border-slate-800/80 rounded-2xl hover:border-emerald-500/30">
                    <div class="flex items-center justify-center w-10 h-10 text-xl rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Épargne Bloquée (Projet)</h3>
                    <p class="text-xs leading-relaxed text-slate-400">Faites fructifier vos fonds sur un compte d'investissement à terme pour concrétiser l'achat de terrains ou de commerces.</p>
                </div>
                <div class="p-6 space-y-4 transition border bg-slate-900/60 border-slate-800/80 rounded-2xl hover:border-emerald-500/30">
                    <div class="flex items-center justify-center w-10 h-10 text-xl rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Assurance & Social</h3>
                    <p class="text-xs leading-relaxed text-slate-400">Un sous-compte de secours pour protéger votre famille face aux imprévus de santé et aux charges urgentes.</p>
                </div>
            </div>
        </section>

        <section id="electro" class="px-6 py-20 bg-slate-900/30 border-y border-slate-900">
            <div class="w-full max-w-6xl mx-auto">
                <div class="mb-12 space-y-2 text-center">
                    <h2 class="text-2xl font-bold text-white md:text-3xl">Boutique Électroménager & Équipements</h2>
                    <p class="max-w-md mx-auto text-xs text-slate-400">Équipez votre maison à votre rythme grâce à nos facilités de paiement liées à votre compte d'épargne.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                        <div class="flex items-center justify-center text-5xl transition h-44 bg-slate-950 text-slate-600 group-hover:text-cyan-400">
                            <i class="bi bi-tv"></i>
                        </div>
                        <div class="p-4 space-y-2">
                            <h4 class="text-sm font-bold text-white">Smart TV LED 4K</h4>
                            <p class="text-[11px] text-slate-400">Haute définition pour votre salon. Option de paiement échelonné disponible.</p>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs font-bold text-cyan-400">Option Crédit OK</span>
                                <span class="text-xs text-slate-500"><i class="bi bi-tags"></i> Premium</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                        <div class="flex items-center justify-center text-5xl transition h-44 bg-slate-950 text-slate-600 group-hover:text-cyan-400">
                            <i class="bi bi-snow"></i>
                        </div>
                        <div class="p-4 space-y-2">
                            <h4 class="text-sm font-bold text-white">Réfrigérateur Combiné</h4>
                            <p class="text-[11px] text-slate-400">Classe énergétique A+, idéal pour conserver vos aliments et activités commerciales.</p>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs font-bold text-cyan-400">Option Crédit OK</span>
                                <span class="text-xs text-slate-500"><i class="bi bi-tags"></i> Essentiel</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                        <div class="flex items-center justify-center text-5xl transition h-44 bg-slate-950 text-slate-600 group-hover:text-cyan-400">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="p-4 space-y-2">
                            <h4 class="text-sm font-bold text-white">Climatiseur Split Inverter</h4>
                            <p class="text-[11px] text-slate-400">Refroidissement rapide et économique pour bureaux et chambres à coucher.</p>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs font-bold text-cyan-400">Financement Spécial</span>
                                <span class="text-xs text-slate-500"><i class="bi bi-tags"></i> Confort</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl group">
                        <div class="flex items-center justify-center text-5xl transition h-44 bg-slate-950 text-slate-600 group-hover:text-cyan-400">
                            <i class="bi bi-refrigerator"></i>
                        </div>
                        <div class="p-4 space-y-2">
                            <h4 class="text-sm font-bold text-white">Cuisinière à Gaz Élite</h4>
                            <p class="text-[11px] text-slate-400">4 foyers avec grand four sécurisé pour les familles et restaurateurs.</p>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs font-bold text-cyan-400">Option Crédit OK</span>
                                <span class="text-xs text-slate-500"><i class="bi bi-tags"></i> Cuisine</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="px-6 py-8 mt-auto text-xs text-center border-t border-slate-900 bg-slate-950 text-slate-500">
            <p>&copy; 2026 S ECO PLUS 2.0. Tous droits réservés. Système de Microfinance Sécurisé.</p>
        </footer>

    </div>

</body>
</html>


