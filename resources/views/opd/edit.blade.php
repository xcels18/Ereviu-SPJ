<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('opd.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="page-title">Edit OPD</h1>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-lg">
            <div class="card p-6">
                <form action="{{ route('opd.update', $opd) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nama_opd" class="form-label">Nama OPD <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_opd" id="nama_opd" value="{{ old('nama_opd', $opd->nama_opd) }}"
                            class="form-input" required>
                        @error('nama_opd')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="singkatan" class="form-label">Singkatan</label>
                        <input type="text" name="singkatan" id="singkatan" value="{{ old('singkatan', $opd->singkatan) }}"
                            class="form-input" placeholder="Opsional">
                        @error('singkatan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('opd.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
