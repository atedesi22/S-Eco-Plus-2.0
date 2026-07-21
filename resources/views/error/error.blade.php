<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur Système - S ECO PLUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="relative flex flex-col justify-between h-full overflow-x-hidden font-sans antialiased text-slate-300 selection:bg-emerald-500 selection:text-slate-950">

    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-1/4 left-1/3 w-[400px] h-[400px] bg-blue-500/10 rounded-full blur-[100px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b15_1px,transparent_1px),linear-gradient(to_bottom,#1e293b15_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
    </div>

    <header class="relative z-10 flex items-center justify-between w-full px-6 py-8 mx-auto max-w-7xl">
        <div class="flex items-center space-x-3">
            <div class="flex items-center justify-center w-10 h-10 text-xl font-black shadow-lg rounded-xl bg-emerald-500 text-slate-950 shadow-emerald-500/20">
                S
            </div>
            <div>
                <span class="block text-base font-black tracking-wider text-white uppercase">S ECO PLUS</span>
                <span class="text-[10px] font-bold text-emerald-400 tracking-widest uppercase">Système Microfinance</span>
            </div>
        </div>
        <a href="/" class="flex items-center px-4 py-2 space-x-2 text-xs font-bold transition border text-slate-400 hover:text-white bg-slate-900/80 border-slate-800 rounded-xl backdrop-blur-md">
            <i class="bi bi-house"></i>
            <span>Accueil Système</span>
        </a>
    </header>

    <main class="relative z-10 flex items-center justify-center flex-grow px-4 py-12">
        @php
            // Récupération sécurisée du code d'erreur HTTP (via $status, $exception ou 403 par défaut)
            $status = $status ?? (isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 403);

            $errorsConfig = [
                403 => [
                    'code' => '403',
                    'badge' => 'Accès Refusé',
                    'color' => 'amber',
                    'icon' => 'bi-shield-lock-fill',
                    'title' => 'Espace Restreint ou Privilèges Insuffisants',
                    'description' => 'Votre rôle actuel ne vous permet pas de consulter cette ressource du système. Si vous pensez qu\'il s\'agit d\'une erreur de permission, contactez votre administrateur ou la direction.',
                ],
                404 => [
                    'code' => '404',
                    'badge' => 'Page Introuvable',
                    'color' => 'emerald',
                    'icon' => 'bi-compass-fill',
                    'title' => 'Ressource Introuvable ou Déplacée',
                    'description' => 'La page ou le dossier que vous recherchez n\'existe pas, a été supprimé ou est temporairement indisponible sur la plateforme S ECO PLUS.',
                ],
                500 => [
                    'code' => '500',
                    'badge' => 'Erreur Serveur',
                    'color' => 'rose',
                    'icon' => 'bi-cpu-fill',
                    'title' => 'Incident Technique Interne',
                    'description' => 'Un problème inattendu est survenu lors du traitement de votre requête par nos serveurs. Nos équipes techniques ont été notifiées automatiquement.',
                ],
                503 => [
                    'code' => '503',
                    'badge' => 'Maintenance',
                    'color' => 'sky',
                    'icon' => 'bi-tools',
                    'title' => 'Service Temporairement Indisponible',
                    'description' => 'Une maintenance planifiée ou une mise à jour du système S ECO PLUS est actuellement en cours. Veuillez recharger la page dans quelques instants.',
                ],
            ];

            $currentError = $errorsConfig[$status] ?? $errorsConfig[403];
        @endphp

        <div class="relative w-full max-w-2xl p-8 overflow-hidden border shadow-2xl bg-slate-900/90 border-slate-800/80 rounded-3xl md:p-12 backdrop-blur-xl group">

            <div class="absolute -right-8 -bottom-10 text-[180px] font-black text-slate-800/20 select-none pointer-events-none font-mono leading-none">
                {{ $currentError['code'] }}
            </div>

            <div class="relative z-10 space-y-6">

                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center text-2xl border shadow-lg w-14 h-14 rounded-2xl bg-slate-950 border-slate-800">
                        <i class="bi {{ $currentError['icon'] }} text-white"></i>
                    </div>
                    <div>
                        <span class="px-3 py-1 bg-slate-950 border border-slate-800 text-slate-300 rounded-full text-[11px] font-bold uppercase tracking-wider">
                            Code Erreur {{ $currentError['code'] }} · {{ $currentError['badge'] }}
                        </span>
                        <h1 class="mt-1 text-2xl font-black tracking-wide text-white uppercase md:text-3xl">
                            {{ $currentError['title'] }}
                        </h1>
                    </div>
                </div>

                <p class="py-1 pl-4 text-sm font-normal leading-relaxed border-l-2 md:text-base text-slate-400 border-slate-800">
                    {{ $currentError['description'] }}
                </p>

                <div class="flex flex-col items-center gap-3 pt-4 sm:flex-row">
                    <a href="javascript:history.back()" class="flex items-center justify-center w-full px-6 py-3 space-x-2 text-xs font-bold tracking-wider text-white uppercase transition border sm:w-auto bg-slate-800 hover:bg-slate-700 border-slate-700 rounded-xl">
                        <i class="bi bi-arrow-left"></i>
                        <span>Retourner en arrière</span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full px-6 py-3 space-x-2 text-xs font-black tracking-wider uppercase transition shadow-lg sm:w-auto bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl shadow-emerald-500/20">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tableau de bord</span>
                    </a>
                </div>

                <div class="flex items-center justify-between pt-6 font-mono text-xs border-t border-slate-800/80 text-slate-500">
                    <span class="flex items-center">
                        <span class="w-2 h-2 mr-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        S ECO PLUS v2.0 · Serveurs opérationnels
                    </span>
                    <a href="mailto:support@secoplus.cm" class="underline transition hover:text-emerald-400">Support SI</a>
                </div>

            </div>
        </div>
    </main>

    <footer class="relative z-10 w-full py-6 font-mono text-xs text-center text-slate-600">
        &copy; {{ date('Y') }} S ECO PLUS Microfinance. Tous droits réservés.
    </footer>

</body>
</html>
