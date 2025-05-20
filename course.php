<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth();

// Cek login dan role mentor
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'mentor') {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID mentor dari sesi
$mentor_id = $_SESSION['user_id'];

// Initialize database connection
$db = db();

// Ambil daftar kelas yang dimiliki mentor
$query = "SELECT * FROM courses WHERE mentor_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$mentor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas - EduConnect</title>
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
                    <a href="dashboardmentor.php" class="text-gray-700 hover:text-primary">
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
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Kelola Kelas</h1>

        <!-- Notifikasi -->
        <?php if (isset($_GET['status'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo $_GET['status'] === 'deleted' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $_GET['status'] === 'deleted' ? 'Kelas berhasil dihapus.' : 'Gagal menghapus kelas. Silakan coba lagi.'; ?>
            </div>
        <?php endif; ?>

        <!-- Daftar Kelas -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Daftar Kelas Anda</h2>
                <a href="/create_course.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                    <i class="fas fa-plus mr-2"></i>Buat Kelas Baru
                </a>
            </div>
            <div class="space-y-4">
                <?php if (empty($courses)): ?>
                    <p class="text-gray-500 text-center">Belum ada kelas yang dibuat.</p>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <p class="text-sm text-gray-500">Tipe: <?php echo ucfirst($course['type']); ?> | Level: <?php echo ucfirst($course['level']); ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="/edit_course.php?id=<?php echo $course['id']; ?>" class="px-3 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600 transition">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <a href="/delete_course.php?id=<?php echo $course['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini? Semua data terkait akan dihapus.')" 
                                       class="px-3 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </a>
                                    <button onclick="showManageOptions(<?php echo $course['id']; ?>)" 
                                            class="px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                                        <i class="fas fa-cog mr-1"></i>Kelola
                                    </button>
                                </div>
                            </div>

                            <!-- Opsi Pengelolaan (Materi, Kuis, Diskusi, Progress Siswa) -->
                            <div id="manage-options-<?php echo $course['id']; ?>" class="hidden mt-4 p-4 bg-gray-100 rounded-lg">
                                <div class="flex flex-wrap gap-3 mb-4">
                                    <button onclick="showAddMaterial(<?php echo $course['id']; ?>)" 
                                            class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition">
                                        <i class="fas fa-plus mr-2"></i>Tambah Materi
                                    </button>
                                    <button onclick="showAddQuiz(<?php echo $course['id']; ?>)" 
                                            class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">
                                        <i class="fas fa-plus mr-2"></i>Tambah Kuis
                                    </button>
                                    <button onclick="showAddDiscussion(<?php echo $course['id']; ?>)" 
                                            class="px-4 py-2 bg-purple-500 text-white text-sm rounded-lg hover:bg-purple-600 transition">
                                        <i class="fas fa-plus mr-2"></i>Tambah Diskusi
                                    </button>
                                    <button onclick="showStudentProgress(<?php echo $course['id']; ?>)" 
                                            class="px-4 py-2 bg-indigo-500 text-white text-sm rounded-lg hover:bg-indigo-600 transition">
                                        <i class="fas fa-users mr-2"></i>Lihat Progress Siswa
                                    </button>
                                </div>

                                <!-- Form Tambah Materi -->
                                <div id="add-material-<?php echo $course['id']; ?>" class="hidden mb-4">
                                    <form action="/add_material.php" method="POST" class="space-y-3">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Judul Materi</label>
                                            <input type="text" name="title" class="w-full p-2 border rounded-lg" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Konten</label>
                                            <textarea name="content" class="w-full p-2 border rounded-lg" rows="3"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tipe</label>
                                            <select name="type" class="w-full p-2 border rounded-lg">
                                                <option value="video">Video</option>
                                                <option value="document">Dokumen</option>
                                                <option value="quiz">Kuis</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                            Simpan Materi
                                        </button>
                                    </form>
                                </div>

                                <!-- Form Tambah Kuis (Placeholder) -->
                                <div id="add-quiz-<?php echo $course['id']; ?>" class="hidden mb-4">
                                    <p class="text-gray-500">Fitur tambah kuis akan segera hadir!</p>
                                </div>

                                <!-- Form Tambah Diskusi (Placeholder) -->
                                <div id="add-discussion-<?php echo $course['id']; ?>" class="hidden mb-4">
                                    <p class="text-gray-500">Fitur tambah diskusi akan segera hadir!</p>
                                </div>

                                <!-- Progress Siswa -->
                                <div id="student-progress-<?php echo $course['id']; ?>" class="hidden">
                                    <?php
                                    $query = "SELECT u.full_name, uc.progress 
                                             FROM user_courses uc 
                                             JOIN users u ON uc.user_id = u.id 
                                             WHERE uc.course_id = ?";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute([$course['id']]);
                                    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Progress Siswa</h4>
                                    <?php if (empty($students)): ?>
                                        <p class="text-gray-500">Belum ada siswa yang terdaftar di kelas ini.</p>
                                    <?php else: ?>
                                        <table class="w-full text-sm text-gray-700">
                                            <thead>
                                                <tr class="bg-gray-200">
                                                    <th class="p-2 text-left">Nama Siswa</th>
                                                    <th class="p-2 text-left">Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student): ?>
                                                    <tr>
                                                        <td class="p-2"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                        <td class="p-2"><?php echo ($student['progress'] ?? 0); ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function showManageOptions(courseId) {
        const manageOptions = document.getElementById(`manage-options-${courseId}`);
        manageOptions.classList.toggle('hidden');
    }

    function showAddMaterial(courseId) {
        document.getElementById(`add-material-${courseId}`).classList.toggle('hidden');
        document.getElementById(`add-quiz-${courseId}`).classList.add('hidden');
        document.getElementById(`add-discussion-${courseId}`).classList.add('hidden');
        document.getElementById(`student-progress-${courseId}`).classList.add('hidden');
    }

    function showAddQuiz(courseId) {
        document.getElementById(`add-quiz-${courseId}`).classList.toggle('hidden');
        document.getElementById(`add-material-${courseId}`).classList.add('hidden');
        document.getElementById(`add-discussion-${courseId}`).classList.add('hidden');
        document.getElementById(`student-progress-${courseId}`).classList.add('hidden');
    }

    function showAddDiscussion(courseId) {
        document.getElementById(`add-discussion-${courseId}`).classList.toggle('hidden');
        document.getElementById(`add-material-${courseId}`).classList.add('hidden');
        document.getElementById(`add-quiz-${courseId}`).classList.add('hidden');
        document.getElementById(`student-progress-${courseId}`).classList.add('hidden');
    }

    function showStudentProgress(courseId) {
        document.getElementById(`student-progress-${courseId}`).classList.toggle('hidden');
        document.getElementById(`add-material-${courseId}`).classList.add('hidden');
        document.getElementById(`add-quiz-${courseId}`).classList.add('hidden');
        document.getElementById(`add-discussion-${courseId}`).classList.add('hidden');
    }
    </script>
</body>
</html>