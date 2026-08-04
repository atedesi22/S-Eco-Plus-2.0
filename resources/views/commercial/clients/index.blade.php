@extends('layouts.app')

@section('content')
<div class="min-h-screen p-6 space-y-6 font-sans bg-slate-950 text-slate-100">

    <!-- Notifications Alertes -->
    @if(session('success'))
        <div class="p-4 mb-4 text-xs font-bold border text-emerald-400 bg-emerald-950/40 border-emerald-800/60 rounded-xl">
            <i class="mr-2 bi bi-check-circle-fill"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 mb-4 text-xs font-bold text-red-400 border bg-red-950/40 border-red-800/60 rounded-xl">
            <i class="mr-2 bi bi-exclamation-triangle-fill"></i>{{ session('error') }}
        </div>
    @endif

    <!-- En-tête -->
    <div class="flex flex-col justify-between gap-4 pb-4 border-b md:flex-row md:items-center border-slate-800">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-white">
                <i class="bi bi-people-fill text-emerald-400"></i> Mon Portefeuille Clients
            </h1>
            <p class="text-xs text-slate-400">Consultez les soldes, tontines et états des comptes de vos clients rattachés.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('commercial.clients.create') }}" class="px-4 py-2.5 text-xs font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 rounded-xl transition shadow-lg shadow-emerald-500/10 flex items-center gap-2">
                <i class="text-sm bi bi-person-plus-fill"></i> Nouveau Client
            </a>
        </div>
    </div>

    <!-- BANNIÈRE INFORMATION SÉCURITÉ -->
    <div class="flex items-center gap-3 p-4 text-xs border bg-slate-900/50 border-slate-800 rounded-xl text-slate-400">
        <i class="text-lg bi bi-shield-lock-fill text-amber-400"></i>
        <span><strong>Note d'information :</strong> En tant que commercial, vous êtes habilité à ouvrir des comptes et consulter les soldes. Les opérations de guichet (dépôts directs, retraits et transferts) sont strictement réservées à la caisse de l'agence.</span>
    </div>

    <!-- LISTE DES CLIENTS -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="flex items-center justify-between p-4 border-b border-slate-800">
            <h3 class="text-xs font-bold tracking-wider text-white uppercase">Répertoire des Clients</h3>

            <form action="{{ route('commercial.clients.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone..." class="px-3 py-1.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl outline-none focus:border-emerald-500">
                <button type="submit" class="px-3 py-1.5 text-xs bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700">Filtrer</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Client</th>
                        <th class="p-3">Téléphone</th>
                        <th class="p-3">Solde Épargne (Principal)</th>
                        <th class="p-3">Sous-comptes / Tontines</th>
                        <th class="p-3">Collectrice Assignée</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($clients as $client)
                        <tr class="transition-colors hover:bg-slate-800/50">
                            <td class="p-3 font-semibold text-white">
                                <div class="flex items-center space-x-3">
                                    <!-- Photo de profil optionnelle avec fallback initiales -->
                                    @if($client->profile_photo)
                                        <img src="{{ asset('storage/' . $client->profile_photo) }}" alt="{{ $client->name }}" class="object-cover w-8 h-8 border rounded-full border-slate-700">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-emerald-400 text-[10px]">
                                            {{ strtoupper(substr($client->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="block font-bold">{{ $client->name }}</span>
                                        <span class="text-[10px] text-slate-500 font-normal">Inscrit le {{ $client->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-slate-400">{{ $client->phone ?? 'N/A' }}</td>
                            <td class="p-3 font-mono font-bold text-emerald-400">
                                {{ number_format($client->accounts->sum('balance'), 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $hasSubAccounts = $client->accounts && $client->accounts->flatMap->subAccounts->isNotEmpty();
                                    @endphp

                                    @if($hasSubAccounts)
                                        @foreach($client->accounts as $acc)
                                            @foreach($acc->subAccounts as $sub)
                                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-800 text-emerald-300 border border-slate-700 rounded-lg">
                                                    {{ $sub->name }}: {{ number_format($sub->balance, 0, ',', ' ') }} XAF
                                                </span>
                                            @endforeach
                                        @endforeach
                                    @else
                                        <span class="text-[10px] text-slate-500 italic">Aucune tontine active</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 text-slate-400">
                                {{ $client->collector->name ?? 'Non assignée' }}
                            </td>
                            <td class="p-3 space-x-2 text-right">
                                {{-- <a href="tel:{{ $client->phone }}" class="px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 rounded-lg transition">
                                    <i class="bi bi-telephone-fill"></i> Contact
                                </a> --}}
                                <a href="{{ route('commercial.clients.show', $client->id) }}" class="px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition">
                                     <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 italic text-center text-slate-500">Aucun client rattaché dans votre portefeuille.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
