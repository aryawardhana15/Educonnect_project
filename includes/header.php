<?php
session_start();
require_once('../config.php');

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - EduConnect' : 'EduConnect'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tambahkan CSS khusus halaman jika ada -->
    <?php if (isset($pageSpecificCSS)): ?>
        <link rel="stylesheet" href="<?php echo $pageSpecificCSS; ?>">
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo dan Judul -->
                <div class="flex items-center">
                    <a href="../index.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-indigo-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-900">EduConnect</span>
                    </a>
                </div>

                <!-- Menu utama (Desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="../index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600'; ?> px-3 py-2 font-medium">Beranda</a>
                    <a href="../kelas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kelas.php' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600'; ?> px-3 py-2 font-medium">Kelas</a>
                    <a href="../belanja.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'belanja.php' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600'; ?> px-3 py-2 font-medium">Belanja</a>
                </div>

                <!-- Ikon dan Login -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <div class="relative">
                            <button id="notification-button" class="text-gray-700 hover:text-indigo-600 text-xl relative">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge">3</span>
                            </button>
                            <!-- Dropdown notifikasi -->
                        </div>
                        <div class="dropdown relative">
                            <button class="flex items-center space-x-2 focus:outline-none">
                                <img src="<?php echo $_SESSION['photo'] ?? 'https://randomuser.me/api/portraits/men/32.jpg'; ?>" alt="User" class="w-8 h-8 rounded-full">
                                <span class="text-gray-700"><?php echo $_SESSION['name']; ?></span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                                <a href="../profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                                <a href="../riwayat.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Riwayat Belajar</a>
                                <a href="../pengaturan.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                                <div class="border-t border-gray-200"></div>
                                <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Keluar</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../includes/auth/login.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 font-medium">Masuk</a>
                        <a href="../includes/auth/register.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Daftar</a>
                    <?php endif; ?>
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
                <a href="../index.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Beranda</a>
                <a href="../kelas.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Kelas</a>
                <a href="../belanja.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Belanja</a>
                <?php if ($isLoggedIn): ?>
                    <a href="../profil.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Profil</a>
                    <a href="../logout.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Keluar</a>
                <?php else: ?>
                    <a href="../includes/auth/login.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Masuk</a>
                    <a href="../includes/auth/register.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>