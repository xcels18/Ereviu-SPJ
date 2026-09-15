<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Pemulihan MFA</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #1e3a8a;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #4b5563;
        }
        .box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff7ed;
            border-color: #fdba74;
            color: #7c2d12;
        }
        .warning strong {
            display: block;
            margin-bottom: 6px;
        }
        .codes {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .code {
            display: inline-block;
            width: 48%;
            margin: 1%;
            padding: 12px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            background: #ffffff;
            border: 1px dashed #9ca3af;
            border-radius: 6px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kode Pemulihan MFA</h1>
        <p>Akun: {{ $user->email }}</p>
        <p>Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <div class="box warning">
        <strong>PENTING</strong>
        Simpan dokumen ini di tempat yang aman. Setiap kode pemulihan hanya dapat digunakan satu kali.
        Jika Anda kehilangan akses ke aplikasi autentikator, gunakan salah satu kode di bawah ini untuk masuk.
    </div>

    <div class="box">
        <p style="margin-top: 0; font-weight: 600;">Kode Pemulihan Anda:</p>
        <div class="codes">
            @foreach ($recoveryCodes as $code)
                <div class="code">{{ $code }}</div>
            @endforeach
        </div>
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem. Jangan bagikan kode pemulihan kepada siapa pun.
    </div>
</body>
</html>
