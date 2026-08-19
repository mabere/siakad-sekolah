<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran {{ $application->application_number }}</title>
    <style>
        @page { margin: 28px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.5; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; }
        .school { color: #1d4ed8; font-size: 18px; font-weight: bold; }
        .title { color: #111827; font-size: 16px; font-weight: bold; margin-top: 4px; }
        .muted { color: #6b7280; }
        .notice { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; margin: 18px 0; padding: 12px; }
        .credentials { border: 1px solid #93c5fd; border-radius: 6px; margin: 16px 0; padding: 12px; }
        .credential-row { display: inline-block; vertical-align: top; width: 48%; }
        .label { color: #4b5563; font-size: 10px; }
        .value { color: #111827; font-family: DejaVu Sans Mono, monospace; font-size: 16px; font-weight: bold; margin-top: 3px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        td { border-bottom: 1px solid #e5e7eb; padding: 7px 4px; vertical-align: top; }
        td:first-child { color: #6b7280; width: 34%; }
        .footer { border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 10px; margin-top: 28px; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school">{{ $application->school?->name }}</div>
        <div class="title">Bukti Pendaftaran Peserta Didik Baru</div>
        <div class="muted">{{ $application->period?->name }}</div>
    </div>

    <div class="notice">
        Simpan dokumen ini. Nomor pendaftaran dan PIN diperlukan untuk memantau status PPDB,
        mengunggah bukti pembayaran, dan melakukan daftar ulang bila dinyatakan diterima.
    </div>

    <div class="credentials">
        <div class="credential-row">
            <div class="label">Nomor pendaftaran</div>
            <div class="value">{{ $application->application_number }}</div>
        </div>
        <div class="credential-row">
            <div class="label">PIN akses</div>
            <div class="value">{{ $accessCode }}</div>
        </div>
    </div>

    <table>
        <tr><td>Nama calon siswa</td><td><strong>{{ $application->candidate?->name }}</strong></td></tr>
        <tr><td>Jalur pendaftaran</td><td>{{ $application->pathway?->name }}</td></tr>
        <tr><td>Sumber pendaftaran</td><td>{{ ucfirst($application->source) }}</td></tr>
        <tr><td>Kontak pendaftaran</td><td>{{ $application->contact_phone ?: '-' }}</td></tr>
        <tr><td>Tanggal pendaftaran</td><td>{{ $application->submitted_at?->format('d-m-Y H:i') }}</td></tr>
    </table>

    <div class="footer">
        Cek status pendaftaran melalui: {{ $statusUrl }}<br>
        Jika PIN hilang, hubungi panitia PPDB untuk dilakukan reset setelah verifikasi identitas.
    </div>
</body>
</html>
