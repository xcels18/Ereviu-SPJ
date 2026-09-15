<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="page-title">Tambah User</h1>
                <p class="text-sm text-slate-500 mt-0.5">Buat akun baru untuk admin, irban, atau inspektur</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <div class="lg:col-span-2 card p-6">
                <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="form-label">Nama <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-input" placeholder="Nama lengkap" required>
                            @error('name')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-input" placeholder="user@inspektorat.go.id" required>
                            @error('email')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password"
                            class="form-input" placeholder="Minimal 8 karakter" required>
                        @error('password')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="roleOptions">
                            @php
                                $roleOptions = [
                                    'admin' => ['label' => 'Admin', 'desc' => 'Akses penuh seluruh OPD', 'ring' => 'peer-checked:ring-purple-400 peer-checked:bg-purple-50 peer-checked:border-purple-300', 'dot' => 'text-purple-600'],
                                    'irban' => ['label' => 'Irban', 'desc' => 'Terbatas pada wilayah kerja', 'ring' => 'peer-checked:ring-blue-400 peer-checked:bg-blue-50 peer-checked:border-blue-300', 'dot' => 'text-blue-600'],
                                    'operator_irban' => ['label' => 'Operator Irban', 'desc' => 'Hanya upload SPJ, menginduk ke Irban tertentu', 'ring' => 'peer-checked:ring-indigo-400 peer-checked:bg-indigo-50 peer-checked:border-indigo-300', 'dot' => 'text-indigo-600'],
                                    'inspektur' => ['label' => 'Inspektur', 'desc' => 'Akses penuh seluruh OPD', 'ring' => 'peer-checked:ring-emerald-400 peer-checked:bg-emerald-50 peer-checked:border-emerald-300', 'dot' => 'text-emerald-600'],
                                ];
                            @endphp
                            @foreach ($roleOptions as $value => $opt)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="{{ $value }}" required
                                        {{ old('role') === $value ? 'checked' : '' }}
                                        onchange="toggleWilayahKerja(this.value)"
                                        class="peer sr-only">
                                    <div class="border border-slate-200 rounded-xl px-4 py-3 transition-all peer-checked:ring-2 {{ $opt['ring'] }}">
                                        <p class="font-semibold text-sm text-slate-800">{{ $opt['label'] }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $opt['desc'] }}</p>
                                    </div>
                                    <svg class="w-4 h-4 absolute top-3 right-3 hidden peer-checked:block {{ $opt['dot'] }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.415 0l-3.5-3.5a1 1 0 111.415-1.415L8.5 12.086l6.79-6.796a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </label>
                            @endforeach
                        </div>
                        @error('role')
                            <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="operatorIrbanWrapper" class="hidden border-t border-slate-100 pt-5">
                        <label for="parent_irban_id" class="form-label">Irban Induk <span class="text-red-500">*</span></label>
                        <p class="text-xs text-slate-500 mb-3">Operator akan mengikuti wilayah kerja (OPD) dari Irban yang dipilih di sini, dan hanya dapat mengunggah SPJ (tanpa hak mereviu).</p>
                        <select name="parent_irban_id" id="parent_irban_id" class="form-input w-full">
                            <option value="">-- Pilih Irban --</option>
                            @foreach ($irbanUsers as $irban)
                                <option value="{{ $irban->id }}" {{ (string) old('parent_irban_id') === (string) $irban->id ? 'selected' : '' }}>{{ $irban->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_irban_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wilayahKerjaWrapper" class="hidden border-t border-slate-100 pt-5">
                        <label class="form-label">Wilayah Kerja (OPD) <span class="text-red-500">*</span></label>
                        <p class="text-xs text-slate-500 mb-3">Irban hanya dapat melihat data SP2D/SPJ dari OPD yang dipilih.</p>
                        <div class="relative mb-3">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                            <input type="text" id="opdSearch" oninput="filterOpdList(this.value)"
                                placeholder="Cari OPD..." class="form-input !pl-9 w-full text-sm">
                        </div>
                        <div class="border border-slate-200 rounded-xl p-3 max-h-64 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-1" id="opdList">
                            @foreach ($opds as $opd)
                            <label class="opd-item flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer" data-name="{{ strtolower($opd->nama_opd) }}">
                                <input type="checkbox" name="opd_ids[]" value="{{ $opd->id }}"
                                    {{ in_array($opd->id, old('opd_ids', [])) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                                <span class="text-sm text-slate-700 truncate">{{ $opd->nama_opd }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('opd_ids')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                        <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1 card p-6 bg-slate-50/60">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">Panduan Role</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex gap-3">
                        <span class="badge-purple shrink-0">Admin</span>
                        <p class="text-slate-500 text-xs leading-relaxed">Akses penuh ke seluruh data SP2D/SPJ dari semua OPD, termasuk pengelolaan user.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="badge-blue shrink-0">Irban</span>
                        <p class="text-slate-500 text-xs leading-relaxed">Hanya dapat melihat & mereviu data dari OPD yang menjadi wilayah kerjanya.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="badge-indigo shrink-0">Operator Irban</span>
                        <p class="text-slate-500 text-xs leading-relaxed">Hanya bertugas mengunggah SPJ (tanpa hak mereviu), mengikuti wilayah kerja Irban induk yang dipilih.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="badge-green shrink-0">Inspektur</span>
                        <p class="text-slate-500 text-xs leading-relaxed">Akses penuh untuk memantau seluruh data dari semua OPD.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleWilayahKerja(role) {
            document.getElementById('wilayahKerjaWrapper').classList.toggle('hidden', role !== 'irban');
            document.getElementById('operatorIrbanWrapper').classList.toggle('hidden', role !== 'operator_irban');
        }
        function filterOpdList(keyword) {
            keyword = keyword.toLowerCase();
            document.querySelectorAll('#opdList .opd-item').forEach(function (el) {
                el.style.display = el.dataset.name.includes(keyword) ? '' : 'none';
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            const checked = document.querySelector('input[name="role"]:checked');
            toggleWilayahKerja(checked ? checked.value : '');
        });
    </script>
</x-app-layout>
