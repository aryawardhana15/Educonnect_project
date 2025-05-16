<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$user = $auth->getCurrentUser();
$db = db(); // Inisialisasi koneksi database

// Cek apakah user adalah siswa
if ($user['role'] !== 'student') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - EduConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                    },
                    animation: {
                        'bounce-slow': 'bounce 3s infinite',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                    },
                    keyframes: {
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
      .sidebar {
            transition: all 0.3s ease;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .active-nav {
            background-color: #e0f2fe;
            color: #0369a1;
            border-left: 4px solid #0ea5e9;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e0f2fe;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #0ea5e9;
            transition: width 0.5s ease;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f00;
            opacity: 0;
        }
        .avatar-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.3);
        }
        .emoji-float {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0;
            z-index: 10;
        }
        /* Navbar-specific styles */
        .mobile-menu {
            transition: all 0.3s ease;
        }
        .mobile-menu-hidden {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        .mobile-menu-visible {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .user-menu {
            transition: all 0.2s ease;
        }
        .user-menu-hidden {
            transform: translateY(-10px);
            opacity: 0;
            pointer-events: none;
        }
        .user-menu-visible {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
     
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e0f2fe;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #0ea5e9;
            transition: width 0.5s ease;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f00;
            opacity: 0;
        }
        .avatar-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.3);
        }
        .emoji-float {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Top Navigation (unchanged as requested) -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo dan Brand -->
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="text-xl sm:text-2xl font-bold flex items-center" aria-label="EduConnect Home">
                        <i class="fas fa-graduation-cap mr-2 text-primary-200"></i>
                        EduConnect
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="kelas.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 transition-colors duration-200" aria-label="Kelas">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="mission.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 transition-colors duration-200" aria-label="Misi">
                        <i class="fas fa-tasks"></i>
                        <span>Misi</span>
                    </a>
                    <a href="community.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 transition-colors duration-200" aria-label="Komunitas">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php
                            if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                            elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                            else echo 'dashboardstudent.php';
                        ?>" class="font-semibold hover:text-primary-200 flex items-center space-x-1 transition-colors duration-200" aria-label="Dashboard">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User Menu -->
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 text-white hover:text-primary-200 focus:outline-none" aria-haspopup="true" aria-expanded="false" aria-label="User menu">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden xl:inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="user-menu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 user-menu user-menu-hidden z-50">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 flex items-center">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/dashboardadmin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 flex items-center">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr class="my-1 border-gray-200">
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="/auth/login.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 transition-colors duration-200" aria-label="Login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-primary-200 focus:outline-none" aria-label="Toggle mobile menu" aria-expanded="false">
                        <i id="menu-icon" class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="lg:hidden bg-primary-700 border-t border-primary-600 mobile-menu mobile-menu-hidden shadow-lg">
            <div class="px-4 pt-4 pb-6 space-y-2 max-h-[calc(100vh-80px)] overflow-y-auto">
                <a href="kelas.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-graduation-cap mr-3"></i>Kelas
                </a>
                <a href="mission.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-tasks mr-3"></i>Misi
                </a>
                <a href="community.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-users mr-3"></i>Komunitas
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php
                    if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                    elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                    else echo 'dashboardstudent.php';
                ?>" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-th-large mr-3"></i>Dashboard
                </a>
                <hr class="my-3 border-primary-600">
                <div class="px-4 py-2 text-sm text-primary-200 font-semibold flex items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
                <a href="profile.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-user mr-3"></i>Profil
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/dashboardadmin.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-cog mr-3"></i>Admin Panel
                </a>
                <?php endif; ?>
                <a href="/auth/logout.php" class="block px-4 py-3 rounded-lg text-base font-medium text-red-300 hover:bg-red-600 hover:text-white hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a superpower: </a>
                <?php else: ?>
                <a href="/auth/login.php" class="block px-4 py-3 rounded-lg text-base font-medium text-white hover:bg-primary-600 hover:shadow-md transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-in-alt mr-3"></i>Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Floating Emojis (hidden by default, will be shown with JS) -->
    <div id="emoji-container"></div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar -->
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="p-6 text-center relative">
                        <!-- Animated avatar with hover effect -->
                        <div class="relative inline-block group">
                            <img class="h-24 w-24 rounded-full mx-auto border-4 border-primary-100 avatar-hover transition-all duration-300" 
                                 src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                            <!-- Cute crown for top students -->
                            <?php if ($user['points'] > 500): ?>
                                <div class="absolute -top-2 -right-2 text-yellow-400 text-2xl animate-bounce-slow">
                                    <i class="fas fa-crown"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-sm text-primary-600 font-medium flex items-center justify-center">
                            <span class="inline-block mr-1">Siswa</span>
                            <i class="fas fa-graduation-cap text-primary-400"></i>
                        </p>
                        
                        <!-- Animated edit profile button -->
                        <div class="mt-6">
                            <a href="profile.php" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:shadow-md">
                                <i class="fas fa-user-edit mr-2 text-primary-500"></i> Edit Profil
                            </a>
                        </div>
                    </div>
                    
                    <!-- Stats with cute icons -->
                    <div class="border-t border-gray-200 px-4 py-5">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-4 flex items-center">
                            <i class="fas fa-chart-line mr-2 text-primary-400"></i> Statistik
                        </h4>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-600 flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i> Poin
                                    </span>
                                    <span class="font-bold text-primary-600"><?php echo $user['points']; ?></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(($user['points'] / 1000) * 100, 100); ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php if (($user['points'] / 1000) * 100 < 100): ?>
                                        <?php echo (1000 - $user['points']); ?> poin menuju level berikutnya!
                                    <?php else: ?>
                                        Level maksimal! 🎉
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-600 flex items-center">
                                        <i class="fas fa-bolt text-green-400 mr-1"></i> Pengalaman
                                    </span>
                                    <span class="font-bold text-green-600"><?php echo $user['experience']; ?> XP</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(($user['experience'] / 5000) * 100, 100); ?>%; background-color: #10b981;"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php if ((5000 - $user['experience']) > 0): ?>
                                        <?php echo (5000 - $user['experience']); ?> XP lagi untuk naik level!
                                    <?php else: ?>
                                        Kamu hebat! 🚀
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation with cute hover effects -->
                    <div class="border-t border-gray-200 px-4 py-5">
                        <div class="space-y-1">
                            <a href="dashboardstudent.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md active-nav transition-all duration-200">
                                <i class="fas fa-tachometer-alt mr-3 text-primary-600"></i>
                                <span class="truncate">Dashboard</span>
                                <i class="fas fa-arrow-right ml-auto text-primary-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                            <a href="kelas.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all duration-200">
                                <i class="fas fa-book-open mr-3 text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                                <span class="truncate">Kelas Saya</span>
                                <i class="fas fa-arrow-right ml-auto text-primary-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                            <a href="mission.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all duration-200">
                                <i class="fas fa-tasks mr-3 text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                                <span class="truncate">Misi Saya</span>
                                <i class="fas fa-arrow-right ml-auto text-primary-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                            <a href="community.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all duration-200">
                                <i class="fas fa-users mr-3 text-gray-400 group-hover:text-primary-500 transition-colors duration-200"></i>
                                <span class="truncate">Komunitas</span>
                                <i class="fas fa-arrow-right ml-auto text-primary-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Welcome Banner with animation -->
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl p-6 mb-6 text-white relative overflow-hidden">
                    <!-- Floating shapes in background -->
                    <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
                        <div class="absolute top-10 left-20 w-16 h-16 rounded-full bg-white opacity-10 animate-floating" style="animation-delay: 0s;"></div>
                        <div class="absolute top-5 right-20 w-10 h-10 rounded-full bg-white opacity-10 animate-floating" style="animation-delay: 0.5s;"></div>
                        <div class="absolute bottom-5 left-1/4 w-12 h-12 rounded-full bg-white opacity-10 animate-floating" style="animation-delay: 1s;"></div>
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between relative z-10">
                        <div>
                            <h2 class="text-2xl font-bold mb-2 animate__animated animate__fadeInDown">Halo, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</h2>
                            <p class="opacity-90 animate__animated animate__fadeIn animate__delay-1s">Apa yang ingin kamu pelajari hari ini?</p>
                        </div>
                        <div class="mt-4 md:mt-0 animate__animated animate__fadeInRight animate__delay-1s">
                            <a href="kelas.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-primary-600 bg-white hover:bg-gray-50 hover:shadow-md transition-all duration-300">
                                <i class="fas fa-search mr-2"></i> Jelajahi Kelas
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Kelas Saya Section -->
                <div class="bg-white shadow rounded-xl overflow-hidden mb-6 border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-primary-100 mr-3">
                                    <i class="fas fa-book-open text-primary-600"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Kelas Saya
                                </h3>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <a href="kelas.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:shadow-md">
                                    <i class="fas fa-plus-circle mr-2"></i> Cari Kelas Baru
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden">
                        <?php
                        $stmt = $db->prepare("
                            SELECT c.*, u.full_name as mentor_name 
                            FROM courses c 
                            JOIN user_courses uc ON c.id = uc.course_id 
                            JOIN users u ON c.mentor_id = u.id 
                            WHERE uc.user_id = ?
                        ");
                        $stmt->execute([$user['id']]);
                        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($courses)): ?>
                            <div class="p-12 text-center">
                                <div class="mx-auto w-24 h-24 bg-primary-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-book-open text-4xl text-primary-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Anda belum mengikuti kelas apapun</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menjelajahi kelas yang tersedia.</p>
                                <div class="mt-6">
                                    <a href="kelas.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:shadow-md">
                                        <i class="fas fa-search mr-2"></i> Jelajahi Kelas
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                                <?php foreach ($courses as $course): ?>
                                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm card-hover relative">
                                        <!-- Popular badge -->
                                        <?php if (rand(0, 1)): ?>
                                            <div class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full flex items-center">
                                                <i class="fas fa-fire mr-1"></i> Populer
                                            </div>
                                        <?php endif; ?>
                                        
                                        <img class="w-full h-40 object-cover" src="<?php echo $course['image'] ?? 'assets/images/default-course.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                        <div class="p-4">
                                            <h4 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h4>
                                            <p class="text-gray-600 text-sm mb-4 flex items-center">
                                                <i class="fas fa-chalkboard-teacher text-primary-500 mr-2"></i> 
                                                <?php echo htmlspecialchars($course['mentor_name']); ?>
                                            </p>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <i class="fas fa-users mr-1 text-gray-400"></i>
                                                    <?php echo rand(50, 200); ?> Siswa
                                                </div>
                                                <a href="course.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 transition-all duration-300">
                                                    Lanjutkan Belajar <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Misi Aktif Section -->
                <div class="bg-white shadow rounded-xl overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-yellow-100 mr-3">
                                    <i class="fas fa-tasks text-yellow-600"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Misi Aktif
                                </h3>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <a href="mission.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:shadow-md">
                                    <i class="fas fa-plus-circle mr-2"></i> Lihat Semua Misi
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden">
                        <?php
                        $stmt = $db->prepare("
                            SELECT m.*, um.status 
                            FROM missions m 
                            JOIN user_missions um ON m.id = um.mission_id 
                            WHERE um.user_id = ? AND um.status != 'completed'
                        ");
                        $stmt->execute([$user['id']]);
                        $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($missions)): ?>
                            <div class="p-12 text-center">
                                <div class="mx-auto w-24 h-24 bg-yellow-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-tasks text-4xl text-yellow-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Tidak ada misi aktif</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menjelajahi misi yang tersedia.</p>
                                <div class="mt-6">
                                    <a href="mission.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 hover:shadow-md">
                                        <i class="fas fa-search mr-2"></i> Jelajahi Misi
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($missions as $mission): ?>
                                    <li class="px-6 py-4 hover:bg-gray-50 transition duration-150 ease-in-out group">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center">
                                                    <?php if ($mission['status'] === 'in_progress'): ?>
                                                        <i class="fas fa-spinner text-yellow-500 mr-2 animate-spin"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-hourglass-half text-blue-500 mr-2"></i>
                                                    <?php endif; ?>
                                                    <p class="text-sm font-medium text-primary-600 truncate">
                                                        <?php echo htmlspecialchars($mission['title']); ?>
                                                    </p>
                                                </div>
                                                <p class="text-sm text-gray-500 truncate pl-6">
                                                    <?php echo htmlspecialchars($mission['description']); ?>
                                                </p>
                                            </div>
                                            <div class="ml-4 flex-shrink-0 flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $mission['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $mission['status'])); ?>
                                                </span>
                                                <a href="mission.php?id=<?php echo $mission['id']; ?>" class="ml-2 inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 group-hover:bg-primary-50 group-hover:text-primary-600">
                                                    Lihat <i class="fas fa-chevron-right ml-1 text-xs transition-transform duration-300 group-hover:translate-x-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Motivational Quote -->
                <div class="mt-6 bg-gradient-to-r from-purple-100 to-blue-100 rounded-xl p-6 text-center border border-purple-200">
                    <div class="max-w-2xl mx-auto">
                        <i class="fas fa-quote-left text-purple-300 text-2xl mb-2"></i>
                        <p class="text-lg font-medium text-gray-800">Belajar hari ini adalah investasi untuk kesuksesanmu besok!</p>
                        <div class="mt-4">
                            <button id="motivation-btn" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-full shadow-sm text-purple-700 bg-white hover:bg-purple-50 transition-all duration-300">
                                <i class="fas fa-redo mr-1"></i> Motivasi Lainnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
          // Navbar functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');

        // Toggle mobile menu
        mobileMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !mobileMenu.classList.contains('mobile-menu-hidden');
            mobileMenu.classList.toggle('mobile-menu-hidden', isOpen);
            mobileMenu.classList.toggle('mobile-menu-visible', !isOpen);
            menuIcon.className = isOpen ? 'fas fa-bars text-xl' : 'fas fa-times text-xl';
            mobileMenuButton.setAttribute('aria-expanded', !isOpen);
            if (userMenu) {
                userMenu.classList.add('user-menu-hidden');
                userMenu.classList.remove('user-menu-visible');
                userMenuButton.setAttribute('aria-expanded', false);
            }
        });

        // Toggle user menu
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !userMenu.classList.contains('user-menu-hidden');
                userMenu.classList.toggle('user-menu-hidden', isOpen);
                userMenu.classList.toggle('user-menu-visible', !isOpen);
                userMenuButton.setAttribute('aria-expanded', !isOpen);
                userMenuButton.querySelector('.fa-chevron-down').style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                if (!isOpen) {
                    mobileMenu.classList.add('mobile-menu-hidden');
                    mobileMenu.classList.remove('mobile-menu-visible');
                    menuIcon.className = 'fas fa-bars text-xl';
                    mobileMenuButton.setAttribute('aria-expanded', false);
                }
            });
        }

        // Close menus on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#mobile-menu-button') && !e.target.closest('#mobile-menu')) {
                mobileMenu.classList.add('mobile-menu-hidden');
                mobileMenu.classList.remove('mobile-menu-visible');
                menuIcon.className = 'fas fa-bars text-xl';
                mobileMenuButton.setAttribute('aria-expanded', false);
            }
            if (userMenu && !e.target.closest('#user-menu-button') && !e.target.closest('#user-menu')) {
                userMenu.classList.add('user-menu-hidden');
                userMenu.classList.remove('user-menu-visible');
                userMenuButton.setAttribute('aria-expanded', false);
                userMenuButton.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
            }
        });

        // Prevent clicks within menus from closing them
        mobileMenu.addEventListener('click', (e) => e.stopPropagation());
        if (userMenu) {
            userMenu.addEventListener('click', (e) => e.stopPropagation());
        }

        // Motivational quotes
        const quotes = [
            "Belajar bukan hanya untuk sekolah, tapi untuk hidup!",
            "Setiap ahli pernah menjadi pemula. Mulailah sekarang!",
            "Kegagalan adalah kesempatan untuk memulai lagi dengan lebih cerdas.",
            "Pengetahuan adalah kekuatan. Belajar adalah kekuatan supermu!",
            "Jangan berhenti sampai kamu bangga dengan apa yang telah kamu pelajari.",
            "Proses belajar adalah perjalanan, bukan tujuan.",
            "Semakin banyak kamu baca, semakin banyak yang kamu ketahui. Semakin banyak kamu belajar, semakin banyak tempat yang bisa kamu kunjungi!",
            "Pendidikan adalah paspor untuk masa depan."
        ];
        
        const motivationBtn = document.getElementById('motivation-btn');
        const quoteElement = document.querySelector('.text-gray-800');
        
        if (motivationBtn && quoteElement) {
            motivationBtn.addEventListener('click', function() {
                const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
                quoteElement.textContent = randomQuote;
                
                // Create floating emojis
                createFloatingEmojis(['🌟', '✨', '💡', '📚', '🎯'], 10);
            });
        }
        
        // Create floating emojis effect
        function createFloatingEmojis(emojis, count) {
            const container = document.getElementById('emoji-container');
            if (!container) return;
            
            // Clear previous emojis
            container.innerHTML = '';
            
            for (let i = 0; i < count; i++) {
                const emoji = document.createElement('div');
                emoji.className = 'emoji-float';
                emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                
                // Random position
                const x = Math.random() * window.innerWidth;
                const y = window.innerHeight + 50;
                
                emoji.style.left = `${x}px`;
                emoji.style.top = `${y}px`;
                
                container.appendChild(emoji);
                
                // Animate
                setTimeout(() => {
                    emoji.style.opacity = '1';
                    emoji.style.transform = `translateY(-${Math.random() * 300 + 200}px) rotate(${Math.random() * 360}deg)`;
                    emoji.style.transition = `all ${Math.random() * 3 + 2}s ease-out`;
                    
                    // Remove after animation
                    setTimeout(() => {
                        emoji.remove();
                    }, 3000);
                }, 100);
            }
        }
        
        // Add some emojis when page loads
        window.addEventListener('load', function() {
            setTimeout(() => {
                createFloatingEmojis(['👋', '😊', '🎓', '📖'], 5);
            }, 1000);
        });
    </script>
    </script>
</body>
</html>