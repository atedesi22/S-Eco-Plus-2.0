@extends('layouts.app')

@section('content')
<div class="space-y-6 text-slate-300">
    <div>
        <h1 class="text-2xl font-black tracking-wide text-white">GESTION CENTRALISÉE DES CLIENTS</h1>
        <p class="text-xs text-slate-400">Registre général des portefeuilles clients actifs sur le réseau S Eco Plus</p>
    </div>

    <!-- TABLEAU DES CLIENTS -->
    <div class="p-5 border bg-slate-900 border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="pb-3">Nom complet</th>
                        <th class="pb-3">Téléphone</th>
                        <th class="pb-3">Agence Rattachée</th>
                        <th class="pb-3">Date d'Inscription</th>
                        <th class="pb-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400">
                    @forelse($clients as $client)
                    <tr>
                        <td class="py-3.5 font-bold text-white">{{ $client->name }}</td>
                        <!-- Remplacement de l'email par le numéro de téléphone conformément à tes règles métiers -->
                        <td class="py-3.5 font-mono text-slate-300">{{ $client->phone ?? 'Non renseigné' }}</td>
                        <td class="py-3.5">{{ $client->structure ? $client->structure->name : 'Siège Principal' }}</td>
                        <td class="py-3.5 text-slate-500">{{ $client->created_at->format('d/m/Y') }}</td>
                        <td class="py-3.5 text-right">
                            <a href="#" class="text-[10px] font-bold bg-slate-950 text-slate-400 border border-slate-800 px-2 py-1 rounded hover:text-white transition">
                                Voir Dossier
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 italic text-center text-slate-500">Aucun client enregistré pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-4">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
