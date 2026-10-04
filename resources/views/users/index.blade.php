<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 items-center justify-between w-full">
            <div>
                <h1 class="page-title">Pengguna & Hak Akses</h1>
                <p class="text-sm text-slate-500 mt-0.5">Atur akun pengguna, peran, dan cakupan wilayah kerja.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengguna
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
    @endphp

    <div class="p-6 users-directory">
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

        <div class="users-overview" aria-label="Ringkasan pengguna berdasarkan peran">
            <div class="users-overview-intro">
                <span>Administrasi akun</span>
                <h2>Direktori pengguna</h2>
                <p>Pilih peran untuk melihat akun dan penugasannya.</p>
            </div>
            <div class="users-role-grid">
                @foreach ($roleLabels as $role => $label)
                    <a href="{{ route('users.index', ['role' => $role]) }}" class="users-role-tile {{ $roleFilter === $role ? 'is-selected' : '' }}" @if ($roleFilter === $role) aria-current="true" @endif>
                        <span>{{ $label }}</span>
                        <strong>{{ $roleCounts[$role] ?? 0 }}</strong>
                        <small>Lihat pengguna <span aria-hidden="true">↗</span></small>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search & filter -->
        <div class="card p-4 mb-5 users-filter">
            <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <label for="user-search" class="sr-only">Cari nama atau email</label>
                    <input id="user-search" type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email..."
                        class="form-input !pl-10 w-full">
                </div>
                <label for="user-role" class="sr-only">Filter peran</label>
                <select id="user-role" name="role" class="form-select sm:w-48">
                    <option value="">Semua peran</option>
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

        <div class="card overflow-hidden users-list">
            <div class="users-list-heading">
                <div><h2>Daftar pengguna <span>{{ $users->total() }}</span></h2><p>Informasi akun dan wilayah kerja yang dapat diakses.</p></div>
                <span class="text-xs text-slate-500">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</span>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Pengguna</th>
                            <th scope="col">Peran</th>
                            <th scope="col">Wilayah Kerja</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="users-avatar">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900 users-name">{{ $user->name }} @if ($user->id === auth()->id()) <span class="users-self">Anda</span> @endif</p>
                                        <p class="text-xs text-slate-500 users-email">{{ $user->email }}</p>
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
                                        aria-label="Edit pengguna {{ $user->name }}"
                                        class="users-edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg><span>Edit</span>
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" data-confirm="Hapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan." onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf @method('DELETE')
                                        <button type="submit" aria-label="Hapus pengguna {{ $user->name }}"
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
                                        Tidak ada pengguna yang cocok dengan pencarian
                                    @else
                                        Belum ada pengguna
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
