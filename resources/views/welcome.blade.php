<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PANTI ASUHAN KASIH AGAPE</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ===================================================
           1. Konfigurasi Dasar & Skema Warna
           =================================================== */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap");

        :root {
            --bg-start: #a8e0ff;
            --bg-end: #86c9ef;
            --text-color: #333;
            --heading-color: #013a63;
            --main-color: #0077b6;
            --container-bg: rgba(255, 255, 255, 0.6);
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-color: rgba(0, 119, 182, 0.3);
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-decoration: none;
            border: none;
            outline: none;
            font-family: "Poppins", sans-serif;
        }

        /* Aturan dasar untuk gambar agar lebih robust */
        img {
            display: block;
            max-width: 100%;
            height: auto;
        }

        html {
            font-size: 62.5%;
            overflow-x: hidden;
            scroll-behavior: smooth;
            scroll-padding-top: var(--header-height);
        }

        body {
            background-image: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            color: var(--text-color);
            line-height: 1.6;
            padding-top: var(--header-height);
        }

        /* ===================================================
           2. Latar Belakang (Particles)
           =================================================== */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
        }

        /* ===================================================
           3. Struktur & Konten Utama
           =================================================== */
        section {
            min-height: calc(100vh - var(--header-height));
            padding: clamp(4rem, 8vw, 8rem) 5%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background: var(--container-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 2rem;
            padding: clamp(2rem, 5vw, 3rem);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .heading {
            text-align: center;
            font-size: clamp(3rem, 6vw, 3.8rem);
            margin-bottom: 3rem;
            color: var(--heading-color);
        }

        .heading span {
            color: var(--main-color);
        }

        /* ===================================================
           4. Header & Navigasi
           =================================================== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            padding: 0 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            transition: top 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }

        .header.hide-navbar {
            top: calc(-1 * var(--header-height));
            opacity: 0;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--heading-color);
        }

        .logo span {
            display: block;
            line-height: 1;
        }

        .logo .logo-bottom {
            color: var(--main-color);
        }

        #menu-icon {
            font-size: 3rem;
            color: var(--heading-color);
            cursor: pointer;
            display: block;
        }

        .navbar {
            position: absolute;
            top: var(--header-height);
            left: -100%;
            width: 100%;
            height: calc(100vh - var(--header-height));
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: left 0.4s ease;
        }

        .navbar.active {
            left: 0;
        }

        .navbar a {
            font-size: 2rem;
            color: var(--heading-color);
            margin: 1.5rem 0;
            transition: color 0.3s;
        }

        .navbar a.active,
        .navbar a:hover {
            color: var(--main-color);
        }

        #login-btn {
            margin-top: 2rem;
            padding: 1rem 2.5rem;
            border: 2px solid var(--main-color);
            color: var(--main-color);
            border-radius: 2rem;
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
        }

        #login-btn:hover {
            background: var(--main-color);
            color: #fff;
        }

        /* ===================================================
           5. Bagian Spesifik (Home, About, dll.)
           =================================================== */
        .home {
            text-align: center;
        }

        .home-content h1 {
            font-size: clamp(4rem, 10vw, 5rem);
            font-weight: 700;
            line-height: 1.2;
            color: var(--heading-color);
        }

        .home-content .hcontent {
            color: var(--main-color);
        }

        .home-content h3 {
            font-size: clamp(2rem, 5vw, 2.8rem);
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .btn,
        .location {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 1.2rem 2.8rem;
            border-radius: 4rem;
            font-size: 1.6rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }

        .btn {
            background: var(--main-color);
            color: #fff;
        }

        .btn:hover {
            box-shadow: 0 0 2rem var(--shadow-color);
            transform: scale(1.05);
        }

        .location {
            border: 2px solid var(--main-color);
            color: var(--main-color);
        }

        .location:hover {
            background: var(--main-color);
            color: #fff;
        }

        .about-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        /* Mencegah lompatan saat gambar 'Tentang Kami' dimuat */
        .about-img {
            width: 100%;
            aspect-ratio: 4 / 3;
            background-color: #e0e0e0;
            /* Warna placeholder */
            border-radius: 2rem;
            overflow: hidden;
            border: 2px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .text-content {
            text-align: center;
        }

        .text-content h3 {
            font-size: 2.4rem;
            color: var(--main-color);
            margin-bottom: 1.5rem;
        }

        .text-content p,
        .background-content p,
        .vision-content p {
            font-size: 1.6rem;
            text-align: justify;
        }

        .vision-content {
            text-align: center;
        }

        .vision-content h3 {
            font-size: 2.8rem;
            color: var(--main-color);
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .company-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .company-box {
            position: relative;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            aspect-ratio: 1 / 1;
        }

        /* Memberi hint ke browser untuk optimasi animasi */
        .company-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            will-change: transform;
        }

        .company-box:hover img {
            transform: scale(1.1);
        }

        .company-layer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            text-align: center;
            color: #fff;
        }

        .company-layer h4 {
            font-size: 1.8rem;
        }

        .contact-container {
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .lokasi iframe {
            width: 100%;
            aspect-ratio: 16 / 10;
            height: auto;
            border-radius: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .pesan form input,
        .pesan form textarea {
            width: 100%;
            padding: 1.5rem;
            font-size: 1.6rem;
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0.8rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .pesan form textarea {
            resize: vertical;
        }

        .pesan form button {
            width: 100%;
        }

        .footer {
            background: var(--heading-color);
            padding: 4rem 5%;
            text-align: center;
            color: #f0f0f0;
        }

        .footer .row {
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .footer-col h1,
        .footer-col h4 {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 1rem;
        }

        .footer-col p,
        .footer-col p a {
            font-size: 1.5rem;
            color: #e0e0e0;
        }

        .footer-col p a:hover {
            color: #a8e0ff;
        }

        .footer-col .wa a {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 4rem;
            height: 4rem;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            color: #e0e0e0;
            font-size: 2rem;
            margin: 0 0.5rem;
            transition: color 0.3s, border-color 0.3s;
        }

        .footer-col .wa a:hover {
            color: #fff;
            border-color: #fff;
        }

        body.no-scroll {
            overflow: hidden;
        }

        /* ===================================================
           6. Media Queries untuk Desktop
           =================================================== */
        @media (min-width: 768px) {
            #menu-icon {
                display: none;
            }

            .navbar {
                position: static;
                left: auto;
                width: auto;
                height: auto;
                background: transparent;
                flex-direction: row;
                align-items: center;
            }

            .navbar a {
                font-size: 1.7rem;
                margin: 0 1.5rem;
            }

            #login-btn {
                margin-top: 0;
                margin-left: 1.5rem;
            }

            .about-content,
            .contact-container {
                flex-direction: row;
                align-items: center;
                gap: 4rem;
            }

            .about-img,
            .text-content,
            .lokasi,
            .pesan {
                flex: 1;
            }

            .text-content {
                text-align: left;
            }

            .footer .row {
                flex-direction: row;
                text-align: left;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 2rem;
            }

            .footer-col {
                flex: 1 1 250px;
            }
        }

        @media (min-width: 1024px) {
            section {
                padding: clamp(4rem, 8vw, 8rem) 9%;
            }

            .header {
                padding: 0 9%;
            }

            .footer {
                padding: 4rem 9%;
            }
        }
    </style>
</head>

<body>
    <div id="particles-js"></div>

    <header class="header">
        <a href="#home" class="logo">
            <span class="logo-top">PANTI ASUHAN</span>
            <span class="logo-bottom">KASIH AGAPE</span>
        </a>
        <i class="fa-solid fa-bars" id="menu-icon"></i>
        <nav class="navbar">
            <a href="#home" class="active">Beranda</a>
            <a href="#about">Tentang</a>
            <a href="#background">Latar Belakang</a>
            <a href="#vision">Visi & Misi</a>
            <a href="#company">Galeri</a>
            <a href="#contact">Kontak</a>
            <a href="{{ route('login') }}" id="login-btn">Login</a>
        </nav>
    </header>

    <section class="home" id="home">
        <div class="home-content">
            <h3><span class="multiple-text"></span></h3>
            <h1>PANTI ASUHAN</h1>
            <h1 class="hcontent">KASIH AGAPE</h1>
            <h2>"DIBERKATI UNTUK MENJADI BERKAT"</h2>
            <div class="tombol">
                <a href="https://wa.me/6281331307503" class="btn">Hubungi Kami</a>
                <a href="#contact" class="location"><i class="fa-solid fa-location-dot"></i></a>
            </div>
        </div>
    </section>

    <section class="about" id="about">
        <div class="container">
            <h2 class="heading">Tentang <span>Kami</span></h2>
            <div class="about-content">
                <div class="about-img">
                    <img src="assets/img/natal2022panti.jpeg" alt="Panti Asuhan Kasih Agape" loading="lazy">
                </div>
                <div class="text-content">
                    <h3>Panti Asuhan Kasih Agape</h3>
                    <p>Kasih Agape, nama panti asuhan ini, didirikan pada tahun 2001 oleh Pendeta Mariana Muskita dan
                        Bapak Kunjariono. Nama 'Kasih Agape' dipilih karena melambangkan Kasih Tuhan Yesus yang tanpa
                        pamrih. Harapannya, siapa pun yang terlibat dengan panti ini akan selalu diberkati.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="background" id="background">
        <div class="container">
            <h2 class="heading">Latar Belakang <span>Kami</span></h2>
            <div class="background-content">
                <p>Pada tahun 1996 Tuhan memberi Visi Dan Misi kepada Ibu Mariana Muskitta, sehingga membuka persekutuan
                    doa dirumahnya. Persekutuan ini di beri nama Kristus Pohon Sejahtera melalui persekutuan ini dapat
                    menjangkau beberapa orang terlantar dan anak-anak dari keluarga tidak mampu.</p>
                <p>Kemudian pada tahun 2000 persekutuan doa ini dirubah menjadi Panti Asuhan yang diberi nama “Kasih
                    Agape”. Panti Asuhan ini dapat bertumbuh sebagai respon atas apa yang telah terjadi di pulau Ambon
                    dan pulau-pulau lain di Indonesia yang dilanda kerusuhan dan bencana alam.</p>
            </div>
        </div>
    </section>

    <section class="vision" id="vision">
        <div class="container">
            <h2 class="heading">Visi & Misi <span>Kami</span></h2>
            <div class="vision-content">
                <h3>Visi</h3>
                <p>Mengasuh, Mendidik & Membangun Generasi Muda seutuhnya yang Hidup Takut Akan Tuhan.</p>
                <p>Menjadikan Generasi Muda yang Memiliki Tanggung Jawab dan Memiliki Masa Depan Yang Cerah & Penuh
                    harapan.</p>
                <h3>Misi</h3>
                <p>Mendidik dan memberikan Mereka Pengajaran - Pengajaran Rohani Untuk Hidup Di Dalam Tuhan dan
                    Memberikan Pendidikan Forman dan Informal Kepada Anak-Anak Sebagai Bekal Hidup Agar Menjadi Pribadi
                    Yang Berintegritas dan
                    Tangguh.</p>
                <p>Memberikan mereka Makanan Rohani serta, pembentukan karakter dan etika kepribadian yang Baik untuk
                    kemuliaan Nama Tuhan.</p>
            </div>
        </div>
    </section>

    <section class="company" id="company">
        <div class="container">
            <h2 class="heading">Galeri <span>Kami</span></h2>
            <div class="company-container">
                <div class="company-box">
                    <img src="assets/img/cewe-pcm.jpg" alt="Galeri 1" loading="lazy">
                    <div class="company-layer">
                        <h4>Foto bersama Cewe- Cewe pada Acara Pakuwon</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/Cowo-pcm.jpg" alt="Galeri 2" loading="lazy">
                    <div class="company-layer">
                        <h4>Foto bersama Cowo - Cowo pada Acara Pakuwon</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/berbagikasih.jpg" alt="Galeri 3" loading="lazy">
                    <div class="company-layer">
                        <h4>Berbagi Kasih bersama SMILEY</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/Bakti-Sosial1.jpg" alt="Galeri 4" loading="lazy">
                    <div class="company-layer">
                        <h4>Bakti Sosial dari PT. ARTHA PERMAI KENCANA</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/anak-anak-panti-asuhan-kasih-agape-saat-berfoto-bersama-di-depan-artotel.jpg"
                        alt="Galeri 5" loading="lazy">
                    <div class="company-layer">
                        <h4>Foto bersama di depan Artotel</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/manusiasalingberbagi.jpg" alt="Galeri 6" loading="lazy">
                    <div class="company-layer">
                        <h4>Manusia Saling Berbagi</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/HDCI.jpg" alt="Galeri 7" loading="lazy">
                    <div class="company-layer">
                        <h4>Natal Bersama HDCI</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/cewe2.jpg" alt="Galeri 8" loading="lazy">
                    <div class="company-layer">
                        <h4>Foto bersama depan Panti</h4>
                    </div>
                </div>
                <div class="company-box">
                    <img src="assets/img/natal2022panti.jpeg" alt="Galeri 9" loading="lazy">
                    <div class="company-layer">
                        <h4>Natal 2022 Bersama</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact" id="contact">
        <div class="container">
            <h2 class="heading">Kontak <span>Kami</span></h2>
            <div class="contact-container">
                <div class="lokasi">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3617.4960708409326!2d112.72114827499992!3d-7.285981492721349!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fbf305035865%3A0x6dbc0df2c71cbcff!2sPanti%20Asuhan%20Kasih%20Agape!5e1!3m2!1sid!2sid!4v1753512268779!5m2!1sid!2sid"
                        style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="pesan">
                    <form id="contact-form">
                        <input type="text" id="name" placeholder="Nama Anda" required>
                        <textarea id="message" cols="30" rows="8" placeholder="Pesan Anda" required></textarea>
                        <button type="submit" class="btn">Kirim Pesan via WhatsApp</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="row">
            <div class="footer-col">
                <h1>Panti Asuhan Kasih Agape</h1>
                <p>DIBERKATI UNTUK MENJADI BERKAT</p>
            </div>
            <div class="footer-col">
                <h4>Lokasi</h4>
                <p><a href="https://www.google.com/maps/place/Panti+Asuhan+Kasih+Agape/@-7.2859815,112.7211483,831m/data=!3m2!1e3!4b1!4m6!3m5!1s0x2dd7fbf305035865:0x6dbc0df2c71cbcff!8m2!3d-7.2859815!4d112.7237232!16s%2Fg%2F11bxfmh0mt?entry=ttu&g_ep=EgoyMDI1MDcyMy4wIKXMDSoASAFQAw%3D%3D"
                        target="_blank">Jl. Pakis Gunung I / 133 B,
                        Surabaya, Jawa Timur</a></p>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <div class="wa">
                    <a href="https://wa.me/6281331307503" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="https://wa.me/6281231663336" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.12.0/tsparticles.bundle.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // ===================================================
            // 1. Konfigurasi tsParticles (OPTIMIZED FOR MOBILE)
            // ===================================================
            const isMobile = window.innerWidth < 768;
            const doveSvgUri = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%23ffffff' d='M442.7,185.3H416V138.7c0-26.5-12.8-50.4-33.1-65.2c-20.3-14.8-46.4-19.1-70-11.3L159.3,131.5c-2.6,0.9-5.1,1.9-7.5,3.2 c-20.9,11.3-33.8,33.4-33.8,57.7v157.9c0,23.6,12.3,45.2,32.3,57l0.1,0.1c19.9,11.7,44.2,12,64.4,0.6l176.4-98.6 c24-13.4,39.8-38.6,39.8-66.9V185.3z M416,298.7c0,13.4-7.6,25.6-19.1,31.9l-176.4,98.6c-9.6,5.4-20.9,5.2-30.4-0.3 c-9.5-5.5-15.4-15.9-15.4-27.1V192.3c0-11.5,6.1-22.1,16.2-27.8c10.1-5.7,22.1-6,32.5-0.9l153.6,85.6 c2.6,1.4,5,3.1,7.3,4.9L416,275.4V298.7z'/%3E%3C/svg%3E";
            const cloudSvgUri = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cpath d='M41.5,16.5A14,14,0,0,0,29.28,20.6,10.5,10.5,0,1,0,16.5,41.5H41.5a14,14,0,0,0,0-28Z' fill='%23ffffff'/%3E%3C/svg%3E";

            tsParticles.load('particles-js', {
                particles: {
                    number: { value: isMobile ? 15 : 30, density: { enable: true, value_area: 800 } },
                    color: { value: "#ffffff" },
                    shape: {
                        type: "image",
                        image: [
                            { src: doveSvgUri, width: 100, height: 100 },
                            { src: cloudSvgUri, width: 120, height: 80 }
                        ]
                    },
                    opacity: { value: 0.5, random: true },
                    size: {
                        value: { min: 15, max: 30 },
                        random: true,
                        anim: { enable: true, speed: 2, size_min: 10, sync: false }
                    },
                    line_linked: { enable: false },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: "top-right",
                        random: true,
                        straight: false,
                        out_mode: "out",
                        bounce: false
                    }
                },
                interactivity: {
                    events: { onhover: { enable: !isMobile, mode: "repulse" } }
                },
                detectRetina: true
            });
        });
        // ===================================================
        // 2. Logika Navigasi (Menu & Scroll)
        // ===================================================
        const header = document.querySelector('.header');
        const menuIcon = document.querySelector('#menu-icon');
        const navbar = document.querySelector('.navbar');
        const navLinks = document.querySelectorAll('.navbar a');

        menuIcon.onclick = () => {
            menuIcon.classList.toggle('fa-xmark');
            navbar.classList.toggle('active');
        };

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navbar.classList.contains('active')) {
                    menuIcon.classList.remove('fa-xmark');
                    navbar.classList.remove('active');
                }
            });
        });

        let lastScrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            if (lastScrollY < window.scrollY && window.scrollY > 100) {
                header.classList.add('hide-navbar');
            } else {
                header.classList.remove('hide-navbar');
            }
            lastScrollY = window.scrollY;
        });

        const sections = document.querySelectorAll('section[id]');
        function scrollActive() {
            const scrollY = window.pageYOffset;
            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 150;
                const sectionId = current.getAttribute('id');
                const link = document.querySelector('.navbar a[href*=' + sectionId + ']');
                if (link) {
                    if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                        document.querySelector('.navbar a.active')?.classList.remove('active');
                        link.classList.add('active');
                    }
                }
            });
        }
        window.addEventListener('scroll', scrollActive);
        scrollActive();

        // ===================================================
        // 3. Animasi Elemen (ScrollReveal & Typed.js)
        // ===================================================
        new Typed(".multiple-text", {
            strings: ["Selamat Datang", "Welcome", "ようこそ", "환영", "欢迎", "DIBERKATI UNTUK MENJADI BERKAT"],
            typeSpeed: 70, backSpeed: 40, backDelay: 2000, loop: true
        });

        const sr = ScrollReveal({
            origin: 'top',
            distance: '50px',
            duration: 1500,
            delay: 200,
            easing: 'ease-in-out'
        });

        // ===================================================
        // 4. Formulir Kontak WhatsApp
        // ===================================================
        const contactForm = document.getElementById("contact-form");
        if (contactForm) {
            contactForm.addEventListener("submit", (e) => {
                e.preventDefault();
                const name = document.getElementById("name").value.trim();
                const message = document.getElementById("message").value.trim();
                if (name && message) {
                    const phoneNumber = "6281331307503";
                    const url = `https://wa.me/${phoneNumber}?text=` +
                        `Nama: ${encodeURIComponent(name)}%0a` +
                        `Pesan: ${encodeURIComponent(message)}`;
                    window.open(url, "_blank").focus();
                    contactForm.reset();
                } else {
                    alert("Mohon isi nama dan pesan Anda.");
                }
            });
        }

    </script>
</body>

</html>