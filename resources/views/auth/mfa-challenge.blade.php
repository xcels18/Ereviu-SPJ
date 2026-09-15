<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Dua Langkah</h2>
        <p class="mt-1.5 text-sm text-slate-500">Masukkan kode dari aplikasi autentikator Anda, atau gunakan salah satu kode pemulihan.</p>
    </div>

    <form method="POST" action="{{ route('mfa.challenge.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="code" class="form-label">Kode Verifikasi</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                class="form-input @error('code') border-red-400 focus:ring-red-400 @enderror"
                placeholder="123456" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-semibold mt-2">
            Verifikasi
        </button>
    </form>
</x-guest-layout>
