<?php
// kelas.php
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Anda - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        
        .tab-active {
            border-bottom: 3px solid #4F46E5;
            color: #4F46E5;
            font-weight: 600;
        }
        
        .course-card {
            transition: all 0.3s ease;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .price-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .swiper {
            width: 100%;
            padding: 20px 0 40px !important;
        }
        
        .swiper-slide {
            width: auto !important;
        }
        
        .swiper-pagination-bullet-active {
            background-color: #4F46E5 !important;
        }
        
        .category-chip {
            transition: all 0.2s ease;
        }
        
        .category-chip:hover {
            background-color: #EEF2FF;
            color: #4F46E5;
        }
        
        .category-chip.active {
            background-color: #4F46E5;
            color: white;
        }
        
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s ease;
        }
        
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .skeleton {
            background-color: #e2e8f0;
            background-image: linear-gradient(90deg, #e2e8f0, #f1f5f9, #e2e8f0);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
        
        .progress-ring {
            position: relative;
            width: 60px;
            height: 60px;
        }
        
        .progress-ring__circle {
            stroke: #4F46E5;
            fill: transparent;
            stroke-width: 6;
            stroke-linecap: round;
        }
        
        .progress-ring__bg {
            stroke: #E5E7EB;
            fill: transparent;
            stroke-width: 6;
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            font-weight: bold;
            color: #4F46E5;
        }
        
        .floating-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 50;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #EF4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo dan Judul -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-indigo-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-900">EduConnect</span>
                    </div>
                </div>

                <!-- Menu utama (Desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 font-medium">Beranda</a>
                    <a href="index.php#features" class="text-gray-700 hover:text-indigo-600 px-3 py-2 font-medium">Fitur</a>
                    <a href="index.php#bootcamp" class="text-gray-700 hover:text-indigo-600 px-3 py-2 font-medium">Bootcamp</a>
                    <a href="kelas.php" class="text-indigo-600 px-3 py-2 font-medium border-b-2 border-indigo-600">Kelas Anda</a>
                </div>

                <!-- Ikon Keranjang dan Login -->
                <div class="hidden md:flex items-center space-x-6">
                    <div class="relative">
                        <button id="notification-button" class="text-gray-700 hover:text-indigo-600 text-xl relative">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div id="notification-dropdown" class="dropdown-menu absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900">Notifikasi</h3>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Pembelajaran baru tersedia</p>
                                            <p class="text-gray-500">Materi Matematika Kelas 5 telah diperbarui</p>
                                            <p class="text-xs text-gray-400 mt-1">2 jam yang lalu</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Jadwal belajar besok</p>
                                            <p class="text-gray-500">Anda memiliki jadwal belajar Bahasa Inggris pukul 15.00</p>
                                            <p class="text-xs text-gray-400 mt-1">Kemarin</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                                            <i class="fas fa-gift"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Promo spesial untuk Anda</p>
                                            <p class="text-gray-500">Diskon 30% untuk semua kelas SMA selama bulan ini</p>
                                            <p class="text-xs text-gray-400 mt-1">2 hari yang lalu</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 bg-gray-50 text-center">
                                <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Lihat semua notifikasi</a>
                            </div>
                        </div>
                    </div>
                    <a href="belanja.php" class="text-gray-700 hover:text-indigo-600 text-xl relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="notification-badge">1</span>
                    </a>
                    <div class="dropdown relative">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-8 h-8 rounded-full">
                            <span class="text-gray-700">John Doe</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                            <a href="riwayat.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Riwayat Belajar</a>
                            <a href="pengaturan.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                            <div class="border-t border-gray-200"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Keluar</a>
                        </div>
                    </div>
                </div>

                <!-- Tombol menu untuk mobile -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" class="p-2 rounded-md text-gray-700 hover:text-indigo-600 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white shadow-lg">
            <div class="px-2 pt-2 pb-4 space-y-1 sm:px-3">
                <a href="index.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Beranda</a>
                <a href="index.php#features" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Fitur</a>
                <a href="index.php#bootcamp" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Bootcamp</a>
                <a href="kelas.php" class="block px-3 py-2 text-base font-medium text-indigo-600 bg-indigo-50 rounded-md">Kelas Anda</a>
                <a href="belanja.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">
                    <i class="fas fa-shopping-cart mr-2"></i>Belanja
                </a>
                <a href="profil.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">
                    <i class="fas fa-user-circle mr-2"></i>Profil
                </a>
                <a href="logout.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">
                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-indigo-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold mb-4">Kelas Anda</h1>
                <p class="text-xl opacity-90">Lanjutkan pembelajaran Anda atau temukan kelas baru untuk meningkatkan pengetahuan</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8 overflow-x-auto pb-2">
                <button id="all-tab" class="tab-active py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                    Semua Kelas
                </button>
                <button id="sd-tab" class="text-gray-500 py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-gray-700">
                    SD
                </button>
                <button id="smp-tab" class="text-gray-500 py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-gray-700">
                    SMP
                </button>
                <button id="sma-tab" class="text-gray-500 py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-gray-700">
                    SMA
                </button>
                <button id="bootcamp-tab" class="text-gray-500 py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-gray-700">
                    Bootcamp
                </button>
                <button id="favorite-tab" class="text-gray-500 py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-gray-700">
                    <i class="fas fa-heart mr-1"></i>Favorit
                </button>
            </nav>
        </div>

        <!-- Search and Filter -->
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <div class="relative w-full md:w-1/3">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="search-input" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Cari kelas...">
            </div>
            <div class="flex items-center space-x-4">
                <div class="dropdown relative">
                    <button class="flex items-center space-x-2 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        <i class="fas fa-filter text-gray-500"></i>
                        <span>Filter</span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    <div class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900">Filter Kelas</h3>
                        </div>
                        <div class="px-4 py-2">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="text-sm text-gray-700">Kelas Aktif</span>
                            </label>
                            <label class="flex items-center space-x-2 mt-2">
                                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Kelas Selesai</span>
                            </label>
                            <label class="flex items-center space-x-2 mt-2">
                                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="text-sm text-gray-700">Kelas Berbayar</span>
                            </label>
                            <label class="flex items-center space-x-2 mt-2">
                                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                                <span class="text-sm text-gray-700">Kelas Gratis</span>
                            </label>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-200">
                            <button class="w-full bg-indigo-600 text-white py-1 px-3 rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="dropdown relative">
                    <button class="flex items-center space-x-2 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        <i class="fas fa-sort-amount-down text-gray-500"></i>
                        <span>Urutkan</span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Terbaru</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Populer</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">A-Z</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Z-A</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Progress Tertinggi</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Chips -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-2">
                <button class="category-chip active px-4 py-2 rounded-full text-sm font-medium bg-indigo-600 text-white">
                    Semua Kategori
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Matematika
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    IPA
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Bahasa Inggris
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Fisika
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Kimia
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Biologi
                </button>
                <button class="category-chip px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-700 border border-gray-300">
                    Pemrograman
                </button>
            </div>
        </div>

        <!-- Kelas Aktif Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Kelas yang Sedang Diikuti</h2>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    Lihat Semua <i class="fas fa-chevron-right ml-1 text-sm"></i>
                </a>
            </div>
            
            <!-- Swiper for Active Courses -->
            <div class="swiper active-courses-swiper">
                <div class="swiper-wrapper">
                    <!-- Kelas Aktif 1 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Matematika SD" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">SD</span>
                                    <h3 class="text-white font-bold text-lg mt-2">Matematika Dasar Kelas 4-6</h3>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Mentor" class="w-8 h-8 rounded-full mr-2">
                                        <span class="text-sm text-gray-600">Bu Siti</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.8</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>65%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 65%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between items-center">
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                        Lanjutkan
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500">Terakhir dibuka: 2 hari lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Aktif 2 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1546410531-bb4caa6b424d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bahasa Inggris SMP" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMP</span>
                                    <h3 class="text-white font-bold text-lg mt-2">Bahasa Inggris Kelas 8</h3>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="fas fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Mentor" class="w-8 h-8 rounded-full mr-2">
                                        <span class="text-sm text-gray-600">Pak Andi</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.9</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>42%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-500 h-2.5 rounded-full" style="width: 42%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between items-center">
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                        Lanjutkan
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500">Terakhir dibuka: 1 minggu lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Aktif 3 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Fisika SMA" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMA</span>
                                    <h3 class="text-white font-bold text-lg mt-2">Fisika Dasar Kelas 10</h3>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Mentor" class="w-8 h-8 rounded-full mr-2">
                                        <span class="text-sm text-gray-600">Pak Budi</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.7</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>78%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-purple-500 h-2.5 rounded-full" style="width: 78%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between items-center">
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                        Lanjutkan
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500">Terakhir dibuka: Kemarin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Aktif 4 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="IPA SD" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">SD</span>
                                    <h3 class="text-white font-bold text-lg mt-2">IPA Kelas 5 - Materi Dasar</h3>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Mentor" class="w-8 h-8 rounded-full mr-2">
                                        <span class="text-sm text-gray-600">Bu Rina</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.6</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>35%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 35%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between items-center">
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                        Lanjutkan
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500">Terakhir dibuka: 5 hari lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Aktif 5 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Matematika SMP" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMP</span>
                                    <h3 class="text-white font-bold text-lg mt-2">Matematika SMP Kelas 7</h3>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="fas fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Mentor" class="w-8 h-8 rounded-full mr-2">
                                        <span class="text-sm text-gray-600">Pak Joko</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.5</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>90%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-500 h-2.5 rounded-full" style="width: 90%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between items-center">
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                        Lanjutkan
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500">Terakhir dibuka: Hari ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- Rekomendasi Kelas Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Rekomendasi Kelas untuk Anda</h2>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    Lihat Semua <i class="fas fa-chevron-right ml-1 text-sm"></i>
                </a>
            </div>
            
            <!-- Swiper for Recommended Courses -->
            <div class="swiper recommended-courses-swiper">
                <div class="swiper-wrapper">
                    <!-- Kelas Gratis 1 -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="IPA SD" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">SD</span>
                                </div>
                                <div class="price-tag bg-green-100 text-green-800">GRATIS</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">IPA Kelas 5 - Materi Dasar</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>12.500+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.8</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Gratis 2 -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Matematika SMP" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMP</span>
                                </div>
                                <div class="price-tag bg-green-100 text-green-800">GRATIS</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Matematika SMP Kelas 7</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>8.200+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.7</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Berbayar 1 -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Fisika SMA" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMA</span>
                                </div>
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 250.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Fisika Lanjutan Kelas 11</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>5.300+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.9</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Berbayar 2 (Kebumian) -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1462331940025-496dfbfc7564?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Kebumian" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMA</span>
                                </div>
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 350.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="fas fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Geologi Dasar & Kebumian</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>3.800+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.8</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Berbayar 3 (Kimia) -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Kimia" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMA</span>
                                </div>
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 300.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Kimia Organik Kelas 12</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>4.100+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.7</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Gratis 3 -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bahasa Indonesia" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">SD</span>
                                </div>
                                <div class="price-tag bg-green-100 text-green-800">GRATIS</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Bahasa Indonesia Kelas 6</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>6.700+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.6</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kelas Berbayar 4 -->
                    <div class="swiper-slide" style="width: 240px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Biologi" class="w-full h-40 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">SMA</span>
                                </div>
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 275.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Biologi Sel Kelas 11</h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>3.200+ siswa</span>
                                </div>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">4.8</span>
                                    </div>
                                    <a href="kelas-detail.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- Bootcamp Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Bootcamp Profesional</h2>
                <a href="index.php#bootcamp" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    Lihat Semua <i class="fas fa-chevron-right ml-1 text-sm"></i>
                </a>
            </div>
            
            <!-- Swiper for Bootcamps -->
            <div class="swiper bootcamp-swiper">
                <div class="swiper-wrapper">
                    <!-- Bootcamp 1 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bootcamp Pemrograman" class="w-full h-40 object-cover">
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 1.500.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Bootcamp Pemrograman Web</h3>
                                <p class="text-sm text-gray-600 mb-3">Pelajari HTML, CSS, JavaScript dan framework modern dalam 12 minggu</p>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-graduate mr-1 text-gray-600"></i>
                                        <span class="text-sm text-gray-600">1.200+ alumni</span>
                                    </div>
                                    <a href="belanja.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Beli Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bootcamp 2 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bootcamp Data Science" class="w-full h-40 object-cover">
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 2.000.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="fas fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Bootcamp Data Science</h3>
                                <p class="text-sm text-gray-600 mb-3">Pengenalan Python, Pandas, dan Machine Learning dasar</p>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-graduate mr-1 text-gray-600"></i>
                                        <span class="text-sm text-gray-600">850+ alumni</span>
                                    </div>
                                    <a href="belanja.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Beli Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bootcamp 3 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bootcamp Digital Marketing" class="w-full h-40 object-cover">
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 1.200.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Bootcamp Digital Marketing</h3>
                                <p class="text-sm text-gray-600 mb-3">Kuasi SEO, Social Media Marketing, dan Google Ads</p>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-graduate mr-1 text-gray-600"></i>
                                        <span class="text-sm text-gray-600">2.100+ alumni</span>
                                    </div>
                                    <a href="belanja.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Beli Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bootcamp 4 -->
                    <div class="swiper-slide" style="width: 320px;">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden course-card h-full flex flex-col">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Bootcamp UI/UX Design" class="w-full h-40 object-cover">
                                <div class="price-tag bg-yellow-100 text-yellow-800">Rp 1.800.000</div>
                                <div class="absolute top-2 right-2">
                                    <button class="text-white hover:text-red-500 focus:outline-none">
                                        <i class="far fa-heart text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="font-bold text-gray-800 mb-2">Bootcamp UI/UX Design</h3>
                                <p class="text-sm text-gray-600 mb-3">Pelajari Figma, User Research, dan Prototyping</p>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-graduate mr-1 text-gray-600"></i>
                                        <span class="text-sm text-gray-600">1.500+ alumni</span>
                                    </div>
                                    <a href="belanja.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Beli Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- Popular Teachers Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Guru & Mentor Populer</h2>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    Lihat Semua <i class="fas fa-chevron-right ml-1 text-sm"></i>
                </a>
            </div>
            
            <!-- Swiper for Teachers -->
            <div class="swiper teachers-swiper">
                <div class="swiper-wrapper">
                    <!-- Teacher 1 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Bu Siti" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Bu Siti</h3>
                            <p class="text-sm text-gray-600">Matematika</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.8</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher 2 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Pak Andi" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Pak Andi</h3>
                            <p class="text-sm text-gray-600">Bahasa Inggris</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.9</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher 3 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Pak Budi" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Pak Budi</h3>
                            <p class="text-sm text-gray-600">Fisika</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.7</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher 4 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Bu Rina" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Bu Rina</h3>
                            <p class="text-sm text-gray-600">IPA</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.6</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher 5 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Pak Joko" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Pak Joko</h3>
                            <p class="text-sm text-gray-600">Matematika</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.5</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher 6 -->
                    <div class="swiper-slide" style="width: 180px;">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Bu Ani" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-500">
                                <div class="absolute -bottom-1 -right-1 bg-indigo-500 rounded-full p-1">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800">Bu Ani</h3>
                            <p class="text-sm text-gray-600">Kimia</p>
                            <div class="flex items-center justify-center mt-1">
                                <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                <span class="text-xs text-gray-600">4.8</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="floating-button bg-indigo-600 text-white rounded-full w-14 h-14 flex items-center justify-center hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
        <i class="fas fa-headset text-xl"></i>
    </button>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-indigo-400 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </div>
                    <p class="text-gray-400">Platform pembelajaran inovatif untuk pemerataan pendidikan di daerah 3T di Indonesia.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Beranda</a></li>
                        <li><a href="kelas.php" class="text-gray-400 hover:text-white">Kelas Anda</a></li>
                        <li><a href="index.php#bootcamp" class="text-gray-400 hover:text-white">Bootcamp</a></li>
                        <li><a href="index.php#ai-chat" class="text-gray-400 hover:text-white">AI Assistant</a></li>
                        <li><a href="belanja.php" class="text-gray-400 hover:text-white">Belanja</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kebijakan</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Pengembalian</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Bantuan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Hubungi Kami</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <span class="text-gray-400">hello@educonnect.id</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-2 text-gray-400"></i>
                            <span class="text-gray-400">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span class="text-gray-400">Jakarta, Indonesia</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock mr-2 text-gray-400"></i>
                            <span class="text-gray-400">Senin-Jumat, 08:00-17:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>© 2025 EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Initialize all swipers
        const swipers = [
            {
                el: '.active-courses-swiper',
                config: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    pagination: {
                        el: '.active-courses-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                        },
                    }
                }
            },
            {
                el: '.recommended-courses-swiper',
                config: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                    pagination: {
                        el: '.recommended-courses-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 3,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 4,
                            spaceBetween: 30,
                        },
                    }
                }
            },
            {
                el: '.bootcamp-swiper',
                config: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    pagination: {
                        el: '.bootcamp-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                        },
                    }
                }
            },
            {
                el: '.teachers-swiper',
                config: {
                    slidesPerView: 3,
                    spaceBetween: 10,
                    pagination: {
                        el: '.teachers-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 4,
                            spaceBetween: 15,
                        },
                        1024: {
                            slidesPerView: 6,
                            spaceBetween: 20,
                        },
                    }
                }
            }
        ];

        // Initialize each swiper
        swipers.forEach(swiper => {
            new Swiper(swiper.el, swiper.config);
        });

        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Notification dropdown toggle
        document.getElementById('notification-button').addEventListener('click', function() {
            document.getElementById('notification-dropdown').classList.toggle('hidden');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown') && !event.target.closest('#notification-button')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
                document.getElementById('notification-dropdown').classList.add('hidden');
            }
        });

        // Tab functionality
        const tabs = ['all', 'sd', 'smp', 'sma', 'bootcamp', 'favorite'];
        tabs.forEach(tab => {
            document.getElementById(`${tab}-tab`).addEventListener('click', function() {
                // Update active tab
                tabs.forEach(t => {
                    const tabElement = document.getElementById(`${t}-tab`);
                    if (t === tab) {
                        tabElement.classList.add('tab-active', 'text-indigo-600', 'border-indigo-600');
                        tabElement.classList.remove('text-gray-500', 'border-transparent');
                    } else {
                        tabElement.classList.remove('tab-active', 'text-indigo-600', 'border-indigo-600');
                        tabElement.classList.add('text-gray-500', 'border-transparent');
                    }
                });
                
                // Here you would typically filter the courses based on the selected tab
                console.log(`Selected tab: ${tab}`);
                
                // Show loading skeleton
                document.querySelectorAll('.course-card').forEach(card => {
                    card.innerHTML = `
                        <div class="skeleton w-full h-48"></div>
                        <div class="p-4">
                            <div class="skeleton w-3/4 h-6 mb-2"></div>
                            <div class="skeleton w-1/2 h-4 mb-4"></div>
                            <div class="skeleton w-full h-2 mb-2"></div>
                            <div class="skeleton w-1/4 h-4"></div>
                        </div>
                    `;
                });
                
                // Simulate loading data
                setTimeout(() => {
                    // Replace with actual data loading logic
                    console.log(`Loading data for ${tab} tab...`);
                    // After data is loaded, update the UI
                }, 1000);
            });
        });

        // Category chips functionality
        document.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                if (this.classList.contains('active')) {
                    // If "All" is already active, do nothing
                    if (this.textContent.trim() === 'Semua Kategori') return;
                    
                    // Otherwise, activate "All" and deactivate others
                    document.querySelectorAll('.category-chip').forEach(c => {
                        c.classList.remove('active', 'bg-indigo-600', 'text-white');
                        c.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                    });
                    document.querySelector('.category-chip').classList.add('active', 'bg-indigo-600', 'text-white');
                } else {
                    // Deactivate "All" if it's active
                    if (document.querySelector('.category-chip').classList.contains('active')) {
                        document.querySelector('.category-chip').classList.remove('active', 'bg-indigo-600', 'text-white');
                        document.querySelector('.category-chip').classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                    }
                    
                    // Toggle active state for clicked chip
                    this.classList.toggle('active');
                    this.classList.toggle('bg-indigo-600');
                    this.classList.toggle('text-white');
                    this.classList.toggle('bg-white');
                    this.classList.toggle('text-gray-700');
                    this.classList.toggle('border');
                    this.classList.toggle('border-gray-300');
                }
                
                // Here you would filter courses by category
                const activeCategories = Array.from(document.querySelectorAll('.category-chip.active')).map(c => c.textContent.trim());
                console.log('Active categories:', activeCategories);
            });
        });

        // Search functionality
        const searchInput = document.getElementById('search-input');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                console.log('Searching for:', searchTerm);
                
                // Here you would implement the search functionality
                if (searchTerm.length > 0) {
                    // Show loading state
                    document.querySelectorAll('.course-card').forEach(card => {
                        card.innerHTML = `
                            <div class="skeleton w-full h-48"></div>
                            <div class="p-4">
                                <div class="skeleton w-3/4 h-6 mb-2"></div>
                                <div class="skeleton w-1/2 h-4 mb-4"></div>
                                <div class="skeleton w-full h-2 mb-2"></div>
                                <div class="skeleton w-1/4 h-4"></div>
                            </div>
                        `;
                    });
                    
                    // Simulate search
                    setTimeout(() => {
                        console.log('Displaying search results for:', searchTerm);
                        // Update UI with search results
                    }, 800);
                }
            }, 500);
        });

        // Favorite button functionality
        document.querySelectorAll('.course-card').forEach(card => {
            const favButton = card.querySelector('.fa-heart, .fa-heart');
            if (favButton) {
                favButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('fas');
                    this.classList.toggle('far');
                    this.classList.toggle('text-red-500');
                    
                    const courseTitle = card.querySelector('h3').textContent;
                    if (this.classList.contains('fas')) {
                        console.log(`Added ${courseTitle} to favorites`);
                    } else {
                        console.log(`Removed ${courseTitle} from favorites`);
                    }
                });
            }
        });

        // Progress rings animation
        document.querySelectorAll('.progress-ring').forEach(ring => {
            const circle = ring.querySelector('.progress-ring__circle');
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            const progressText = ring.querySelector('.progress-text');
            const progress = parseInt(progressText.textContent);
            
            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = circumference;
            
            const offset = circumference - (progress / 100) * circumference;
            circle.style.strokeDashoffset = offset;
            
            // Animate on scroll
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    circle.style.transition = 'stroke-dashoffset 1s ease-in-out';
                    circle.style.strokeDashoffset = offset;
                    observer.unobserve(ring);
                }
            }, { threshold: 0.5 });
            
            observer.observe(ring);
        });

        // Course card hover effect
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });

        // Floating button hover effect
        const floatingButton = document.querySelector('.floating-button');
        floatingButton.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        floatingButton.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
        
        floatingButton.addEventListener('click', function() {
            alert('Hubungi customer service kami di 1500-123 atau hello@educonnect.id');
        });
    </script>
</body>
</html>