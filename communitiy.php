<?php
// community.php
require_once('config.php');
require_once('db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil postingan forum
$stmt = $conn->prepare("SELECT p.post_id, p.content, p.created_at, u.username FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC LIMIT 10");
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Proses posting baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $content);
        $stmt->execute();
        $stmt->close();
        header('Location: community.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komunitas - EduConnect</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                    <span class="text-xl font-bold text-dark">EduConnect</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-primary">Beranda</a>
                    <a href="kelas.php" class="text-gray-700 hover:text-primary">Kelas</a>
                    <a href="mission.php" class="text-gray-700 hover:text-primary">Misi</a>
                    <a href="community.php" class="text-gray-700 hover:text-primary font-bold">Komunitas</a>
                    <a href="portfolio.php" class="text-gray-700 hover:text-primary">Portofolio</a>
                    <a href="auth/logout.php" class="text-gray-700 hover:text-primary">Logout</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="hamburger p-2">
                        <span class="hamburger-line block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                        <span class="hamburger-line block w-6 h-0.5 bg-gray-700 mb-1.5"></span>
                        <span class="hamburger-line block w-6 h-0.5 bg-gray-700"></span>
                    </button>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-nav md:hidden bg-white shadow-lg hidden">
                <div class="px-2 pt-2 pb-4 space-y-1">
                    <a href="index.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Beranda</a>
                    <a href="kelas.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Kelas</a>
                    <a href="mission.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Misi</a>
                    <a href="community.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Komunitas</a>
                    <a href="portfolio.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Portofolio</a>
                    <a href="auth/logout.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Forum Komunitas -->
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-gray-900">Komunitas EduConnect</h1>
            <p class="text-gray-600">Berbagi ide, berkolaborasi, dan belajar bersama siswa lain.</p>
        </div>

        <!-- Form Posting -->
        <div class="bg-white p-6 rounded-xl shadow-md mb-8" data-aos="fade-up" data-aos-delay="100">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Buat Postingan Baru</h2>
            <form action="community.php" method="POST">
                <textarea name="content" rows="4" placeholder="Apa yang ingin Anda bagikan?" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                <button type="submit" class="mt-4 bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">Posting</button>
            </form>
        </div>

        <!-- Daftar Postingan -->
        <div data-aos="fade-up" data-aos-delay="200">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Postingan Terbaru</h2>
            <div class="space-y-6">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card bg-white p-6 rounded-xl shadow-md transition">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-user-circle text-primary text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($post['username']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></p>
                            </div>
                        </div>
                        <p class="text-gray-700"><?php echo htmlspecialchars($post['content']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
        AOS.init();
        document.getElementById('mobile-menu-button').addEventListener('click', () => {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('scale-y-100');
        });
    </script>
</body>
</html>