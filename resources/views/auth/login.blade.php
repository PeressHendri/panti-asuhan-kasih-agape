<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Panti Asuhan Kasih Agape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Default Theme Variables (Blue Gradient) */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 62.5%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            background-image: linear-gradient(135deg,rgb(255, 255, 255) 0%,rgb(87, 195, 245) 100%);
            min-height: 100vh;
        }

        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .center-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--container-bg);
            border-radius: 20px;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
            transition: background 0.3s, border 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-header {
            text-align: center;
            padding: 3rem 3rem 2rem;
            color: var(--text-color);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: #e3f2fd;
            border: 2px solid #bbdefb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--main-color);
            font-size: 3rem;
            transition: var(--transition);
        }

        .login-header h3 {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--heading-color);
            margin-bottom: 0.7rem;
            transition: color 0.3s;
            line-height: 1.2;
        }

        .judul-atas, .judul-bawah { display: block; }
        
        .login-header p {
            font-size: 1.6rem;
            color: var(--muted-text-color);
            transition: color 0.3s;
        }

        .login-body {
            padding: 0 3rem 3rem;
        }

        .form-label {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: var(--text-color);
        }

        .form-control {
            height: 50px;
            font-size: 1.6rem;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-color);
            transition: var(--transition);
        }
        
        .form-control::placeholder { color: var(--muted-text-color); }

        .form-control:focus {
            background: var(--input-bg);
            color: var(--text-color);
            outline: none;
            border-color: var(--main-color);
            box-shadow: 0 0 8px var(--shadow-color);
        }

        .input-group .btn-outline-secondary {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-left: none;
            color: var(--muted-text-color);
            border-radius: 10px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .input-group:focus-within .form-control,
        .input-group:focus-within .btn-outline-secondary {
             border-color: var(--main-color);
             box-shadow: 0 0 8px var(--shadow-color);
        }
        .input-group .form-control:focus { box-shadow: none; }


        .form-check-label { font-size: 1.4rem; }
        .form-check-input:checked {
            background-color: var(--main-color);
            border-color: var(--main-color);
        }

        .btn-login {
            width: 100%;
            padding: 1.2rem;
            font-size: 1.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #fff;
            background: var(--main-color);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-login:hover {
            box-shadow: 0 0 20px var(--shadow-color);
            transform: translateY(-2px);
            background: var(--main-color);
            color: #fff;
        }
        
        .alert {
            font-size: 1.4rem;
            border-radius: 10px;
            border-width: 1px;
            border-style: solid;
        }

        .alert-success {
            background-color: var(--success-bg);
            border-color: rgba(40, 167, 69, 0.4);
            color: #155724;
        }

        .alert-danger {
            background-color: var(--danger-bg);
            border-color: rgba(220, 53, 69, 0.4);
            color: #721c24;
        }
        
        .btn-close { filter: none; }

        .kembali-beranda {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--muted-text-color);
            text-align: center;
            display: inline-block;
            margin-top: 1.2rem;
            transition: color 0.2s;
            text-decoration: none;
        }
        .kembali-beranda:hover {
            color: var(--main-color);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            html { font-size: 58%; }
            .login-card { max-width: 100%; }
            .login-header { padding: 2rem; }
            .login-body { padding: 0 2rem 2rem; }
            .login-header h3 { font-size: 2.6rem; }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>

    <div class="center-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>
                    <span class="judul-atas">PANTI ASUHAN</span>
                    <span class="judul-bawah">KASIH AGAPE</span>
                </h3>
                <p>Silakan masuk ke akun Anda</p>
            </div>
            <div class="login-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" value="{{ old('email', session('last_email')) }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                           <input type="checkbox" class="form-check-input" id="remember" name="remember">
                           <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
                <div class="text-center mt-4">
                     <a href="{{ route('home') }}" class="kembali-beranda">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // --- Particles.js Configuration ---
            // PERUBAHAN UTAMA DI SINI: fill='red'
            const heartSvgUri = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='purple'><path d='M12 4.248c-3.148-5.402-12-3.825-12 2.944 0 4.661 5.571 9.427 12 15.808 6.43-6.381 12-11.147 12-15.808 0-6.792-8.875-8.306-12-2.944z'/></svg>";

            const heartParticlesConfig = {
                particles: {
                    number: { value: 40, density: { enable: true, value_area: 800 } },
                    // Opsi color ini sekarang mungkin tidak berpengaruh, karena warna sudah diatur di SVG
                    color: { value: "#ff0000" }, 
                    shape: {
                        type: 'image',
                        image: { src: heartSvgUri, width: 100, height: 100 }
                    },
                    opacity: { value: 0.8, random: true },
                    size: { value: 15, random: true, anim: { enable: true, speed: 4, size_min: 10, sync: false } },
                    line_linked: { enable: false },
                    move: {
                        enable: true, speed: 2, direction: 'none', random: true,
                        straight: false, out_mode: 'out', bounce: false,
                    }
                },
                interactivity: {
                    events: { onhover: { enable: true, mode: 'bubble' } },
                    modes: { bubble: { distance: 150, size: 25, duration: 2, opacity: 1 } }
                },
                retina_detect: true
            };
            
            // Initialize Particles
            particlesJS('particles-js', heartParticlesConfig);


            // --- Password Toggle Functionality ---
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', () => {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = togglePassword.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // --- Auto-show alert if exists ---
            const alertElement = document.querySelector('.alert');
            if(alertElement) {
                // The bootstrap JS will handle the rest (like dismissing)
                var bsAlert = new bootstrap.Alert(alertElement);
            }
        });
    </script>
</body>
</html>