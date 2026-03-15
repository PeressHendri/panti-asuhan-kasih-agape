<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi | PANTI ASUHAN KASIH AGAPE</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
    <div id="particles-js"></div>

    <header class="header">
        <a href="{{ url('/#home') }}" class="logo">
            <span class="logo-top">PANTI ASUHAN</span>
            <span class="logo-bottom">KASIH AGAPE</span>
        </a>
        <i class="fa-solid fa-bars" id="menu-icon"></i>
        <nav class="navbar">
            <a href="{{ url('/#home') }}">Beranda</a>
            <a href="{{ url('/#about') }}">Tentang</a>
            <a href="{{ url('/#background') }}">Latar Belakang</a>
            <a href="{{ url('/#vision') }}">Visi & Misi</a>
            <a href="{{ url('/#company') }}">Galeri</a>
            <a href="{{ url('/#contact') }}">Kontak</a>
            <a href="{{ route('public.donasi') }}" class="active">Donasi</a>
            <a href="{{ route('login') }}" id="login-btn">Login</a>
        </nav>
    </header>

    <section class="donasi" id="donasi" style="padding-top: 12rem;">
        <div class="container" style="max-width: 800px; margin: 0 auto;">
            <h2 class="heading">Form <span>Donasi</span></h2>

            @if(session('success'))
                <div
                    style="background: #28a745; color: white; padding: 1.5rem; border-radius: 0.8rem; margin-bottom: 2rem; text-align: center; font-size: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    style="background: #dc3545; color: white; padding: 1.5rem; border-radius: 0.8rem; margin-bottom: 2rem; font-size: 1.5rem;">
                    <ul style="margin-left: 2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pesan" style="width: 100%;">
                <form action="{{ route('public.donasi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Nama
                        Lengkap / Instansi Anda <span style="color:red">*</span></label>
                    <input type="text" name="nama_donatur" value="{{ old('nama_donatur') }}" required
                        placeholder="Masukkan Nama Anda">

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Alamat
                        Email (Opsional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="user@example.com">

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Nomor
                        Telepon / WhatsApp</label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx">

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Tanggal
                        Donasi <span style="color:red">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal') ?? date('Y-m-d') }}" required>

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Pilih
                        Jenis Donasi <span style="color:red">*</span></label>
                    <select name="jenis_donasi"
                        style="width: 100%; padding: 1.5rem; font-size: 1.6rem; border-radius: 0.8rem; margin-bottom: 2rem; border: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.8); outline: none;"
                        required onchange="toggleDonasiFields(this.value)">
                        <option value="" disabled selected>-- Pilih Jenis Donasi --</option>
                        <option value="uang" {{ old('jenis_donasi') == 'uang' ? 'selected' : '' }}>Donasi Berupa Uang /
                            Finansial</option>
                        <option value="barang" {{ old('jenis_donasi') == 'barang' ? 'selected' : '' }}>Donasi Barang
                            (Titipan / Ekspedisi)</option>
                        <option value="sponsor_anak" {{ old('jenis_donasi') == 'sponsor_anak' ? 'selected' : '' }}>Program
                            Sponsor Pendidikan Anak</option>
                    </select>

                    <div id="field_jumlah" style="display: {{ old('jenis_donasi') == 'uang' ? 'block' : 'none' }};">
                        <label
                            style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Jumlah
                            Nominal Donasi (Rp)</label>
                        <input type="number" name="jumlah" value="{{ old('jumlah') }}" placeholder="Contoh: 1000000">
                    </div>

                    <div id="field_keterangan"
                        style="display: {{ in_array(old('jenis_donasi'), ['uang', 'barang', 'sponsor_anak']) ? 'block' : 'none' }};">
                        <label
                            style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Keterangan
                            / Pesan Anda</label>
                        <textarea name="keterangan" rows="4"
                            placeholder="Sebutkan detail barang (jika barang) atau pesan kasih yang ingin disampaikan.">{{ old('keterangan') }}</textarea>
                    </div>

                    <div
                        style="background: rgba(0, 119, 182, 0.1); padding: 2rem; border-radius: 1rem; margin-bottom: 2rem; border: 1px dashed var(--main-color);">
                        <h3 style="font-size: 1.8rem; color: var(--main-color); margin-bottom: 1rem;"><i
                                class="fa-solid fa-credit-card"></i> Rekening Tujuan Panti</h3>
                        <p style="font-size: 1.5rem; margin-bottom: 0.5rem;"><strong>Bank BCA:</strong> 123-456-7890 a/n
                            Panti Asuhan Kasih Agape</p>
                        <p style="font-size: 1.5rem; margin-bottom: 0.5rem;"><strong>Bank Mandiri:</strong> 098-765-4321
                            a/n Panti Asuhan Kasih Agape</p>
                        <hr style="margin: 1.5rem 0; border: 0; border-top: 1px dashed var(--main-color);">
                        <p style="font-size: 1.3rem; color: #555;">Bagi donatur yang menitipkan barang melalui kurir,
                            Anda dapat melampirkan foto resi di kolom bukti atau via WA Panti.</p>
                    </div>

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Nomor
                        Referensi Transfer / Resi Pengiriman (Opsional)</label>
                    <input type="text" name="nomor_resi" value="{{ old('nomor_resi') }}"
                        placeholder="Nomor referensi bank/resi">

                    <label style="font-size: 1.5rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Upload
                        Struk Bukti Transfer / Resi (Opsional)</label>
                    <input type="file" name="bukti_transfer" accept=".jpg, .jpeg, .png"
                        style="padding: 1.5rem; background: rgba(255,255,255,0.8); width: 100%; border-radius: 0.8rem; border: 1px solid var(--border-color); margin-bottom: 2rem;">

                    <button type="submit" class="btn" style="width: 100%; cursor: pointer;">Kirim Data Donasi <i
                            class="fa-solid fa-paper-plane" style="margin-left: 0.5rem;"></i></button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer Sama dengan welcome.blade.php -->
    <footer class="footer">
        <div class="row">
            <div class="footer-col">
                <h1>KASIH AGAPE</h1>
                <p>Bersama kami ciptakan senyuman dan harapan bagi anak-anak Indonesia yang membutuhkan.</p>
            </div>
            <div class="footer-col">
                <h4>KONTAK</h4>
                <p>0813 3130 7503</p>
                <p>pantiagape@example.com</p>
            </div>
            <div class="footer-col">
                <h4>ALAMAT</h4>
                <p>Jl. Contoh No. 123, Surabaya, Jawa Timur, Indonesia.</p>
            </div>
            <div class="footer-col wa">
                <h4>SOSIAL MEDIA</h4>
                <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Init Particles JS sama seperti di halaman welcome
        particlesJS('particles-js', {
            fps_limit: 30, // limit FPS
            particles: {
                number: { value: 30, density: { enable: true, value_area: 800 } },
                color: { value: "#ffffff" },
                shape: { type: "circle" },
                opacity: { value: 0.3, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.2, width: 1 },
                move: { enable: true, speed: 1.5, direction: "none", random: true, straight: false, out_mode: "out", bounce: false }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: false },
                    onclick: { enable: false }
                }
            },
            retina_detect: false
        });

        // Script Navbar Toggle
        const menuIcon = document.querySelector('#menu-icon');
        const navbar = document.querySelector('.navbar');
        menuIcon.onclick = () => {
            menuIcon.classList.toggle('fa-xmark');
            navbar.classList.toggle('active');
        };

        // UI Logic untuk toggle field Form
        function toggleDonasiFields(val) {
            document.getElementById('field_jumlah').style.display = (val === 'uang') ? 'block' : 'none';
            document.getElementById('field_keterangan').style.display = (val !== '') ? 'block' : 'none';
        }

        // Jalankan saat pertama kali jika old value tersimpan
        window.onload = function () {
            var val = document.querySelector('select[name="jenis_donasi"]').value;
            if (val) toggleDonasiFields(val);
        };
    </script>
</body>

</html>