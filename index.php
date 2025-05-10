<?php
// index.php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Pastikan koneksi database tersedia
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
}

// Inisialisasi objek Auth
$auth = new Auth($conn);

$kelas_link = 'kelas.php';
$misi_link = 'mission.php';
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    if ($user['role'] === 'mentor') {
        $kelas_link = 'mentor_classes.php';
        $misi_link = 'mentor_missions.php';
    }
    // Jika admin ingin diarahkan ke halaman khusus, tambahkan else if di sini
}
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
        .mobile-nav {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: scaleY(0);
        transform-origin: top;
        opacity: 0;
        transition: transform 0.3s ease-out, opacity 0.2s ease;
    }
    
    .mobile-nav.scale-y-100 {
        transform: scaleY(1);
        opacity: 1;
    }
    
    .hamburger-line {
        transition: all 0.3s ease;
    }
    
    .mobile-menu-item {
        transition: all 0.2s ease;
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
        <div class="flex justify-between h-16 items-center">
            <!-- Logo dan Judul -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <i class="fas fa-graduation-cap text-primary text-2xl mr-2 transform hover:rotate-12 transition-transform"></i>
                    <span class="text-xl font-bold text-dark hover:text-primary transition-colors">EduConnect</span>
                </div>
            </div>

            <!-- Menu utama (Desktop) -->
            <div class="hidden md:flex items-center space-x-8">
                <?php if (
                    $auth->isLoggedIn()
                ): ?>
                    <?php
                    $is_dashboard = basename($_SERVER['PHP_SELF']) === 'dashboardadmin.php' || basename($_SERVER['PHP_SELF']) === 'dashboardmentor.php' || basename($_SERVER['PHP_SELF']) === 'dashboardstudent.php';
                    ?>
                    <a href="<?php echo $is_dashboard ? 'index.php' : ($user['role'] === 'admin' ? 'dashboardadmin.php' : ($user['role'] === 'mentor' ? 'dashboardmentor.php' : 'dashboardstudent.php')); ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">
                        <?php echo $is_dashboard ? 'Landing Page' : 'Dashboard'; ?>
                    </a>
                    <a href="<?php echo $kelas_link; ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Kelas</a>
                    <a href="<?php echo $misi_link; ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Misi</a>
                    <a href="community.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Komunitas</a>
                <?php else: ?>
                    <a href="daftar_kelas.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Kelas</a>
                    <a href="mission.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Misi</a>
                    <a href="community.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Komunitas</a>
                <?php endif; ?>
            </div>

            <!-- Ikon Kelas, Keranjang, dan Login -->
            <div class="hidden md:flex items-center space-x-4 relative">
                <?php if ($auth->isLoggedIn()): ?>
                    <?php $user = $auth->getCurrentUser(); ?>
                    <div class="relative group" id="profileDropdown">
                        <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                            <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-8 h-8 border-2 border-primary" alt="Avatar">
                        </button>
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">

                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <div class="border-t my-1"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keluar</a>
                        </div>
                    </div>
                    <script>
                    // Dropdown logic
                    const avatarBtn = document.getElementById('avatarBtn');
                    const dropdownMenu = document.getElementById('dropdownMenu');
                    document.addEventListener('click', function(e) {
                        if (avatarBtn.contains(e.target)) {
                            dropdownMenu.classList.toggle('hidden');
                        } else if (!dropdownMenu.contains(e.target)) {
                            dropdownMenu.classList.add('hidden');
                        }
                    });
                    </script>
                <?php else: ?>
                    <a href="auth/login.php" class="text-gray-700 hover:text-primary text-xl transition-transform transform hover:scale-110">
                        <i class="fas fa-user-circle"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Tombol menu untuk mobile -->
            <div class="flex items-center md:hidden">
                <button id="mobile-menu-button" class="hamburger p-2 rounded-md text-gray-700 hover:text-primary focus:outline-none transition-transform duration-300">
                    <span class="hamburger-line block w-6 h-0.5 bg-gray-700 mb-1.5 transition-all duration-300"></span>
                    <span class="hamburger-line block w-6 h-0.5 bg-gray-700 mb-1.5 transition-all duration-300"></span>
                    <span class="hamburger-line block w-6 h-0.5 bg-gray-700 transition-all duration-300"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="mobile-nav md:hidden bg-white shadow-lg hidden transform origin-top transition-all duration-300 ease-out">
        <div class="px-2 pt-2 pb-4 space-y-1 sm:px-3">
            <a href="kelas.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                <i class="fas fa-chalkboard-teacher mr-2"></i>Kelas
            </a>
            <a href="mission.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                <i class="fas fa-tasks mr-2"></i>Misi
            </a>
            <a href="community.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                <i class="fas fa-users mr-2"></i>Komunitas
            </a>
            <?php if ($auth->isLoggedIn()): ?>
                <a href="profile.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <a href="auth/logout.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </a>
                <a href="auth/register.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-user-plus mr-2"></i>Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>



    <!-- Hero Section --><section id="hero" class="hero-section relative overflow-hidden min-h-screen flex items-center">
  <!-- Animated Gradient Background -->
  <div class="absolute inset-0 bg-gradient-to-br from-primary via-secondary to-accent animate-gradient"></div>
  
  <!-- Floating Particles -->
  <div class="particles absolute inset-0 overflow-hidden">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 py-20 md:py-0 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      <!-- Text Content -->
      <div class="text-center lg:text-left" data-aos="fade-up">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
          <span class="text-white">Pendidikan berkualitas</span>
          <span class="text-yellow-300 block mt-2">Untuk semua</span>
        </h1>
        
        <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-lg mx-auto lg:mx-0">
          EduConnect membuka akses pendidikan berkualitas melalui teknologi inovatif dengan
          <span class="font-semibold text-yellow-300">mentor profesional</span> dan 
          <span class="font-semibold text-yellow-300">materi pembelajaran terbaik</span>.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
          <a href="#signup" class="hero-btn-primary px-8 py-4 text-lg font-bold transform transition-all duration-300 hover:-translate-y-1">
            Mulai Belajar Gratis
            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
          </a>
          
          <a href="#how-it-works" class="hero-btn-secondary px-8 py-4 text-lg font-bold transform transition-all duration-300 hover:-translate-y-1">
            <i class="fas fa-play-circle mr-2"></i>
            Lihat Demo
          </a>
        </div>
        
        <!-- Trust Badges -->
        <div class="mt-12 flex flex-wrap justify-center lg:justify-start items-center gap-4">
          <div class="flex items-center bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
            <i class="fas fa-check-circle text-green-400 mr-2"></i>
            <span class="text-white text-sm font-medium">500+ Mentor Profesional</span>
          </div>
          <div class="flex items-center bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
            <i class="fas fa-users text-blue-300 mr-2"></i>
            <span class="text-white text-sm font-medium">10.000+ Siswa</span>
          </div>
        </div>
      </div>

      <!-- Image Content -->
      <div class="relative" data-aos="fade-left" data-aos-delay="300">
        <div class="relative z-10">
          <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
               alt="Students learning together" 
               class="rounded-2xl shadow-2xl border-4 border-white/30 w-full hover:border-white/50 transition-all duration-500">
        </div>
        
        <!-- Floating Cards -->
        <div class="floating-card mentor-card hidden lg:flex">
          <div class="flex items-center">
            <div class="icon-box bg-primary">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
              <p class="text-xs text-gray-300">Expert Mentors</p>
              <p class="font-bold text-white">500+ Profesional</p>
            </div>
          </div>
        </div>
        
        <div class="floating-card student-card hidden lg:flex">
          <div class="flex items-center">
            <div class="icon-box bg-secondary">
              <i class="fas fa-users"></i>
            </div>
            <div>
              <p class="text-xs text-gray-300">Active Students</p>
              <p class="font-bold text-white">10,000+</p>
            </div>
          </div>
        </div>
        
        <div class="floating-card rating-card hidden lg:flex">
          <div class="flex items-center">
            <div class="icon-box bg-yellow-500">
              <i class="fas fa-star"></i>
            </div>
            <div>
              <p class="text-xs text-gray-300">Average Rating</p>
              <p class="font-bold text-white">4.9/5.0</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scroll Down Indicator -->
  <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10 animate-bounce">
    <a href="#features" class="scroll-down-btn">
      <i class="fas fa-chevron-down text-white text-2xl"></i>
    </a>
  </div>
</section>

<style>
  .hero-section {
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
    background-size: 200% 200%;
  }
  
  .animate-gradient {
    animation: gradientBG 12s ease infinite;
  }
  
  @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  
  .particles {
    position: absolute;
    z-index: 1;
  }
  
  .particle {
    position: absolute;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: float 15s infinite linear;
  }
  
  .particle:nth-child(1) {
    width: 20px;
    height: 20px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
  }
  
  .particle:nth-child(2) {
    width: 30px;
    height: 30px;
    top: 60%;
    left: 80%;
    animation-delay: 2s;
  }
  
  .particle:nth-child(3) {
    width: 15px;
    height: 15px;
    top: 80%;
    left: 30%;
    animation-delay: 4s;
  }
  
  .particle:nth-child(4) {
    width: 25px;
    height: 25px;
    top: 30%;
    left: 60%;
    animation-delay: 6s;
  }
  
  .particle:nth-child(5) {
    width: 10px;
    height: 10px;
    top: 70%;
    left: 20%;
    animation-delay: 8s;
  }
  
  @keyframes float {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
  }
  
  .hero-btn-primary {
    background: white;
    color: #3b82f6;
    border-radius: 9999px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  }
  
  .hero-btn-primary:hover {
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    background: #f8fafc;
  }
  
  .hero-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 9999px;
    backdrop-filter: blur(5px);
  }
  
  .hero-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: white;
  }
  
  .floating-card {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    animation: floatCard 6s ease-in-out infinite;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  
  .mentor-card {
    top: -5%;
    right: -5%;
    animation-delay: 0.5s;
  }
  
  .student-card {
    bottom: -5%;
    left: -5%;
    animation-delay: 1s;
  }
  
  .rating-card {
    top: 50%;
    right: -10%;
    animation-delay: 1.5s;
  }
  
  @keyframes floatCard {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
  }
  
  .icon-box {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: white;
    font-size: 18px;
  }
  
  .scroll-down-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
  }
  
  .scroll-down-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(5px);
  }
  
  @media (max-width: 1023px) {
    .hero-section {
      padding-top: 100px;
      padding-bottom: 100px;
    }
    
    .floating-card {
      display: none !important;
    }
    
    .hero-btn-primary, .hero-btn-secondary {
      padding: 12px 24px;
      font-size: 16px;
    }
  }
