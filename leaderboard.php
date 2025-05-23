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

// Query untuk mengambil data leaderboard (top 10)
$query = "
    SELECT u.id, u.full_name, u.profile_picture, SUM(m.points) as total_points
    FROM user_missions um
    JOIN missions m ON um.mission_id = m.id
    JOIN users u ON um.user_id = u.id
    WHERE um.status = 'completed'
    GROUP BY u.id, u.full_name, u.profile_picture
    ORDER BY total_points DESC
    LIMIT 10
";

$stmt = $db->prepare($query);
$stmt->execute();
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk posisi pengguna saat ini
$current_user_query = "
    SELECT t.user_id, t.full_name, t.profile_picture, t.total_points, (
        SELECT COUNT(*) + 1
        FROM (
            SELECT u.id, SUM(m.points) as total_points
            FROM user_missions um
            JOIN missions m ON um.mission_id = m.id
            JOIN users u ON um.user_id = u.id
            WHERE um.status = 'completed'
            GROUP BY u.id
            HAVING SUM(m.points) > (
                SELECT SUM(m.points)
                FROM user_missions um
                JOIN missions m ON um.mission_id = m.id
                WHERE um.user_id = ? AND um.status = 'completed'
            )
        ) as higher
    ) as rank
    FROM (
        SELECT u.id as user_id, u.full_name, u.profile_picture, SUM(m.points) as total_points
        FROM user_missions um
        JOIN missions m ON um.mission_id = m.id
        JOIN users u ON um.user_id = u.id
        WHERE um.status = 'completed' AND u.id = ?
        GROUP BY u.id, u.full_name, u.profile_picture
    ) t
";

