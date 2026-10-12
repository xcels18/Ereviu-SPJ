<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Tambah SP2D Baru</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6">
                <form action="{{ route('sp2d.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="opd_id" class="form-label">OPD</label>
                            <select name="opd_id" id="opd_id" class="form-input" required>
                                <option value="">Pilih OPD</option>
                                @foreach ($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="nomor_sp2d" class="form-label">Nomor SP2D</label>
                            <input type="text" name="nomor_sp2d" id="nomor_sp2d" value="{{ old('nomor_sp2d') }}"
                                class="form-input" required>
                            @error('nomor_sp2d')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                                class="form-input" required>
                            @error('tanggal')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <p class="mb-4 text-sm text-slate-500">Tahun dan triwulan dihitung otomatis dari tanggal SP2D.</p>

                    <div class="mb-6">
                        <label for="jumlah_total" class="form-label">Jumlah Total</label>
                        <input type="number" name="jumlah_total" id="jumlah_total" value="{{ old('jumlah_total') }}"
                            class="form-input" step="0.01" required>
                        @error('jumlah_total')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="border-t border-slate-100 pt-6 mb-6">
                        <h3 class="text-sm font-semibold text-slate-800 mb-3">Kode Belanja (Opsional)</h3>
                        <div id="budget-codes" class="space-y-3">
                            <div class="grid grid-cols-12 gap-2 items-center budget-row">
                                <input type="text" name="budget_codes[0][kode_belanja]" placeholder="Kode"
                                    class="col-span-3 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                                <input type="text" name="budget_codes[0][uraian]" placeholder="Uraian"
                                    class="col-span-6 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                                <input type="number" name="budget_codes[0][jumlah]" placeholder="Jumlah"
                                    class="col-span-2 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                                <button type="button" class="col-span-1 text-red-600 hover:text-red-800" onclick="removeBudgetRow(this)">✕</button>
                            </div>
                        </div>
                        <button type="button" onclick="addBudgetRow()" class="mt-2 text-sm text-brand-700 hover:text-brand-800 font-medium">+ Tambah Kode Belanja</button>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('sp2d.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    let budgetRowIndex = 1;
    function addBudgetRow() {
        const container = document.getElementById('budget-codes');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 items-center budget-row';
        row.innerHTML = `
            <input type="text" name="budget_codes[${budgetRowIndex}][kode_belanja]" placeholder="Kode" class="col-span-3 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
            <input type="text" name="budget_codes[${budgetRowIndex}][uraian]" placeholder="Uraian" class="col-span-6 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
            <input type="number" name="budget_codes[${budgetRowIndex}][jumlah]" placeholder="Jumlah" class="col-span-2 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
            <button type="button" class="col-span-1 text-red-600 hover:text-red-800" onclick="removeBudgetRow(this)">✕</button>
        `;
        container.appendChild(row);
        budgetRowIndex++;
    }

    function removeBudgetRow(btn) {
        const rows = document.querySelectorAll('.budget-row');
        if (rows.length > 1) {
            btn.closest('.budget-row').remove();
        }
    }
    </script>
    @endpush
</x-app-layout>
