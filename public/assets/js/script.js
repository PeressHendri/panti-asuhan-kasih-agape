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
