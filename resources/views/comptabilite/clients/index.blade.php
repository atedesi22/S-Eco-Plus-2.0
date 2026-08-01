@extends('layouts.app')

@section('content')
<div x-data="guichetExpress()" class="min-h-screen p-6 space-y-6 bg-slate-950 text-slate-100">

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
                <i class="bi bi-people text-emerald-400"></i> Comptes Clients Agence
            </h1>
            <p class="text-xs text-slate-400">Gérez les comptes d'épargne et tontines des membres de votre agence.</p>
        </div>
    </div>

    <!-- GUICHET EXPRESS DE CAISSE -->
    <div class="p-5 space-y-4 border bg-slate-900 border-slate-800 rounded-2xl">
        <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-emerald-400">
            <i class="bi bi-lightning-charge-fill"></i> Guichet Express (Dépôt / Retrait Rapide)
        </h2>

        <form action="{{ route('comptabilite.transactions.guichet') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @csrf

            <!-- Type d'opération -->
            <div>
                <label class="block mb-1 text-[11px] font-semibold text-slate-400">Type *</label>
                <select name="type" x-model="type" required class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                    <option value="deposit">Dépôt (+)</option>
                    <option value="withdrawal">Retrait (-)</option>
                </select>
            </div>

            <!-- Sélection du Client (Autocomplétion Alpine) -->
            <div class="relative">
                <label class="block mb-1 text-[11px] font-semibold text-slate-400">Client *</label>
                <input type="hidden" name="user_id" :value="selectedClient ? selectedClient.id : ''" required>

                <div @click="toggleDropdown()" class="flex items-center justify-between w-full px-3 py-2 text-xs text-white border cursor-pointer bg-slate-950 border-slate-800 rounded-xl">
                    <span x-text="selectedClient ? selectedClient.name + ' (' + selectedClient.phone + ')' : 'Rechercher un client...'"></span>
                    <i class="bi bi-chevron-down text-slate-500"></i>
                </div>

                <!-- Dropdown -->
                <div x-show="open" @click.outside="open = false" class="absolute z-50 w-full mt-1 overflow-y-auto border shadow-2xl bg-slate-900 border-slate-800 rounded-xl max-h-48">
                    <div class="sticky top-0 p-2 border-b bg-slate-900 border-slate-800">
                        <input x-ref="searchInput" x-model="search" @input="filterClients()" type="text" placeholder="Nom ou Téléphone..." class="w-full px-2 py-1 text-xs text-white border rounded-lg outline-none bg-slate-950 border-slate-800">
                    </div>
                    <template x-for="client in filteredClients" :key="client.id">
                        <div @click="selectClient(client)" class="flex justify-between px-3 py-2 text-xs cursor-pointer text-slate-300 hover:bg-slate-800">
                            <span x-text="client.name"></span>
                            <span class="font-mono text-slate-500" x-text="client.phone"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Compte / Sous-Compte Cible -->
            <div>
                <label class="block mb-1 text-[11px] font-semibold text-slate-400">Destination *</label>
                <select name="sub_account_id" x-model="selectedSubAccountId" class="w-full px-3 py-2 text-xs text-white border outline-none bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                    <option value="">Compte Principal (Épargne)</option>
                    <template x-if="selectedClient && selectedClient.sub_accounts">
                        <template x-for="sub in selectedClient.sub_accounts" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name + ' (Solde: ' + formatMoney(sub.balance) + ' XAF)'"></option>
                        </template>
                    </template>
                </select>
            </div>

            <!-- Montant & Bouton -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block mb-1 text-[11px] font-semibold text-slate-400">Montant (XAF) *</label>
                    <input type="number" name="amount" min="100" required placeholder="Ex: 5000" class="w-full px-3 py-2 text-xs font-bold border outline-none text-emerald-400 bg-slate-950 border-slate-800 rounded-xl focus:border-emerald-500">
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-bold transition text-slate-950 bg-emerald-500 hover:bg-emerald-400 rounded-xl">
                    Valider
                </button>
            </div>
        </form>
    </div>

    <!-- LISTE DES CLIENTS -->
    <div class="overflow-hidden border bg-slate-900 border-slate-800 rounded-2xl">
        <div class="flex items-center justify-between p-4 border-b border-slate-800">
            <h3 class="text-xs font-bold tracking-wider text-white uppercase">Répertoire des Clients</h3>

            <form action="{{ route('comptabilite.clients.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="px-3 py-1.5 text-xs text-white bg-slate-950 border border-slate-800 rounded-xl outline-none">
                <button type="submit" class="px-3 py-1.5 text-xs bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700">Filtrer</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
                        <th class="p-3">Client</th>
                        <th class="p-3">Téléphone</th>
                        <th class="p-3">Solde Compte Principal</th>
                        <th class="p-3">Sous-comptes / Tontines</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-800 text-slate-300">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-800/50">
                            <td class="p-3 font-semibold text-white">{{ $client->name }}</td>
                            <td class="p-3 font-mono text-slate-400">{{ $client->phone ?? 'N/A' }}</td>
                            <td class="p-3 font-bold text-emerald-400">
                                {{ number_format($client->accounts->sum('balance'), 0, ',', ' ') }} XAF
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        // Vérifie si le client a au moins un sous-compte à travers tous ses comptes
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
                            <td class="p-3 space-x-2 text-right">
                                <a href="{{ route('comptabilite.clients.show', $client->id) }}" class="px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition">
                                    Fiche Détaillée
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-500">Aucun client trouvé.</td>
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

<script>
    function guichetExpress() {
        return {
            type: 'deposit',
            open: false,
            search: '',
            selectedClient: null,
            selectedSubAccountId: '',
            rawClients: @json($clientsAgence ?? []),
            clients: [],
            filteredClients: [],

            init() {
                this.clients = (this.rawClients || []).sort((a, b) => (a.name || '').localeCompare(b.name || '', 'fr'));
                this.filteredClients = this.clients;
            },

            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => { if (this.$refs.searchInput) this.$refs.searchInput.focus(); });
                }
            },

            filterClients() {
                if (!this.search || this.search.trim() === '') {
                    this.filteredClients = this.clients;
                    return;
                }
                const q = this.search.toLowerCase().trim();
                this.filteredClients = this.clients.filter(c =>
                    (c.name && c.name.toLowerCase().includes(q)) ||
                    (c.phone && c.phone.toLowerCase().includes(q))
                );
            },

            selectClient(client) {
                this.selectedClient = client;
                this.selectedSubAccountId = '';
                this.open = false;
            },

            formatMoney(val) {
                return new Intl.NumberFormat('fr-FR').format(val || 0);
            }
        }
    }
</script>
@endsection
