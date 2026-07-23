<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S ECO PLUS 2.0 - Administration Interne</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2 family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>

<!-- Initialisation globale d'Alpine sur le body pour que le bouton header ET leaside partagent la même variable -->
<body class="flex min-h-screen antialiased bg-slate-950 text-slate-100" x-data="{ sidebarOpen: false }">

    <!-- 1. INCLUSION DE LA SIDEBAR DYNAMIQUE -->
        @include('layouts.partials.sidebar')

    <!-- Zone principale -->
    <main class="flex flex-col flex-1 min-w-0 overflow-y-auto">

        <header class="flex items-center justify-between h-16 px-6 border-b border-slate-800 bg-slate-900/40 backdrop-blur">
            <div class="flex items-center space-x-4">
                <!-- Ouverture Mobile (Bouton d'activation) -->
                <button @click="sidebarOpen = true" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <i class="text-2xl bi bi-list"></i>
                </button>
                <h3 class="text-lg font-bold text-white">Console Administration Métier</h3>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center space-x-2 text-xs font-semibold transition text-slate-400 hover:text-red-400">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </header>

        <!-- Injection du contenu des dashboards spécifiques -->
        <div class="p-6 space-y-6">
            @yield('content')
        </div>
    </main>

    <!-- Overlay d'assombrissement mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 md:hidden" x-cloak></div>

</body>
</html>
