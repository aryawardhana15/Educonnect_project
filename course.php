<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth();

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID kelas dari URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail kelas
$query = "SELECT c.*, u.full_name as mentor_name, u.profile_image as mentor_image
          FROM courses c
          JOIN users u ON c.mentor_id = u.id
          WHERE c.id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: /');
    exit;
}

// Cek status enrollment
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil materi
$query = "SELECT * FROM materials WHERE course_id = ? ORDER BY sequence ASC";
$stmt = $auth->db->prepare($query);
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil kuis
$query = "SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at DESC";
$stmt = $auth->db->prepare($query);
$stmt->execute([$course_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil diskusi
$query = "SELECT d.*, u.full_name, u.profile_image
          FROM discussions d
          JOIN users u ON d.user_id = u.id
          WHERE d.course_id = ?
          ORDER BY d.created_at DESC";
$stmt = $auth->db->prepare($query);
$stmt->execute([$course_id]);
$discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .text-primary { color: #4F46E5; }
        .bg-primary { background-color: #4F46E5; }
        .hover\:bg-primary-dark:hover { background-color: #4338CA; }
        .border-primary { border-color: #4F46E5; }
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
                    <a href="/dashboard/student/index.php" class="text-gray-700 hover:text-primary">
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
        <!-- Course Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="flex items-center">
                        <img src="<?php echo $course['mentor_image'] ?: '/assets/images/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($course['mentor_name']); ?>"
                             class="w-10 h-10 rounded-full mr-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['mentor_name']); ?></p>
                            <p class="text-sm text-gray-500">Mentor</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:mt-0">
                    <?php if ($course['type'] === 'premium'): ?>
                        <?php if ($enrollment): ?>
                            <a href="/course.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                <i class="fas fa-play mr-2"></i>
                                Masuk Kelas
                            </a>
                        <?php else: ?>
                            <a href="/payment.php?course_id=<?php echo $course['id']; ?>" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Daftar Kelas
                                <span class="ml-2 font-medium">Rp <?php echo number_format($course['price']); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/course.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                            <i class="fas fa-play mr-2"></i>
                            Masuk Kelas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($enrollment): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Materials -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Materi Pembelajaran</h2>
                    <div class="space-y-4">
                        <?php foreach ($materials as $material): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-file-alt text-gray-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($material['title']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($material['description']); ?></p>
                                </div>
                            </div>
                            <a href="/material.php?id=<?php echo $material['id']; ?>" 
                               class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                                Pelajari
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quizzes -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kuis</h2>
                    <div class="space-y-4">
                        <?php foreach ($quizzes as $quiz): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-question-circle text-gray-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($quiz['title']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $quiz['question_count']; ?> Pertanyaan</p>
                                </div>
                            </div>
                            <a href="/quiz.php?id=<?php echo $quiz['id']; ?>" 
                               class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                                Mulai Kuis
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Discussions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Diskusi</h2>
                        <button onclick="showDiscussionForm()" 
                                class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                            <i class="fas fa-plus mr-2"></i>Buat Diskusi
                        </button>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($discussions as $discussion): ?>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center mb-3">
                                <img src="<?php echo $discussion['profile_image'] ?: '/assets/images/default-avatar.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($discussion['full_name']); ?>"
                                     class="w-8 h-8 rounded-full mr-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($discussion['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('d M Y H:i', strtotime($discussion['created_at'])); ?></p>
                                </div>
                            </div>
                            <p class="text-gray-700 mb-3"><?php echo nl2br(htmlspecialchars($discussion['content'])); ?></p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <button class="flex items-center hover:text-primary">
                                    <i class="far fa-thumbs-up mr-1"></i>
                                    Suka
                                </button>
                                <button class="flex items-center hover:text-primary">
                                    <i class="far fa-comment mr-1"></i>
                                    Balas
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Progress -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Progress Pembelajaran</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Progress Keseluruhan</span>
                                <span class="font-medium text-gray-900"><?php echo $enrollment['progress']; ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full" style="width: <?php echo $enrollment['progress']; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Materi Selesai</span>
                                <span class="font-medium text-gray-900">
                                    <?php 
                                    $completed = 0;
                                    foreach ($materials as $material) {
                                        if ($material['is_completed']) $completed++;
                                    }
                                    echo $completed . '/' . count($materials);
                                    ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full" 
                                     style="width: <?php echo count($materials) ? ($completed / count($materials) * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kelas</h2>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-clock w-5"></i>
                            <span class="ml-2">Durasi: <?php echo $course['duration']; ?> jam</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-users w-5"></i>
                            <span class="ml-2"><?php echo $course['student_count']; ?> Siswa</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-star w-5"></i>
                            <span class="ml-2">Rating: <?php echo number_format($course['rating'], 1); ?>/5.0</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar w-5"></i>
                            <span class="ml-2">Terakhir diperbarui: <?php echo date('d M Y', strtotime($course['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function showDiscussionForm() {
        // Implementasi form diskusi
        alert('Fitur diskusi akan segera hadir!');
    }
    </script>
</body>
</html> 