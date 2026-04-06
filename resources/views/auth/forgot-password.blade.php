<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - Panti Asuhan Kasih Agape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #e6f7ff;
            --text-color: #333740;
            --heading-color: #0d47a1;
            --muted-text-color: #5f6c7b;
            --container-bg: #ffffff;
            --input-bg: #f8f9fa;
            --main-color: #007bff;
            --border-color: #ced4da;
            --shadow-color: rgba(0, 123, 255, 0.25);
            --success-bg: rgba(40, 167, 69, 0.15);
            --danger-bg: rgba(220, 53, 69, 0.15);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: 62.5%; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            background-image: linear-gradient(135deg, rgb(255,255,255) 0%, rgb(87,195,245) 100%);
            min-height: 100vh;
            color: var(--text-color);
        }
        #particles-js {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; z-index: 1;
        }
        .center-wrapper {
            min-height: 100vh; display: flex;
            justify-content: center; align-items: center;
            padding: 2rem; position: relative; z-index: 2;
        }
        .login-card {
            width: 100%; max-width: 420px;
            background: var(--container-bg);
            border-radius: 20px; overflow: hidden;
            animation: fadeIn 0.8s ease-out;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .login-header {
            text-align: center;
            padding: 3rem 3rem 2rem;
            color: var(--text-color);
        }
        .logo {
            width: 80px; height: 80px;
            background: #e3f2fd; border: 2px solid #bbdefb;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 1.5rem;
            color: var(--main-color); font-size: 3rem;
        }
        .login-header h3 {
            font-size: 2.4rem; font-weight: 700;
            color: var(--heading-color); margin-bottom: 0.5rem; line-height: 1.3;
        }
        .login-header p {
            font-size: 1.5rem; color: var(--muted-text-color);
        }
        .login-body { padding: 0 3rem 3rem; }
        .form-label {
            font-size: 1.5rem; font-weight: 600;
            margin-bottom: 0.8rem; color: var(--text-color);
        }
        .form-control {
            height: 50px; font-size: 1.6rem;
            background: var(--input-bg); border: 1px solid var(--border-color);
            border-radius: 10px; color: var(--text-color); transition: var(--transition);
        }
        .form-control::placeholder { color: var(--muted-text-color); }
        .form-control:focus {
            background: var(--input-bg); color: var(--text-color);
            border-color: var(--main-color); box-shadow: 0 0 8px var(--shadow-color); outline: none;
        }
        .btn-login {
            width: 100%; padding: 1.2rem; font-size: 1.7rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px;
            color: #fff; background: var(--main-color);
            border: none; border-radius: 12px; cursor: pointer; transition: var(--transition);
        }
        .btn-login:hover {
            box-shadow: 0 0 20px var(--shadow-color);
            transform: translateY(-2px); background: var(--main-color); color: #fff;
        }
        .btn-wa {
            width: 100%; padding: 1.2rem; font-size: 1.6rem;
            font-weight: 600; color: #fff; background: #25D366;
            border: none; border-radius: 12px; cursor: pointer;
            transition: var(--transition); text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 0.8rem;
        }
        .btn-wa:hover {
            background: #1da851; color: #fff;
            transform: translateY(-2px); box-shadow: 0 0 20px rgba(37,211,102,0.3);
        }
        .divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 2rem 0; color: var(--muted-text-color); font-size: 1.4rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border-color);
        }
        .alert { font-size: 1.4rem; border-radius: 10px; border-width: 1px; border-style: solid; }
        .alert-success { background-color: var(--success-bg); border-color: rgba(40,167,69,0.4); color: #155724; }
        .alert-danger  { background-color: var(--danger-bg);  border-color: rgba(220,53,69,0.4);  color: #721c24; }
        .kembali-beranda {
            font-size: 1.5rem; font-weight: 500;
            color: var(--muted-text-color); text-align: center;
            display: block; margin-top: 1.5rem;
            transition: color 0.2s; text-decoration: none;
        }
        .kembali-beranda:hover { color: var(--main-color); text-decoration: underline; }
        .invalid-feedback { font-size: 1.3rem; }
        .hint-text { font-size: 1.3rem; color: var(--muted-text-color); text-align: center; margin-top: 0.6rem; }
        @media (max-width: 480px) {
            html { font-size: 58%; }
            .login-card { max-width: 100%; }
            .login-header { padding: 2rem; }
            .login-body { padding: 0 2rem 2rem; }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="center-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-key"></i>
                </div>
                <h3>Lupa Password?</h3>
                <p>Pilih cara untuk reset password Anda</p>
            </div>
            <div class="login-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Form kirim via Email --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email Akun Anda
                        </label>
                        <input
                            type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Masukkan email akun Anda"
                            value="{{ old('email') }}" required autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset via Email
                    </button>
                </form>

                <div class="divider">atau hubungi admin</div>

                {{-- Hubungi Admin via WhatsApp --}}
                <a href="https://wa.me/6285117779885?text=Halo%20Admin%2C%20saya%20lupa%20password%20akun%20saya%20di%20Panti%20Asuhan%20Kasih%20Agape.%20Mohon%20bantuannya%20untuk%20reset%20password.%20Email%20saya%3A%20" class="btn-wa" target="_blank">
                    <i class="fab fa-whatsapp" style="font-size:2rem;"></i>
                    Hubungi Admin via WhatsApp
                </a>
                <p class="hint-text">Sertakan email akun Anda saat menghubungi admin</p>

                <a href="{{ route('login') }}" class="kembali-beranda">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const heartSvgUri = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='purple'><path d='M12 4.248c-3.148-5.402-12-3.825-12 2.944 0 4.661 5.571 9.427 12 15.808 6.43-6.381 12-11.147 12-15.808 0-6.792-8.875-8.306-12-2.944z'/></svg>";
            particlesJS('particles-js', {
                particles: {
                    number: { value: 40, density: { enable: true, value_area: 800 } },
                    color: { value: "#ff0000" },
                    shape: { type: 'image', image: { src: heartSvgUri, width: 100, height: 100 } },
                    opacity: { value: 0.8, random: true },
                    size: { value: 15, random: true, anim: { enable: true, speed: 4, size_min: 10, sync: false } },
                    line_linked: { enable: false },
                    move: { enable: true, speed: 2, direction: 'none', random: true, straight: false, out_mode: 'out', bounce: false }
                },
                interactivity: {
                    events: { onhover: { enable: true, mode: 'bubble' } },
                    modes: { bubble: { distance: 150, size: 25, duration: 2, opacity: 1 } }
                },
                retina_detect: true
            });
        });
    </script>
</body>
</html>
