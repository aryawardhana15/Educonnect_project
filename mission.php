<?php
// mission.php
require_once('config.php');
require_once('db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$mission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT title, description, requirements, deadline FROM missions WHERE mission_id = ? AND user_id = ?");
$stmt->bind_param("ii", $mission_id, $_SESSION['user_id']);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mission) {
    echo "Misi tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Misi - EduConnect</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .upload-btn:hover {
            transform: scale(1.05);
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
                    <a href="community.php" class="text-gray-700 hover:text-primary">Komunitas</a>
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
                    <a href="community.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Komunitas</a>
                    <a href="portfolio.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Portofolio</a>
                    <a href="auth/logout.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Misi Detail -->
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-xl shadow-md" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($mission['title']); ?></h1>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($mission['description']); ?></p>
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Persyaratan</h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($mission['requirements']); ?></p>
            </div>
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Tenggat Waktu</h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($mission['deadline']); ?></p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Unggah Hasil Misi</h2>
                <form action="api/submit_mission.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="mission_id" value="<?php echo $mission_id; ?>">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">File Proyek</label>
                        <input type="file" name="project_file" accept=".pdf,.docx,.zip" class="w-full p-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Catatan Tambahan</label>
                        <textarea name="notes" rows="4" class="w-full p-2 border rounded-lg"></textarea>
                    </div>
                    <button type="submit" class="upload-btn bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">Kirim Hasil</button>
                </form>
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