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

// Cek apakah user adalah mentor
if ($user['role'] !== 'mentor') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mentor - EduConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .active-nav {
            background-color: #e0f2fe;
            color: #0369a1;
            border-left: 4px solid #0ea5e9;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden text-gray-500 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-primary-600">EduConnect</span>
                    </div>
                </div>
                
                <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                    <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-home mr-1"></i> Landing Page
                    </a>
                    <a href="dashboardmentor.php" class="px-3 py-2 rounded-md text-sm font-medium text-primary-600 bg-primary-50">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    <a href="mentor_classes.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-book-open mr-1"></i> Kelas Saya
                    </a>
                </div>
                
                <div class="ml-4 flex items-center md:ml-6">
                    <!-- Profile dropdown -->
                    <div class="ml-3 relative">
                        <div>
                            <button id="user-menu-button" class="max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <img class="h-8 w-8 rounded-full" src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                                <span class="ml-2 hidden md:inline text-sm font-medium text-gray-700"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs text-gray-500"></i>
                            </button>
                        </div>
                        <!-- Dropdown menu -->
                        <div id="user-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                            <a href="dashboardmentor.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profil
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                    <i class="fas fa-home mr-2"></i> Landing Page
                </a>
                <a href="dashboardmentor.php" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 bg-primary-50">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="mentor_classes.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                    <i class="fas fa-book-open mr-2"></i> Kelas Saya
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar -->
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 text-center">
                        <img class="h-24 w-24 rounded-full mx-auto border-4 border-primary-100" src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                        <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-sm text-primary-600 font-medium">Mentor</p>
                        
                        <div class="mt-6 space-y-2">
                            <a href="profile.php" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-user-edit mr-2"></i> Edit Profil
                            </a>
                            <a href="create_course.php" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-plus-circle mr-2"></i> Buat Kelas Baru
                            </a>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 px-4 py-5">
                        <div class="space-y-1">
                            <a href="dashboardmentor.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md active-nav">
                                <i class="fas fa-tachometer-alt mr-3 text-primary-600"></i>
                                <span class="truncate">Dashboard</span>
                            </a>
                            <a href="mentor_classes.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                                <i class="fas fa-book-open mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                <span class="truncate">Kelas Saya</span>
                            </a>
                            <a href="create_course.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                                <i class="fas fa-plus-circle mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                <span class="truncate">Buat Kelas</span>
                            </a>
                            <a href="create_mission.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                                <i class="fas fa-tasks mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                <span class="truncate">Buat Misi</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl p-6 mb-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
                            <p class="opacity-90">Lihat statistik dan kelola kelas Anda dari dashboard ini.</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="create_course.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-primary-600 bg-white hover:bg-gray-50">
                                <i class="fas fa-plus-circle mr-2"></i> Buat Kelas Baru
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <?php
                    // Total Kelas
                    $stmt = $db->prepare("SELECT COUNT(*) FROM courses WHERE mentor_id = ?");
                    $stmt->execute([$user['id']]);
                    $total_courses = $stmt->fetchColumn();
                    
                    // Total Siswa
                    $stmt = $db->prepare("
                        SELECT COUNT(DISTINCT uc.user_id)
                        FROM user_courses uc
                        JOIN courses c ON uc.course_id = c.id
                        WHERE c.mentor_id = ?
                    ");
                    $stmt->execute([$user['id']]);
                    $total_students = $stmt->fetchColumn();
                    
                    // Total Misi
                    $stmt = $db->prepare("SELECT COUNT(*) FROM missions WHERE mentor_id = ?");
                    $stmt->execute([$user['id']]);
                    $total_missions = $stmt->fetchColumn();
                    ?>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                                    <i class="fas fa-book-open text-white text-xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Kelas</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900"><?php echo $total_courses; ?></div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900"><?php echo $total_students; ?></div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <i class="fas fa-tasks text-white text-xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Misi</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900"><?php echo $total_missions; ?></div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kelas Saya Section -->
                <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    <i class="fas fa-book-open mr-2 text-primary-600"></i> Kelas Saya
                                </h3>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <a href="create_course.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-plus-circle mr-2"></i> Buat Kelas Baru
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden">
                        <?php
                        $stmt = $db->prepare("SELECT * FROM courses WHERE mentor_id = ?");
                        $stmt->execute([$user['id']]);
                        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($courses)): ?>
                            <div class="p-12 text-center">
                                <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900">Anda belum membuat kelas apapun</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat kelas pertama Anda.</p>
                                <div class="mt-6">
                                    <a href="create_course.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        <i class="fas fa-plus-circle mr-2"></i> Buat Kelas Baru
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                                <?php foreach ($courses as $course): 
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM user_courses WHERE course_id = ?");
                                    $stmt->execute([$course['id']]);
                                    $student_count = $stmt->fetchColumn();
                                ?>
                                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm card-hover">
                                        <img class="w-full h-40 object-cover" src="<?php echo $course['image'] ?? 'assets/images/default-course.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                        <div class="p-4">
                                            <h4 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h4>
                                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($course['description']); ?></p>
                                            <div class="flex justify-between items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-users mr-1"></i> <?php echo $student_count; ?> Siswa
                                                </span>
                                                <a href="course.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700">
                                                    Kelola Kelas <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Misi yang Dibuat Section -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    <i class="fas fa-tasks mr-2 text-primary-600"></i> Misi yang Dibuat
                                </h3>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <a href="create_mission.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-plus-circle mr-2"></i> Buat Misi Baru
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden">
                        <?php
                        $stmt = $db->prepare("SELECT * FROM missions WHERE mentor_id = ?");
                        $stmt->execute([$user['id']]);
                        $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($missions)): ?>
                            <div class="p-12 text-center">
                                <i class="fas fa-tasks text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900">Anda belum membuat misi apapun</h3>
                                <p class="mt-1 text-sm text-gray-500">Buat misi untuk memberikan tantangan kepada siswa Anda.</p>
                                <div class="mt-6">
                                    <a href="create_mission.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        <i class="fas fa-plus-circle mr-2"></i> Buat Misi Baru
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($missions as $mission): ?>
                                    <li class="px-6 py-4 hover:bg-gray-50 transition duration-150 ease-in-out">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-primary-600 truncate">
                                                    <?php echo htmlspecialchars($mission['title']); ?>
                                                </p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    <?php echo htmlspecialchars($mission['description']); ?>
                                                </p>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <a href="mission.php?id=<?php echo $mission['id']; ?>" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                    Lihat <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // User menu toggle
        document.getElementById('user-menu-button').addEventListener('click', function() {
            document.getElementById('user-menu').classList.toggle('hidden');
        });

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#user-menu-button') && !event.target.closest('#user-menu')) {
                document.getElementById('user-menu').classList.add('hidden');
            }
            if (!event.target.closest('#mobile-menu-button') && !event.target.closest('#mobile-menu')) {
                document.getElementById('mobile-menu').classList.add('hidden');
            }
        });
    </script>
</body>
</html>