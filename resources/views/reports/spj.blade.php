<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Laporan SPJ</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center flex-wrap gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Daftar SPJ</h2>
                    <div class="flex gap-2 items-center">
                        <form method="GET" class="flex gap-2 items-center">
                            <select name="opd_id" class="form-select !py-1.5 !text-sm">
                                <option value="">Semua OPD</option>
                                @foreach ($opds as $opd)
                                <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="form-select !py-1.5 !text-sm">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            </select>
                            <button type="submit" class="btn-secondary btn-sm">Filter</button>
                        </form>
                        <div class="flex gap-2">
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-sm bg-red-50 text-red-700 border border-red-100 hover:bg-red-100">Export PDF</a>
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-sm bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100">Export Excel</a>
                        </div>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>No SPJ</th>
                            <th>OPD</th>
                            <th>Kode Belanja</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-center">Status</th>
                            <th>Reviewed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($spjs as $spj)
                        <tr>
                            <td class="font-medium">{{ $spj->nomor_spj }}</td>
                            <td>{{ $spj->opd->nama_opd }}</td>
                            <td>{{ $spj->budgetCode->kode_belanja ?? 'Gabungan (Seluruh Akun)' }}</td>
                            <td class="text-right">Rp {{ number_format($spj->jumlah, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="
                                    @if($spj->status == 'reviewed') badge-green
                                    @elseif($spj->status == 'submitted') badge-blue
                                    @else badge-slate @endif">
                                    {{ ucfirst($spj->status) }}
                                </span>
                            </td>
                            <td>{{ $spj->reviewer->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada data SPJ.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="px-6 pb-6">{{ $spjs->links() }}</div>
</x-app-layout>
