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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduConnect: Digital Innovation for Equitable Education in Remote Areas</title>
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
                <!-- Logo and Title -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2 transform hover:rotate-12 transition-transform"></i>
                        <span class="text-xl font-bold text-dark hover:text-primary transition-colors">EduConnect</span>
                    </div>
                </div>

                <!-- Main Menu (Desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php
                        $is_dashboard = basename($_SERVER['PHP_SELF']) === 'dashboardadmin.php' || basename($_SERVER['PHP_SELF']) === 'dashboardmentor.php' || basename($_SERVER['PHP_SELF']) === 'dashboardstudent.php';
                        ?>
                        <a href="<?php echo $is_dashboard ? 'index.php' : ($user['role'] === 'admin' ? 'dashboardadmin.php' : ($user['role'] === 'mentor' ? 'dashboardmentor.php' : 'dashboardstudent.php')); ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">
                            <?php echo $is_dashboard ? 'Landing Page' : 'Dashboard'; ?>
                        </a>
                        <a href="<?php echo $kelas_link; ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Courses</a>
                        <a href="<?php echo $misi_link; ?>" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Missions</a>
                        <a href="community.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Community</a>
                    <?php else: ?>
                        <a href="daftar_kelas.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Courses</a>
                        <a href="mission.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Missions</a>
                        <a href="community.php" class="text-gray-700 hover:text-primary px-3 py-2 font-medium transition-colors hover:scale-105">Community</a>
                    <?php endif; ?>
                </div>

                <!-- Class, Cart, and Login Icons -->
                <div class="hidden md:flex items-center space-x-4 relative">
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php $user = $auth->getCurrentUser(); ?>
                        <div class="relative group" id="profileDropdown">
                            <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                                <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-8 h-8 border-2 border-primary" alt="Avatar">
                            </button>
                            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                                <div class="border-t my-1"></div>
                                <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
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
                        <a href="auth/login.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="auth/register.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
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
                <a href="daftar_kelas.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>Courses
                </a>
                <a href="mission.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-tasks mr-2"></i>Missions
                </a>
                <a href="community.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                    <i class="fas fa-users mr-2"></i>Community
                </a>
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="profile.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <a href="auth/logout.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="auth/register.php" class="mobile-menu-item block px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero-section relative overflow-hidden min-h-screen flex items-center">
        <!-- Animated Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary via-secondary to-accent animate-gradient"></div>
        
        <!-- Floating Particles -->
        <div class="particles absolute inset-0 overflow-hidden">
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
                        <span class="text-white">Quality Education</span>
                        <span class="text-yellow-300 block mt-2">For Everyone</span>
                    </h1>
                    
                    <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-lg mx-auto lg:mx-0">
                        EduConnect provides access to quality education through innovative technology with
                        <span class="font-semibold text-yellow-300">professional mentors</span> and 
                        <span class="font-semibold text-yellow-300">top-tier learning materials</span>.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#signup" class="hero-btn-primary px-8 py-4 text-lg font-bold transform transition-all duration-300 hover:-translate-y-1">
                            Start Learning for Free
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        
                        <a href="#how-it-works" class="hero-btn-secondary px-8 py-4 text-lg font-bold transform transition-all duration-300 hover:-translate-y-1">
                            <i class="fas fa-play-circle mr-2"></i>
                            Watch Demo
                        </a>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="mt-12 flex flex-wrap justify-center lg:justify-start items-center gap-4">
                        <div class="flex items-center bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span class="text-white text-sm font-medium">500+ Professional Mentors</span>
                        </div>
                        <div class="flex items-center bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                            <i class="fas fa-users text-blue-300 mr-2"></i>
                            <span class="text-white text-sm font-medium">10,000+ Students</span>
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
                                <p class="font-bold text-white">500+ Professionals</p>
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
                    <p class="text-gray-600">Regions Reached</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-secondary mb-2 countup" data-target="500">0</p>
                    <p class="text-gray-600">Professional Mentors</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-accent mb-2 countup" data-target="10000">0</p>
                    <p class="text-gray-600">Registered Students</p>
                </div>
                <div class="p-6 transform hover:scale-110 transition duration-300">
                    <p class="text-4xl font-bold text-primary mb-2">24/7</p>
                    <p class="text-gray-600">AI Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-dark mb-4">What Makes EduConnect Different?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">An innovative learning platform designed specifically to address educational challenges in remote areas</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                     data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-comments text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Interactive Mentoring</h3>
                    <p class="text-gray-600 text-center">Students can interact directly with mentors through live chat and video calls for Q&A and discussions.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                     data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-secondary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-users text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Collaborative Learning</h3>
                    <p class="text-gray-600 text-center">Virtual discussion rooms allow students from various regions to share ideas and learn together.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                     data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-accent bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-robot text-accent text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Adaptive Learning</h3>
                    <p class="text-gray-600 text-center">Our AI system tailors learning materials to each student's interests and abilities.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300 hover:shadow-xl" 
                     data-aos="fade-up" data-aos-delay="400">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-6 mx-auto hover:rotate-12 transition-transform">
                        <i class="fas fa-cloud-download-alt text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3 text-center">Offline Access</h3>
                    <p class="text-gray-600 text-center">Learning content can be downloaded and accessed without a stable internet connection, ideal for remote areas.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern How It Works Section -->
    <section id="how-it-works" class="py-16 md:py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
            <!-- Animated Header -->
            <div class="text-center mb-12 md:mb-20" data-aos="fade-up">
                <span class="inline-block text-primary font-semibold mb-3 tracking-wider">LEARNING PROCESS</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">Learn Easier<br class="hidden md:block"> with <span class="text-primary">EduConnect</span></h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">In just 3 simple steps, start your learning journey with professional mentors</p>
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
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Sign Up for Free</h3>
                                <p class="text-gray-600">Create a student account and complete your profile to receive personalized learning recommendations.</p>
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
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Choose a Mentor or Material</h3>
                                <p class="text-gray-600">Find professional mentors or select learning materials from various available fields.</p>
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
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Start Learning</h3>
                                <p class="text-gray-600">Join interactive learning sessions, group discussions, or access learning materials anytime.</p>
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
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Sign Up for Free</h3>
                            <p class="text-gray-600">Create a student account and complete your profile to receive personalized learning recommendations.</p>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative transform hover:scale-[1.02] transition duration-300" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-secondary flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-white font-bold">2</span>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Choose a Mentor or Material</h3>
                            <p class="text-gray-600">Find professional mentors or select learning materials from various available fields.</p>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="relative transform hover:scale-[1.02] transition duration-300" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-accent flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-white font-bold">3</span>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Start Learning</h3>
                            <p class="text-gray-600">Join interactive learning sessions, group discussions, or access learning materials anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Animated CTA Button -->
            <div class="mt-16 md:mt-20 text-center" data-aos="fade-up" data-aos-delay="400">
                <a href="#signup" class="relative inline-block bg-gradient-to-r from-primary to-secondary text-white font-semibold px-8 py-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                    <span class="relative z-10">Start Now</span>
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
    <section id="ai-chat" class="py-16 md:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
            <div class="text-center mb-12" data-aos="fade-up">
                <span class="inline-block text-primary font-semibold mb-3 tracking-wider">EDUCATION TECHNOLOGY</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">24/7 AI Support for <span class="text-primary">Unlimited Learning</span></h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">With the latest AI Assistant technology, EduConnect offers a more interactive and responsive learning experience.</p>
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
                                <h3 class="text-xl font-bold text-gray-900">EduConnect AI Assistant</h3>
                                <p class="text-sm text-gray-500">Available 24/7 to assist</p>
                            </div>
                        </div>
                        
                        <!-- Chat Container -->
                        <div id="chat-box" class="mb-6 p-4 bg-gray-50 rounded-lg h-64 overflow-y-auto shadow-inner">
                            <div class="ai-message p-3 mb-3 bg-white rounded-lg shadow-sm max-w-[80%]">
                                <p class="font-medium text-primary">ðŸ¤– AI Assistant:</p>
                                <p class="text-gray-700">Hi! I'm EduConnect's AI Assistant. How can I help with your learning today?</p>
                                <p class="text-xs text-gray-400 mt-1">Just now</p>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="flex items-center mb-3">
                            <textarea id="user-message" placeholder="Type your question..." rows="2"
                                class="flex-grow px-4 py-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 resize-none"></textarea>
                            <button onclick="sendMessage()" class="bg-primary text-white px-5 py-3 rounded-r-lg hover:bg-primary-dark transition duration-300 h-full hover:scale-105 active:scale-95">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <div class="text-xs text-gray-500">
                            <p>Example prompts: "Explain photosynthesis" or "Help me with algebra"</p>
                        </div>
                    </div>
                    
                    <!-- Floating Feature Card -->
                    < ðŸ™‚

                    <div class="absolute -bottom-5 -right-5 bg-white p-4 rounded-xl shadow-lg hidden lg:block" data-aos="zoom-in" data-aos-delay="300">
                        <div class="flex items-center">
                            <div class="bg-secondary rounded-full p-2 mr-3">
                                <i class="fas fa-bolt text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Quick Response</p>
                                <p class="font-bold text-gray-900">< 1 Second</p>
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
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Instant Answers</h3>
                                    <p class="text-gray-600">Get immediate explanations for all your learning-related questions.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="200">
                            <div class="flex items-start">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                    <i class="fas fa-language text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Multi-Language Support</h3>
                                    <p class="text-gray-600">Support for various regional languages to enhance understanding.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="250">
                            <div class="flex items-start">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                    <i class="fas fa-brain text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Adaptive Learning</h3>
                                    <p class="text-gray-600">Content tailored to your comprehension level and learning style.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-item p-5 bg-white rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-left" data-aos-delay="300">
                            <div class="flex items-start">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-4 flex-shrink-0">
                                    <i class="fas fa-book text-primary"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Material Integration</h3>
                                    <p class="text-gray-600">Seamlessly connected to EduConnectâ€™s learning content.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8" data-aos="fade-up" data-aos-delay="350">
                        <a href="#ai-chat" class="inline-flex items-center text-primary font-semibold hover:text-primary-dark transition">
                            Try the AI feature now
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
            userMsgDiv.className = 'user-message-text p-3 mb-3 bg-blue-50 rounded-lg shadow-sm max-w-[80%] ml-auto text-right';
            userMsgDiv.innerHTML = `
                <p class="font-medium text-blue-600">ðŸ‘¤ You:</p>
                <p class="text-gray-700">${userMessage.value}</p>
                <p class="text-xs text-gray-400 mt-1">Just now</p>
            `;
            chatBox.appendChild(userMsgDiv);
            
            // Simulate AI response
            setTimeout(() => {
                const aiMsgDiv = document.createElement('div');
                aiMsgDiv.className = 'ai-message p-3 mb-3 bg-white rounded-lg shadow-sm max-w-[80%]';
                aiMsgDiv.innerHTML = `
                    <p class="font-medium text-primary">ðŸ¤– AI Assistant:</p>
                    <p>I understand your question about "${userMessage.value}". Here's the explanation:...</p>
                    <p class="text-xs text-gray-400 mt-1">Just now</p>
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
    


<!-- Testimonials -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">What They Say About EduConnect?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Testimonials from students and mentors who have experienced the benefits of our platform</p>
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
                            <p class="text-gray-600 mb-6">"Thanks to EduConnect, I can learn directly from experienced mentors despite living in a remote area. The materials are easy to understand, and the AI feature is very helpful when mentors are unavailable!"</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Student" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Siti Rahayu</p>
                                    <p class="text-gray-500 text-sm">Student, Papua</p>
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
                            <p class="text-gray-600 mb-6">"As a mentor, Iâ€™m impressed by the enthusiasm of students in remote areas. EduConnect provides the perfect platform to share knowledge. The AI feature also helps me address basic questions, allowing me to focus on more complex material."</p>
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
                            <p class="text-gray-600 mb-6">"The digital marketing bootcamp at EduConnect helped me start an online business. Now I can sell products nationwide! The AI Assistant was also very helpful when I studied at night and couldnâ€™t reach a mentor."</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Student" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Dewi Anggraeni</p>
                                    <p class="text-gray-500 text-sm">Student, NTT</p>
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
                            <p class="text-gray-600 mb-6">"As a school principal in a remote area, EduConnect has been a solution for our teacher shortage. Students can learn from quality mentors and an always-ready AI Assistant. Our studentsâ€™ academic performance has significantly improved!"</p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Principal" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold text-dark">Drs. Ahmad Yani</p>
                                    <p class="text-gray-500 text-sm">Principal, Maluku</p>
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
            <h2 class="text-3xl font-bold mb-6 animate__animated animate__pulse animate__infinite">Ready to Start Your Learning Journey?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Join thousands of other students who have experienced the benefits of EduConnect for equitable education in Indonesia.</p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-8">
                <!-- Register Button - Cute with animation -->
                <a href="auth/register.php" class="relative inline-flex items-center px-8 py-3 bg-white text-primary rounded-full font-bold text-lg shadow-lg transform transition-all hover:scale-105 hover:shadow-xl animate__animated animate__bounceIn">
                    <span class="mr-2">Register Now</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="absolute -top-2 -right-2 h-6 w-6 flex items-center justify-center bg-yellow-300 text-primary rounded-full text-xs animate-ping">âœ¨</span>
                </a>
                
                <!-- Login Button - Cute alternative style -->
                <a href="auth/login.php" class="relative inline-flex items-center px-8 py-3 border-2 border-white text-white rounded-full font-bold text-lg hover:bg-white hover:text-primary transition-all duration-300 group">
                    <span class="mr-2">Login</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-3/4 h-1 bg-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                </a>
            </div>
            
            <!-- Decorative elements -->
            <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-yellow-300 opacity-20 mix-blend-overlay"></div>
            <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-pink-300 opacity-20 mix-blend-overlay"></div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold text-dark mb-6">Contact Us</h2>
                    <p class="text-gray-600 mb-8">Have questions or feedback? The EduConnect team is ready to assist you.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start transform hover:translate-x-2 transition-transform">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-dark mb-1">Address</h4>
                                <p class="text-gray-600">Jl. Pendidikan No. 123, Central Jakarta, Indonesia</p>
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
                                <h4 class="font-semibold text-dark mb-1">Phone</h4>
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
                    <h2 class="text-3xl font-bold text-dark mb-6">Send a Message</h2>
                    <form class="space-y-4">
                        <div>
                            <input type="text" placeholder="Your Name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <input type="email" placeholder="Your Email" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <input type="text" placeholder="Subject" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <textarea rows="4" placeholder="Your Message" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="bg-primary text-white font-semibold px-6 py-2 rounded-lg hover:bg-primary-dark transition duration-300 transform hover:-translate-y-1">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </div>
                    <p class="text-gray-400">An innovative learning platform for equitable education in remote areas of Indonesia.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition duration-300">Features</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition duration-300">How It Works</a></li>
                        <li><a href="#bootcamp" class="text-gray-400 hover:text-white transition duration-300">Bootcamp</a></li>
                        <li><a href="#ai-chat" class="text-gray-400 hover:text-white transition duration-300">AI Assistant</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Policies</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Terms & Conditions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Refund Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Subscribe</h3>
                    <p class="text-gray-400 mb-4">Get the latest updates about our programs and new features.</p>
                    <form class="flex">
                        <input type="email" placeholder="Your Email" class="px-4 py-2 rounded-l-lg border border-gray-600 bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent w-full">
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition duration-300">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">Â© 2025 EduConnect. All rights reserved. Designed by Arya Wardhana</p>
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

   
    <div class="fixed bottom-6 right-6 z-50 flex flex-col space-y-3">
        <button id="chat-button" class="bg-primary text-white p-4 rounded-full shadow-lg hover:bg-primary-dark transition duration-300 transform hover:scale-110 group relative">
            <i class="fas fa-comment-dots text-xl"></i>
            <span class="absolute right-full top-1/2 transform -translate-y-1/2 mr-2 bg-primary text-white text-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Need Help?
            </span>
        </button>
        
        <button id="scroll-top" class="bg-secondary text-white p-4 rounded-full shadow-lg hover:bg-secondary-dark transition duration-300 transform hover:scale-110 group relative">
            <i class="fas fa-arrow-up text-xl"></i>
            <span class="absolute right-full top-1/2 transform -translate-y-1/2 mr-2 bg-secondary text-white text-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                To Top
            </span>
        </button>
    </div>

    <!-- Chat Popup -->
    <div id="chat-popup" class="fixed bottom-24 right-6 w-80 bg-white rounded-xl shadow-xl z-50 hidden transform transition-all duration-300 origin-bottom-right">
        <div class="bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
            <h3 class="font-semibold">EduConnect AI Assistant</h3>
            <button id="close-chat" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chat-box-popup" class="p-4 h-64 overflow-y-auto">
            <div class="ai-message p-3 mb-3 max-w-xs">
                <p class="font-medium">ðŸ¤– AI Assistant:</p>
                <p>Hi! How can I help you?</p>
            </div>
        </div>
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center">
                <textarea id="user-message-popup" placeholder="Type your message..." rows="2" class="flex-grow px-4 py-2 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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
            <h3 class="text-xl font-bold text-dark">Loading EduConnect</h3>
            <p class="text-gray-600 mt-2">Please wait a moment...</p>
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
                div.innerHTML = `<p class="font-medium">Ã°Å¸â€˜Â¤ Anda:</p><p>${message}</p>`;
            } else {
                div.classList.add('mr-auto');
                div.innerHTML = `<p class="font-medium">Ã°Å¸Â¤â€“ AI Assistant:</p><p>${message}</p>`;
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
