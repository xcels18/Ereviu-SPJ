<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Laporan Temuan</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center flex-wrap gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Daftar Temuan Review</h2>
                    <div class="flex gap-2 items-center">
                        <form method="GET" class="flex gap-2 items-center">
                            <select name="opd_id" class="form-select !py-1.5 !text-sm">
                                <option value="">Semua OPD</option>
                                @foreach ($opds as $opd)
                                <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                                @endforeach
                            </select>
                            <select name="finding_type" class="form-select !py-1.5 !text-sm">
                                <option value="">Semua Jenis</option>
                                <option value="minor" {{ request('finding_type') == 'minor' ? 'selected' : '' }}>Minor</option>
                                <option value="major" {{ request('finding_type') == 'major' ? 'selected' : '' }}>Major</option>
                                <option value="note" {{ request('finding_type') == 'note' ? 'selected' : '' }}>Note</option>
                            </select>
                            <button type="submit" class="btn-secondary btn-sm">Filter</button>
                        </form>
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-sm bg-red-50 text-red-700 border border-red-100 hover:bg-red-100">Export PDF</a>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>SPJ</th>
                            <th>OPD</th>
                            <th>Jenis</th>
                            <th>Deskripsi</th>
                            <th>Auditor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($findings as $finding)
                        <tr>
                            <td class="font-medium">{{ $finding->spj->nomor_spj }}</td>
                            <td>{{ $finding->spj->opd->nama_opd }}</td>
                            <td>
                                <span class="
                                    @if($finding->finding_type == 'major') badge-red
                                    @elseif($finding->finding_type == 'minor') badge-yellow
                                    @else badge-blue @endif">
                                    {{ ucfirst($finding->finding_type) }}
                                </span>
                            </td>
                            <td>{{ $finding->description }}</td>
                            <td>{{ $finding->creator->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada temuan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="px-6 pb-6">{{ $findings->links() }}</div>
</x-app-layout>
