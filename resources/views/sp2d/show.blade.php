<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Detail SP2D</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <span class="text-sm text-slate-500">Nomor SP2D</span>
                            <p class="font-medium text-lg">{{ $sp2d->nomor_sp2d }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">OPD</span>
                            <p class="font-medium text-lg">{{ $sp2d->opd->nama_opd }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">Tanggal</span>
                            <p class="font-medium">{{ $sp2d->tanggal->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">Triwulan</span>
                            <p class="font-medium">Triwulan {{ $sp2d->triwulan }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">Tahun</span>
                            <p class="font-medium">{{ $sp2d->tahun }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">Jumlah Total</span>
                            <p class="font-medium text-lg text-brand-700">Rp {{ number_format($sp2d->jumlah_total, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="text-sm font-semibold text-slate-800 mb-3">Kode Belanja</h3>
                        @if ($sp2d->budgetCodes->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Uraian dan hierarki belanja</th>
                                    <th>Sumber dana</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sp2d->budgetCodes as $code)
                                <tr>
                                    <td class="font-medium">{{ $code->kode_belanja }}</td>
                                    <td>
                                        <p class="font-medium">{{ $code->uraian }}</p>
                                        @if ($code->subactivity)
                                            <dl class="mt-2 text-sm space-y-1">
                                                <div><dt class="text-slate-500">Urusan</dt><dd>{{ $code->subactivity->activity->program->affair->name }}</dd></div>
                                                <div><dt class="text-slate-500">Program</dt><dd>{{ $code->subactivity->activity->program->name }}</dd></div>
                                                <div><dt class="text-slate-500">Kegiatan</dt><dd>{{ $code->subactivity->activity->name }}</dd></div>
                                                <div><dt class="text-slate-500">Subkegiatan</dt><dd>{{ $code->subactivity->name }}</dd></div>
                                            </dl>
                                        @else
                                            <p class="mt-2 text-sm text-slate-500">Hierarki belanja belum tersedia.</p>
                                        @endif
                                    </td>
                                    <td>
                                        @forelse ($code->fundingAllocations as $allocation)
                                            <p class="text-sm">{{ $allocation->fundingSource->name }}</p>
                                            <p class="text-sm text-slate-500 mb-2">Rp {{ number_format($allocation->amount, 2, ',', '.') }}</p>
                                        @empty
                                            <p class="text-sm text-slate-500">Belum ditentukan</p>
                                        @endforelse
                                        <a class="text-sm font-medium text-brand-700" href="{{ route('sp2d.funding.edit', [$sp2d, $code]) }}">Atur sumber dana</a>
                                    </td>
                                    <td class="text-right">Rp {{ number_format($code->jumlah, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-slate-400 text-center py-4">Belum ada kode belanja.</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50/60 flex justify-between">
                    <a href="{{ route('sp2d.index') }}" class="btn-secondary">Kembali</a>
                    <div class="flex gap-2">
                        <a href="{{ route('sp2d.edit', $sp2d) }}" class="btn btn-sm bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-100">Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
