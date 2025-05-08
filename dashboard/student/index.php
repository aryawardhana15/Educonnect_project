<?php
require_once('../config.php');
require_once('../db_connect.php');
require_once('../auth/auth.php');

// Inisialisasi Auth
$auth = new Auth();

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Cek role
if (!$auth->hasRole('student')) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_data = $auth->getUserById($user_id);

// Ambil data kelas yang diikuti
$query = "SELECT c.*, u.full_name as mentor_name, uc.progress, uc.status 
          FROM user_courses uc 
          JOIN courses c ON uc.course_id = c.id 
          JOIN users u ON c.mentor_id = u.id 
          WHERE uc.user_id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$user_id]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data sertifikat
$query = "SELECT c.*, s.issue_date, s.certificate_url 
          FROM certificates s 
          JOIN courses c ON s.course_id = c.id 
          WHERE s.user_id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$user_id]);
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data misi yang perlu diselesaikan
$query = "SELECT m.*, mc.status 
          FROM mission_courses mc 
          JOIN missions m ON mc.mission_id = m.id 
          WHERE mc.user_id = ? AND mc.status != 'completed'";
$stmt = $auth->db->prepare($query);
$stmt->execute([$user_id]);
$pending_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .text-primary { color: #4F46E5; }
        .bg-primary { background-color: #4F46E5; }
        .hover\:bg-primary-dark:hover { background-color: #4338CA; }
        .text-secondary { color: #10B981; }
        .bg-secondary { background-color: #10B981; }
        .hover\:bg-secondary-dark:hover { background-color: #059669; }
        .text-accent { color: #F59E0B; }
        .bg-accent { background-color: #F59E0B; }
        .hover\:bg-accent-dark:hover { background-color: #D97706; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/profile.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-user-circle text-xl"></i>
                    </a>
                    <a href="/auth/logout.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Selamat datang, <?php echo htmlspecialchars($user_data['full_name']); ?>!</h1>
            <p class="text-gray-600">Lanjutkan perjalanan belajarmu di EduConnect</p>
        </div>

        <!-- Progress Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Progress Pembelajaran</h3>
                    <i class="fas fa-chart-line text-primary text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-primary mb-2">
                    <?php
                    $total_progress = 0;
                    $total_courses = count($enrolled_courses);
                    if ($total_courses > 0) {
                        foreach ($enrolled_courses as $course) {
                            $total_progress += $course['progress'];
                        }
                        echo round($total_progress / $total_courses) . '%';
                    } else {
                        echo '0%';
                    }
                    ?>
                </div>
                <p class="text-gray-600">Dari <?php echo $total_courses; ?> kelas yang diikuti</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Misi Aktif</h3>
                    <i class="fas fa-tasks text-secondary text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-secondary mb-2">
                    <?php echo count($pending_missions); ?>
                </div>
                <p class="text-gray-600">Misi yang perlu diselesaikan</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sertifikat</h3>
                    <i class="fas fa-certificate text-accent text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-accent mb-2">
                    <?php echo count($certificates); ?>
                </div>
                <p class="text-gray-600">Sertifikat yang telah diperoleh</p>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Kelas yang Diikuti</h2>
            <?php if (empty($enrolled_courses)): ?>
            <div class="text-center py-8">
                <i class="fas fa-book-open text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-600">Anda belum mengikuti kelas apapun</p>
                <a href="/courses.php" class="inline-block mt-4 bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition">
                    Jelajahi Kelas
                </a>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrolled_courses as $course): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="text-sm text-gray-600 mb-4">Mentor: <?php echo htmlspecialchars($course['mentor_name']); ?></p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div class="bg-primary h-2 rounded-full" style="width: <?php echo $course['progress']; ?>%"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Progress: <?php echo $course['progress']; ?>%</span>
                        <span><?php echo ucfirst($course['status']); ?></span>
                    </div>
                    <a href="/course.php?id=<?php echo $course['id']; ?>" class="mt-4 block text-center bg-primary text-white py-2 rounded-lg hover:bg-primary-dark transition">
                        Lanjutkan Belajar
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pending Missions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Misi yang Perlu Diselesaikan</h2>
            <?php if (empty($pending_missions)): ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-600">Tidak ada misi yang perlu diselesaikan</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($pending_missions as $mission): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($mission['title']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($mission['description']); ?></p>
                    </div>
                    <a href="/mission.php?id=<?php echo $mission['id']; ?>" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-secondary-dark transition">
                        Mulai Misi
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Certificates -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Sertifikat</h2>
            <?php if (empty($certificates)): ?>
            <div class="text-center py-8">
                <i class="fas fa-certificate text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-600">Anda belum memiliki sertifikat</p>
                <p class="text-sm text-gray-500 mt-2">Selesaikan kelas untuk mendapatkan sertifikat</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $cert): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($cert['title']); ?></h3>
                        <i class="fas fa-certificate text-accent text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Diterbitkan: <?php echo date('d M Y', strtotime($cert['issue_date'])); ?></p>
                    <div class="flex space-x-2">
                        <a href="<?php echo $cert['certificate_url']; ?>" target="_blank" class="flex-1 bg-accent text-white text-center py-2 rounded-lg hover:bg-accent-dark transition">
                            Lihat Sertifikat
                        </a>
                        <button onclick="shareCertificate('<?php echo $cert['certificate_url']; ?>')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function shareCertificate(url) {
            if (navigator.share) {
                navigator.share({
                    title: 'Sertifikat EduConnect',
                    text: 'Lihat sertifikat saya di EduConnect!',
                    url: url
                });
            } else {
                // Fallback untuk browser yang tidak mendukung Web Share API
                alert('URL sertifikat: ' + url);
            }
        }
    </script>
</body>
</html> 