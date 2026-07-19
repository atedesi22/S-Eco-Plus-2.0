@extends('layouts.app') {{-- Ou le nom de ton layout admin --}}

@section('content')
<div class="space-y-6 text-slate-300">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-wide text-white">CONSOLE SUPERADMIN</h1>
            <p class="text-xs text-slate-400">Vue d'ensemble du réseau structurel et du personnel de haut niveau</p>
        </div>
        <div class="text-xs bg-emerald-500/10 text-emerald-400 px-3 py-1.5 rounded-full font-mono font-bold border border-emerald-500/25 animate-pulse">
            <i class="mr-1 bi bi-shield-check"></i> Mode Sécurisé Actif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-4 sm:grid-cols-2 md:grid-cols-4">
        <div class="flex items-center p-4 space-x-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-center w-12 h-12 text-2xl text-indigo-400 rounded-lg bg-indigo-500/10">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Directions Reg.</p>
                <h3 class="text-xl font-black text-white">4</h3> {{-- Remplacer par $totalRegions dynamiquement --}}
            </div>
        </div>

        <div class="flex items-center p-4 space-x-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-center w-12 h-12 text-2xl rounded-lg bg-emerald-500/10 text-emerald-400">
                <i class="bi bi-bank"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Agences</p>
                <h3 class="text-xl font-black text-white">12</h3> {{-- Remplacer par $totalAgencies --}}
            </div>
        </div>

        <div class="flex items-center p-4 space-x-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-center w-12 h-12 text-2xl rounded-lg bg-amber-500/10 text-amber-400">
                <i class="bi bi-journal-bookmark-fill"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Plans Tontine</p>
                <h3 class="text-xl font-black text-white">{{ $totalPlans }}</h3>
            </div>
        </div>

        <div class="flex items-center p-4 space-x-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-center w-12 h-12 text-2xl rounded-lg bg-rose-500/10 text-rose-400">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Clients Totaux</p>
                <h3 class="text-xl font-black text-white">{{ $totalClients }}</h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center pb-3 space-x-2 border-b border-slate-800">
                <i class="text-lg text-indigo-400 bi bi-person-plus"></i>
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">Nommer un Cadre ou Personnel</h2>
            </div>

            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nom Complet</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Adresse Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Rôle Affecté</label>
                        <select name="role" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                            <option value="PDG">PDG</option>
                            <option value="DG">DG</option>
                            <option value="DAF">DAF</option>
                            <option value="DOM">DOM</option>
                            <option value="Directeur Regional">Directeur Régional</option>
                            <option value="Directeur Agence">Directeur d'Agence</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Supérieur Direct (Optionnel)</label>
                        <select name="parent_id" class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                            <option value="">Aucun (Sommet de la pyramide)</option>
                            {{-- Boucle dynamique sur les responsables existants --}}
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Mot de passe initial</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 rounded-lg transition duration-200">
                    Générer les accès sécurisés
                </button>
            </form>
        </div>

        <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center pb-3 space-x-2 border-b border-slate-800">
                <i class="text-lg bi bi-patch-plus text-amber-400"></i>
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">Créer un Produit de Tontine</h2>
            </div>

            <form action="{{ route('admin.tontines.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nom commercial</label>
                        <input type="text" name="name" placeholder="Ex: S Eco Plus 2.0" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Couleur Identitaire</label>
                        <select name="default_color" required class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-amber-500">
                            <option value="emerald">Vert Émeraude</option>
                            <option value="indigo">Bleu Indigo</option>
                            <option value="amber">Orange Ambre</option>
                            <option value="rose">Rouge Rose</option>
                            <option value="purple">Violet Royal</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Description & Règles d'octroi</label>
                    <textarea name="description" rows="3" placeholder="Spécifier les détails du plan pour les commerciaux de terrain..." class="w-full px-3 py-2 text-xs text-white border rounded-lg bg-slate-950 border-slate-800 focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2.5 rounded-lg transition duration-200">
                    Publier au catalogue central
                </button>
            </form>
        </div>
    </div>

    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
            <div class="flex items-center space-x-2">
                <i class="text-lg bi bi-shield-lock text-rose-400"></i>
                <h2 class="text-sm font-bold tracking-wider text-white uppercase">Derniers Comptes Staff Déployés</h2>
            </div>
            <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">Réseau d'audit</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-400">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Nom / Agent</th>
                        <th class="pb-3">Email</th>
                        <th class="pb-3">Rôle System</th>
                        <th class="pb-3">Supérieur</th>
                        <th class="pb-3 text-right">Date de création</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($staffMembers as $member)
                    <tr>
                        <td class="flex items-center py-3 space-x-2 font-bold text-white">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ $member->name }}</span>
                        </td>
                        <td class="py-3 font-mono text-slate-400">{{ $member->email }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/25">
                                {{ $member->roles->first()?->name ?? 'Aucun Rôle' }}
                            </span>
                        </td>
                        <td class="py-3 italic text-slate-500">
                            {{ $member->superior?->name ?? 'Direction Générale' }}
                        </td>
                        <td class="py-3 font-mono text-right text-slate-500">
                            {{ $member->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 italic text-center text-slate-600">
                            Aucun agent de haut niveau n'a été déployé pour le moment.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Header + Filtres Temporels -->
    <div class="flex flex-col gap-4 pb-5 border-b md:flex-row md:items-center md:justify-between border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-wide text-white">CONSOLE DE SUPERVISION S ECO PLUS</h1>
            <p class="text-xs text-slate-400">Analyses macro-financières et suivi de croissance du réseau en temps réel</p>
        </div>

        <!-- Formulaire de filtrage rapide -->
        <form method="GET" action="{{ route('admin.dashboard') }}" id="periodForm" class="flex p-1 space-x-1 border bg-slate-950 border-slate-800 rounded-xl">
            @foreach(['day' => 'Jour', 'week' => 'Semaine', 'month' => 'Mois', 'quarter' => 'Trimestre', 'semester' => 'Semestre', 'year' => 'Annuel'] as $key => $label)
                <button type="submit" name="period" value="{{ $key }}"
                        class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition {{ ($period ?? 'month') === $key ? 'bg-emerald-500 text-slate-950' : 'text-slate-400 hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </form>
    </div>

    <!-- Les 4 Cartes Métriques (KPI) -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Comptes Créés -->
        <div class="p-5 space-y-2 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] uppercase font-bold tracking-wider">Nouveaux Comptes</span>
                <i class="text-base bi bi-person-plus text-emerald-400"></i>
            </div>
            <p class="font-mono text-2xl font-black text-white">{{ number_format($accountsCount) }}</p>
            <p class="text-[10px] text-slate-500 italic">Comptes enregistrés sur la période</p>
        </div>

        <!-- Volume Transactions -->
        <div class="p-5 space-y-2 border bg-slate-900 border-slate-800 rounded-xl">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] uppercase font-bold tracking-wider">Flux Transactions</span>
                <i class="text-base text-blue-400 bi bi-cash-stack"></i>
            </div>
            <p class="font-mono text-2xl font-black text-white">{{ number_format($totalTransactions, 0, '.', ' ') }} <span class="text-xs text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 italic">Volume d'argent brassé global</p>
        </div>

        <!-- Gains Globaux -->
        <div class="p-5 space-y-2 border border-b-2 bg-slate-900 border-slate-800 rounded-xl border-b-emerald-500">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] uppercase font-bold tracking-wider">Gains / Recettes</span>
                <i class="text-base bi bi-graph-up text-emerald-500"></i>
            </div>
            <p class="font-mono text-2xl font-black text-emerald-400">+{{ number_format($gains, 0, '.', ' ') }} <span class="text-xs text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 italic">Frais de dossiers, tontines & marges</p>
        </div>

        <!-- Pertes Globales -->
        <div class="p-5 space-y-2 border border-b-2 bg-slate-900 border-slate-800 rounded-xl border-b-rose-500">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] uppercase font-bold tracking-wider">Pertes / Charges</span>
                <i class="text-base bi bi-graph-down text-rose-500"></i>
            </div>
            <p class="font-mono text-2xl font-black text-rose-400">-{{ number_format($pertes, 0, '.', ' ') }} <span class="text-xs text-slate-400">XAF</span></p>
            <p class="text-[10px] text-slate-500 italic">Défauts de recouvrement & charges</p>
        </div>
    </div>

    <!-- Section des Graphiques (Deux colonnes) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Graphe 1: Activité des Transactions -->
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
            <h3 class="mb-4 text-xs font-bold tracking-wider uppercase text-slate-400">Évolution des Flux Financiers</h3>
            <div class="h-64">
                <canvas id="transactionsChart"></canvas>
            </div>
        </div>

        <!-- Graphe 2: Balance Gains / Pertes -->
        <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
            <h3 class="mb-4 text-xs font-bold tracking-wider uppercase text-slate-400">Rendement Net (Gains vs Pertes)</h3>
            <div class="h-64">
                <canvas id="pnlChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts Graphiques Chart.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($chartData->pluck('date')) !!};

        // Graphe 1 : Évolution des transactions
        new Chart(document.getElementById('transactionsChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume (XAF)',
                    data: {!! json_encode($chartData->pluck('total')) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } }
                }
            }
        });

        // Graphe 2 : Gains et Pertes
        new Chart(document.getElementById('pnlChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gains',
                        data: {!! json_encode($chartData->pluck('gains')) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Pertes',
                        data: {!! json_encode($chartData->pluck('pertes')) !!},
                        backgroundColor: '#f43f5e',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#64748b' } } },
                scales: {
                    x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b' } }
                }
            }
        });
    </script>

</div>
@endsection
