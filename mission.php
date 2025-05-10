<?php
// mission.php
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-mission:hover { box-shadow: 0 8px 32px 0 rgba(0,0,0,0.12); transform: translateY(-2px) scale(1.01); }
        .status-badge { font-size: 0.85rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    <span class="font-bold text-xl text-gray-800">EduConnect</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="kelas.php" class="text-gray-700 hover:text-blue-600">Kelas</a>
                    <a href="mission.php" class="text-blue-600 font-semibold hover:underline">Misi</a>
                    <a href="community.php" class="text-gray-700 hover:text-blue-600">Komunitas</a>
                    <div class="relative group">
                        <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                            <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-9 h-9 border-2 border-blue-600" alt="Avatar">
                        </button>
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <?php if ($auth->hasRole('admin')): ?>
                            <a href="admin/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Admin Panel</a>
                            <?php endif; ?>
                            <div class="border-t my-1"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keluar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Misi</h1>
                <p class="text-gray-500">Selesaikan misi untuk mendapatkan poin dan pengalaman</p>
            </div>
            <?php if ($role === 'mentor'): ?>
            <div>
                <a href="create_mission.php" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Buat Misi
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mission List -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($missions as $mission): ?>
            <div class="bg-white rounded-2xl shadow card-mission transition p-6 flex flex-col h-full">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg font-bold text-gray-800 mb-0"><?php echo htmlspecialchars($mission['title']); ?></h2>
                    <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold status-badge">
                        <i class="fas fa-star mr-1"></i><?php echo $mission['points']; ?> Poin
                    </span>
                </div>
                <p class="text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($mission['description']); ?></p>
                <div class="flex flex-col gap-2 mb-4">
                    <span class="text-sm text-gray-500"><i class="fas fa-user mr-1"></i> Mentor: <?php echo htmlspecialchars($mission['mentor_name']); ?></span>
                    <span class="text-sm text-gray-500"><i class="fas fa-clock mr-1"></i> Deadline: <?php echo date('d M Y', strtotime($mission['deadline'])); ?></span>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <?php if ($role === 'student'): ?>
                        <?php if ($mission['user_status'] === 'not_started'): ?>
                        <a href="mission/start.php?id=<?php echo $mission['id']; ?>" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 font-semibold transition">
                            <i class="fas fa-play mr-2"></i> Mulai
                        </a>
                        <?php elseif ($mission['user_status'] === 'in_progress'): ?>
                        <a href="mission/submit.php?id=<?php echo $mission['id']; ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim
                        </a>
                        <?php else: ?>
                        <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">
                            <i class="fas fa-check-circle mr-2"></i> Selesai
                        </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="mission/view.php?id=<?php echo $mission['id']; ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition">
                            <i class="fas fa-eye mr-2"></i> Lihat
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    // Dropdown logic
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    document.addEventListener('click', function(e) {
        if (avatarBtn && avatarBtn.contains(e.target)) {
            dropdownMenu.classList.toggle('hidden');
        } else if (dropdownMenu && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
    </script>
</body>
</html>