@extends('layouts.app')

@section('content')
<div x-data="{
    search: '',
    roleFilter: 'all',
    showCreateModal: false,
    selectedUser: null
}" class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Personnel de l'Agence</h1>
            <p class="text-xs text-slate-400">Gérez les employés de l'agence, suivez les présences et la répartition des rôles.</p>
        </div>
        <button @click="showCreateModal = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition text-slate-950 rounded-xl bg-emerald-500 hover:bg-emerald-400">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nouveau Membre</span>
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Total Effectif</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="text-lg bi bi-people-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $total_staff }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Présents Aujourd'hui</span>
                <div class="flex items-center justify-center w-8 h-8 text-teal-400 rounded-lg bg-teal-500/10">
                    <i class="text-lg bi bi-check-circle-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">{{ $today_attendance['present'] }}</p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Absents / Congés</span>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <i class="text-lg bi bi-person-dash-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">
                {{ $today_attendance['absent'] + $today_attendance['on_leave'] }}
            </p>
        </div>

        <div class="p-5 border bg-slate-900/60 border-slate-800 rounded-2xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-slate-400">Agents de Collecte</span>
                <div class="flex items-center justify-center w-8 h-8 text-blue-400 rounded-lg bg-blue-500/10">
                    <i class="text-lg bi bi-person-badge-fill"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-white">
                {{ $staff_by_role['collector'] ?? $staff_by_role['agent_collecte'] ?? $staff_by_role['Collectrice'] ?? 0 }}
            </p>
        </div>
    </div>

    <div class="flex flex-col items-center justify-between gap-4 p-4 border sm:flex-row bg-slate-900/40 border-slate-800 rounded-2xl">
        <div class="relative w-full sm:w-80">
            <i class="absolute text-slate-500 left-3 top-2.5 bi bi-search"></i>
            <input x-model="search" type="text" placeholder="Rechercher un employé (nom, email, tel)..."
                   class="w-full py-2 pr-4 text-sm text-white border pl-9 rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="flex items-center w-full gap-2 sm:w-auto">
            <select x-model="roleFilter" class="w-full px-4 py-2 text-sm text-white border sm:w-auto rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="all">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ strtolower($role->name) }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-hidden border border-slate-800 bg-slate-900/50 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-300">
                <thead class="font-mono text-xs uppercase border-b bg-slate-950 text-slate-400 border-slate-800">
                    <tr>
                        <th class="p-4">Employé</th>
                        <th class="p-4">Rôle</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Charge / Clients</th>
                        <th class="p-4">Statut Aujourd'hui</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($staff as $member)
                    @php
                        $memberRoles = strtolower(json_encode($member->roles->pluck('name')->toArray()));
                    @endphp
                    <tr x-show="(roleFilter === 'all' || {{ $memberRoles }}.includes(roleFilter)) &&
                                (search === '' || '{{ strtolower($member->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($member->email) }}'.includes(search.toLowerCase()))"
                        class="transition hover:bg-slate-800/40">
                        <td class="p-4 font-bold text-white">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-8 h-8 font-bold uppercase border rounded-full bg-slate-800 text-emerald-400 border-slate-700">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="block text-sm">{{ $member->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $member->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ ucfirst(str_replace('_', ' ', $member->getRoleNames()->first() ?? 'Agent')) }}
                            </span>
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-300">
                            {{ $member->phone ?? 'N/A' }}
                        </td>
                        <td class="p-4">
                            @if($member->hasRole('Collectrice') || $member->hasRole('agent_collecte') || isset($member->managed_clients_count) || isset($member->total_clients))
                                <span class="text-xs text-slate-300">
                                    <strong class="text-white">{{ $member->managed_clients_count ?? $member->total_clients ?? 0 }}</strong> clients suivis
                                </span>
                            @else
                                <span class="text-xs text-slate-500">aucun client suivi</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @php
                                $status = $member->today_attendance_status ?? 'absent';
                            @endphp
                            @if($status === 'present')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    PRÉSENT
                                </span>
                            @elseif($status === 'on_leave')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    CONGÉ
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    ABSENT
                                </span>
                            @endif
                        </td>
                        <td class="p-4 space-x-2 text-right">
                            <button @click="selectedUser = {{ json_encode([
                                        'id' => $member->id,
                                        'name' => $member->name,
                                        'email' => $member->email,
                                        'phone' => $member->phone ?? 'N/A',
                                        'role' => $member->getRoleNames()->first() ?? 'Personnel',
                                        'status' => $member->today_attendance_status ?? 'absent',
                                        'total_clients' => $member->managed_clients_count ?? $member->total_clients ?? 0,
                                        'created_at' => $member->created_at ? $member->created_at->format('d/m/Y') : 'N/A'
                                    ]) }}"
                                    class="p-2 transition text-slate-400 hover:text-white"
                                    title="Voir profil">
                                <i class="text-base bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">
                            Aucun membre du personnel enregistré dans votre agence.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 border shadow-xl bg-slate-900 border-slate-800 rounded-2xl">
            <h3 class="mb-4 text-lg font-bold text-white">Ajouter un Membre du Personnel</h3>
            <form action="{{ route('directeur.personnel.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Nom Complet</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Adresse Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Téléphone</label>
                    <input type="text" name="phone" class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-bold uppercase text-slate-400">Rôle</label>
                    <select name="role" required class="w-full px-4 py-2 text-sm text-white border rounded-xl bg-slate-950 border-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="" disabled selected>Sélectionner un rôle</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="selectedUser"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">

        <div @click.away="selectedUser = null"
         class="w-full max-w-2xl p-6 space-y-6 border shadow-2xl bg-slate-900 border-slate-800 rounded-2xl">

            <div class="flex items-start justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-12 h-12 text-lg font-bold uppercase border rounded-full bg-slate-800 text-emerald-400 border-slate-700">
                        <span x-text="selectedUser ? selectedUser.name.charAt(0) : ''"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white" x-text="selectedUser?.name"></h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                            x-text="selectedUser?.role.replace('_', ' ').toUpperCase()">
                        </span>
                    </div>
                </div>
                <button @click="selectedUser = null" class="transition text-slate-400 hover:text-white">
                    <i class="text-lg bi bi-x-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 font-mono text-xs sm:grid-cols-2">
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Adresse Email</span>
                    <span class="text-sm font-semibold text-white" x-text="selectedUser?.email"></span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Téléphone</span>
                    <span class="text-sm font-semibold text-white" x-text="selectedUser?.phone"></span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Statut Aujourd'hui</span>
                    <span class="inline-block mt-1 font-bold uppercase"
                        :class="{
                            'text-emerald-400': selectedUser?.status === 'present',
                            'text-amber-400': selectedUser?.status === 'on_leave',
                            'text-rose-400': selectedUser?.status === 'absent'
                        }"
                        x-text="selectedUser?.status === 'present' ? 'PRÉSENT' : (selectedUser?.status === 'on_leave' ? 'CONGÉ' : 'ABSENT')">
                    </span>
                </div>
                <div class="p-3 border bg-slate-950/60 rounded-xl border-slate-800">
                    <span class="block font-sans uppercase text-slate-500">Date d'embauche / Inscription</span>
                    <span class="text-sm font-semibold text-white" x-text="selectedUser?.created_at"></span>
                </div>
            </div>

            <div class="pt-2 space-y-3">
                <h4 class="text-xs font-bold tracking-wider uppercase text-slate-400">Performances & Portefeuille</h4>

                <template x-if="selectedUser?.role.toLowerCase().includes('collect') || selectedUser?.role.toLowerCase().includes('agent')">
                    <div class="p-4 space-y-3 border bg-emerald-950/20 border-emerald-800/40 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-300">Clients sous gestion :</span>
                            <span class="text-base font-bold text-emerald-400" x-text="selectedUser?.total_clients + ' clients'"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Zone affectée :</span>
                            <span class="text-slate-200">Secteur Agence Principale</span>
                        </div>
                    </div>
                </template>

                <template x-if="selectedUser?.role.toLowerCase().includes('cashier') || selectedUser?.role.toLowerCase().includes('caiss')">
                    <div class="p-4 space-y-2 border bg-blue-950/20 border-blue-800/40 rounded-xl">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-300">Poste :</span>
                            <span class="font-bold text-blue-400">Guichet & Opérations Caisse</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Plafond autorisé :</span>
                            <span class="text-slate-200">5,000,000 XAF</span>
                        </div>
                    </div>
                </template>

                <template x-if="selectedUser?.role.toLowerCase().includes('loan') || selectedUser?.role.toLowerCase().includes('credit')">
                    <div class="p-4 space-y-2 border bg-purple-950/20 border-purple-800/40 rounded-xl">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-300">Dossiers de crédit actifs :</span>
                            <span class="font-bold text-purple-400">12 dossiers</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Encours de crédit géré :</span>
                            <span class="text-slate-200">18,500,000 XAF</span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button @click="selectedUser = null"
                        class="px-4 py-2 text-sm transition text-slate-400 hover:text-white">
                    Fermer
                </button>
                <a :href="'/directeur/personnel/' + selectedUser?.id + '/edit'"
                class="px-4 py-2 text-sm font-semibold transition rounded-xl bg-emerald-500 text-slate-950 hover:bg-emerald-400">
                    Modifier le profil
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
