<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Profil Saya</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola informasi akun dan keamanan Anda</p>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-2xl space-y-5">
            <div class="card p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="card p-6">
                @include('profile.partials.update-password-form')
            </div>

            <div class="card p-6">
                @include('profile.partials.mfa-form')
            </div>

            <div class="card p-6 border-red-100">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
