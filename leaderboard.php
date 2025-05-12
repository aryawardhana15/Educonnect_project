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

// Query untuk mengambil data leaderboard
$query = "
    SELECT u.full_name, u.profile_picture, SUM(m.points) as total_points
    FROM user_missions um
    JOIN missions m ON um.mission_id = m.id
    JOIN users u ON um.user_id = u.id
    WHERE um.status = 'completed'
    GROUP BY u.id
    ORDER BY total_points DESC
    LIMIT 10
";

$stmt = $db->prepare($query);
$stmt->execute();
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .leaderboard-card {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(247,250,252,0.9) 100%);
            backdrop-filter: blur(5px);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .leaderboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(14, 165, 233, 0.2);
            background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(247,250,252,1) 100%);
        }
        .rank-1 {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 1px solid rgba(255, 215, 0, 0.3);
        }
        .rank-2 {
            background: linear-gradient(135deg, rgba(192, 192, 192, 0.15) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 1px solid rgba(192, 192, 192, 0.3);
        }
        .rank-3 {
            background: linear-gradient(135deg, rgba(205, 127, 50, 0.15) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 1px solid rgba(205, 127, 50, 0.3);
        }
        .podium {
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 40px;
        }
        .podium-item {
            width: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }
        .podium-1 {
            height: 180px;
            background: linear-gradient(to top, #facc15, #fef08a);
            box-shadow: 0 10px 15px rgba(250, 204, 21, 0.3);
        }
        .podium-2 {
            height: 140px;
            background: linear-gradient(to top, #e5e7eb, #f3f4f6);
            box-shadow: 0 8px 12px rgba(209, 213, 219, 0.3);
        }
        .podium-3 {
            height: 100px;
            background: linear-gradient(to top, #f97316, #fdba74);
            box-shadow: 0 6px 10px rgba(249, 115, 22, 0.3);
        }
        .podium-rank {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1e293b;
        }
        .podium-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
            margin-top: -30px;
            object-fit: cover;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .podium-name {
            margin-top: 10px;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
        }
        .podium-points {
            font-size: 18px;
            font-weight: bold;
            color: #0ea5e9;
            margin-top: 5px;
        }
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #f0f9ff, #e0f2fe, #bae6fd, #7dd3fc);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen">
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    
    <!-- Navbar (unchanged) -->
 
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
                    <a href="dashboardstudent.php" class="px-3 py-2 rounded-md text-sm font-medium text-primary-600 bg-primary-50">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    <a href="student_courses.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-book-open mr-1"></i> Kelas Saya
                    </a>
                    <a href="mentoring.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-chalkboard-teacher mr-1"></i> Mentoring
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
                            <a href="dashboardstudent.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
                <a href="dashboardstudent.php" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 bg-primary-50">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="student_courses.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                    <i class="fas fa-book-open mr-2"></i> Kelas Saya
                </a>
                <a href="mentoring.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50">
                    <i class="fas fa-chalkboard-teacher mr-2"></i> Mentoring
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-10 relative">
        <!-- Decorative Elements -->
        <div class="absolute -top-10 -left-10 w-32 h-32 rounded-full bg-primary-100 opacity-20"></div>
        <div class="absolute bottom-20 -right-10 w-40 h-40 rounded-full bg-purple-100 opacity-20"></div>
        
        <h1 class="text-4xl font-bold text-center mb-2 text-primary-800">🏆 Papan Peringkat</h1>
        <p class="text-center text-gray-600 mb-10 max-w-2xl mx-auto">Lihat peringkat pengguna teratas berdasarkan poin yang telah dikumpulkan</p>
        
        <!-- Podium for top 3 -->
<?php if (count($leaderboard) >= 3): ?>
<div class="podium flex justify-center items-end gap-8 mb-12">
    <!-- 2nd Place -->
    <div class="podium-item bg-blue-500 text-white text-center p-6 rounded-lg shadow-lg w-32">
        <div class="podium-rank text-3xl font-bold mb-2">2</div>
        <div class="podium-name text-lg font-semibold"><?= htmlspecialchars($leaderboard[1]['full_name']) ?></div>
        <div class="podium-points text-sm mt-1"><?= $leaderboard[1]['total_points'] ?> pts</div>
    </div>

    <!-- 1st Place -->
    <div class="podium-item bg-yellow-500 text-white text-center p-8 rounded-lg shadow-lg w-36">
        <div class="podium-rank text-4xl font-bold mb-2">1</div>
        <div class="podium-name text-xl font-semibold"><?= htmlspecialchars($leaderboard[0]['full_name']) ?></div>
        <div class="podium-points text-sm mt-1"><?= $leaderboard[0]['total_points'] ?> pts</div>
    </div>

    <!-- 3rd Place -->
    <div class="podium-item bg-gray-500 text-white text-center p-6 rounded-lg shadow-lg w-32">
        <div class="podium-rank text-3xl font-bold mb-2">3</div>
        <div class="podium-name text-lg font-semibold"><?= htmlspecialchars($leaderboard[2]['full_name']) ?></div>
        <div class="podium-points text-sm mt-1"><?= $leaderboard[2]['total_points'] ?> pts</div>
    </div>
</div>
<?php endif; ?>
        
        <!-- Leaderboard Table -->
        <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-xl overflow-hidden border border-white/30">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-primary-600 text-white">
                        <tr>
                            <th class="py-4 px-6 text-left rounded-tl-xl">Peringkat</th>
                            <th class="py-4 px-6 text-left">Nama Pengguna</th>
                            <th class="py-4 px-6 text-left">Prestasi</th>
                            <th class="py-4 px-6 text-right rounded-tr-xl">Total Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($leaderboard as $index => $row): ?>
                        <tr class="leaderboard-card <?= $index < 3 ? 'rank-'.($index+1) : '' ?>">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <?php if($index < 3): ?>
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full <?= 
                                            $index == 0 ? 'bg-yellow-100 text-yellow-800' : 
                                            ($index == 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') 
                                        ?> font-bold mr-3">
                                            <?= $index + 1 ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-600 font-medium mr-3">
                                            <?= $index + 1 ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-4">
                                    <img src="<?= asset('uploads/profiles/' . $row['profile_picture']) ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white shadow">
                                    <span class="font-medium text-gray-800"><?= htmlspecialchars($row['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <?php if($index == 0): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-crown mr-2"></i> Top Learner
                                    </span>
                                <?php elseif($index == 1): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-medal mr-2"></i> Excellent
                                    </span>
                                <?php elseif($index == 2): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-award mr-2"></i> Great Job
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                                        <i class="fas fa-star mr-2"></i> Rising Star
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-primary-600">
                                <?= $row['total_points'] ?>
                                <span class="text-gray-500 text-sm font-normal">pts</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Current User Position (if not in top 10) -->
            <?php 
            // You would need to query the current user's position
            // This is just a placeholder implementation
            $current_user_position = 15; // Example position
            $current_user_points = 120; // Example points
            ?>
            <div class="bg-gray-50/80 border-t border-gray-100 px-6 py-4 rounded-b-xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-100 text-primary-800 font-medium">
                            <?= $current_user_position ?>
                        </span>
                        <div class="flex items-center space-x-3">
                            <img src="<?= asset('uploads/profiles/' . $user['profile_picture']) ?>" alt="Profile" class="w-8 h-8 rounded-full border-2 border-white shadow">
                            <span class="font-medium text-gray-800">Posisi Anda</span>
                        </div>
                    </div>
                    <div class="font-bold text-primary-600">
                        <?= $current_user_points ?>
                        <span class="text-gray-500 text-sm font-normal">pts</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Motivational Section -->
        <div class="mt-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl shadow-lg p-6 text-white">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-2xl font-bold mb-3">Teruslah Belajar dan Berkembang!</h2>
                <p class="mb-5 opacity-90">Setiap poin yang kamu kumpulkan adalah langkah menuju kesuksesan. Lihat misi tersedia untuk mendapatkan lebih banyak poin!</p>
                <a href="mission.php" class="inline-flex items-center px-6 py-3 bg-white text-primary-600 font-medium rounded-lg shadow hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-tasks mr-2"></i> Lihat Misi Tersedia
                </a>
            </div>
        </div>
    </div>

    <!-- Footer (unchanged) -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?= date('Y') ?> EduConnect. Semua Hak Dilindungi.</p>
        </div>
    </footer>
</body>
</html>