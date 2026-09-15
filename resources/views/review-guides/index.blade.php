<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Panduan Reviu</h1>
            </div>
            <div class="flex gap-2 items-center flex-wrap">
                <form action="{{ route('review-guides.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2 items-center">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-slate-200 file:text-sm file:font-medium file:bg-white file:text-slate-700 hover:file:bg-slate-50">
                    <button type="submit" class="btn btn-sm bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100">Import</button>
                </form>
                <a href="{{ route('review-guides.export') }}" class="btn btn-sm bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100">Export</a>
                @can('manage_review_guide')
                <a href="{{ route('review-guides.create') }}" class="btn-primary">
                    + Tambah Panduan
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card overflow-hidden">
                <div class="table-container"><table class="table">
                    <thead>
                        <tr>
                            <th>Kode Rekening</th>
                            <th>Nama Belanja</th>
                            <th>Dokumen Wajib</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guides as $guide)
                        <tr>
                            <td class="font-medium">{{ $guide->kode_rekening }}</td>
                            <td>{{ $guide->nama_belanja }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($guide->required_documents as $doc)
                                    <span class="badge-slate">{{ $doc }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($guide->is_active)
                                <span class="badge-green">Aktif</span>
                                @else
                                <span class="badge-slate">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('manage_review_guide')
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('review-guides.edit', $guide) }}" class="btn btn-sm bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-100">Edit</a>
                                    <form action="{{ route('review-guides.destroy', $guide) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-red-50 text-red-700 border border-red-100 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada panduan reviu.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table></div>

                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $guides->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
