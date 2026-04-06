<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background:#1565C0; padding:32px 40px;">
                            <img
                                src="{{ config('app.url') }}/assets/img/logoagape.png"
                                alt="Logo Panti Asuhan Kasih Agape"
                                width="72" height="72"
                                style="border-radius:50%; background:rgba(255,255,255,0.15); padding:8px; display:block; margin:0 auto 16px;"
                            >
                            <h1 style="color:#ffffff; font-size:22px; margin:0 0 6px; font-weight:700;">Panti Asuhan Kasih Agape</h1>
                            <p style="color:#bbdefb; font-size:14px; margin:0;">Permintaan Reset Password</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="font-size:16px; color:#333; margin:0 0 12px;">Halo, <strong style="color:#1565C0;">{{ $user->name }}</strong>!</p>

                            <p style="font-size:15px; color:#555; line-height:1.7; margin:0 0 20px;">
                                Kami menerima permintaan untuk mereset password akun Anda
                                (<strong>{{ $user->email }}</strong>).
                                Klik tombol di bawah untuk membuat password baru:
                            </p>

                            {{-- Tombol Reset --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <a href="{{ $resetUrl }}"
                                           style="display:inline-block; background:#1565C0; color:#ffffff; text-decoration:none; font-size:16px; font-weight:700; padding:14px 36px; border-radius:8px; letter-spacing:0.5px;">
                                            Reset Password Sekarang
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Info Expired --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#FFF8E1; border-left:4px solid #FFC107; padding:14px 16px; border-radius:6px;">
                                        <p style="font-size:14px; color:#856404; margin:0;">
                                            ⏰ Link ini hanya berlaku <strong>60 menit</strong>. Jika sudah lewat, minta link baru.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#999; margin:24px 0 8px;">
                                Jika tombol tidak berfungsi, salin link berikut ke browser:
                            </p>
                            <p style="font-size:12px; color:#888; word-break:break-all; background:#f8f8f8; padding:10px 12px; border-radius:6px; margin:0;">
                                {{ $resetUrl }}
                            </p>

                            <p style="font-size:13px; color:#e53935; margin:20px 0 0;">
                                ⚠️ Jika Anda tidak merasa meminta reset password, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8f9fa; padding:20px 40px; border-top:1px solid #eee; text-align:center;">
                            <p style="font-size:13px; color:#aaa; margin:0 0 4px;">© {{ date('Y') }} Panti Asuhan Kasih Agape</p>
                            <p style="font-size:12px; color:#bbb; margin:0;">
                                Jl. Pakis Gunung I / 133 B, Surabaya &nbsp;|&nbsp;
                                <a href="https://wa.me/6281231663336" style="color:#25D366; text-decoration:none;">WhatsApp Admin</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
