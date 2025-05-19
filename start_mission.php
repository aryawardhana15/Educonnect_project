<?php
require_once 'auth/auth.php';
require_once 'db_connect.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$mission_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error = '';
$success = '';

if (!$mission_id) {
    header('Location: missions.php');
    exit;
}

// Ambil data misi
$db = db();
$stmt = $db->prepare("SELECT * FROM missions WHERE id = ?");
$stmt->execute([$mission_id]);
$mission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mission) {
    $error = "Misi tidak ditemukan.";
    header('Location: missions.php');
    exit;
}

// Cek status misi pengguna
$stmt = $db->prepare("SELECT * FROM user_missions WHERE user_id = ? AND mission_id = ?");
$stmt->execute([$user['id'], $mission_id]);
$user_mission = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_mission && $user_mission['status'] === 'in_progress') {
    // Misi sudah dimulai, redirect ke halaman submit
    header('Location: mission_submit.php?id=' . $mission_id);
    exit;
} elseif ($user_mission && $user_mission['status'] === 'completed') {
    // Misi sudah selesai
    $error = "Misi ini sudah selesai.";
    header('Refresh: 2; url=missions.php');
}

// Tangani mulai misi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        // Insert atau update user_missions
        if ($user_mission) {
            $stmt = $db->prepare("UPDATE user_missions SET status = 'in_progress', started_at = NOW() WHERE user_id = ? AND mission_id = ?");
            $stmt->execute([$user['id'], $mission_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO user_missions (user_id, mission_id, status, started_at) VALUES (?, ?, 'in_progress', NOW())");
            $stmt->execute([$user['id'], $mission_id]);
        }
        $success = "Misi berhasil dimulai! Anda akan diarahkan ke halaman pengumpulan misi.";
        header("Refresh: 2; url=mission_submit.php?id=" . $mission_id);
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mulai Misi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .floating-emoji {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0.1;
            z-index: 0;
        }
        .emoji-1 { top: 10%; left: 5%; }
        .emoji-2 { top: 30%; right: 5%; }
        .emoji-3 { bottom: 20%; left: 15%; }
        .emoji-4 { bottom: 10%; right: 10%; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navbar (Sama seperti missions.php) -->
    <nav class="bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="kelas.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="leaderboard.php" class="hover:text-gray-200">Leaderboard</a>
                    <a href="community.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <a href="dashboardstudent.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-white hover:text-blue-200 focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden lg:inline"><?php echo htmlspecialchars($user['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <hr class="my-1">
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-blue-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-700 border-t border-blue-600">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="kelas.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelas
                </a>
                <a href="missions.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-tasks mr-2"></i>Misi
                </a>
                <a href="community.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-users mr-2"></i>Komunitas
                </a>
                <a href="dashboardstudent.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <hr class="my-2 border-blue-600">
                <a href="/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <a href="/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-300 hover:bg-blue-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 py-10 relative">
        <!-- Floating Emojis -->
        <div class="floating-emoji emoji-1 animate-float">🎯</div>
        <div class="floating-emoji emoji-2 animate-float-delay">🏆</div>
        <div class="floating-emoji emoji-3 animate-float">📚</div>
        <div class="floating-emoji emoji-4 animate-float-delay">✨</div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Mulai Misi: <?php echo htmlspecialchars($mission['title']); ?></h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <p class="text-sm text-gray-600 mt-2">Anda akan diarahkan dalam beberapa detik.</p>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="text-center mb-6">
                        <p class="text-gray-600 mb-4">Klik tombol di bawah untuk memulai misi ini. Anda akan diarahkan ke halaman pengumpulan misi.</p>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all font-bold transform hover:scale-105">
                            <i class="fas fa-play mr-2"></i> Mulai Misi Sekarang
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer (Sama seperti missions.php) -->
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
                        <ul class="space-y-2

">
                            <li><a href="kelas.php" class="text-gray-400 hover:text-white transition">Kelas</a></li>
                            <li><a href="missions.php" class="text-gray-400 hover:text-white transition">Misi</a></li>
                            <li><a href="community.php" class="text-gray-400 hover:text-white transition">Komunitas</a></li>
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
        });
    </script>
</body>
</html>