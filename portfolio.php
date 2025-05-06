<?php
// portfolio.php
require_once('config.php');
require_once('db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil sertifikat
$stmt = $conn->prepare("SELECT c.certificate_id, c.course_id, c.issue_date, co.title FROM certificates c JOIN courses co ON c.course_id = co.course_id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$certificates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio - EduConnect</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .certificate-card:hover {
            transform: scale(1.05);
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
                    <a href="community.php" class="text-gray-700 hover:text-primary">Komunitas</a>
                    <a href="portfolio.php" class="text-gray-700 hover:text-primary font-bold">Portofolio</a>
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

    <!-- Portofolio -->
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-gray-900">Portofolio Anda</h1>
            <p class="text-gray-600">Lihat semua sertifikat dan prestasi Anda di EduConnect.</p>
        </div>

        <!-- Daftar Sertifikat -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-aos="fade-up" data-aos-delay="100">
            <?php foreach ($certificates as $certificate): ?>
                <div class="certificate-card bg-white p-6 rounded-xl shadow-md transition">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-certificate text-primary text-2xl mr-3"></i>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($certificate['title']); ?></h3>
                    </div>
                    <p class="text-gray-600 mb-2">Diterbitkan: <?php echo date('d M Y', strtotime($certificate['issue_date'])); ?></p>
                    <a href="certificate.php?id=<?php echo $certificate['certificate_id']; ?>" class="text-primary hover:text-primary-dark">Lihat Sertifikat</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tambah Portofolio Eksternal -->
        <div class="mt-12 bg-white p-6 rounded-xl shadow-md" data-aos="fade-up" data-aos-delay="200">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Tambah Proyek Eksternal</h2>
            <form action="api/add_portfolio.php" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Judul Proyek</label>
                    <input type="text" name="project_title" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="4" class="w-full p-2 border rounded-lg"></textarea>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">Tambah Proyek</button>
            </form>
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