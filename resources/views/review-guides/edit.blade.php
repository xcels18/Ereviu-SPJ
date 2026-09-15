<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Edit Panduan Reviu</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6">
                <form action="{{ route('review-guides.update', $reviewGuide) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="kode_rekening" class="form-label">Kode Rekening</label>
                        <input type="text" name="kode_rekening" id="kode_rekening" value="{{ old('kode_rekening', $reviewGuide->kode_rekening) }}"
                            class="form-input" required>
                    </div>

                    <div class="mb-4">
                        <label for="nama_belanja" class="form-label">Nama Belanja</label>
                        <input type="text" name="nama_belanja" id="nama_belanja" value="{{ old('nama_belanja', $reviewGuide->nama_belanja) }}"
                            class="form-input" required>
                    </div>

                    <div class="mb-4">
                        <label for="required_documents" class="form-label">Dokumen Wajib</label>
                        <input type="text" name="required_documents" id="required_documents"
                            value="{{ old('required_documents', implode(', ', $reviewGuide->required_documents ?? [])) }}"
                            class="form-input" required>
                        <p class="text-xs text-slate-500 mt-1">Pisahkan dengan koma (,)</p>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" id="notes" rows="3" class="form-input">{{ old('notes', $reviewGuide->notes) }}</textarea>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ $reviewGuide->is_active ? 'checked' : '' }}
                                class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                            <span class="ml-2 text-sm text-slate-700">Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('review-guides.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
