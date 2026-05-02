<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\View;

class ForgotPasswordController extends Controller
{
    // ─── Halaman form Lupa Password ────────────────────────────────────────────
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // ─── Proses kirim link reset via Email ─────────────────────────────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email tidak ditemukan dalam sistem kami.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Hapus token lama jika ada
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Buat token baru
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => hash('sha256', $token),
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $request->email]);

        // Render email HTML
        $htmlContent = View::make('emails.reset-password', [
            'user'     => $user,
            'resetUrl' => $resetUrl,
        ])->render();

        // Kirim via Brevo HTTP API (port 443 - tidak pernah diblokir)
        $apiKey = env('BREVO_API_KEY');

        if (!$apiKey) {
            // Fallback: coba via Mail facade biasa
            try {
                Mail::send('emails.reset-password', ['user' => $user, 'resetUrl' => $resetUrl],
                    function ($m) use ($user) {
                        $m->to($user->email)->subject('Reset Password - Panti Asuhan Kasih Agape');
                    });
                return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
            } catch (\Exception $e) {
                \Log::error('Mail fallback gagal: ' . $e->getMessage());
                // Jangan tampilkan error teknis ke user — arahkan ke WhatsApp
                return back()->with('info', 'Pengiriman email tidak tersedia saat ini. Silakan hubungi admin via WhatsApp untuk reset password.');
            }
        }

        try {
            $client = new Client(['timeout' => 15]);
            $response = $client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept'       => 'application/json',
                    'api-key'      => $apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'sender'      => [
                        'name'  => 'Panti Asuhan Kasih Agape',
                        'email' => env('MAIL_FROM_ADDRESS', 'noreply@pantiasuhankasihagape.id'),
                    ],
                    'to'          => [['email' => $user->email, 'name' => $user->name]],
                    'subject'     => 'Reset Password - Panti Asuhan Kasih Agape',
                    'htmlContent' => $htmlContent,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                return back()->with('success', 'Link reset password telah dikirim ke email Anda. Periksa kotak masuk (atau folder spam).');
            }

            return back()->with('error', 'Gagal mengirim email. Status: ' . $response->getStatusCode());

        } catch (\Exception $e) {
            \Log::error('Brevo API gagal: ' . $e->getMessage());
            return back()->with('info', 'Pengiriman email sedang bermasalah. Silakan coba lagi beberapa saat, atau hubungi admin via WhatsApp di bawah.');
        }
    }

    // ─── Halaman form isi password baru ────────────────────────────────────────
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // ─── Proses simpan password baru ───────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Cek token valid dan belum expired (60 menit)
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->first();

        if (!$record) {
            return back()->with('error', 'Token reset password tidak valid.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->with('error', 'Link reset password sudah kedaluwarsa. Silakan minta yang baru.');
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => bcrypt($request->password)]);

        // Hapus token setelah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}
