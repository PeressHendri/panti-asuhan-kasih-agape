// document.addEventListener("DOMContentLoaded", function () {
//     const header = document.querySelector(".header");
//     const header1 = document.querySelector(".header1");
//     const menuIcon = document.querySelector("#menu-icon");
//     const navbar = document.querySelector(".navbar");
//     const navLinks = document.querySelectorAll(".navbar a");
//     // Hapus logika scroll untuk active nav

//     // Aktifkan menu saat diklik
//     navLinks.forEach((link) => {
//         link.addEventListener("click", function () {
//             navLinks.forEach((l) => l.classList.remove("active"));
//             this.classList.add("active");
//             // Tutup menu mobile jika perlu
//             if (navbar.classList.contains("active")) {
//                 menuIcon.classList.remove("bx-x");
//                 navbar.classList.remove("active");
//                 navbar.style.transition = "left 0.4s ease";
//             }
//         });
//     });

//     // Sticky header and slide down effect with smooth transition
//     window.addEventListener("scroll", () => {
//         if (window.scrollY > 100) {
//             header.classList.add("slidedown");
//             if (header1) header1.classList.add("hidden");
//         } else {
//             header.classList.remove("slidedown");
//             if (header1) header1.classList.remove("hidden");
//         }
//     });

//     // Scrollspy: aktifkan menu navbar sesuai section yang sedang terlihat
//     window.addEventListener("scroll", () => {
//         const sections = document.querySelectorAll("section");
//         let scrollPos = window.scrollY + 120; // offset agar pas dengan header

//         sections.forEach((section) => {
//             if (
//                 scrollPos >= section.offsetTop &&
//                 scrollPos < section.offsetTop + section.offsetHeight
//             ) {
//                 navLinks.forEach((link) => {
//                     link.classList.remove("active");
//                     if (link.getAttribute("href") === "#" + section.id) {
//                         link.classList.add("active");
//                     }
//                 });
//             }
//         });
//     });

//     // Mobile menu toggle with animation
//     menuIcon.onclick = () => {
//         menuIcon.classList.toggle("bx-x");
//         navbar.classList.toggle("active");
//         navbar.style.transition = "left 0.4s ease";
//     };

//     // ScrollReveal animations with enhanced effects
//     ScrollReveal({
//         // reset: true,
//         distance: "100px",
//         duration: 1500,
//         delay: 100,
//         // easing: "cubic-bezier(0.5, 0, 0, 1)",
//     });

//     ScrollReveal().reveal(".home-content, .heading", {
//         origin: "top",
//         interval: 100,
//     });
//     ScrollReveal().reveal(
//         ".home-img, .company-container, .contact form, .background-content, .vision-content, .goals-content",
//         { origin: "bottom", interval: 200 }
//     );
//     ScrollReveal().reveal(".home-content h1, .about-img", {
//         origin: "left",
//         interval: 200,
//     });
//     ScrollReveal().reveal(".home-content p, .text-content", {
//         origin: "right",
//         interval: 200,
//     });

//     // Typed.js for dynamic text with enhanced settings
//     new Typed(".multiple-text", {
//         strings: [
//             "Selamat Datang",
//             "Welcome",
//             "Bienvenue",
//             "ようこそ",
//             "환영",
//             "欢迎",
//         ],
//         typeSpeed: 80,
//         backSpeed: 50,
//         backDelay: 1000,
//         // startDelay: 500,
//         loop: true,
//         showCursor: true,
//         cursorChar: "|",
//         fadeOut: true,
//     });

//     const contactForm = document.getElementById("contact-form");
//     if (contactForm) {
//         contactForm.addEventListener("submit", (e) => {
//             e.preventDefault();
//             const name = document.getElementById("name").value.trim();
//             const message = document.getElementById("message").value.trim();
//             if (name && message) {
//                 sendToWhatsapp(name, message);
//             } else {
//                 alert("Please fill in all fields.");
//             }
//         });
//     }

//     function sendToWhatsapp(name, message) {
//         const number = "+6285159452235";
//         const url =
//             `https://wa.me/${number}?text=` +
//             `Name : ${encodeURIComponent(name)}%0a` +
//             `Message : ${encodeURIComponent(message)}%0a%0a`;

//         console.log("Nama:", name, "Pesan:", message);
//         console.log("URL:", url);
//         window.open(url, "_blank").focus();
//         document.getElementById("contact-form").reset();
//     }
// });

// particlesJS("particles-js", {
//     particles: {
//         number: {
//             value: 50,
//             density: {
//                 enable: true,
//                 value_area: 600,
//             },
//         },
//         color: {
//             value: ["#ff69b4", "#00ced1"], // Warna pink dan teal
//         },
//         shape: {
//             type: "circle",
//         },
//         opacity: {
//             value: 0.8,
//             random: true,
//             anim: {
//                 enable: true,
//                 speed: 1,
//                 opacity_min: 0.1,
//                 sync: false,
//             },
//         },
//         size: {
//             value: 2,
//             random: true,
//         },
//         line_linked: {
//             enable: false,
//         },
//         move: {
//             enable: true,
//             speed: 1,
//             direction: "none",
//             random: true,
//             straight: false,
//             out_mode: "out",
//         },
//     },
//     interactivity: {
//         detect_on: "canvas",
//         events: {
//             onhover: {
//                 enable: false,
//             },
//             onclick: {
//                 enable: false,
//             },
//             resize: true,
//         },
//     },
//     retina_detect: true,
// });
