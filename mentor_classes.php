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

$stmt = $db->prepare("SELECT * FROM courses WHERE mentor_id = ?");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Saya - Mentor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
<!-- Navbar -->
<nav class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <a href="index.php" class="flex items-center space-x-2">
            <i class="fas fa-graduation-cap text-primary text-2xl"></i>
            <span class="font-bold text-xl text-gray-800">EduConnect</span>
        </a>
        <div class="flex items-center space-x-6">
            <a href="mentor_classes.php" class="text-primary font-semibold hover:underline">Kelas</a>
            <a href="mentor_missions.php" class="text-gray-700 hover:text-primary">Misi</a>
            <a href="dashboardmentor.php" class="text-gray-700 hover:text-primary">Dashboard</a>
            <div class="relative group">
                <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                    <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-9 h-9 border-2 border-primary" alt="Avatar">
                </button>
                <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                    <a href="dashboardmentor.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                    <div class="border-t my-1"></div>
                    <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keluar</a>
                </div>
            </div>
        </div>
    </div>
</nav>
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

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Kelas yang Saya Ajarkan</h2>
        <a href="create_course.php"
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-lg border-2 border-blue-700 hover:bg-blue-700 hover:shadow-xl transition font-bold text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i> Buat Kelas Baru
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($courses)): ?>
            <div class="col-span-full text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                    <i class="fas fa-book text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada kelas</h3>
                <p class="text-gray-500">Anda belum membuat kelas apapun.</p>
            </div>
        <?php else: foreach ($courses as $course): ?>
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition p-5 flex flex-col">
                <img src="<?php echo $course['image'] ?? 'assets/images/default-course.jpg'; ?>" class="rounded-lg w-full h-40 object-cover mb-4 border border-gray-100" alt="<?php echo htmlspecialchars($course['title']); ?>">
                <h5 class="font-bold text-lg text-gray-900 mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                <p class="text-gray-600 text-sm flex-1"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                <a href="course.php?id=<?php echo $course['id']; ?>" class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-center font-semibold">Kelola Kelas</a>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
</body>
</html> 