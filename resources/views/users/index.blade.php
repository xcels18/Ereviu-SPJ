<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Kelola User</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola akun admin, irban, dan inspektur beserta wilayah kerjanya</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah User
            </a>
        </div>
    </x-slot>

    @php
        $roleBadges = [
            'admin' => 'badge-purple',
            'irban' => 'badge-blue',
            'operator_irban' => 'badge-indigo',
            'inspektur' => 'badge-green',
        ];
        $roleLabels = [
            'admin' => 'Admin',
            'irban' => 'Irban',
            'operator_irban' => 'Operator Irban',
            'inspektur' => 'Inspektur',
        ];
        $roleAvatarColors = [
            'admin' => 'bg-purple-100 text-purple-700',
            'irban' => 'bg-blue-100 text-blue-700',
            'operator_irban' => 'bg-indigo-100 text-indigo-700',
            'inspektur' => 'bg-emerald-100 text-emerald-700',
        ];
    @endphp

    <div class="p-6">
        @if (session('success'))
            <div class="alert-success mb-5">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert-error mb-5">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Stat summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Total User</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 2a4 4 0 10-4-4"/></svg>
                    </div>
                </div>
                <span class="stat-value">{{ $roleCounts->sum() }}</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Admin</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                </div>
                <span class="stat-value">{{ $roleCounts['admin'] ?? 0 }}</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Irban</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <span class="stat-value">{{ $roleCounts['irban'] ?? 0 }}</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Inspektur</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                </div>
                <span class="stat-value">{{ $roleCounts['inspektur'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Search & filter -->
        <div class="card p-4 mb-5">
            <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email..."
                        class="form-input !pl-10 w-full">
                </div>
                <select name="role" class="form-select sm:w-48" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="irban" {{ $roleFilter === 'irban' ? 'selected' : '' }}>Irban</option>
                    <option value="operator_irban" {{ $roleFilter === 'operator_irban' ? 'selected' : '' }}>Operator Irban</option>
                    <option value="inspektur" {{ $roleFilter === 'inspektur' ? 'selected' : '' }}>Inspektur</option>
                </select>
                <button type="submit" class="btn-secondary">Terapkan</button>
                @if ($search !== '' || $roleFilter !== '')
                    <a href="{{ route('users.index') }}" class="btn-secondary text-slate-500">Reset</a>
                @endif
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Wilayah Kerja</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 font-semibold text-sm {{ $roleAvatarColors[$user->primary_role] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="{{ $roleBadges[$user->primary_role] ?? 'badge-slate' }}">{{ $roleLabels[$user->primary_role] ?? ucfirst($user->primary_role) }}</span>
                            </td>
                            <td>
                                @if ($user->primary_role === 'irban')
                                    @if ($user->auditorOpdAssignments->count() > 0)
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            @foreach ($user->auditorOpdAssignments as $assignment)
                                                <span class="badge-slate">{{ $assignment->opd->singkatan ?? $assignment->opd->nama_opd }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-amber-600 text-xs font-medium">Belum ada wilayah kerja</span>
                                    @endif
                                @elseif ($user->primary_role === 'operator_irban')
                                    @if ($user->parentIrban)
                                        <span class="text-slate-600 text-xs">Menginduk ke <span class="font-medium text-slate-800">{{ $user->parentIrban->name }}</span></span>
                                    @else
                                        <span class="text-amber-600 text-xs font-medium">Belum ada Irban induk</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-sm">Semua OPD</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}"
                                        title="Edit user"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus user {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus user"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <p class="font-medium text-slate-500">
                                    @if ($search !== '' || $roleFilter !== '')
                                        Tidak ada user yang cocok dengan pencarian
                                    @else
                                        Belum ada data user
                                    @endif
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
