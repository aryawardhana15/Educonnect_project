<?php
require_once('config.php');
require_once('db_connect.php');
require_once 'auth/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$db = db();
$role = $user['role'];

// Query untuk mengambil misi
$query = "
    SELECT m.*, u.full_name as mentor_name,
           CASE WHEN um.user_id IS NOT NULL THEN um.status ELSE 'not_started' END as user_status
    FROM missions m
    JOIN users u ON m.mentor_id = u.id
    LEFT JOIN user_missions um ON m.id = um.mission_id AND um.user_id = ?
    ORDER BY m.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil total poin siswa
$total_points = 0;
if ($role === 'student') {
    $stmt = $db->prepare("SELECT SUM(m.points) FROM user_missions um JOIN missions m ON um.mission_id = m.id WHERE um.user_id = ? AND um.status = 'completed'");
    $stmt->execute([$user['id']]);
    $total_points = (int) $stmt->fetchColumn();
}

// Ambil galeri hasil misi siswa
$gallery = [];
if ($role === 'student') {
    $stmt = $db->prepare("
        SELECT um.*, m.title as mission_title, m.points, u.full_name, u.profile_picture
        FROM user_missions um
        JOIN missions m ON um.mission_id = m.id
        JOIN users u ON um.user_id = u.id
        WHERE um.status = 'completed' AND um.submission IS NOT NULL AND um.submission != ''
        ORDER BY COALESCE(um.submitted_at, um.updated_at) DESC
        LIMIT 30
    ");
    $stmt->execute();
    $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* General Styles */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }

        /* Card Styles */
        .mission-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .mission-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .mission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            transition: all 0.3s ease;
        }
        .mission-card:hover::before {
            width: 8px;
        }

        /* Gallery Card */
        .gallery-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .gallery-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border-color: #a855f7;
        }
        .gallery-slider {
            -ms-overflow-style: none;
            scrollbar-width: none;
            touch-action: pan-x;
        }
        .gallery-slider::-webkit-scrollbar {
            display: none;
        }

        /* Header */
        .mission-header {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
            padding: 2rem;
        }

        /* Points Indicator */
        .point-indicator {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(118, 75, 162, 0.3);
            padding: 1.5rem;
        }

        /* Progress Bar */
        .progress-bar {
            height: 10px;
            border-radius: 5px;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        }

        /* Category Tags */
        .category-tag {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 8px;
            margin-bottom: 8px;
            background: rgba(168, 85, 247, 0.1);
            color: #a855f7;
        }

        /* Buttons */
        .action-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .action-button:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Floating Emojis */
        .floating-emoji {
            position: absolute;
            font-size: 2rem;
            opacity: 0.08;
            z-index: 0;
            animation: float 20s ease-in-out infinite;
        }
        .emoji-1 { top: 10%; left: 5%; }
        .emoji-2 { top: 30%; right: 5%; }
        .emoji-3 { bottom: 20%; left: 15%; }
        .emoji-4 { bottom: 10%; right: 10%; }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Mobile Adjustments */
        @media (max-width: 640px) {
            .mission-header {
                padding: 1.5rem;
            }
            .point-indicator {
                padding: 1rem;
            }
            .gallery-card {
                min-width: 240px;
            }
            .mission-card {
                padding: 1.25rem;
            }
            h1 {
                font-size: 1.75rem;
            }
            h2 {
                font-size: 1.25rem;
            }
            .category-tag {
                font-size: 0.65rem;
                padding: 4px 10px;
            }
        }
    </style>
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
                        success: {
                            100: '#d1fae5',
                            500: '#10b981',
                            700: '#047857'
                        },
                        warning: {
                            100: '#fef3c7',
                            500: '#f59e0b',
                            700: '#b45309'
                        },
                        danger: {
                            100: '#fee2e2',
                            500: '#ef4444',
                            700: '#b91c1c'
                        },
                        purple: {
                            100: '#e9d5ff',
                            500: '#a855f7',
                            700: '#7e22ce'
                        },
                        pink: {
                            100: '#fce7f3',
                            500: '#ec4899',
                            700: '#be185d'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="kelas.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="leaderboard.php" class="hover:text-gray-200">Leaderboard</a>
                    <a href="community.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php
                            if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                            elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                            else echo 'dashboardstudent.php';
                        ?>" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-white hover:text-primary-200 focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden lg:inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/dashboardadmin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="/auth/login.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-primary-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-primary-700 border-t border-primary-600">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="kelas.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelas
                </a>
                <a href="mission.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-tasks mr-2"></i>Misi
                </a>
                <a href="community.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-users mr-2"></i>Komunitas
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php
                    if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                    elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                    else echo 'dashboardstudent.php';
                ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <hr class="my-2 border-primary-600">
                <a href="/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/dashboardadmin.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-cog mr-2"></i>Admin Panel
                </a>
                <?php endif; ?>
                <a href="/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-300 hover:bg-primary-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
                <?php else: ?>
                <a href="/auth/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-10 relative">
        <!-- Floating Emojis Background -->
        <div class="floating-emoji emoji-1 animate-float">🎯</div>
        <div class="floating-emoji emoji-2 animate-float-delay">🏆</div>
        <div class="floating-emoji emoji-3 animate-float">📚</div>
        <div class="floating-emoji emoji-4 animate-float-delay">✨</div>

        <!-- Header Section -->
        <div class="mission-header mb-10 animate__animated animate__fadeIn">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Misi Pembelajaran</h1>
                    <p class="text-gray-600 max-w-2xl text-sm md:text-base">Selesaikan misi untuk mengumpulkan poin, tingkatkan skillmu, dan raih prestasi!</p>
                </div>
                <?php if ($role === 'mentor'): ?>
                <div class="flex justify-center md:justify-end">
                    <a href="create_mission.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl transition-all font-bold action-button">
                        <i class="fas fa-plus mr-2"></i> Buat Misi Baru
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Indikator Poin Siswa -->
        <?php if ($role === 'student'): ?>
        <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 animate__animated animate__fadeInUp">
            <div class="point-indicator flex items-center text-white text-xl font-bold w-full md:w-auto">
                <div class="bg-white/20 p-3 rounded-full mr-4">
                    <i class="fas fa-trophy text-yellow-300 text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm font-medium opacity-80">Total Poin Kamu</div>
                    <div class="text-3xl font-extrabold"><?php echo $total_points; ?> <span class="text-xl">Poin</span></div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-md w-full md:w-auto backdrop-filter backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="mr-4 text-purple-500">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-1">Progress Misi</div>
                        <div class="flex items-center">
                            <?php 
                            $completed = count(array_filter($missions, fn($m) => $m['user_status'] === 'completed'));
                            $total = count($missions);
                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                            ?>
                            <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                <div class="progress-bar h-2.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-700"><?php echo $percentage; ?>%</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1"><?php echo $completed; ?> dari <?php echo $total; ?> misi selesai</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Galeri Slider Hasil Misi Siswa -->
        <?php if ($role === 'student' && !empty($gallery)): ?>
        <div class="mb-12 animate__animated animate__fadeIn">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                    <span class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-2 rounded-lg mr-3">
                        <i class="fas fa-images"></i>
                    </span>
                    Galeri Hasil Misi Siswa
                </h2>
                <a href="#" class="text-sm font-medium text-purple-600 hover:text-purple-800 flex items-center">
                    Lihat Semua <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            
            <div class="relative">
                <button id="sliderPrev" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white hover:bg-primary-100 text-primary-600 rounded-full shadow-lg p-3 transition hidden md:block hover:scale-110">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div id="gallerySlider" class="gallery-slider flex space-x-4 overflow-x-auto pb-6 snap-x snap-mandatory scroll-smooth">
                    <?php foreach ($gallery as $item): ?>
                    <div class="gallery-card min-w-[260px] max-w-xs p-4 flex flex-col items-center snap-center">
                        <div class="w-full h-48 rounded-xl overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 mb-4 flex items-center justify-center relative">
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $item['submission']) && file_exists($item['submission'])): ?>
                                <img src="<?php echo htmlspecialchars($item['submission']); ?>" alt="Hasil Misi" class="object-cover w-full h-full transition duration-500 hover:scale-105">
                            <?php elseif (filter_var($item['submission'], FILTER_VALIDATE_URL)): ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-link text-5xl text-gray-300 mb-2"></i>
                                    <a href="<?php echo htmlspecialchars($item['submission']); ?>" target="_blank" class="text-xs text-blue-500 hover:underline">Lihat Link</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-file-alt text-5xl text-gray-300 mb-2"></i>
                                    <p class="text-xs text-gray-400">File Submission: <?php echo htmlspecialchars($item['submission']); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="absolute bottom-2 right-2 bg-white/90 px-2 py-1 rounded-full shadow text-xs font-semibold text-purple-600">
                                <?php echo $item['points']; ?> pts
                            </div>
                        </div>
                        <div class="flex items-center mb-3 w-full">
                            <img src="<?php echo $item['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="w-10 h-10 rounded-full border-2 border-white shadow-md mr-3">
                            <div>
                                <div class="font-semibold text-gray-700 text-sm"><?php echo htmlspecialchars($item['full_name']); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo $item['submitted_at'] ? date('d M Y', strtotime($item['submitted_at'])) : 'Tanggal tidak tersedia'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-700 font-medium mb-2 text-center w-full truncate"><?php echo htmlspecialchars($item['mission_title']); ?></div>
                        <div class="flex justify-center w-full">
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i> Selesai
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button id="sliderNext" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white hover:bg-primary-100 text-primary-600 rounded-full shadow-lg p-3 transition hidden md:block hover:scale-110">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <script>
        const slider = document.getElementById('gallerySlider');
        const prevBtn = document.getElementById('sliderPrev');
        const nextBtn = document.getElementById('sliderNext');
        let autoSlideInterval;
        let cardWidth = 300;
        
        function slideToNext() {
            slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
        }
        
        function slideToPrev() {
            slider.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        }
        
        function startAutoSlide() {
            autoSlideInterval = setInterval(() => {
                if (slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth - 10) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slideToNext();
                }
            }, 4000);
        }
        
        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }
        
        if (prevBtn && nextBtn) {
            prevBtn.onclick = () => { slideToPrev(); stopAutoSlide(); startAutoSlide(); };
            nextBtn.onclick = () => { slideToNext(); stopAutoSlide(); startAutoSlide(); };
        }
        
        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);
        startAutoSlide();
        </script>
        <?php endif; ?>

        <!-- Mission List -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                    <span class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-lg mr-3">
                        <i class="fas fa-tasks"></i>
                    </span>
                    Daftar Misi Tersedia
                </h2>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 action-button">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <button class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 action-button">
                        <i class="fas fa-sort mr-1"></i> Urutkan
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($missions as $mission): 
                    $statusClass = [
                        'not_started' => 'bg-gray-100 text-gray-700',
                        'in_progress' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700'
                    ][$mission['user_status'] ?? 'bg-gray-100 text-gray-700'];
                    
                    $statusIcon = [
                        'not_started' => 'far fa-circle',
                        'in_progress' => 'fas fa-spinner fa-pulse',
                        'completed' => 'fas fa-check-circle'
                    ][$mission['user_status'] ?? 'far fa-circle'];
                ?>
                <div class="mission-card p-6 flex flex-col h-full">
                    <!-- Status Ribbon -->
                    <?php if ($mission['user_status'] === 'completed'): ?>
                    <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 transform rotate-45 translate-x-8 translate-y-4 w-32 text-center">
                        SELESAI
                    </div>
                    <?php elseif ($mission['user_status'] === 'in_progress'): ?>
                    <div class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-bold px-3 py-1 transform rotate-45 translate-x-8 translate-y-4 w-32 text-center">
                        DALAM PROSES
                    </div>
                    <?php endif; ?>
                    
                    <!-- Category Tags -->
                    <div class="flex flex-wrap mb-3">
                        <span class="category-tag">Mentor: <?php echo htmlspecialchars($mission['mentor_name']); ?></span>
                        <span class="category-tag bg-blue-100 text-blue-700"><?php echo $mission['points']; ?> Poin</span>
                    </div>
                    
                    <!-- Mission Content -->
                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($mission['title']); ?></h3>
                    <p class="text-gray-600 mb-4 line-clamp-3 text-sm"><?php echo htmlspecialchars($mission['description']); ?></p>
                    
                    <!-- Deadline -->
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Deadline: <?php echo date('d M Y', strtotime($mission['deadline'])); ?></span>
                    </div>
                    
                    <!-- Progress Bar (for in_progress missions) -->
                    <?php if ($mission['user_status'] === 'in_progress'): ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>50%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="progress-bar h-2 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Button -->
                    <div class="mt-auto">
                        <?php if ($role === 'student'): ?>
                            <?php if ($mission['user_status'] === 'not_started'): ?>
                            <a href="start_mission.php?id=<?php echo $mission['id']; ?>" class="block w-full text-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold action-button">
                                <i class="fas fa-play mr-2"></i> Mulai Misi
                            </a>
                            <?php elseif ($mission['user_status'] === 'in_progress'): ?>
                            <a href="submit_mission.php?id=<?php echo $mission['id']; ?>" class="block w-full text-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg font-semibold action-button">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Hasil
                            </a>
                            <?php else: ?>
                            <div class="flex justify-between items-center">
                                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">
                                    <i class="fas fa-check-circle mr-2"></i> Selesai
                                </span>
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Lihat Detail <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="mission/view.php?id=<?php echo $mission['id']; ?>" class="block w-full text-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold action-button">
                                <i class="fas fa-eye mr-2"></i> Lihat Detail
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Motivational Quote -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 text-white shadow-lg mb-10 animate__animated animate__fadeIn">
            <div class="text-center max-w-3xl mx-auto">
                <i class="fas fa-quote-left text-2xl opacity-50 mb-4"></i>
                <p class="text-xl font-medium mb-4">"Pendidikan adalah senjata paling mematikan di dunia, karena dengan pendidikan Anda dapat mengubah dunia."</p>
                <p class="font-bold">- Nelson Mandela</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                    <p class="mt-2 text-gray-400">Platform pembelajaran online terbaik untuk generasi muda.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                        <ul class="space-y-2">
                            <li><a href="kelas.php" class="text-gray-400 hover:text-white transition">Kelas</a></li>
                            <li><a href="mission.php" class="text-gray-400 hover:text-white transition">Misi</a></li>
                            <li><a href="community.php" gels="text-gray-400 hover:text-white transition">Komunitas</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Bantuan</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition">FAQ</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Kontak</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a></li>
                        </ul>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <h3 class="text-lg font-semibold mb-4">Ikuti Kami</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>© <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
            } else {
                mobileMenu.style.maxHeight = '0';
            }
        });

        document.addEventListener('click', function(event) {
            if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenu.style.maxHeight = '0';
            }
        });

        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('nav a');
        menuLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath.split('/').pop()) {
                link.classList.add('text-primary-200');
            }
        });

        const emojis = document.querySelectorAll('.floating-emoji');
        emojis.forEach((emoji, index) => {
            emoji.style.animationDelay = `${index * 2}s`;
            emoji.style.animationDuration = `${15 + Math.random() * 10}s`;
        });
    });
    </script>
</body>
</html>