<x-app-layout>
    <x-slot name="header"><h1 class="page-title">Alokasi Sumber Dana</h1></x-slot>
    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6">
        <div class="card p-6">
            <p class="text-sm text-slate-500">{{ $sp2d->nomor_sp2d }} · {{ $sp2d->opd->nama_opd }}</p>
            <h2 class="mt-2 text-lg font-semibold">{{ $budgetCode->kode_belanja }} — {{ $budgetCode->uraian }}</h2>
            <p class="mt-2 font-semibold">Nominal rincian: Rp {{ number_format($budgetCode->jumlah, 2, ',', '.') }}</p>
            <p class="mt-2 mb-6 text-sm text-slate-500">Pilih nama yang sudah tersedia atau ketik nama sumber dana baru. Jika diisi, total alokasi wajib sama dengan nominal rincian. Hapus seluruh baris untuk menandai “Belum ditentukan”.</p>
            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-lg bg-red-50 p-4 text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif
            <datalist id="funding-sources">@foreach ($sources as $source)<option value="{{ $source }}"></option>@endforeach</datalist>
            <form method="POST" action="{{ route('sp2d.funding.update', [$sp2d, $budgetCode]) }}" x-data="{ rows: {{ Illuminate\Support\Js::from(old('allocations', $allocations)) }} }">
                @csrf
                @method('PUT')
                <input type="hidden" name="confirm_funding" value="1">
                <div class="space-y-3">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 border-b border-slate-100 pb-4">
                            <label class="sm:col-span-7"><span class="form-label">Sumber dana</span><input class="form-input" list="funding-sources" :name="`allocations[${index}][name]`" x-model="row.name" maxlength="255" required autocomplete="off"></label>
                            <label class="sm:col-span-4"><span class="form-label">Nominal (Rp)</span><input class="form-input" type="number" min="0" max="9999999999999.99" step="0.01" :name="`allocations[${index}][amount]`" x-model="row.amount" required></label>
                            <div class="sm:col-span-1 flex items-end"><button type="button" class="text-sm text-red-700 py-2" @click="rows.splice(index, 1)" :aria-label="`Hapus sumber dana ${index + 1}`">Hapus</button></div>
                        </div>
                    </template>
                    <p x-show="rows.length === 0" class="text-sm text-slate-500">Belum ditentukan</p>
                </div>
                <button type="button" class="btn-secondary mt-4" @click="rows.push({name: '', amount: ''})" :disabled="rows.length >= 30">Tambah sumber dana</button>
                <div class="mt-6 flex justify-between gap-3">
                    <a class="btn-secondary" href="{{ route('sp2d.show', $sp2d) }}">Kembali</a>
                    <button type="submit" class="btn-primary">Simpan alokasi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
