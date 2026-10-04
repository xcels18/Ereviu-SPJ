<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KawalSPJ') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ek-theme ek-public font-sans antialiased">
    <header class="ek-public-header"><a href="{{ url('/') }}" class="ek-brand"><img src="{{ asset('images/logo-inspektorat.png') }}" alt="Logo Inspektorat" width="52" height="64"><span>KawalSPJ<small>INSPEKTORAT PUNCAK JAYA</small></span></a><span class="text-xs text-slate-500">Pengelolaan &amp; reviu SPJ</span></header>
    <main class="ek-entry">
        <section class="ek-entry-intro"><p class="ek-eyebrow">AKUNTABILITAS DAERAH</p><h1>Dokumen tertib.<br><span>Reviu terarah.</span></h1><p>Kelola dokumen pertanggungjawaban, periksa kelengkapan bukti, dan pantau hasil reviu dalam satu ruang kerja.</p><div class="ek-entry-note">Inspektorat Kabupaten Puncak Jaya<br><strong>Tercatat dengan baik. Diperiksa dengan cermat.</strong></div></section>
        <section class="ek-entry-card" aria-label="Akses akun">{{ $slot }}</section>
    </main>
    <footer class="ek-public-footer"><span>© {{ date('Y') }} Inspektorat Kabupaten Puncak Jaya</span><span>KawalSPJ · Ruang pertanggungjawaban daerah</span></footer>
</body>
</html>
