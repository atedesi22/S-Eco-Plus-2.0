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

        /* Animation de remplissage de la barre de progression */
        @keyframes fill-bar {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }

        /* Animation de la pièce (Chute 3D + Rebond) */
        @keyframes coin-drop-3d {
            0% {
                transform: translateY(-60px) rotateY(0deg) scale(0.8);
                opacity: 0;
                z-index: 20; /* Devant le portefeuille */
            }
            15% {
                opacity: 1;
            }
            /* Arrivée juste au dessus de l'ouverture du portefeuille */
            50% {
                transform: translateY(35px) rotateY(540deg) scale(1);
                opacity: 1;
                z-index: 20;
            }
            /* La pièce commence à entrer et passe derrière le portefeuille */
            60% {
                transform: translateY(60px) rotateY(630deg) scale(0.8);
                opacity: 1;
                z-index: 5; /* Passe derrière */
            }
            /* Descente profonde à l'intérieur avant disparition */
            85% {
                transform: translateY(110px) rotateY(810deg) scale(0.5);
                opacity: 0;
                z-index: 5;
            }
            100% {
                transform: translateY(110px) rotateY(900deg) scale(0.5);
                opacity: 0;
                z-index: 5;
            }
        }

        /* Impulsion du portefeuille à la réception de la pièce */
        @keyframes wallet-bounce {
            0%, 65%, 100% {
                transform: scale(1) translateY(0);
            }
            75% {
                transform: scale(1.12, 0.9) translateY(4px);
            }
            85% {
                transform: scale(0.98, 1.05) translateY(-2px);
            }
        }

        /* Animation de l'ombre au sol */
        @keyframes shadow-scale {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            75% { transform: scale(1.2); opacity: 0.9; }
        }

        /* Éclat lors de l'insertion */
        @keyframes sparkle-pulse {
            0%, 70%, 100% { opacity: 0; transform: scale(0.5); }
            80% { opacity: 1; transform: scale(1.4); }
        }

        /* Classes CSS d'animation */
        .animate-progress-fill {
            animation: fill-bar 3s ease-in-out infinite;
        }
        .animate-coin {
            animation: coin-drop-3d 3.4s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }
        .animate-wallet {
            animation: wallet-bounce 3.4s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }
        .animate-shadow {
            animation: shadow-scale 3.4s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }
        .animate-coin-sparkle {
            animation: sparkle-pulse 3.4s infinite ease-out;
        }
    </style>
</head>
<body class="antialiased bg-slate-950 text-slate-100" x-data="{ loading: true }" x-init="window.addEventListener('load', () => { setTimeout(() => loading = false, 2500) })">

    <div x-show="loading"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center overflow-hidden select-none bg-slate-950">

        <!-- Halos lumineux d'arrière-plan (Glow Effect) -->
        <div class="absolute rounded-full w-72 h-72 bg-emerald-500/10 blur-3xl -top-10 -left-10 animate-pulse"></div>
        <div class="absolute rounded-full w-72 h-72 bg-amber-500/10 blur-3xl -bottom-10 -right-10 animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative flex flex-col items-center justify-center w-40 h-44">

            <!-- Lumière d'impact au moment de l'insertion de la pièce -->
            <div class="absolute w-12 h-12 rounded-full bottom-8 bg-amber-400/30 blur-md animate-coin-sparkle"></div>

            <!-- Pièce de monnaie animée (Effet 3D) -->
            <div class="absolute top-0 z-20 flex items-center justify-center w-10 h-10 border border-amber-200 rounded-full shadow-[0_0_15px_rgba(245,158,11,0.5)] bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-300 animate-coin">
                <i class="text-sm font-black bi bi-currency-exchange text-amber-950"></i>
            </div>

            <!-- Portefeuille animé -->
            <div class="absolute text-6xl bottom-6 text-emerald-400 drop-shadow-[0_10px_20px_rgba(16,185,129,0.3)] animate-wallet">
                <i class="bi bi-wallet2"></i>
            </div>

            <!-- Ombre portée sous le portefeuille -->
            <div class="absolute bottom-2 w-16 h-2 bg-emerald-950/60 rounded-[100%] blur-sm animate-shadow"></div>
        </div>

        <!-- Conteneur Texte & Progress Bar -->
        <div class="z-10 mt-6 space-y-3 text-center">
            <h2 class="text-2xl font-black tracking-wider text-white">
                S ECO PLUS <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-yellow-200">2.0</span>
            </h2>

            <!-- Barre de chargement fonctionnelle -->
            <div class="w-48 h-2 mx-auto bg-slate-900 rounded-full overflow-hidden p-0.5 border border-slate-800">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-cyan-400 to-amber-400 animate-progress-fill"></div>
            </div>

            <p class="text-[11px] font-semibold tracking-[0.2em] uppercase text-slate-400 animate-pulse">
                Sécurisation de votre avenir...
            </p>
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


