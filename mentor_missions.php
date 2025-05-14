<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'mentor') {
    header('Location: auth/login.php');
    exit;
}
$user = $auth->getCurrentUser();
$db = db();

$stmt = $db->prepare("SELECT * FROM missions WHERE mentor_id = ?");
$stmt->execute([$user['id']]);
$missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misi Saya - Mentor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mission-card {
            transition: all 0.3s ease;
        }
        .mission-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<!-- Navbar -->
<nav class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <!-- Logo -->
        <a href="index.php" class="flex items-center space-x-2">
            <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
            <span class="font-bold text-xl text-gray-800">EduConnect</span>
        </a>

        <!-- Menu utama (Desktop) -->
        <div class="hidden md:flex items-center space-x-6">
            <a href="mentor_classes.php" class="text-gray-700 hover:text-blue-600">Kelas</a>
            <a href="mentor_missions.php" class="text-blue-600 font-semibold hover:underline">Misi</a>
            <a href="dashboardmentor.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
            <div class="relative group">
                <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                    <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-9 h-9 border-2 border-blue-600" alt="Avatar">
                </button>
                <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                    <a href="dashboardmentor.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                    <div class="border-t my-1"></div>
                    <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keluar</a>
                </div>
            </div>
        </div>

        <!-- Tombol hamburger (Mobile) -->
        <div class="md:hidden">
            <button id="mobile-menu-button" class="hamburger p-2 rounded-md text-gray-700 hover:text-blue-600 focus:outline-none">
                <span class="block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                <span class="block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                <span class="block w-6 h-0.5 bg-gray-700"></span>
            </button>
        </div>
    </div>

    <!-- Menu mobile -->
    <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg">
        <div class="px-4 py-2 space-y-2">
            <a href="mentor_classes.php" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">Kelas</a>
            <a href="mentor_missions.php" class="block text-blue-600 font-semibold hover:underline px-3 py-2 rounded-md">Misi</a>
            <a href="dashboardmentor.php" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">Dashboard</a>
            <a href="profile.php" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">Profil</a>
            <a href="auth/logout.php" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">Keluar</a>
        </div>
    </div>
</nav>

<script>
    // Dropdown logic
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    document.addEventListener('click', function (e) {
        if (avatarBtn && avatarBtn.contains(e.target)) {
            dropdownMenu.classList.toggle('hidden');
        } else if (dropdownMenu && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });

    // Mobile menu logic
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuButton.addEventListener('click', function () {
        mobileMenu.classList.toggle('hidden');
    });
</script>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Misi yang Saya Buat</h2>
        <a href="create_mission.php" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-lg border-2 border-blue-700 hover:bg-blue-700 hover:shadow-xl transition font-bold text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i> Buat Misi Baru
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($missions)): ?>
            <div class="col-span-full text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                    <i class="fas fa-tasks text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada misi</h3>
                <p class="text-gray-500">Anda belum membuat misi apapun.</p>
            </div>
        <?php else: foreach ($missions as $mission): ?>
            <div class="mission-card bg-white rounded-xl shadow-md hover:shadow-xl p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">
                        <?php echo $mission['status'] ?? 'Aktif'; ?>
                    </span>
                    <div class="flex space-x-2">
                        <button onclick="editMission(<?php echo $mission['id']; ?>)" class="text-gray-500 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteMission(<?php echo $mission['id']; ?>)" class="text-gray-500 hover:text-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <h5 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($mission['title']); ?></h5>
                <p class="text-gray-600 text-sm flex-1 mb-4"><?php echo htmlspecialchars(substr($mission['description'], 0, 100)); ?>...</p>
                <div class="flex items-center justify-between mt-auto">
                    <span class="text-sm text-gray-500">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <?php echo date('d M Y', strtotime($mission['created_at'] ?? 'now')); ?>
                    </span>
                    <a href="mission.php?id=<?php echo $mission['id']; ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-cog mr-2"></i> Kelola Misi
                    </a>
                </div>
            </div>
        <?php endforeach; endif; ?>
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

// Mission management functions
function editMission(id) {
    window.location.href = `edit_mission.php?id=${id}`;
}

function deleteMission(id) {
    if (confirm('Apakah Anda yakin ingin menghapus misi ini?')) {
        // Implementasi penghapusan misi
        fetch(`delete_mission.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus misi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus misi');
        });
    }
}
</script>
</body>
</html> 