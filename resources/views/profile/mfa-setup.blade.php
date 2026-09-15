<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Aktifkan Verifikasi Dua Langkah</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pindai QR code menggunakan aplikasi autentikator (mis. Google Authenticator)</p>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-2xl space-y-5">
            <div class="card p-6">
                <div class="flex flex-col items-start gap-4">
                    <div class="border border-slate-200 rounded-lg p-4 bg-white">
                        {!! $qrCodeSvg !!}
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Atau masukkan kode berikut secara manual:</p>
                        <p class="mt-1 font-mono text-sm font-semibold tracking-widest text-slate-900">{{ $secret }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('mfa.enable') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="code" value="Kode Verifikasi" />
                        <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" class="mt-1 block w-full" placeholder="123456" required autofocus />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Aktifkan MFA</x-primary-button>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