</style>

<script>
    
  // Initialize AOS animation
  document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
      duration: 800,
      easing: 'ease-out-quad',
      once: true
    });
    
    // Create particles dynamically
    const particlesContainer = document.querySelector('.particles');
    for (let i = 0; i < 10; i++) {
      const particle = document.createElement('div');
      particle.classList.add('particle');
      particle.style.width = `${Math.random() * 20 + 10}px`;
      particle.style.height = particle.style.width;
      particle.style.left = `${Math.random() * 100}%`;
      particle.style.top = `${Math.random() * 100}%`;
      particle.style.animationDelay = `${Math.random() * 10}s`;
      particle.style.opacity = Math.random() * 0.5 + 0.1;
      particlesContainer.appendChild(particle);
    }
  });
</script>

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
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-dark mb-4">Apa yang Membuat EduConnect Berbeda?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">Platform pembelajaran inovatif yang dirancang khusus untuk menjawab tantangan pendidikan di daerah 3T</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                 data-aos="fade-up" data-aos-delay="100">
                <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                    <i class="fas fa-comments text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-dark mb-3 text-center">Mentoring Interaktif</h3>
                <p class="text-gray-600 text-center">Siswa bisa berinteraksi langsung dengan mentor melalui sesi live chat dan video call untuk tanya jawab dan diskusi.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                 data-aos="fade-up" data-aos-delay="200">
                <div class="bg-secondary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                    <i class="fas fa-users text-secondary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-dark mb-3 text-center">Belajar Kolaboratif</h3>
                <p class="text-gray-600 text-center">Ruang diskusi virtual memungkinkan siswa dari berbagai daerah saling berbagi ide dan belajar bersama.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                 data-aos="fade-up" data-aos-delay="300">
                <div class="bg-accent bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                    <i class="fas fa-robot text-accent text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-dark mb-3 text-center">Pembelajaran Adaptif</h3>
                <p class="text-gray-600 text-center">Sistem AI kami menyesuaikan materi belajar dengan minat dan kemampuan masing-masing siswa.</p>
            </div>
            
            <!-- Feature 4 -->
            <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                 data-aos="fade-up" data-aos-delay="400">
                <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                    <i class="fas fa-cloud-download-alt text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-dark mb-3 text-center">Akses Offline</h3>
                <p class="text-gray-600 text-center">Konten pembelajaran bisa diunduh dan diakses tanpa koneksi internet stabil, cocok untuk daerah 3T.</p>
            </div>
        </div>
    </div>
