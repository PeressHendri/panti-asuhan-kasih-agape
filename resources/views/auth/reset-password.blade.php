<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Panti Asuhan Kasih Agape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --main-color: #007bff; --shadow-color: rgba(0,123,255,0.25); }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { font-size: 62.5%; }
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(135deg, rgb(255,255,255) 0%, rgb(87,195,245) 100%);
            min-height: 100vh;
        }
        #particles-js { position:fixed; top:0; left:0; width:100%; height:100%; z-index:1; }
        .center-wrapper {
            min-height: 100vh; display:flex; justify-content:center;
            align-items:center; padding:2rem; position:relative; z-index:2;
        }
        .card-box {
            width:100%; max-width:440px; background:#fff;
            border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.12);
            overflow:hidden; animation: fadeIn 0.7s ease-out;
        }
        @keyframes fadeIn {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .card-header-custom {
            background: linear-gradient(135deg, #28a745, #1a7a32);
            padding:3rem 3rem 2rem; text-align:center; color:#fff;
        }
        .card-header-custom .icon-circle {
            width:70px; height:70px; background:rgba(255,255,255,0.2);
            border-radius:50%; display:flex; align-items:center;
            justify-content:center; margin:0 auto 1.5rem; font-size:2.8rem;
        }
        .card-header-custom h2 { font-size:2.4rem; font-weight:700; margin-bottom:0.5rem; }
        .card-header-custom p  { font-size:1.4rem; opacity:0.85; }
        .card-body-custom { padding:3rem; }
        .form-label { font-size:1.5rem; font-weight:600; margin-bottom:0.8rem; color:#333; }
        .form-control {
            height:50px; font-size:1.6rem;
            border-radius:10px; border:1px solid #ced4da; transition:all 0.3s;
        }
        .form-control:focus {
            border-color: var(--main-color);
            box-shadow: 0 0 8px var(--shadow-color);
        }
        .btn-submit {
            width:100%; padding:1.3rem; font-size:1.7rem;
            font-weight:700; color:#fff; background:#28a745;
            border:none; border-radius:12px; cursor:pointer;
            transition:all 0.3s; text-transform:uppercase; letter-spacing:1px;
        }
        .btn-submit:hover {
            background:#1a7a32; transform:translateY(-2px);
            box-shadow: 0 8px 20px rgba(40,167,69,0.35);
        }
        .alert { font-size:1.4rem; border-radius:10px; }
        .alert-danger { background:#f8d7da; border-color:#f5c6cb; color:#721c24; }
        .back-link {
            display:block; text-align:center; font-size:1.5rem;
            color:#6c757d; margin-top:2rem; text-decoration:none; transition:color 0.2s;
        }
        .back-link:hover { color:var(--main-color); text-decoration:underline; }
        .invalid-feedback { font-size:1.3rem; }
        .input-group .btn-outline-secondary {
            border-radius:10px; border-top-left-radius:0; border-bottom-left-radius:0;
            border:1px solid #ced4da; border-left:none;
        }
        .strength-bar { height:5px; border-radius:3px; transition:all 0.3s; margin-top:6px; }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="center-wrapper">
        <div class="card-box">
            <div class="card-header-custom">
                <div class="icon-circle">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h2>Buat Password Baru</h2>
                <p>Masukkan password baru untuk akun Anda</p>
            </div>
            <div class="card-body-custom">

                @if(session('error'))
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control" value="{{ $email }}" readonly style="background:#f8f9fa; color:#6c757d;">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password Baru
                        </label>
                        <div class="input-group">
                            <input type="password" id="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Minimal 8 karakter" required
                                oninput="checkStrength(this.value)">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleVis('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="strengthBar" class="strength-bar mt-1" style="width:0; background:#dee2e6;"></div>
                        <small id="strengthText" class="text-muted" style="font-size:1.2rem;"></small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-check-lock me-2"></i>Konfirmasi Password
                        </label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" placeholder="Ulangi password baru" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleVis('password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i>Simpan Password Baru
                    </button>
                </form>

                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS('particles-js', {
            particles: {
                number: { value: 30, density: { enable: true, value_area: 800 } },
                color: { value: "#ffffff" }, shape: { type: "circle" },
                opacity: { value: 0.4, random: true }, size: { value: 4, random: true },
                line_linked: { enable: false },
                move: { enable: true, speed: 1.5, direction: "none", random: true, out_mode: "out" }
            },
            interactivity: { events: { onhover: { enable: false } } },
            retina_detect: false
        });

        function toggleVis(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function checkStrength(val) {
            const bar  = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');
            let score  = 0;
            if (val.length >= 8)          score++;
            if (/[A-Z]/.test(val))         score++;
            if (/[0-9]/.test(val))         score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { w: '25%',  color: '#dc3545', label: 'Lemah' },
                { w: '50%',  color: '#fd7e14', label: 'Cukup' },
                { w: '75%',  color: '#ffc107', label: 'Baik' },
                { w: '100%', color: '#28a745', label: 'Kuat ✓' },
            ];
            const idx = Math.max(0, score - 1);
            bar.style.width     = levels[idx].w;
            bar.style.background= levels[idx].color;
            text.textContent    = 'Kekuatan: ' + levels[idx].label;
            text.style.color    = levels[idx].color;
        }
    </script>
</body>
</html>
