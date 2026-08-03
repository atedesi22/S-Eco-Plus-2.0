@extends('layouts.app')

@section('header_title', 'Console Comptable - ' . ($agency->name ?? 'Agence'))

@section('content')

        <!-- En-tête Agence -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-xl font-bold text-white">Espace Comptabilité & Trésorerie</h2>
                <p class="text-xs text-slate-400">Agence : <span class="font-semibold text-emerald-400">{{ $agency->name ?? 'N/A' }}</span></p>
            </div>
            <span class="px-3 py-1 font-mono text-xs font-semibold border rounded-full bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                Session Active
            </span>
        </div>

        <!-- Indicateurs Financiers -->
        <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-3">
            <!-- Total Encaissé (Dépôts) -->
            <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <div>
                    <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Total Encaissements</p>
                    <h3 class="mt-2 text-2xl font-bold text-emerald-400">
                        {{ number_format($statsFinancieres->total_depots ?? 0, 0, ',', ' ') }} XAF
                    </h3>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-emerald-500/10 rounded-xl text-emerald-400">
                    <i class="text-xl bi bi-arrow-down-left-circle"></i>
                </div>
            </div>

            <!-- Total Décaisse (Retraits) -->
            <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <div>
                    <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Total Décaissements</p>
                    <h3 class="mt-2 text-2xl font-bold text-rose-400">
                        {{ number_format($statsFinancieres->total_retraits ?? 0, 0, ',', ' ') }} XAF
                    </h3>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-rose-500/10 rounded-xl text-rose-400">
                    <i class="text-xl bi bi-arrow-up-right-circle"></i>
                </div>
            </div>

            <!-- Solde Net Théorique -->
            @php
                $soldeNet = ($statsFinancieres->total_depots ?? 0) - ($statsFinancieres->total_retraits ?? 0);
            @endphp
            <div class="flex items-center justify-between p-6 border bg-slate-900 border-slate-800 rounded-2xl">
                <div>
                    <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Solde Liquide Agence</p>
                    <h3 class="mt-2 text-2xl font-bold {{ $soldeNet >= 0 ? 'text-white' : 'text-amber-400' }}">
                        {{ number_format($soldeNet, 0, ',', ' ') }} XAF
                    </h3>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-amber-500/10 rounded-xl text-amber-400">
                    <i class="text-xl bi bi-safe2"></i>
                </div>
            </div>
        </div>

        <!-- Section Guichet de Test & Opérations Récentes -->
        <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-3">

            <!-- Formulaire Guichet - Retrait Express -->
            <!-- Widget Guichet - Dépôt & Retrait Express -->
            <div class="p-6 border bg-slate-900 border-slate-800 rounded-2xl lg:col-span-1" x-data="guichetExpress()">

                <h5 class="flex items-center mb-4 space-x-2 text-sm font-bold text-white">
                    <i class="bi bi-bank text-emerald-400"></i>
                    <span>Guichet - Opération Express</span>
                </h5>

                @if(session('success'))
                    <div class="p-3 mb-4 text-xs font-semibold border rounded-xl bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3 mb-4 text-xs font-semibold border rounded-xl bg-rose-500/10 text-rose-400 border-rose-500/20">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('comptabilite.transactions.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-2 p-1 border bg-slate-950 border-slate-800 rounded-xl">
                        <button
                            type="button"
                            @click="type = 'deposit'"
                            :class="type === 'deposit' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                            class="flex items-center justify-center py-2 space-x-1 text-xs transition duration-150 rounded-lg"
                        >
                            <i class="bi bi-arrow-down-circle-fill"></i>
                            <span>Dépôt</span>
                        </button>

                        <button
                            type="button"
                            @click="type = 'withdrawal'"
                            :class="type === 'withdrawal' ? 'bg-rose-500 text-white font-bold' : 'text-slate-400 hover:text-white'"
                            class="flex items-center justify-center py-2 space-x-1 text-xs transition duration-150 rounded-lg"
                        >
                            <i class="bi bi-arrow-up-circle-fill"></i>
                            <span>Retrait</span>
                        </button>
                    </div>

                    <input type="hidden" name="type" :value="type">
                    <input type="hidden" name="user_id" :value="selectedClient ? selectedClient.id : ''" required>

                    <div class="relative" @click.away="open = false">
                        <label class="block mb-2 text-xs font-medium text-slate-400">Sélectionner un Client</label>

                        <button
                            type="button"
                            @click="toggleDropdown()"
                            class="w-full px-4 py-2.5 text-left text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 flex items-center justify-between"
                        >
                            <span x-text="selectedClient ? selectedClient.name + ' (' + (selectedClient.phone || 'Sans num.') + ')' : '--- Rechercher un client ---'"
                                :class="selectedClient ? 'text-white font-medium' : 'text-slate-500'"></span>
                            <i class="text-xs transition-transform duration-200 bi bi-chevron-down text-slate-400" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open"
                            x-transition
                            class="absolute left-0 right-0 z-50 p-2 mt-2 overflow-hidden border shadow-2xl bg-slate-950 border-slate-800 rounded-xl">

                            <div class="relative mb-2">
                                <input
                                    type="text"
                                    x-ref="searchInput"
                                    x-model="search"
                                    @input="filterClients()"
                                    placeholder="Filtrer par nom ou numéro..."
                                    class="w-full py-2 pr-3 text-xs text-white border rounded-lg pl-9 bg-slate-900 border-slate-800 focus:outline-none focus:border-emerald-500"
                                >
                                <i class="bi bi-search absolute left-3 top-2.5 text-slate-500 text-xs"></i>
                            </div>

                            <div class="pr-1 space-y-1 overflow-y-auto max-h-48 custom-scrollbar">
                                <template x-for="client in filteredClients" :key="client.id">
                                    <button
                                        type="button"
                                        @click="selectClient(client)"
                                        class="flex items-center justify-between w-full px-3 py-2 text-xs text-left transition rounded-lg group hover:bg-slate-800"
                                        :class="selectedClient && selectedClient.id === client.id ? 'bg-emerald-500/10 border border-emerald-500/30' : ''"
                                    >
                                        <div>
                                            <p class="font-bold text-slate-200 group-hover:text-emerald-400" x-text="client.name"></p>
                                            <span class="text-[10px] text-slate-500" x-text="client.phone || 'Pas de numéro'"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-emerald-400" x-text="formatMoney(client.total_balance) + ' XAF'"></span>
                                        </div>
                                    </button>
                                </template>

                                <div x-show="filteredClients.length === 0" class="py-4 text-xs text-center text-slate-500">
                                    Aucun client trouvé pour "<span x-text="search"></span>"
                                </div>
                            </div>
                        </div>
                    </div>

                    <template x-if="selectedClient">
                        <div class="p-3.5 space-y-3 border bg-slate-950/80 border-slate-800 rounded-xl">

                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Solde Total Cumulé</span>
                                    <span class="text-xl font-extrabold text-emerald-400" x-text="formatMoney(selectedClient.total_balance) + ' XAF'"></span>
                                </div>
                                <button type="button" @click="clearSelection()" class="text-xs transition text-slate-400 hover:text-rose-400">
                                    <i class="text-lg bi bi-x-circle-fill"></i>
                                </button>
                            </div>

                            <div class="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold block">Tontine de Départ</span>
                                    <p class="text-xs font-bold text-slate-200" x-text="selectedClient.main_account_name"></p>
                                </div>
                                <span class="text-xs font-bold text-amber-400" x-text="formatMoney(selectedClient.main_balance) + ' XAF'"></span>
                            </div>

                            <div>
                                <label class="block mb-1 text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    Sous-Comptes / Autres Tontines (<span x-text="selectedClient.sub_accounts ? selectedClient.sub_accounts.length : 0"></span>)
                                </label>

                                <select name="sub_account_id"
                                        x-model="selectedSubAccountId"
                                        class="w-full px-3 py-2 text-xs border rounded-lg text-slate-200 bg-slate-900 border-slate-800 focus:outline-none focus:border-emerald-500">
                                    <option value="">-- Compte Principal (Par défaut) --</option>
                                    <template x-for="sub in selectedClient.sub_accounts" :key="sub.id">
                                        <option x-bind:value="sub.id"
                                                x-text="sub.name + ' — (' + formatMoney(sub.balance) + ' XAF)'">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="block mb-2 text-xs font-medium text-slate-400">Montant de l'opération (XAF)</label>
                        <input
                            type="number"
                            name="amount"
                            x-model="amount"
                            placeholder="Ex: 10000"
                            min="100"
                            :max="type === 'withdrawal' && selectedClient ? selectedClient.total_balance : ''"
                            class="w-full px-4 py-2.5 text-sm text-white border bg-slate-950 border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500"
                            required
                        >

                        <template x-if="type === 'withdrawal' && selectedClient && Number(amount) > Number(selectedClient.total_balance)">
                            <span class="text-[11px] text-rose-400 mt-1 block font-medium">⚠️ Le montant dépasse le solde total disponible !</span>
                        </template>
                    </div>

                    <button
                        type="submit"
                        :disabled="!selectedClient || amount <= 0 || (type === 'withdrawal' && Number(amount) > Number(selectedClient.total_balance))"
                        :class="type === 'deposit' ? 'bg-emerald-500 hover:bg-emerald-600 text-slate-950' : 'bg-rose-500 hover:bg-rose-600 text-white'"
                        class="w-full px-4 py-3 text-xs font-bold transition disabled:opacity-50 disabled:cursor-not-allowed rounded-xl"
                    >
                        <span x-text="type === 'deposit' ? 'Valider le Dépôt' : 'Valider le Retrait'"></span>
                    </button>
                </form>
            </div>

            <script>
                function guichetExpress() {
                    return {
                        type: 'deposit',
                        open: false,
                        search: '',
                        selectedClient: null,
                        selectedSubAccountId: '', // 🟢 Correctement déclaré ici
                        amount: '',
                        rawClients: @json($clientsAgence ?? []),
                        clients: [],
                        filteredClients: [],

                        init() {
                            // Tri alphabétique A-Z
                            this.clients = (this.rawClients || []).sort((a, b) => {
                                return (a.name || '').localeCompare(b.name || '', 'fr', { sensitivity: 'base' });
                            });
                            this.filteredClients = this.clients;
                        },

                        toggleDropdown() {
                            this.open = !this.open;
                            if (this.open) {
                                this.$nextTick(() => {
                                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                                });
                            }
                        },

                        filterClients() {
                            if (!this.search || this.search.trim() === '') {
                                this.filteredClients = this.clients;
                                return;
                            }
                            const query = this.search.toLowerCase().trim();
                            this.filteredClients = this.clients.filter(client => {
                                const nameMatch = client.name ? client.name.toLowerCase().includes(query) : false;
                                const phoneMatch = client.phone ? client.phone.toLowerCase().includes(query) : false;
                                return nameMatch || phoneMatch;
                            });
                        },

                        selectClient(client) {
                            this.selectedClient = client;
                            this.selectedSubAccountId = ''; // Réinitialiser le sous-compte
                            this.open = false;
                        },

                        clearSelection() {
                            this.selectedClient = null;
                            this.selectedSubAccountId = '';
                            this.amount = '';
                        },

                        formatMoney(val) {
                            return new Intl.NumberFormat('fr-FR').format(val || 0);
                        }
                    }
                }
            </script>



            <!-- Journal des Dernières Transactions de l'Agence -->
            <div class="p-6 border bg-slate-900 border-slate-800 rounded-2xl lg:col-span-2">
                <h5 class="flex items-center justify-between mb-4 text-sm font-bold text-white">
                    <span>Dernières Opérations de l'Agence</span>
                    <a href="{{ route('comptabilite.ecritures.index') }}" class="text-xs text-emerald-400 hover:underline">Voir tout →</a>
                </h5>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-300">
                        <thead class="font-mono uppercase border-b bg-slate-950 text-slate-400 border-slate-800">
                            <tr>
                                <th class="p-3">Client</th>
                                <th class="p-3">Agent</th>
                                <th class="p-3">Type</th>
                                <th class="p-3 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($recentTransactions as $tx)
                                <tr class="transition hover:bg-slate-800/50">
                                    <td class="p-3 font-semibold text-white">
                                        {{ $tx->client_name ?? 'Client inconnu' }}
                                    </td>
                                    <td class="p-3 text-slate-400">{{ $tx->agent_name ?? 'Guichet' }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tx->type === 'deposit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                            {{ $tx->type === 'deposit' ? 'Dépôt' : 'Retrait' }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-mono font-bold text-right {{ $tx->type === 'deposit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $tx->type === 'deposit' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} XAF
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-slate-500">
                                        Aucune transaction enregistrée pour cette agence.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

@endsection
