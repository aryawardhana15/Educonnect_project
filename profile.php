<?php
// profile.php
require_once('config.php');
require_once('db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pengguna
$stmt = $conn->prepare("SELECT username, email, profile_picture, bio FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil progres kursus
$stmt = $conn->prepare("SELECT c.title, e.progress FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$progress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil sertifikat
$stmt = $conn->prepare("SELECT c.certificate_id, c.course_id, c.issue_date, co.title FROM certificates c JOIN courses co ON c.course_id = co.course_id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$certificates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Proses pembaruan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $profile_picture = $user['profile_picture'];

    // Proses unggahan foto
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/';
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
            $profile_picture = '/' . $target;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET bio = ?, profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $bio, $profile_picture, $user_id);
    if ($stmt->execute()) {
        header('Location: profile.php?success=1');
        exit;
    } else {
        $error = 'Gagal memperbarui profil.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - EduConnect</title>
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
        .profile-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
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
                    <a href="portfolio.php" class="text-gray-700 hover:text-primary">Portofolio</a>
                    <a href="profile.php" class="text-gray-700 hover:text-primary font-bold">Profil</a>
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
                    <a href="profile.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Profil</a>
                    <a href="auth/logout.php" class="block px-3 py-2 text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profil -->
    <section class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-gray-900">Profil Anda</h1>
            <p class="text-gray-600">Kelola informasi pribadi dan lihat progres belajar Anda.</p>
        </div>

        <!-- Informasi Pengguna -->
        <div class="bg-white p-8 rounded-xl shadow-md profile-card mb-8" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center mb-6">
                <img src="<?php echo $user['profile_picture'] ?: 'https://via.placeholder.com/100'; ?>" alt="Foto Profil" class="w-24 h-24 rounded-full mr-4 object-cover">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Bio</h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($user['bio'] ?: 'Belum ada bio.'); ?></p>
            </div>
        </div>

        <!-- Edit Profil -->
        <div class="bg-white p-8 rounded-xl shadow-md mb-8" data-aos="fade-up" data-aos-delay="200">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Edit Profil</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="text-green-500 mb-4">Profil berhasil diperbarui!</p>
            <?php elseif (isset($error)): ?>
                <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Bio</label>
                    <textarea name="bio" rows="4" class="w-full p-3 border rounded-lg"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Foto Profil</label>
                    <input type="file" name="profile_picture" accept="image/*" class="w-full p-2 border rounded-lg">
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Progres Belajar -->
        <div class="bg-white p-8 rounded-xl shadow-md mb-8" data-aos="fade-up" data-aos-delay="300">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Progres Belajar</h2>
            <div class="space-y-4">
                <?php foreach ($progress as $course): ?>
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($course['title']); ?></p>
                        <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
                            <div class="bg-primary h-4 rounded-full progress-bar" style="width: <?php echo $course['progress']; ?>%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1"><?php echo $course['progress']; ?>% selesai</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sertifikat -->
        <div class="bg-white p-8 rounded-xl shadow-md" data-aos="fade-up" data-aos-delay="400">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Sertifikat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($certificates as $certificate): ?>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($certificate['title']); ?></p>
                        <p class="text-sm text-gray-600">Diterbitkan: <?php echo date('d M Y', strtotime($certificate['issue_date'])); ?></p>
                        <a href="certificate.php?id=<?php echo $certificate['certificate_id']; ?>" class="text-primary hover:text-primary-dark">Lihat Sertifikat</a>
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

        // Registrasi Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('Service Worker registered', reg))
                .catch(err => console.log('Service Worker registration failed', err));
        }
    </script>
</body>
</html>