</section>

    <!-- Modern How It Works Section -->
<section id="how-it-works" class="py-16 md:py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
        <!-- Animated Header -->
        <div class="text-center mb-12 md:mb-20" data-aos="fade-up">
            <span class="inline-block text-primary font-semibold mb-3 tracking-wider">PROSES BELAJAR</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">Belajar Lebih Mudah<br class="hidden md:block"> dengan <span class="text-primary">EduConnect</span></h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Hanya dengan 3 langkah sederhana, mulai perjalanan belajar Anda dengan mentor profesional</p>
        </div>
        
        <!-- Timeline Steps - Desktop -->
        <div class="hidden md:block relative">
            <!-- Timeline line -->
            <div class="absolute left-1/2 top-0 h-full w-1 bg-gradient-to-b from-primary to-accent transform -translate-x-1/2"></div>
            
            <div class="grid grid-cols-9 gap-0 relative z-10">
                <!-- Step 1 -->
                <div class="col-span-3 text-right pr-10 transform hover:scale-[1.02] transition duration-300 group" data-aos="fade-right">
                    <div class="relative">
                        <div class="absolute -right-10 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-full bg-primary flex items-center justify-center border-4 border-white shadow-lg group-hover:scale-110 transition">
                            <span class="text-white font-bold">1</span>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Daftar Gratis</h3>
                            <p class="text-gray-600">Buat akun siswa dan lengkapi profil untuk mendapatkan rekomendasi pembelajaran yang sesuai.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Spacer -->
                <div class="col-span-3"></div>
                
                <!-- Step 2 -->
                <div class="col-span-3 text-left pl-10 transform hover:scale-[1.02] transition duration-300 group" data-aos="fade-left" data-aos-delay="150">
                    <div class="relative">
                        <div class="absolute -left-10 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-full bg-secondary flex items-center justify-center border-4 border-white shadow-lg group-hover:scale-110 transition">
                            <span class="text-white font-bold">2</span>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Pilih Mentor atau Materi</h3>
                            <p class="text-gray-600">Temukan mentor profesional atau pilih materi pembelajaran dari berbagai bidang yang tersedia.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="col-span-3 text-right pr-10 mt-16 transform hover:scale-[1.02] transition duration-300 group" data-aos="fade-right" data-aos-delay="300">
                    <div class="relative">
                        <div class="absolute -right-10 top-1/2 transform -translate-y-1/2 w-8 h-8 rounded-full bg-accent flex items-center justify-center border-4 border-white shadow-lg group-hover:scale-110 transition">
                            <span class="text-white font-bold">3</span>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Mulai Belajar</h3>
                            <p class="text-gray-600">Ikuti sesi belajar interaktif, diskusi kelompok, atau akses materi pembelajaran kapan saja.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Steps - Vertical Timeline -->
        <div class="md:hidden relative">
            <!-- Vertical line -->
            <div class="absolute left-6 top-0 h-full w-1 bg-gradient-to-b from-primary to-accent transform -translate-x-1/2"></div>
            
            <div class="space-y-10 pl-16">
                <!-- Step 1 -->
                <div class="relative transform hover:scale-[1.02] transition duration-300" data-aos="fade-up">
                    <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-primary flex items-center justify-center border-4 border-white shadow-lg">
                        <span class="text-white font-bold">1</span>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Daftar Gratis</h3>
                        <p class="text-gray-600">Buat akun siswa dan lengkapi profil untuk mendapatkan rekomendasi pembelajaran yang sesuai.</p>
                    </div>
                </div>

            
                <!-- Step 2 -->
                <div class="relative transform hover:scale-[1.02] transition duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-secondary flex items-center justify-center border-4 border-white shadow-lg">
                        <span class="text-white font-bold">2</span>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Pilih Mentor atau Materi</h3>
                        <p class="text-gray-600">Temukan mentor profesional atau pilih materi pembelajaran dari berbagai bidang yang tersedia.</p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="relative transform hover:scale-[1.02] transition duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-accent flex items-center justify-center border-4 border-white shadow-lg">
                        <span class="text-white font-bold">3</span>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Mulai Belajar</h3>
                        <p class="text-gray-600">Ikuti sesi belajar interaktif, diskusi kelompok, atau akses materi pembelajaran kapan saja.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Animated CTA Button -->
        <div class="mt-16 md:mt-20 text-center" data-aos="fade-up" data-aos-delay="400">
            <a href="#signup" class="relative inline-block bg-gradient-to-r from-primary to-secondary text-white font-semibold px-8 py-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                <span class="relative z-10">Mulai Sekarang</span>
                <span class="absolute inset-0 bg-gradient-to-r from-primary-dark to-secondary-dark opacity-0 group-hover:opacity-100 rounded-full transition-opacity duration-300"></span>
                <span class="absolute -bottom-1 left-1/2 w-4/5 h-2 bg-primary/30 blur-md transform -translate-x-1/2 group-hover:blur-lg transition-all duration-300"></span>
            </a>
        </div>
    </div>
