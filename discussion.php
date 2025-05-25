<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');
require_once 'helpers.php';

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID kelas dari URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Ambil detail kelas
$query = "SELECT c.*, u.full_name as mentor_name
          FROM courses c
          JOIN users u ON c.mentor_id = u.id
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header('Location: /');
    exit;
}

// Cek status enrollment
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    header('Location: /course.php?id=' . $course_id);
    exit;
}

// Proses pembuatan diskusi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (!empty($title) && !empty($content)) {
        $query = "INSERT INTO discussions (course_id, user_id, title, content, created_at)
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $course_id, $user_id, $title, $content);
        $stmt->execute();
        
        header('Location: /discussion.php?course_id=' . $course_id);
        exit;
    }
}

// Proses pembuatan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'comment') {
    $discussion_id = (int)$_POST['discussion_id'];
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        $query = "INSERT INTO discussion_comments (discussion_id, user_id, content, created_at)
                  VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $discussion_id, $user_id, $content);
        $stmt->execute();
        
        header('Location: /discussion.php?course_id=' . $course_id . '&discussion_id=' . $discussion_id);
        exit;
    }
}

// Ambil diskusi
$discussion_id = isset($_GET['discussion_id']) ? (int)$_GET['discussion_id'] : 0;

if ($discussion_id) {
    // Ambil detail diskusi
    $query = "SELECT d.*, u.full_name, u.profile_image, u.role
              FROM discussions d
              JOIN users u ON d.user_id = u.id
              WHERE d.id = ? AND d.course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $discussion_id, $course_id);
    $stmt->execute();
    $discussion = $stmt->get_result()->fetch_assoc();
    
    if (!$discussion) {
        header('Location: /discussion.php?course_id=' . $course_id);
        exit;
    }
    
    // Ambil komentar
    $query = "SELECT c.*, u.full_name, u.profile_image, u.role
              FROM discussion_comments c
              JOIN users u ON c.user_id = u.id
              WHERE c.discussion_id = ?
              ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $discussion_id);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Ambil daftar diskusi
    $query = "SELECT d.*, u.full_name, u.profile_image, u.role,
              (SELECT COUNT(*) FROM discussion_comments WHERE discussion_id = d.id) as comment_count
              FROM discussions d
              JOIN users u ON d.user_id = u.id
              WHERE d.course_id = ?
              ORDER BY d.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $discussions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diskusi - <?php echo htmlspecialchars($course['title']); ?> - EduConnect</title>
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
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="flex items-center text-sm text-gray-500 mb-6">
            <a href="/course.php?id=<?php echo $course_id; ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($course['title']); ?>
            </a>
            <i class="fas fa-chevron-right mx-2"></i>
            <span class="text-gray-900">Diskusi</span>
        </div>

        <?php if ($discussion_id): ?>
        <!-- Detail Diskusi -->
        <div class="space-y-6">
            <!-- Diskusi -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-start">
                    <img src="<?php echo $discussion['profile_image'] ?: getRandomDefaultAvatar($discussion['user_id']); ?>" 
                         alt="<?php echo htmlspecialchars($discussion['full_name']); ?>"
                         class="w-10 h-10 rounded-full mr-4">
                    <div class="flex-grow">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($discussion['title']); ?></h2>
                                <div class="flex items-center text-sm text-gray-500">
                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($discussion['full_name']); ?></span>
                                    <span class="mx-2">•</span>
                                    <span><?php echo date('d M Y H:i', strtotime($discussion['created_at'])); ?></span>
                                    <?php if ($discussion['role'] === 'mentor'): ?>
                                    <span class="mx-2">•</span>
                                    <span class="text-primary">Mentor</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($discussion['content'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Komentar -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Komentar</h3>
                
                <!-- Form Komentar -->
                <form method="POST" class="bg-white rounded-xl shadow-md p-6">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="discussion_id" value="<?php echo $discussion_id; ?>">
                    <div class="mb-4">
                        <textarea name="content" 
                                  rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Tulis komentar Anda..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                            Kirim Komentar
                        </button>
                    </div>
                </form>

                <!-- Daftar Komentar -->
                <div class="space-y-4">
                    <?php foreach ($comments as $comment): ?>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-start">
                            <img src="<?php echo $comment['profile_image'] ?: getRandomDefaultAvatar($comment['user_id']); ?>" 
                                 alt="<?php echo htmlspecialchars($comment['full_name']); ?>"
                                 class="w-8 h-8 rounded-full mr-4">
                            <div class="flex-grow">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($comment['full_name']); ?></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></span>
                                        <?php if ($comment['role'] === 'mentor'): ?>
                                        <span class="mx-2">•</span>
                                        <span class="text-primary">Mentor</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="prose max-w-none">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Daftar Diskusi -->
        <div class="space-y-6">
            <!-- Form Diskusi Baru -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Buat Diskusi Baru</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-4">
                        <input type="text" 
                               name="title" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="Judul diskusi">
                    </div>
                    <div class="mb-4">
                        <textarea name="content" 
                                  rows="4" 
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Tulis pertanyaan atau topik diskusi Anda..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                            Buat Diskusi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Diskusi -->
            <div class="space-y-4">
                <?php foreach ($discussions as $discussion): ?>
                <a href="/discussion.php?course_id=<?php echo $course_id; ?>&discussion_id=<?php echo $discussion['id']; ?>" 
                   class="block bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex items-start">
                        <img src="<?php echo $discussion['profile_image'] ?: getRandomDefaultAvatar($discussion['user_id']); ?>" 
                             alt="<?php echo htmlspecialchars($discussion['full_name']); ?>"
                             class="w-10 h-10 rounded-full mr-4">
                        <div class="flex-grow">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($discussion['title']); ?></h3>
                            <div class="flex items-center text-sm text-gray-500">
                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($discussion['full_name']); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo date('d M Y H:i', strtotime($discussion['created_at'])); ?></span>
                                <?php if ($discussion['role'] === 'mentor'): ?>
                                <span class="mx-2">•</span>
                                <span class="text-primary">Mentor</span>
                                <?php endif; ?>
                                <span class="mx-2">•</span>
                                <span class="flex items-center">
                                    <i class="far fa-comment mr-1"></i>
                                    <?php echo $discussion['comment_count']; ?> komentar
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 