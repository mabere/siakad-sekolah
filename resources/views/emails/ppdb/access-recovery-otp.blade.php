<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Pemulihan PIN PPDB</title></head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2>Pemulihan PIN PPDB</h2>
    <p>Kami menerima permintaan pemulihan PIN untuk nomor pendaftaran <strong>{{ $application->application_number }}</strong>.</p>
    <p>Kode OTP Anda:</p>
    <p style="font-size: 28px; letter-spacing: 8px; font-weight: 700;">{{ $code }}</p>
    <p>Kode berlaku selama {{ $expiresInMinutes }} menit dan hanya dapat digunakan satu kali.</p>
    <p>Jika Anda tidak meminta pemulihan PIN, abaikan email ini.</p>
</body>
</html>
