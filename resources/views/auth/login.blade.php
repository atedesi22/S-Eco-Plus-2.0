<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S ECO PLUS 2.0 - Connexion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="flex min-h-screen antialiased text-white bg-slate-900">

    <div class="flex w-full min-h-screen">
        <div class="relative flex-col justify-between hidden w-1/2 p-12 overflow-hidden lg:flex bg-gradient-to-tr from-emerald-900 via-slate-900 to-cyan-900">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-emerald-500/10 via-transparent to-transparent opacity-50"></div>

            <div class="z-10 flex items-center space-x-3">
                <div class="flex items-center justify-center w-10 h-10 shadow-lg rounded-xl bg-emerald-500 shadow-emerald-500/30">
                    <span class="text-xl font-black text-slate-950">S</span>
                </div>
                <span class="text-xl font-bold tracking-wider text-emerald-400">S ECO <span class="text-white">PLUS 2.0</span></span>
            </div>

            <div class="z-10 max-w-md space-y-4">
                <h1 class="text-4xl font-extrabold leading-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-200 to-emerald-400">
                    La microfinance sécurisée, au plus proche de vous.
                </h1>
                <p class="text-sm leading-relaxed text-slate-400">
                    Plateforme intégrée de gestion des tontines, collectes de terrain et d'inclusion financière. Certifiée ACID & Haute Disponibilité.
                </p>
            </div>

            <div class="z-10 text-xs text-slate-500">
                &copy; 2026 S Eco Plus. Tous droits réservés. Système d'Information Sécurisé.
            </div>
        </div>

        <div class="flex items-center justify-center w-full p-6 lg:w-1/2 sm:p-12 bg-slate-950">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-white">Portail de Connexion</h2>
                    <p class="mt-2 text-sm text-slate-400">Saisissez vos accès d'entreprise ou votre numéro de compte tontine.</p>
                </div>

                @if ($errors->any())
                    <div class="p-4 space-y-1 text-xs text-red-400 border bg-red-500/10 border-red-500/20 rounded-xl">
                        @foreach ($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('status'))
                    <div class="p-4 text-xs border bg-emerald-500/10 border-emerald-500/20 text-emerald-400 rounded-xl">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="text-xs font-semibold tracking-wider uppercase text-slate-400">Adresse email ou Numéro de téléphone</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input id="email" class="block w-full py-3 pl-10 pr-4 text-sm transition-all border bg-slate-900 border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder-slate-600"
                                   type="text" name="email" value="{{ old('email') }}" required autofocus placeholder="ex: 677xxxxxx ou nom@secoplus.com" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-xs font-semibold tracking-wider uppercase text-slate-400">Mot de passe</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs text-emerald-400 hover:underline" href="{{ route('password.request') }}">Oublié ?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input id="password" class="block w-full py-3 pl-10 pr-4 text-sm transition-all border bg-slate-900 border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder-slate-600"
                                   type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded bg-slate-900 border-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-950" name="remember">
                            <span class="ml-2 text-xs select-none text-slate-400">Se souvenir de moi</span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-slate-950 font-bold rounded-xl transition-all shadow-lg shadow-emerald-900/20 active:scale-[0.98]">
                            Connexion Sécurisée
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