$stmt = $db->prepare($current_user_query);
$stmt->execute([$user['id'], $user['id']]);
$current_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if user has no points
$current_user_position = $current_user_data['rank'] ?? 'N/A';
$current_user_points = $current_user_data['total_points'] ?? 0;
$current_user_name = $current_user_data['full_name'] ?? $user['full_name'];
$current_user_profile_picture = $current_user_data['profile_picture'] ?? 'assets/images/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        /* General Styles */
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #e0f2fe, #fce7f3, #e9d5ff, #f0f9ff);
            background-size: 400%;
            animation: gradientShift 20s ease infinite;
            opacity: 0.7;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Mobile-first styles */
        .navbar {
            padding: 0.75rem 1rem;
        }
        
        .mobile-menu a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .user-dropdown {
            width: 160px;
        }
        
        .user-dropdown a {
            font-size: 0.85rem;
        }
        
        /* Podium */
        .podium {
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .podium-item {
            width: 30%;
            min-width: 90px;
        }
        
        .podium-1 {
            height: 160px;
        }
        
        .podium-2 {
            height: 120px;
        }
        
        .podium-3 {
            height: 80px;
        }
        
        .podium-avatar {
            width: 40px;
            height: 40px;
            margin-top: -20px;
        }
        
        .podium-rank {
            font-size: 18px;
        }
        
        .podium-name {
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90%;
        }
        
        .podium-points {
            font-size: 0.75rem;
        }
        
        /* Leaderboard card */
        .leaderboard-card {
            padding: 0.75rem;
        }
        
        .leaderboard-card img {
            width: 32px;
            height: 32px;
        }
        
        .leaderboard-card span.text-lg {
            font-size: 0.9rem;
        }
        
        .leaderboard-card span.text-sm {
            font-size: 0.75rem;
        }
        
        /* Progress bar */
        .progress-bar {
            height: 8px;
        }
        
        /* Motivational section */
        .motivational-section {
            padding: 1.5rem;
        }
        
        .motivational-section h2 {
            font-size: 1.5rem;
        }
        
        .motivational-section p {
            font-size: 0.9rem;
        }
        
        .motivational-section a {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
        
        /* Table styles for mobile */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            min-width: 600px;
        }
        
        /* Desktop styles */
        @media (min-width: 768px) {
            .navbar {
                padding: 1rem 2rem;
            }
            
            .podium {
                gap: 12px;
                margin-bottom: 32px;
            }
            
            .podium-item {
                width: 120px;
            }
            
            .podium-1 {
                height: 200px;
            }
            
            .podium-2 {
                height: 160px;
            }
            
            .podium-3 {
                height: 120px;
            }
            
            .podium-avatar {
                width: 60px;
                height: 60px;
                margin-top: -30px;
            }
            
            .podium-rank {
                font-size: 24px;
            }
            
            .podium-name {
                font-size: 1rem;
            }
            
            .podium-points {
                font-size: 0.9rem;
            }
            
            .leaderboard-card {
                padding: 1rem;
            }
            
            .leaderboard-card img {
                width: 40px;
                height: 40px;
            }
            
            .progress-bar {
                height: 10px;
            }
        }

        /* Special effects */
        .rank-1::before {
            content: '👑';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            opacity: 0.5;
        }
        
        .rank-2::before {
            content: '🥈';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            opacity: 0.5;
        }
        
        .rank-3::before {
            content: '🥉';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            opacity: 0.5;
        }
        
        .leaderboard-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(247,250,252,0.95));
            backdrop-filter: blur(8px);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .podium-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
            position: relative;
            animation: podiumPop 0.5s ease-out;
        }
        
        @keyframes podiumPop {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .podium-1 {
            background: linear-gradient(to top, #facc15, #fef08a);
            box-shadow: 0 12px 20px rgba(250, 204, 21, 0.4);
        }
        
        .podium-2 {
            background: linear-gradient(to top, #d1d5db, #f3f4f6);
            box-shadow: 0 10px 15px rgba(209, 213, 219, 0.4);
        }
        
        .podium-3 {
            background: linear-gradient(to top, #f97316, #fdba74);
            box-shadow: 0 8px 12px rgba(249, 115, 22, 0.4);
        }
        
        .podium-avatar {
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float-particle 12s infinite ease-in-out;
            z-index: 0;
        }
        
        @keyframes float-particle {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-40px) scale(0.7); }
        }
        
        /* Mobile menu transitions */
        .mobile-menu {
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        
        .mobile-menu.open {
            max-height: 500px;
            opacity: 1;
        }
        
        .user-dropdown {
            transition: all 0.2s ease-in-out;
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
    <!-- Animated Background -->
    <div class="animated-bg"></div>

    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden text-white hover:text-primary-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <!-- Logo -->
                    <a href="index.php" class="flex-shrink-0 flex items-center text-xl font-bold ml-2">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <span class="hidden sm:inline">EduConnect</span>
                    </a>
                </div>
                <div class="hidden md:flex md:items-center md:space-x-4">
                    <a href="kelas.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-primary-800">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="mission.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-primary-800">
                        <i class="fas fa-tasks"></i>
                        <span>Misi</span>
                    </a>
                    <a href="community.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-primary-800">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <a href="leaderboard.php" class="font-semibold text-primary-200 bg-primary-800 px-3 py-2 rounded-md flex items-center space-x-1">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php
                            if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                            elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                            else echo 'dashboardstudent.php';
                        ?>" class="font-semibold hover:text-primary-200 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-primary-800">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center space-x-2 text-white hover:text-primary-200 focus:outline-none">
                                <img src="<?php echo htmlspecialchars($user['profile_picture'] ? asset('Uploads/profiles/' . $user['profile_picture']) : 'assets/images/default-avatar.png'); ?>" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="hidden lg:inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 user-dropdown">
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
                        <a href="/auth/login.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-primary-800">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="md:hidden bg-primary-700 border-t border-primary-600 mobile-menu">
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
                <a href="leaderboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-primary-200 bg-primary-600">
                    <i class="fas fa-trophy mr-2"></i>Leaderboard
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
    <div class="max-w-6xl mx-auto px-4 py-6 relative">
        <!-- Floating Particles -->
        <div class="particle bg-purple-200 w-8 h-8 top-10 left-5"></div>
        <div class="particle bg-pink-200 w-6 h-6 top-40 right-10" style="animation-delay: 2s;"></div>
        <div class="particle bg-blue-200 w-10 h-10 bottom-20 left-15" style="animation-delay: 4s;"></div>

        <!-- Header -->
        <h1 class="text-3xl font-bold text-center mb-2 text-primary-800 animate__animated animate__bounceIn">
            �️ Papan Peringkat
        </h1>
        <p class="text-center text-gray-600 mb-6 mx-auto text-sm sm:text-base">
            Bersinarlah seperti bintang! 🌟 Lihat siapa yang memimpin dan kejar posisi teratas!
        </p>

        <!-- Podium for Top 3 -->
        <?php if (count($leaderboard) >= 3): ?>
            <div class="podium flex justify-center items-end animate__animated animate__fadeIn">
                <!-- 2nd Place -->
                <div class="podium-item podium-2">
                    <div class="podium-rank">🥈</div>
                    <img src="<?php echo htmlspecialchars($leaderboard[1]['profile_picture'] ? asset('Uploads/profiles/' . $leaderboard[1]['profile_picture']) : 'assets/images/default-avatar.png'); ?>" alt="2nd Place" class="podium-avatar">
                    <div class="podium-name"><?php echo htmlspecialchars($leaderboard[1]['full_name']); ?></div>
                    <div class="podium-points"><?php echo $leaderboard[1]['total_points']; ?> pts</div>
                </div>
                <!-- 1st Place -->
                <div class="podium-item podium-1">
                    <div class="podium-rank">👑</div>
                    <img src="<?php echo htmlspecialchars($leaderboard[0]['profile_picture'] ? asset('Uploads/profiles/' . $leaderboard[0]['profile_picture']) : 'assets/images/default-avatar.png'); ?>" alt="1st Place" class="podium-avatar">
                    <div class="podium-name"><?php echo htmlspecialchars($leaderboard[0]['full_name']); ?></div>
                    <div class="podium-points"><?php echo $leaderboard[0]['total_points']; ?> pts</div>
                </div>
                <!-- 3rd Place -->
                <div class="podium-item podium-3">
                    <div class="podium-rank">🥉</div>
                    <img src="<?php echo htmlspecialchars($leaderboard[2]['profile_picture'] ? asset('Uploads/profiles/' . $leaderboard[2]['profile_picture']) : 'assets/images/default-avatar.png'); ?>" alt="3rd Place" class="podium-avatar">
                    <div class="podium-name"><?php echo htmlspecialchars($leaderboard[2]['full_name']); ?></div>
                    <div class="podium-points"><?php echo $leaderboard[2]['total_points']; ?> pts</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Leaderboard Table -->
        <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-lg overflow-hidden border border-white/30 mb-8 animate__animated animate__fadeInUp">
            <div class="table-container">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-primary-600 to-primary-500 text-white">
                        <tr>
                            <th class="py-3 px-3 sm:px-4 text-left rounded-tl-xl">Rank</th>
                            <th class="py-3 px-3 sm:px-4 text-left">Nama</th>
                            <th class="py-3 px-3 sm:px-4 text-left">Prestasi</th>
                            <th class="py-3 px-3 sm:px-4 text-right rounded-tr-xl">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($leaderboard as $index => $row): ?>
                            <tr class="leaderboard-card <?php echo $index < 3 ? 'rank-' . ($index + 1) : ''; ?>" onclick="triggerConfetti(<?php echo $index; ?>)">
                                <td class="py-3 px-3 sm:px-4">
                                    <div class="flex items-center">
                                        <span class="w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center rounded-full <?php
                                            echo $index == 0 ? 'bg-yellow-100 text-yellow-800' :
                                                ($index == 1 ? 'bg-gray-100 text-gray-800' :
                                                    ($index == 2 ? 'bg-orange-100 text-orange-800' : 'bg-primary-50 text-primary-600'));
                                        ?> font-bold text-sm sm:text-base mr-2">
                                            <?php echo $index + 1; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:px-4">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <img src="<?php echo htmlspecialchars($row['profile_picture'] ? asset('Uploads/profiles/' . $row['profile_picture']) : 'assets/images/default-avatar.png'); ?>" alt="Profile" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-white shadow-md">
                                        <span class="font-semibold text-gray-800 text-sm sm:text-base"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 sm:px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-semibold <?php
                                        echo $index == 0 ? 'bg-yellow-100 text-yellow-800' :
                                            ($index == 1 ? 'bg-gray-100 text-gray-800' :
                                                ($index == 2 ? 'bg-orange-100 text-orange-800' : 'bg-purple-100 text-purple-800'));
                                    ?>">
                                        <i class="fas <?php
                                            echo $index == 0 ? 'fa-crown' :
                                                ($index == 1 ? 'fa-medal' :
                                                    ($index == 2 ? 'fa-award' : 'fa-star'));
                                        ?> mr-1"></i>
                                        <?php
                                        echo $index == 0 ? 'Top Star' :
                                            ($index == 1 ? 'Super' :
                                                ($index == 2 ? 'Great' : 'Rising'));
                                        ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 sm:px-4 text-right font-bold text-primary-600 text-sm sm:text-base">
                                    <?php echo $row['total_points']; ?>
                                    <span class="text-gray-500 text-xs font-normal">pts</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Current User Position -->
            <div class="bg-gradient-to-r from-primary-50 to-purple-50 border-t border-gray-100 px-4 py-4 rounded-b-xl">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-purple-100 text-purple-800 font-bold text-sm sm:text-base">
                            <?php echo $current_user_position; ?>
                        </span>
                        <div class="flex items-center space-x-2">
                            <img src="<?php echo htmlspecialchars($current_user_profile_picture ? asset('Uploads/profiles/' . $current_user_profile_picture) : 'assets/images/default-avatar.png'); ?>" alt="Profile" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white shadow-md">
                            <div>
                                <span class="font-semibold text-gray-800 text-sm sm:text-base"><?php echo htmlspecialchars($current_user_name); ?></span>
                                <div class="text-xs text-gray-500">Posisi Anda</div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full sm:w-1/3 mt-2 sm:mt-0">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Poin Anda</span>
                            <span><?php echo $current_user_points; ?> pts</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(($current_user_points / ($leaderboard[0]['total_points'] ?: 1)) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motivational Section -->
        <div class="mt-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl shadow-lg p-5 text-white animate__animated animate__pulse">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-xl sm:text-2xl font-bold mb-2">Jadilah Bintang Berikutnya! 🌟</h2>
                <p class="mb-4 text-sm sm:text-base opacity-90">Setiap misi yang kamu selesaikan membawa kamu lebih dekat ke puncak!</p>
                <a href="mission.php" class="inline-flex items-center px-5 py-2 bg-white text-purple-600 font-semibold rounded-lg shadow hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-rocket mr-2"></i> Ambil Misi
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-primary-800 to-primary-900 text-white py-6">
        <div class="container mx-auto px-4 text-center">
            <p class="text-xs sm:text-sm opacity-80">© <?php echo date('Y'); ?> EduConnect. Dibuat dengan 💖 untuk pembelajaran yang menyenangkan!</p>
        </div>
    </footer>

    <!-- JavaScript for Interactivity -->
    <script>
        // Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });

        // User Menu Dropdown
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');
        userMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Confetti Animation for Top Ranks
        function triggerConfetti(index) {
            if (index < 3) {
                confetti({
                    particleCount: 60,
                    spread: 50,
                    origin: { y: 0.6 },
                    colors: index === 0 ? ['#facc15', '#fef08a'] :
                            index === 1 ? ['#d1d5db', '#f3f4f6'] :
                            ['#f97316', '#fdba74']
                });
            }
        }

        // Trigger confetti on page load for top 3
        window.addEventListener('load', () => {
            if (<?php echo count($leaderboard) >= 3 ? 'true' : 'false'; ?>) {
                setTimeout(() => triggerConfetti(0), 500);
            }
        });
    </script>
</body>
</html>