</section>

<!-- AOS Animation CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- Initialize AOS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-quad',
        once: true
    });
</script>

    <!-- AI Chat Section -->
  <!-- AI Chat Section -->
<section id="ai-chat" class="py-16 md:py-24 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="inline-block text-primary font-semibold mb-3 tracking-wider">TEKNOLOGI PENDIDIKAN</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Dukungan AI 24/7 untuk <span class="text-primary">Pembelajaran Tanpa Batas</span></h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Dengan teknologi Asisten AI terbaru, EduConnect memberikan pengalaman bimbingan belajar yang lebih interaktif dan responsif.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <!-- AI Chat Box -->
            <div class="relative" data-aos="fade-right">
                <div class="bg-white p-6 rounded-2xl shadow-xl transform hover:-translate-y-2 transition duration-500 hover:shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="bg-primary rounded-full p-3 mr-4 animate-waving-hand">
                            <i class="fas fa-robot text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">AI Assistant EduConnect</h3>
                            <p class="text-sm text-gray-500">Siap membantu 24 jam sehari</p>
                        </div>
                    </div>
                    
                    <!-- Chat Container -->
                    <div id="chat-box" class="mb-6 p-4 bg-gray-50 rounded-lg h-64 overflow-y-auto shadow-inner">
                        <div class="ai-message p-3 mb-3 bg-white rounded-lg shadow-sm max-w-[80%]">
                            <p class="font-medium text-primary">🤖 AI Assistant:</p>
                            <p class="text-gray-700">Hai! Saya AI Assistant EduConnect. Ada yang bisa saya bantu terkait pembelajaran Anda hari ini?</p>
                            <p class="text-xs text-gray-400 mt-1">Baru saja</p>
                        </div>
                    </div>
                    
                    <!-- Input Area -->
                    <div class="flex items-center mb-3">
                        <textarea id="user-message" placeholder="Ketik pertanyaan Anda..." rows="2"
                            class="flex-grow px-4 py-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 resize-none"></textarea>
                        <button onclick="sendMessage()" class="bg-primary text-white px-5 py-3 rounded-r-lg hover:bg-primary-dark transition duration-300 h-full hover:scale-105 active:scale-95">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    
                    <div class="text-xs text-gray-500">
                        <p>Contoh pertanyaan: "Jelaskan fotosintesis" atau "Bantu saya memahami aljabar"</p>
                    </div>
                </div>
                
                <!-- Floating Feature Card -->
                <div class="absolute -bottom-5 -right-5 bg-white p-4 rounded-xl shadow-lg hidden lg:block" data-aos="zoom-in" data-aos-delay="300">
                    <div class="flex items-center">
                        <div class="bg-secondary rounded-full p-2 mr-3">
                            <i class="fas fa-bolt text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Respons Cepat</p>
                            <p class="font-bold text-gray-900">< 1 Detik</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features List -->
            <div data-aos="fade-left" data-aos-delay="100">
                <div class="space-y-6">
                    <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="150">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Jawaban Instan</h3>
                                <p class="text-gray-600">Dapatkan penjelasan langsung untuk semua pertanyaan seputar materi pembelajaran</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="200">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-language text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Multi-Bahasa</h3>
                                <p class="text-gray-600">Dukungan berbagai bahasa daerah untuk memudahkan pemahaman</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="250">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-brain text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Pembelajaran Adaptif</h3>
                                <p class="text-gray-600">Materi disesuaikan dengan tingkat pemahaman dan gaya belajar Anda</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="300">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-book text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Integrasi Materi</h3>
                                <p class="text-gray-600">Terhubung langsung dengan konten pembelajaran di platform EduConnect</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8" data-aos="fade-up" data-aos-delay="350">
                    <a href="#ai-chat" class="inline-flex items-center text-primary font-semibold hover:text-primary-dark transition">
                        Coba fitur AI sekarang
                        <i class="fas fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .animate-waving-hand {
        animation: wave 2s infinite;
    }
    
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(15deg); }
        50% { transform: rotate(-10deg); }
        75% { transform: rotate(5deg); }
    }
    
    .ai-message {
        border-left: 3px solid #3b82f6;
    }
    
    .feature-item:hover {
        transform: translateY(-3px);
    }
    
    #chat-box::-webkit-scrollbar {
        width: 6px;
    }
    
    #chat-box::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #chat-box::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    #chat-box::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<script>
    // Initialize AOS
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-out-quad',
            once: true
        });
    });
    
    // Simple chat function (mock)
    function sendMessage() {
        const userMessage = document.getElementById('user-message');
        const chatBox = document.getElementById('chat-box');
        
        if (userMessage.value.trim() === '') return;
        
        // Add user message
        const userMsgDiv = document.createElement('div');
        userMsgDiv.className = 'user-message p-3 mb-3 bg-blue-50 rounded-lg shadow-sm max-w-[80%] ml-auto text-right';
        userMsgDiv.innerHTML = `
            <p class="font-medium text-blue-600">👤 Anda:</p>
            <p class="text-gray-700">${userMessage.value}</p>
            <p class="text-xs text-gray-400 mt-1">Baru saja</p>
        `;
        chatBox.appendChild(userMsgDiv);
        
        // Simulate AI response
        setTimeout(() => {
            const aiMsgDiv = document.createElement('div');
            aiMsgDiv.className = 'ai-message p-3 mb-3 bg-white rounded-lg shadow-sm max-w-[80%]';
            aiMsgDiv.innerHTML = `
                <p class="font-medium text-primary">🤖 AI Assistant:</p>
                <p class="text-gray-700">Saya memahami pertanyaan Anda tentang "${userMessage.value}". Berikut penjelasannya: ...</p>
                <p class="text-xs text-gray-400 mt-1">Baru saja</p>
            `;
            chatBox.appendChild(aiMsgDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }, 1000);
        
        userMessage.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    
    // Allow sending message with Enter key
    document.getElementById('user-message').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
</script>
    <!-- Bootcamp Section -->
    <section id="bootcamp" class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
        <div class="text-center mb-12 md:mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Bootcamp Profesional</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Tingkatkan skill dengan program bootcamp intensif bersama mentor ahli di bidangnya</p>
        </div>
        
        <!-- Desktop Grid (3 columns) -->
        <div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Bootcamp 1 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                <div class="bg-primary h-2 w-full"></div>
                <div class="p-6 md:p-8">
                    <div class="flex items-center mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                            <i class="fas fa-code text-primary text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Bootcamp Pemrograman</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Pelajari dasar-dasar pemrograman hingga siap kerja dalam 12 minggu intensif.</p>
                    
                    <div class="mb-6">
                        <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.500.000</p>
                        <p class="text-gray-500 text-sm">atau 3x Rp 500.000</p>
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
                    
                    <a href="#signup" class="block w-full bg-primary text-white text-center font-semibold py-3 rounded-lg hover:bg-primary-dark transition duration-300 transform hover:scale-[1.02]">Daftar Sekarang</a>
                </div>
            </div>
            
            <!-- Bootcamp 2 (Populer) -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2 scale-105 z-10 relative">
                <div class="bg-secondary h-2 w-full"></div>
                <div class="absolute top-4 right-4 bg-secondary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                    POPULER
                </div>
                <div class="p-6 md:p-8">
                    <div class="flex items-center mb-4">
                        <div class="bg-secondary bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                            <i class="fas fa-chart-line text-secondary text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Bootcamp Digital Marketing</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Kuasi skill digital marketing dari dasar hingga strategi lanjutan dalam 8 minggu.</p>
                    
                    <div class="mb-6">
                        <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.200.000</p>
                        <p class="text-gray-500 text-sm">atau 3x Rp 400.000</p>
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
                    
                    <a href="#signup" class="block w-full bg-secondary text-white text-center font-semibold py-3 rounded-lg hover:bg-secondary-dark transition duration-300 transform hover:scale-[1.02]">Daftar Sekarang</a>
                </div>
            </div>
            
            <!-- Bootcamp 3 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                <div class="bg-accent h-2 w-full"></div>
                <div class="p-6 md:p-8">
                    <div class="flex items-center mb-4">
                        <div class="bg-accent bg-opacity-10 p-3 rounded-full mr-4 hover:rotate-12 transition-transform">
                            <i class="fas fa-paint-brush text-accent text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Bootcamp Desain Grafis</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Pelajari prinsip desain dan tools populer untuk menjadi desainer profesional.</p>
                    
                    <div class="mb-6">
                        <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.000.000</p>
                        <p class="text-gray-500 text-sm">atau 2x Rp 500.000</p>
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
                    
                    <a href="#signup" class="block w-full bg-accent text-white text-center font-semibold py-3 rounded-lg hover:bg-accent-dark transition duration-300 transform hover:scale-[1.02]">Daftar Sekarang</a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Horizontal Scroll -->
        <div class="md:hidden pb-6 -mx-5 px-5 overflow-x-auto">
            <div class="flex space-x-5 w-max" style="padding-right: 1.25rem;">
                <!-- Bootcamp 1 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 w-80 flex-shrink-0">
                    <div class="bg-primary h-2 w-full"></div>
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-code text-primary text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Bootcamp Pemrograman</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Pelajari dasar-dasar pemrograman hingga siap kerja dalam 12 minggu intensif.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.500.000</p>
                            <p class="text-gray-500 text-sm">atau 3x Rp 500.000</p>
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
                        
                        <a href="#signup" class="block w-full bg-primary text-white text-center font-semibold py-3 rounded-lg">Daftar Sekarang</a>
                    </div>
                </div>
                
                <!-- Bootcamp 2 (Populer) -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 w-80 flex-shrink-0 relative">
                    <div class="bg-secondary h-2 w-full"></div>
                    <div class="absolute top-4 right-4 bg-secondary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                        POPULER
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-secondary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-chart-line text-secondary text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Bootcamp Digital Marketing</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Kuasi skill digital marketing dari dasar hingga strategi lanjutan dalam 8 minggu.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.200.000</p>
                            <p class="text-gray-500 text-sm">atau 3x Rp 400.000</p>
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
                        
                        <a href="#signup" class="block w-full bg-secondary text-white text-center font-semibold py-3 rounded-lg">Daftar Sekarang</a>
                    </div>
                </div>
                
                <!-- Bootcamp 3 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 w-80 flex-shrink-0">
                    <div class="bg-accent h-2 w-full"></div>
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-accent bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-paint-brush text-accent text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Bootcamp Desain Grafis</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Pelajari prinsip desain dan tools populer untuk menjadi desainer profesional.</p>
                        
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-gray-900 mb-1">Rp 1.000.000</p>
                            <p class="text-gray-500 text-sm">atau 2x Rp 500.000</p>
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
                        
                        <a href="#signup" class="block w-full bg-accent text-white text-center font-semibold py-3 rounded-lg">Daftar Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <a href="#bootcamp" class="text-primary font-semibold hover:underline inline-flex items-center group">
                Lihat semua program bootcamp
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
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
  document.addEventListener('DOMContentLoaded', function() {
    // Nonaktifkan scroll restoration browser
    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
    }
    
    // Scroll ke hero section
    const hero = document.getElementById('hero');
    if (hero) {
      window.scrollTo({
        top: hero.offsetTop,
        behavior: 'instant'
      });
    }
  });


        // Loading overlay
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loading-overlay').classList.add('hidden');
            }, 1500);
        });

        // Pastikan dijalankan setelah halaman selesai dimuat
