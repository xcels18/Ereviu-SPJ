<x-guest-layout>
    <div class="mb-8">
        <p class="eyebrow">RUANG KERJA KAWALSPJ</p>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat datang.</h2>
        <p class="mt-1.5 text-sm text-slate-500">Masuk untuk mengelola dokumen dan memantau reviu SPJ.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="form-label">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-input @error('email') border-red-400 focus:ring-red-400 @enderror"
                placeholder="nama@instansi.go.id" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-700 hover:text-brand-800 transition-colors">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                class="form-input @error('password') border-red-400 focus:ring-red-400 @enderror"
                placeholder="••••••••" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox"
                class="w-4 h-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500 transition"
                name="remember">
            <label for="remember_me" class="ms-2.5 text-sm text-slate-600 select-none">Ingat saya</label>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-semibold mt-2">
            Masuk
        </button>
    </form>
</x-guest-layout>
