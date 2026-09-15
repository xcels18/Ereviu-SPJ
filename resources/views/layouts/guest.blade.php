<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SPJ OPD') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Left branding panel -->
            <div class="auth-brand hidden lg:flex lg:w-1/2 bg-brand-900 relative overflow-hidden flex-col justify-between p-12">
                <!-- Background photo -->
                <img src="{{ asset('images/login-bg.jpg') }}" alt="" aria-hidden="true"
                    class="absolute inset-0 w-full h-full object-cover">
                <!-- Color overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-brand-950/95 via-brand-900/90 to-brand-800/80"></div>
                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-brand-950/70 to-transparent"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-puncak-jaya.png') }}" alt="Logo Kabupaten Puncak Jaya" class="w-12 h-12 shrink-0 object-contain">
                        <span class="text-white font-bold text-xl tracking-tight">KawalSPJ</span>
                    </div>
                    <div class="mt-16">
                        <div class="w-10 h-1 bg-gold-400 rounded-full mb-5"></div>
                        <h1 class="text-4xl font-bold text-white leading-tight">Akuntabilitas daerah.<br>Lebih terarah.</h1>
                        <p class="mt-4 text-brand-200 text-base leading-relaxed max-w-sm">Platform pengelolaan dan reviu Surat Pertanggungjawaban (SPJ) secara terintegrasi dan efisien.</p>
                    </div>
                    <div class="mt-12 grid grid-cols-1 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-brand-200 text-sm">Upload & tracking dokumen SPJ</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-brand-200 text-sm">Reviu terstruktur berbasis panduan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-brand-200 text-sm">Laporan temuan otomatis & ekspor</span>
                        </div>
                    </div>
                </div>
                <p class="text-brand-300 text-xs relative z-10">&copy; {{ date('Y') }} Inspektorat Puncak Jaya. Hak cipta dilindungi.</p>
            </div>

            <!-- Right form panel -->
            <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white">
                <div class="w-full max-w-md">
                    <!-- Mobile logo -->
                    <div class="lg:hidden flex items-center gap-2.5 mb-10">
                        <img src="{{ asset('images/logo-puncak-jaya.png') }}" alt="Logo Kabupaten Puncak Jaya" class="w-10 h-10 shrink-0 object-contain">
                        <span class="font-bold text-base text-slate-900">Kawal<span class="text-brand-700">SPJ</span></span>
                    </div>

                    <div class="auth-card">
                        {{ $slot }}
                    </div>

                    <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} Inspektorat Puncak Jaya</p>
                </div>
            </div>
        </div>
    </body>
</html>
