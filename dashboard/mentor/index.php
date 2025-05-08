<?php
require_once('../../config.php');
require_once('../../db_connect.php');
require_once('../../auth/auth.php');

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login dan role mentor
if (!$auth->isLoggedIn() || !$auth->isMentor()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_data = $auth->getUserById($user_id);

// Ambil statistik
$stats = [
    'total_courses' => 0,
    'total_students' => 0,
    'total_completed' => 0,
    'average_progress' => 0
];

// Total Courses
$query = "SELECT COUNT(*) as total FROM courses WHERE mentor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['total_courses'] = $stmt->get_result()->fetch_assoc()['total'];

// Total Students & Progress
$query = "SELECT 
            COUNT(DISTINCT uc.user_id) as total_students,
            AVG(uc.progress) as avg_progress,
            COUNT(DISTINCT CASE WHEN uc.progress = 100 THEN uc.user_id END) as completed
          FROM user_courses uc
          JOIN courses c ON uc.course_id = c.id
          WHERE c.mentor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['total_students'] = $result['total_students'];
$stats['total_completed'] = $result['completed'];
$stats['average_progress'] = round($result['avg_progress'] ?? 0);

// Ambil kelas yang diajar
$query = "SELECT c.*, 
            COUNT(DISTINCT uc.user_id) as student_count,
            AVG(uc.progress) as avg_progress
          FROM courses c
          LEFT JOIN user_courses uc ON c.id = uc.course_id
          WHERE c.mentor_id = ?
          GROUP BY c.id
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil jadwal mengajar hari ini
$query = "SELECT c.*, s.start_time, s.end_time
          FROM schedules s
          JOIN courses c ON s.course_id = c.id
          WHERE c.mentor_id = ? 
          AND DATE(s.start_time) = CURDATE()
          ORDER BY s.start_time ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$today_schedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mentor - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <span class="text-gray-700">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        <?php echo htmlspecialchars($user_data['full_name']); ?>
                    </span>
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
            <p class="text-gray-600">Kelola kelas dan pantau perkembangan siswa Anda</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-book text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Kelas</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_courses']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Siswa</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_students']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Average Progress -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Rata-rata Progress</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['average_progress']; ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Completed Students -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-award text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Siswa Selesai</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_completed']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Teaching Courses -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Kelas yang Diajar</h2>
                    <a href="/mentor/courses.php" class="text-primary hover:text-primary-dark text-sm">
                        Lihat Semua
                    </a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($courses as $course): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-book text-gray-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['title']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo number_format($course['student_count']); ?> Siswa</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo round($course['avg_progress'] ?? 0); ?>%
                            </div>
                            <div class="text-xs text-gray-500">Progress</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Jadwal Mengajar Hari Ini</h2>
                    <a href="/mentor/schedule.php" class="text-primary hover:text-primary-dark text-sm">
                        Lihat Semua
                    </a>
                </div>
                <?php if (empty($today_schedule)): ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Jadwal</h3>
                    <p class="text-gray-500">Anda tidak memiliki jadwal mengajar hari ini</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($today_schedule as $schedule): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-video text-gray-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($schedule['title']); ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php 
                                    echo date('H:i', strtotime($schedule['start_time'])) . ' - ' . 
                                         date('H:i', strtotime($schedule['end_time']));
                                    ?>
                                </p>
                            </div>
                        </div>
                        <a href="/mentor/live.php?course_id=<?php echo $schedule['id']; ?>" 
                           class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                            Mulai Kelas
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 