window.addEventListener('load', function() {
  const heroSection = document.querySelector('.hero-section');
  if (heroSection) {
    // Scroll ke hero section dengan efek smooth
    heroSection.scrollIntoView({ behavior: 'smooth' });
    
    // Atau untuk scroll instan tanpa animasi:
    // window.scrollTo(0, 0);
  }
});

        // Simpan posisi scroll dalam history state
window.addEventListener('scroll', function() {
  history.replaceState({ scrollPosition: window.scrollY }, '');
});

// Kembalikan posisi scroll
window.addEventListener('load', function() {
  if (history.state && history.state.scrollPosition) {
    window.scrollTo(0, history.state.scrollPosition);
  }
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

        
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const hamburgerLines = document.querySelectorAll('.hamburger-line');
        
        mobileMenuButton.addEventListener('click', function() {
            const isOpen = mobileMenu.classList.contains('hidden');
            
            if (isOpen) {
                // Open menu
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('scale-y-100', 'opacity-100');
                mobileMenu.classList.remove('scale-y-0', 'opacity-0');
                
                // Hamburger to X animation
                hamburgerLines[0].classList.add('transform', 'rotate-45', 'translate-y-2');
                hamburgerLines[1].classList.add('opacity-0');
                hamburgerLines[2].classList.add('transform', '-rotate-45', '-translate-y-2');
            } else {
                // Close menu
                mobileMenu.classList.add('scale-y-0', 'opacity-0');
                mobileMenu.classList.remove('scale-y-100', 'opacity-100');
                
                // X to hamburger animation
                hamburgerLines[0].classList.remove('transform', 'rotate-45', 'translate-y-2');
                hamburgerLines[1].classList.remove('opacity-0');
                hamburgerLines[2].classList.remove('transform', '-rotate-45', '-translate-y-2');
                
                // Hide after animation completes
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 300);
            }
        });
        
        // Close menu when clicking on a link
        document.querySelectorAll('.mobile-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                mobileMenu.classList.add('scale-y-0', 'opacity-0', 'hidden');
                hamburgerLines[0].classList.remove('transform', 'rotate-45', 'translate-y-2');
                hamburgerLines[1].classList.remove('opacity-0');
                hamburgerLines[2].classList.remove('transform', '-rotate-45', '-translate-y-2');
            });
        });
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
