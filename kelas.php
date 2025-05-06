<?php
// kelas.php
require_once('config.php');
require_once('db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile_picture = $_SESSION['profile_picture'] ?? 'https://via.placeholder.com/40';

// Ambil data kursus yang diikuti
$stmt = $conn->prepare("SELECT c.course_id, c.title, c.description FROM courses c JOIN enrollments e ON c.course_id = e.course_id WHERE e.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data misi aktif
$stmt = $conn->prepare("SELECT mission_id, title, description FROM missions WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$missions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kelas - EduConnect</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .dropdown:hover .dropdown-menu {
            display: block;
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
                    <a href="kelas.php" class="text-gray-700 hover:text-primary font-bold">Kelas</a>
                    <a href="mission.php" class="text-gray-700 hover:text-primary">Misi</a>
                    <a href="community.php" class="text-gray-700 hover:text-primary">Komunitas</a>
                    <a href="portfolio.php" class="text-gray-700 hover:text-primary">Portofolio</a>
                    <div class="relative dropdown">
                        <div class="flex items-center cursor-pointer">
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil" class="w-10 h-10 rounded-full mr-2 object-cover">
                            <span class="text-gray-700"><?php echo htmlspecialchars($username); ?></span>
                        </div>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
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
                    <div class="flex items-center px-3 py-2">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil" class="w-8 h-8 rounded-full mr-2 object-cover">
                        <span class="text-gray-700"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <a href="index.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Beranda</a>
                    <a href="kelas.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Kelas</a>
                    <a href="mission.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Misi</a>
                    <a href="community.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Komunitas</a>
                    <a href="portfolio.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Portofolio</a>
                    <a href="profile.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Profil</a>
                    <a href="auth/logout.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard -->
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-gray-900">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
            <p class="text-gray-600">Lanjutkan perjalanan belajar Anda di EduConnect.</p>
        </div>

        <!-- Kursus -->
        <div id="courses" class="mb-12" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Kursus Saya</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="card bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        <a href="course.php?id=<?php echo $course['course_id']; ?>" class="text-primary hover:text-primary-dark">Lanjutkan Kursus</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Misi Aktif -->
        <div id="missions" class="mb-12" data-aos="fade-up" data-aos-delay="100">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Misi Aktif</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($missions as $mission): ?>
                    <div class="card bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($mission['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($mission['description']); ?></p>
                        <a href="mission.php?id=<?php echo $mission['mission_id']; ?>" class="text-primary hover:text-primary-dark">Kerjakan Misi</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Papan Peringkat -->
        <div id="leaderboard" data-aos="fade-up" data-aos-delay="200">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Papan Peringkat</h2>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="space-y-4">
                    <div class="flex items-center p-3 rounded-lg">
                        <span class="text-yellow-400 text-2xl mr-4">🥇</span>
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-900">Siti Rahayu</p>
                            <p class="text-sm text-gray-600">1.250 Poin</p>
                        </div>
                    </div>
                    <div class="flex items-center p-3 rounded-lg">
                        <span class="text-gray-400 text-2xl mr-4">🥈</span>
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-900">Ahmad Yani</p>
                            <p class="text-sm text-gray-600">1.100 Poin</p>
                        </div>
                    </div>
                    <div class="flex items-center p-3 rounded-lg">
                        <span class="text-yellow-600 text-2xl mr-4">🥉</span>
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-900">Dewi Anggraeni</p>
                            <p class="text-sm text-gray-600">950 Poin</p>
                        </div>
                    </div>
                </div>
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

        // Registrasi Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('Service Worker registered', reg))
                .catch(err => console.log('Service Worker registration failed', err));
        }
    </script>
</body>
</html>