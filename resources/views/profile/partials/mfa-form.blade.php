<section class="space-y-4">
    <header>
        <h2 class="text-base font-semibold text-slate-900">
            {{ __('Verifikasi Dua Langkah (MFA)') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Tambahkan lapisan keamanan ekstra pada akun Anda menggunakan aplikasi autentikator.') }}
        </p>
    </header>

    @if (session('status') === 'mfa-enabled' && session('mfa_recovery_codes_show'))
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-800">Simpan kode pemulihan berikut di tempat yang aman.</p>
            <p class="text-xs text-amber-700 mt-1">Setiap kode hanya dapat digunakan satu kali jika Anda kehilangan akses ke aplikasi autentikator.</p>
            <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm text-amber-900">
                @foreach (session('mfa_recovery_codes_show') as $recoveryCode)
                    <div class="bg-white/60 rounded px-2 py-1">{{ $recoveryCode }}</div>
                @endforeach
            </div>

            <div class="mt-4">
                <a href="{{ route('mfa.recovery-codes.pdf') }}" target="_blank" rel="noopener noreferrer"
                   class="btn-primary inline-flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12M6 12a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 00-2-2M6 12V8a2 2 0 012-2h8a2 2 0 012 2v4m-6-6h2" />
                    </svg>
                    Cetak / Simpan PDF
                </a>
            </div>
        </div>
    @endif

    @if ($user->mfa_enabled)
        <div class="flex items-center gap-2 text-sm text-emerald-700">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            MFA aktif untuk akun Anda.
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('mfa.recovery-codes.pdf') }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 text-sm font-medium text-brand-700 hover:text-brand-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Unduh kode pemulihan (PDF)
            </a>
        </div>

        <form method="post" action="{{ route('mfa.disable') }}" class="space-y-4" x-data="{ confirmingDisable: false }">
            @csrf
            @method('delete')

            <div x-show="confirmingDisable" style="display: none;">
                <x-input-label for="mfa_disable_password" :value="__('Masukkan password untuk menonaktifkan MFA')" />
                <x-text-input id="mfa_disable_password" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-danger-button type="button" x-show="!confirmingDisable" x-on:click="confirmingDisable = true">
                    {{ __('Nonaktifkan MFA') }}
                </x-danger-button>

                <x-danger-button type="submit" x-show="confirmingDisable" style="display: none;">
                    {{ __('Konfirmasi Nonaktifkan') }}
                </x-danger-button>
            </div>
        </form>
    @else
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="inline-block w-2 h-2 rounded-full bg-slate-300"></span>
            MFA belum aktif untuk akun Anda.
        </div>

        <a href="{{ route('mfa.setup') }}" class="btn-primary inline-flex">
            {{ __('Aktifkan MFA') }}
        </a>
    @endif
</section>
