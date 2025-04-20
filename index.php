<?php
// index.php
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduConnect: Inovasi Digital untuk Pemerataan Pendidikan di Daerah 3T</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#10B981',
                        accent: '#F59E0B',
                        dark: '#1F2937',
                        light: '#F3F4F6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 6s infinite',
                        'wave': 'wave 1.5s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        wave: {
                            '0%': { transform: 'rotate(0deg)' },
                            '10%': { transform: 'rotate(14deg)' },
                            '20%': { transform: 'rotate(-8deg)' },
                            '30%': { transform: 'rotate(14deg)' },
                            '40%': { transform: 'rotate(-4deg)' },
                            '50%': { transform: 'rotate(10deg)' },
                            '60%': { transform: 'rotate(0deg)' },
                            '100%': { transform: 'rotate(0deg)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --secondary: #10B981;
            --secondary-dark: #0D9F6E;
            --accent: #F59E0B;
            --accent-dark: #D97706;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            overflow-x: hidden;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        .chat-bubble {
            animation: float 6s ease-in-out infinite;
        }
        
        #chat-box {
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) #F3F4F6;
        }
        
        #chat-box::-webkit-scrollbar {
            width: 6px;
        }
        
        #chat-box::-webkit-scrollbar-track {
            background: #F3F4F6;
        }
        
        #chat-box::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 3px;
        }
        
        .user-message {
            background-color: #E0E7FF;
            border-radius: 1rem 1rem 0 1rem;
            animation: fadeInUp 0.3s ease-out;
        }
        
        .ai-message {
            background-color: #D1FAE5;
            border-radius: 1rem 1rem 1rem 0;
            animation: fadeInUp 0.3s ease-out 0.1s backwards;
        }
        
        .testimonial-card {
            min-width: 100%;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }
        
        .testimonial-active {
            opacity: 1;
            transform: translateX(0);
        }
        
        .testimonial-next {
            opacity: 0.5;
            transform: translateX(100%);
        }
        
        .testimonial-prev {
            opacity: 0.5;
            transform: translateX(-100%);
        }
        
        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        @keyframes gradient-wave {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-animate {
            background-size: 200% 200%;
            animation: gradient-wave 8s ease infinite;
        }
        
        .blob {
            position: absolute;
            filter: blur(40px);
            opacity: 0.7;
            z-index: -1;
            border-radius: 50%;
        }
        
        .mobile-nav {
            transform: translateY(-100%);
            transition: transform 0.3s ease-out;
        }
        
        .mobile-nav.open {
            transform: translateY(0);
        }
        
        .hamburger span {
            transition: all 0.3s ease;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        
        @media (max-width: 768px) {
            .hero-gradient {
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            
            .mobile-flex-col {
                flex-direction: column;
            }
            
            .mobile-text-center {
                text-align: center;
            }
            
            .mobile-mb-4 {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 relative overflow-x-hidden">
    <!-- Animated Background Blobs -->
    <div class="blob bg-purple-300 w-64 h-64 top-0 left-0 animate-float"></div>
    <div class="blob bg-emerald-300 w-96 h-96 bottom-0 right-0 animate-float animation-delay-2000"></div>
    <div class="blob bg-amber-200 w-80 h-80 top-1/3 right-1/4 animate-float animation-delay-4000"></div>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 backdrop-blur-sm bg-opacity-80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2 transform hover:rotate-12 transition-transform"></i>
                        <span class="text-xl font-bold text-dark hover:text-primary transition-colors">EduConnect</span>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Fitur</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Cara Kerja</a>
                    <a href="#bootcamp" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Bootcamp</a>
                    <a href="#ai-chat" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">AI Assistant</a>
                    <a href="#contact" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Kontak</a>
                </div>
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" class="hamburger p-2 rounded-md text-gray-700 hover:text-primary focus:outline-none">
                        <span class="block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                        <span class="block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                        <span class="block w-6 h-0.5 bg-gray-700"></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="mobile-nav md:hidden bg-white shadow-lg absolute w-full">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#features" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-100 rounded-md transition">Fitur</a>
                <a href="#how-it-works" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-100 rounded-md transition">Cara Kerja</a>
                <a href="#bootcamp" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-100 rounded-md transition">Bootcamp</a>
                <a href="#ai-chat" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-100 rounded-md transition">AI Assistant</a>
                <a href="#contact" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-100 rounded-md transition">Kontak</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="animate-fadeInLeft">
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6 animate__animated animate__fadeIn">Pendidikan Berkualitas untuk Semua</h1>
                    <p class="text-xl mb-8 animate__animated animate__fadeIn animate__delay-1s">EduConnect menghubungkan siswa di daerah 3T dengan mentor profesional dan materi pembelajaran berkualitas melalui platform digital inovatif.</p>
                    <div class="flex flex-col sm:flex-row gap-4 animate__animated animate__fadeIn animate__delay-2s">
                        <a href="#signup" class="bg-white text-primary font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-gray-100 hover:scale-105 transition duration-300 text-center transform hover:-translate-y-1">Daftar Sekarang</a>
                        <a href="#how-it-works" class="border-2 border-white text-white font-semibold px-6 py-3 rounded-lg hover:bg-white hover:text-primary hover:scale-105 transition duration-300 text-center transform hover:-translate-y-1">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
                <div class="relative animate__animated animate__fadeInRight">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" alt="Students learning together" class="rounded-xl shadow-2xl border-4 border-white transform hover:rotate-1 transition duration-500">
                    <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-lg shadow-lg hidden md:block animate-bounce">
                        <div class="flex items-center">
                            <div class="bg-primary rounded-full p-2 mr-3">
                                <i class="fas fa-chalkboard-teacher text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Mentor Tersedia</p>
                                <p class="font-bold text-dark">500+ Profesional</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-6 -right-6 bg-white p-4 rounded-lg shadow-lg hidden md:block animate-bounce animate-delay-1000">
                        <div class="flex items-center">
                            <div class="bg-secondary rounded-full p-2 mr-3">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Siswa Terdaftar</p>
                                <p class="font-bold text-dark">10,000+</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-50 to-transparent"></div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-primary mb-2 countup" data-target="50">0</p>
                    <p class="text-gray-600">Daerah Terjangkau</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-secondary mb-2 countup" data-target="500">0</p>
                    <p class="text-gray-600">Mentor Profesional</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-accent mb-2 countup" data-target="10000">0</p>
                    <p class="text-gray-600">Siswa Terdaftar</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-primary mb-2">24/7</p>
                    <p class="text-gray-600">Dukungan AI</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Apa yang Membuat EduConnect Berbeda?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Platform pembelajaran inovatif yang dirancang khusus untuk menjawab tantangan pendidikan di daerah 3T</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-comments text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Mentoring Interaktif</h3>
                    <p class="text-gray-600 text-center">Siswa bisa berinteraksi langsung dengan mentor melalui sesi live chat dan video call untuk tanya jawab dan diskusi.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                    <div class="bg-secondary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-users text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Belajar Kolaboratif</h3>
                    <p class="text-gray-600 text-center">Ruang diskusi virtual memungkinkan siswa dari berbagai daerah saling berbagi ide dan belajar bersama.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                    <div class="bg-accent bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-robot text-accent text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Pembelajaran Adaptif</h3>
                    <p class="text-gray-600 text-center">Sistem AI kami menyesuaikan materi belajar dengan minat dan kemampuan masing-masing siswa.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-cloud-download-alt text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Akses Offline</h3>
                    <p class="text-gray-600 text-center">Konten pembelajaran bisa diunduh dan diakses tanpa koneksi internet stabil, cocok untuk daerah 3T.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Bagaimana EduConnect Bekerja?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Hanya dengan 3 langkah sederhana, siswa bisa mulai belajar dengan mentor profesional</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center transform hover:scale-105 transition duration-300">
                    <div class="bg-primary bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 hover:rotate-12 transition-transform">
                        <span class="text-primary text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Daftar Gratis</h3>
                    <p class="text-gray-600">Buat akun siswa dan lengkapi profil untuk mendapatkan rekomendasi pembelajaran yang sesuai.</p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center transform hover:scale-105 transition duration-300">
                    <div class="bg-secondary bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 hover:rotate-12 transition-transform">
                        <span class="text-secondary text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Pilih Mentor atau Materi</h3>
                    <p class="text-gray-600">Temukan mentor profesional atau pilih materi pembelajaran dari berbagai bidang yang tersedia.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center transform hover:scale-105 transition duration-300">
                    <div class="bg-accent bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 hover:rotate-12 transition-transform">
                        <span class="text-accent text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Mulai Belajar</h3>
                    <p class="text-gray-600">Ikuti sesi belajar interaktif, diskusi kelompok, atau akses materi pembelajaran kapan saja.</p>
                </div>
            </div>
            
            <div class="mt-16 text-center">
                <a href="#signup" class="bg-primary text-white font-semibold px-8 py-3 rounded-lg shadow-lg hover:bg-primary-dark hover:scale-105 transition duration-300 inline-block transform hover:-translate-y-1">Mulai Sekarang</a>
            </div>
        </div>
    </section>

    <!-- AI Chat Section -->
    <section id="ai-chat" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="relative">
                    <div class="bg-white p-6 rounded-xl shadow-lg transform hover:-translate-y-2 transition duration-500">
                        <div class="flex items-center mb-4">
                            <div class="bg-primary rounded-full p-2 mr-3 animate-waving-hand">
                                <i class="fas fa-robot text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-dark">AI Assistant EduConnect</h3>
                        </div>
                        
                        <div id="chat-box" class="mb-4 p-4 bg-gray-50 rounded-lg h-64 overflow-y-auto">
                            <div class="ai-message p-3 mb-3 max-w-xs">
                                <p class="font-medium">🤖 AI Assistant:</p>
                                <p>Hai! Saya AI Assistant EduConnect. Ada yang bisa saya bantu terkait pembelajaran Anda hari ini?</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <textarea id="user-message" placeholder="Ketik pertanyaan Anda..." rows="2" 
                                class="flex-grow px-4 py-2 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            <button onclick="sendMessage()" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition duration-300 h-full hover:scale-105">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <div class="mt-2 text-sm text-gray-500">
                            <p>Contoh pertanyaan: "Apa itu fotosintesis?" atau "Bantu saya dengan soal matematika ini" dan jika anda kebingungan dengan bahasa inggris silahkan ketik "menggunakan bahasa indonesia"</p>
                        </div>
                    </div>
                    
                    <div class="absolute -bottom-6 -right-6 bg-white p-4 rounded-lg shadow-lg hidden md:block animate-pulse-slow">
                        <div class="flex items-center">
                            <div class="bg-secondary rounded-full p-2 mr-3">
                                <i class="fas fa-lightbulb text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fitur AI</p>
                                <p class="font-bold text-dark">24/7 Tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-3xl font-bold text-dark mb-6">Dukungan AI 24/7 untuk Pembelajaran Tanpa Batas</h2>
                    <p class="text-gray-600 mb-6">Dengan teknologi Asistent AI terbaru, EduConnect memberikan pengalaman bimbingan belajar yang lebih interaktif dan responsif.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <p class="text-gray-700">Jawaban instan untuk pertanyaan seputar materi pembelajaran</p>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <p class="text-gray-700">Penjelasan konsep sulit dengan bahasa yang mudah dimengerti</p>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <p class="text-gray-700">Rekomendasi materi belajar berdasarkan kebutuhan individu</p>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <p class="text-gray-700">Dukungan multi-bahasa untuk daerah dengan bahasa ibu berbeda</p>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <p class="text-gray-700">Integrasi dengan materi pembelajaran di platform EduConnect</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootcamp Section -->
    <section id="bootcamp" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Bootcamp Profesional</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Tingkatkan skill dengan program bootcamp intensif bersama mentor ahli di bidangnya</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Bootcamp 1 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                    <div class="bg-primary h-2 w-full"></div>
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                                <i class="fas fa-code text-primary text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-dark">Bootcamp Pemrograman</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Pelajari dasar-dasar pemrograman hingga siap kerja dalam 12 minggu intensif.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-dark mb-2">Rp 1.500.000</p>
                            <p class="text-gray-500">atau 3x Rp 500.000</p>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">24 sesi mentoring</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Proyek akhir</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Sertifikat kelulusan</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Dukungan karir</span>
                            </li>
                        </ul>
                        
                        <a href="#signup" class="block w-full bg-primary text-white text-center font-semibold py-3 rounded-lg hover:bg-primary-dark transition duration-300 hover:scale-105">Daftar Sekarang</a>
                    </div>
                </div>
                
                <!-- Bootcamp 2 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2 scale-105 z-10">
                    <div class="relative">
                        <div class="bg-secondary h-2 w-full"></div>
                        <div class="absolute top-0 right-0 bg-secondary text-white text-xs font-bold px-2 py-1 rounded-bl-lg">
                            POPULER
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-secondary bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                                <i class="fas fa-chart-line text-secondary text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-dark">Bootcamp Digital Marketing</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Kuasi skill digital marketing dari dasar hingga strategi lanjutan dalam 8 minggu.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-dark mb-2">Rp 1.200.000</p>
                            <p class="text-gray-500">atau 3x Rp 400.000</p>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">16 sesi mentoring</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Studi kasus nyata</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Sertifikat kelulusan</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Akses komunitas</span>
                            </li>
                        </ul>
                        
                        <a href="#signup" class="block w-full bg-secondary text-white text-center font-semibold py-3 rounded-lg hover:bg-secondary-dark transition duration-300 hover:scale-105">Daftar Sekarang</a>
                    </div>
                </div>
                
                <!-- Bootcamp 3 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                    <div class="bg-accent h-2 w-full"></div>
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-accent bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                                <i class="fas fa-paint-brush text-accent text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-dark">Bootcamp Desain Grafis</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Pelajari prinsip desain dan tools populer untuk menjadi desainer profesional.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-dark mb-2">Rp 1.000.000</p>
                            <p class="text-gray-500">atau 2x Rp 500.000</p>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">12 sesi mentoring</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Portofolio desain</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Sertifikat kelulusan</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span class="text-gray-700">Review karya</span>
                            </li>
                        </ul>
                        
                        <a href="#signup" class="block w-full bg-accent text-white text-center font-semibold py-3 rounded-lg hover:bg-accent-dark transition duration-300 hover:scale-105">Daftar Sekarang</a>
                    </div>
                </div>
            </div>
            
            <div class="mt-12 text-center">
                <a href="#bootcamp" class="text-primary font-semibold hover:underline transform hover:scale-105 inline-block">Lihat semua program bootcamp →</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Apa Kata Mereka Tentang EduConnect?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Testimoni dari siswa dan mentor yang telah merasakan manfaat platform kami</p>
            </div>
            
            <div class="relative overflow-hidden">
                <div id="testimonial-slider" class="flex transition-transform duration-500 ease-in-out">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card flex-shrink-0 w-full px-4">
                        <div class="bg-white p-8 rounded-xl shadow-md h-full">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 mr-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">"Berkat EduConnect, saya bisa belajar langsung dengan mentor yang berpengalaman meskipun tinggal di daerah terpencil. Materinya sangat mudah dipahami dan fitur AI-nya sangat membantu ketika mentor tidak tersedia!"</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Student" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Siti Rahayu</p>
                                    <p class="text-gray-500 text-sm">Siswa, Papua</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial-card flex-shrink-0 w-full px-4">
                        <div class="bg-white p-8 rounded-xl shadow-md h-full">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 mr-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">"Sebagai mentor, saya sangat terkesan dengan semangat belajar siswa-siswa di daerah 3T. EduConnect memberikan platform yang tepat untuk berbagi ilmu. Fitur AI-nya juga membantu saya dalam menjawab pertanyaan dasar sehingga bisa fokus pada materi yang lebih kompleks."</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Mentor" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Budi Santoso</p>
                                    <p class="text-gray-500 text-sm">Mentor, Jakarta</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial-card flex-shrink-0 w-full px-4">
                        <div class="bg-white p-8 rounded-xl shadow-md h-full">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 mr-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">"Bootcamp digital marketing di EduConnect sangat membantu saya memulai bisnis online. Sekarang saya bisa menjual produk ke seluruh Indonesia! AI Assistant-nya juga sangat membantu ketika saya belajar di malam hari dan tidak bisa bertanya ke mentor."</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Student" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Dewi Anggraeni</p>
                                    <p class="text-gray-500 text-sm">Siswa, NTT</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 4 -->
                    <div class="testimonial-card flex-shrink-0 w-full px-4">
                        <div class="bg-white p-8 rounded-xl shadow-md h-full">
                            <div class="flex items-center mb-4">
                                <div class="text-yellow-400 mr-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">"Sebagai kepala sekolah di daerah terpencil, EduConnect telah menjadi solusi bagi kami yang kekurangan guru. Anak-anak bisa belajar dengan mentor berkualitas dan AI Assistant yang selalu siap membantu. Prestasi akademik siswa kami meningkat signifikan!"</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Principal" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Drs. Ahmad Yani</p>
                                    <p class="text-gray-500 text-sm">Kepala Sekolah, Maluku</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center mt-8 space-x-2">
                    <button id="testimonial-prev" class="p-2 rounded-full bg-white shadow hover:bg-gray-100 transition">
                        <i class="fas fa-chevron-left text-primary"></i>
                    </button>
                    <div id="testimonial-dots" class="flex items-center space-x-2">
                        <!-- Dots will be added by JavaScript -->
                    </div>
                    <button id="testimonial-next" class="p-2 rounded-full bg-white shadow hover:bg-gray-100 transition">
                        <i class="fas fa-chevron-right text-primary"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="signup" class="py-20 bg-primary text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-primary to-secondary opacity-90"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl font-bold mb-6 animate__animated animate__pulse animate__infinite">Siap Memulai Perjalanan Belajarmu?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Bergabunglah dengan ribuan siswa lainnya yang telah merasakan manfaat EduConnect untuk pemerataan pendidikan di Indonesia.</p>
            
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-500">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-dark mb-4">Daftar Gratis</h3>
                    <form class="space-y-4">
                        <input type="text" placeholder="Nama Lengkap" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-dark">
                        <input type="email" placeholder="Email" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-dark">
                        <input type="password" placeholder="Password" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-dark">
                        <select class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-dark">
                            <option value="">Pilih peran</option>
                            <option value="student">Siswa</option>
                            <option value="mentor">Mentor</option>
                        </select>
                        <button type="submit" class="w-full bg-primary text-white font-semibold py-3 rounded-lg hover:bg-primary-dark transition duration-300 transform hover:-translate-y-1">Daftar Sekarang</button>
                    </form>
                    <p class="text-gray-600 mt-4 text-sm">Sudah punya akun? <a href="#login" class="text-primary font-semibold hover:underline">Masuk</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold text-dark mb-6">Hubungi Kami</h2>
                    <p class="text-gray-600 mb-8">Punya pertanyaan atau masukan? Tim EduConnect siap membantu Anda.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-dark mb-1">Alamat</h4>
                                <p class="text-gray-600">Jl. Pendidikan No. 123, Jakarta Pusat, Indonesia</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-dark mb-1">Email</h4>
                                <p class="text-gray-600">hello@educonnect.id</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-phone-alt text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-dark mb-1">Telepon</h4>
                                <p class="text-gray-600">+62 21 1234 5678</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex space-x-4">
                        <a href="#" class="bg-gray-100 p-3 rounded-full text-gray-700 hover:bg-primary hover:text-white transition duration-300 transform hover:-translate-y-1">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-100 p-3 rounded-full text-gray-700 hover:bg-primary hover:text-white transition duration-300 transform hover:-translate-y-1">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-gray-100 p-3 rounded-full text-gray-700 hover:bg-primary hover:text-white transition duration-300 transform hover:-translate-y-1">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-gray-100 p-3 rounded-full text-gray-700 hover:bg-primary hover:text-white transition duration-300 transform hover:-translate-y-1">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-3xl font-bold text-dark mb-6">Kirim Pesan</h2>
                    <form class="space-y-4">
                        <div>
                            <input type="text" placeholder="Nama Anda" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <input type="email" placeholder="Email Anda" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <input type="text" placeholder="Subjek" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <textarea rows="4" placeholder="Pesan Anda" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="bg-primary text-white font-semibold px-6 py-2 rounded-lg hover:bg-primary-dark transition duration-300 transform hover:-translate-y-1">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </div>
                    <p class="text-gray-400">Platform pembelajaran inovatif untuk pemerataan pendidikan di daerah 3T di Indonesia.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition duration-300">Fitur</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition duration-300">Cara Kerja</a></li>
                        <li><a href="#bootcamp" class="text-gray-400 hover:text-white transition duration-300">Bootcamp</a></li>
                        <li><a href="#ai-chat" class="text-gray-400 hover:text-white transition duration-300">AI Assistant</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kebijakan</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Kebijakan Pengembalian</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Berlangganan</h3>
                    <p class="text-gray-400 mb-4">Dapatkan update terbaru tentang program dan fitur baru kami.</p>
                    <form class="flex">
                        <input type="email" placeholder="Email Anda" class="px-4 py-2 rounded-l-lg border border-gray-600 bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent w-full">
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition duration-300">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">© 2025 EduConnect. All rights reserved. Designed by Arya Wardhana</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition duration-300 transform hover:-translate-y-1">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition duration-300 transform hover:-translate-y-1">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition duration-300 transform hover:-translate-y-1">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition duration-300 transform hover:-translate-y-1">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Action Buttons -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col space-y-3">
        <button id="chat-button" class="bg-primary text-white p-4 rounded-full shadow-lg hover:bg-primary-dark transition duration-300 transform hover:scale-110 group relative">
            <i class="fas fa-comment-dots text-xl"></i>
            <span class="absolute right-full top-1/2 transform -translate-y-1/2 mr-2 bg-primary text-white text-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Butuh Bantuan?
            </span>
        </button>
        
        <button id="scroll-top" class="bg-secondary text-white p-4 rounded-full shadow-lg hover:bg-secondary-dark transition duration-300 transform hover:scale-110 group relative">
            <i class="fas fa-arrow-up text-xl"></i>
            <span class="absolute right-full top-1/2 transform -translate-y-1/2 mr-2 bg-secondary text-white text-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Ke Atas
            </span>
        </button>
    </div>

    <!-- Chat Popup -->
    <div id="chat-popup" class="fixed bottom-24 right-6 w-80 bg-white rounded-xl shadow-xl z-50 hidden transform transition-all duration-300 origin-bottom-right">
        <div class="bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
            <h3 class="font-semibold">AI Assistant EduConnect</h3>
            <button id="close-chat" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chat-box-popup" class="p-4 h-64 overflow-y-auto">
            <div class="ai-message p-3 mb-3 max-w-xs">
                <p class="font-medium">🤖 AI Assistant:</p>
                <p>Hai! Ada yang bisa saya bantu?</p>
            </div>
        </div>
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center">
                <textarea id="user-message-popup" placeholder="Ketik pesan..." rows="2" class="flex-grow px-4 py-2 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                <button onclick="sendMessagePopup()" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition duration-300 h-full hover:scale-105">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-xl text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-primary border-solid mx-auto mb-4"></div>
            <h3 class="text-xl font-bold text-dark">Memuat EduConnect</h3>
            <p class="text-gray-600 mt-2">Harap tunggu sebentar...</p>
        </div>
    </div>

    <script>
        // Loading overlay
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loading-overlay').classList.add('hidden');
            }, 1500);
        });

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('open');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuButton.classList.remove('active');
                mobileMenu.classList.remove('open');
            });
        });

        // Chat popup toggle
        const chatButton = document.getElementById('chat-button');
        const chatPopup = document.getElementById('chat-popup');
        const closeChat = document.getElementById('close-chat');
        
        chatButton.addEventListener('click', () => {
            chatPopup.classList.toggle('hidden');
            chatPopup.classList.toggle('animate__animated', 'animate__fadeInUp');
        });
        
        closeChat.addEventListener('click', () => {
            chatPopup.classList.add('hidden');
        });

        // Scroll to top button
        const scrollTopButton = document.getElementById('scroll-top');
        
        scrollTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopButton.classList.remove('opacity-0', 'invisible');
                scrollTopButton.classList.add('opacity-100');
            } else {
                scrollTopButton.classList.add('opacity-0', 'invisible');
                scrollTopButton.classList.remove('opacity-100');
            }
        });

        // Enhanced chat functionality
        function appendMessage(message, sender, chatBoxId = 'chat-box') {
            const chatBox = document.getElementById(chatBoxId);
            const div = document.createElement('div');
            div.classList.add(sender === 'user' ? 'user-message' : 'ai-message');
            div.classList.add('p-3', 'mb-3', 'max-w-xs');
            
            if (sender === 'user') {
                div.classList.add('ml-auto');
                div.innerHTML = `<p class="font-medium">👤 Anda:</p><p>${message}</p>`;
            } else {
                div.classList.add('mr-auto');
                div.innerHTML = `<p class="font-medium">🤖 AI Assistant:</p><p>${message}</p>`;
            }
            
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function sendMessage(chatBoxId = 'chat-box', inputId = 'user-message') {
            const message = document.getElementById(inputId).value.trim();
            if (message === '') return;
            
            appendMessage(message, 'user', chatBoxId);
            document.getElementById(inputId).value = '';

            // Show typing indicator
            const chatBox = document.getElementById(chatBoxId);
            const typingDiv = document.createElement('div');
            typingDiv.classList.add('ai-message', 'p-3', 'mb-3', 'max-w-xs', 'mr-auto');
            typingDiv.innerHTML = '<div class="flex space-x-1"><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce animation-delay-150"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce animation-delay-300"></div></div>';
            chatBox.appendChild(typingDiv);
            chatBox.scrollTop = chatBox.scrollHeight;

            // Simulate AI thinking delay
            setTimeout(() => {
                // Remove typing indicator
                chatBox.removeChild(typingDiv);
                
                // Kirim pesan ke server
                fetch('chat-process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    appendMessage(data.response, 'ai', chatBoxId);
                })
                .catch(error => {
                    console.error('Error:', error);
                    appendMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai', chatBoxId);
                });
            }, 1500);
        }

        function sendMessagePopup() {
            sendMessage('chat-box-popup', 'user-message-popup');
        }

        // Allow sending message with Enter key
        document.getElementById('user-message').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        document.getElementById('user-message-popup').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessagePopup();
            }
        });

        // Testimonial slider
        const testimonialSlider = document.getElementById('testimonial-slider');
        const testimonialCards = document.querySelectorAll('.testimonial-card');
        const testimonialPrev = document.getElementById('testimonial-prev');
        const testimonialNext = document.getElementById('testimonial-next');
        const testimonialDots = document.getElementById('testimonial-dots');
        
        let currentTestimonial = 0;
        const totalTestimonials = testimonialCards.length;
        
        // Create dots
        for (let i = 0; i < totalTestimonials; i++) {
            const dot = document.createElement('button');
            dot.className = 'w-3 h-3 rounded-full bg-gray-300 hover:bg-primary transition';
            dot.dataset.index = i;
            if (i === 0) dot.classList.add('bg-primary');
            testimonialDots.appendChild(dot);
        }
        
        const dots = document.querySelectorAll('#testimonial-dots button');
        
        function updateTestimonial(index) {
            testimonialSlider.style.transform = `translateX(-${index * 100}%)`;
            
            // Update active dot
            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('bg-primary');
                    dot.classList.remove('bg-gray-300');
                } else {
                    dot.classList.remove('bg-primary');
                    dot.classList.add('bg-gray-300');
                }
            });
            
            currentTestimonial = index;
        }
        
        testimonialPrev.addEventListener('click', () => {
            const newIndex = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials;
            updateTestimonial(newIndex);
        });
        
        testimonialNext.addEventListener('click', () => {
            const newIndex = (currentTestimonial + 1) % totalTestimonials;
            updateTestimonial(newIndex);
        });
        
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const index = parseInt(dot.dataset.index);
                updateTestimonial(index);
            });
        });
        
        // Auto slide testimonials
        let testimonialInterval = setInterval(() => {
            const newIndex = (currentTestimonial + 1) % totalTestimonials;
            updateTestimonial(newIndex);
        }, 5000);
        
        // Pause on hover
        testimonialSlider.addEventListener('mouseenter', () => {
            clearInterval(testimonialInterval);
        });
        
        testimonialSlider.addEventListener('mouseleave', () => {
            testimonialInterval = setInterval(() => {
                const newIndex = (currentTestimonial + 1) % totalTestimonials;
                updateTestimonial(newIndex);
            }, 5000);
        });
        
        // Count-up animation for stats
        const countups = document.querySelectorAll('.countup');
        
        function animateCountUp() {
            countups.forEach(countup => {
                const target = parseInt(countup.dataset.target);
                const duration = 2000;
                const start = 0;
                const increment = target / (duration / 16);
                let current = start;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        clearInterval(timer);
                        current = target;
                    }
                    countup.textContent = Math.floor(current).toLocaleString();
                }, 16);
            });
        }
        
        // GSAP animations
        gsap.registerPlugin(ScrollTrigger);
        
        // Animate elements on scroll
        gsap.utils.toArray('.animate-on-scroll').forEach(element => {
            gsap.from(element, {
                opacity: 0,
                y: 50,
                duration: 0.8,
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    toggleActions: "play none none none"
                }
            });
        });
        
        // Animate hero elements
        gsap.from('nav', { opacity: 0, y: -50, duration: 0.8 });
        gsap.from('.hero-gradient h1', { opacity: 0, y: 30, duration: 0.8, delay: 0.2 });
        gsap.from('.hero-gradient p', { opacity: 0, y: 30, duration: 0.8, delay: 0.4 });
        gsap.from('.hero-gradient a', { opacity: 0, y: 30, duration: 0.8, delay: 0.6, stagger: 0.1 });
        gsap.from('.hero-gradient img', { opacity: 0, x: 50, duration: 0.8, delay: 0.8 });
        
        // Initialize countup animation when stats section is in view
        ScrollTrigger.create({
            trigger: ".bg-white.py-12",
            start: "top 80%",
            onEnter: animateCountUp,
            once: true
        });
        
        // Parallax effect for CTA section
        gsap.to(".bg-gradient-to-r", {
            backgroundPosition: "50% 100%",
            ease: "none",
            scrollTrigger: {
                trigger: "#signup",
                start: "top bottom",
                end: "bottom top",
                scrub: true
            }
        });
        
        // Animate features on hover
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                gsap.to(card, { y: -10, duration: 0.3 });
            });
            card.addEventListener('mouseleave', () => {
                gsap.to(card, { y: 0, duration: 0.3 });
            });
        });
        
        // Animate buttons on hover
        document.querySelectorAll('a, button').forEach(btn => {
            if (btn.classList.contains('transform')) return;
            
            btn.addEventListener('mouseenter', () => {
                gsap.to(btn, { y: -2, duration: 0.2 });
            });
            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { y: 0, duration: 0.2 });
            });
        });
    </script>
</body>
</html>