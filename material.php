<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID materi dari URL
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail materi
$query = "SELECT m.*, c.title as course_title, c.id as course_id, u.full_name as mentor_name
          FROM materials m
          JOIN courses c ON m.course_id = c.id
          JOIN users u ON c.mentor_id = u.id
          WHERE m.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$material = $stmt->get_result()->fetch_assoc();

if (!$material) {
    header('Location: /');
    exit;
}

// Cek status enrollment
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $material['course_id']);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    header('Location: /course.php?id=' . $material['course_id']);
    exit;
}

// Ambil materi sebelumnya dan selanjutnya
$query = "SELECT id, title FROM materials 
          WHERE course_id = ? AND sequence < ? 
          ORDER BY sequence DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $material['course_id'], $material['sequence']);
$stmt->execute();
$prev_material = $stmt->get_result()->fetch_assoc();

$query = "SELECT id, title FROM materials 
          WHERE course_id = ? AND sequence > ? 
          ORDER BY sequence ASC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $material['course_id'], $material['sequence']);
$stmt->execute();
$next_material = $stmt->get_result()->fetch_assoc();

// Update progress jika belum selesai
if (!$material['is_completed']) {
    $query = "UPDATE user_materials SET is_completed = 1, completed_at = NOW() 
              WHERE user_id = ? AND material_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $material_id);
    $stmt->execute();

    // Update progress keseluruhan
    $query = "UPDATE user_courses SET progress = (
                SELECT (COUNT(*) * 100) / (
                    SELECT COUNT(*) FROM materials WHERE course_id = ?
                )
                FROM user_materials 
                WHERE user_id = ? AND course_id = ? AND is_completed = 1
              )
              WHERE user_id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiii", $material['course_id'], $user_id, $material['course_id'], $user_id, $material['course_id']);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($material['title']); ?> - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($material['type'] === 'video'): ?>
    <link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/7.20.3/video.min.js"></script>
    <?php endif; ?>
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
        <!-- Breadcrumb -->
        <div class="flex items-center text-sm text-gray-500 mb-6">
            <a href="/course.php?id=<?php echo $material['course_id']; ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($material['course_title']); ?>
            </a>
            <i class="fas fa-chevron-right mx-2"></i>
            <span class="text-gray-900"><?php echo htmlspecialchars($material['title']); ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($material['title']); ?></h1>
                    
                    <?php if ($material['type'] === 'video'): ?>
                    <!-- Video Player -->
                    <div class="aspect-w-16 aspect-h-9 mb-6">
                        <video
                            id="material-video"
                            class="video-js vjs-big-play-centered"
                            controls
                            preload="auto"
                            width="100%"
                            height="100%"
                            poster="<?php echo $material['thumbnail']; ?>"
                            data-setup="{}"
                        >
                            <source src="<?php echo $material['content']; ?>" type="video/mp4">
                            <p class="vjs-no-js">
                                Untuk melihat video ini, aktifkan JavaScript dan pertimbangkan untuk mengupgrade ke browser yang mendukung HTML5 video
                            </p>
                        </video>
                    </div>
                    <?php else: ?>
                    <!-- Text Content -->
                    <div class="prose max-w-none mb-6">
                        <?php echo $material['content']; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Navigation -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t">
                        <?php if ($prev_material): ?>
                        <a href="/material.php?id=<?php echo $prev_material['id']; ?>" 
                           class="flex items-center text-gray-600 hover:text-primary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <div>
                                <div class="text-sm">Sebelumnya</div>
                                <div class="font-medium"><?php echo htmlspecialchars($prev_material['title']); ?></div>
                            </div>
                        </a>
                        <?php else: ?>
                        <div></div>
                        <?php endif; ?>

                        <?php if ($next_material): ?>
                        <a href="/material.php?id=<?php echo $next_material['id']; ?>" 
                           class="flex items-center text-gray-600 hover:text-primary text-right">
                            <div>
                                <div class="text-sm">Selanjutnya</div>
                                <div class="font-medium"><?php echo htmlspecialchars($next_material['title']); ?></div>
                            </div>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <?php else: ?>
                        <div></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Material Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Materi</h2>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-book w-5"></i>
                            <span class="ml-2">Tipe: <?php echo ucfirst($material['type']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-clock w-5"></i>
                            <span class="ml-2">Durasi: <?php echo $material['duration']; ?> menit</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-user w-5"></i>
                            <span class="ml-2">Mentor: <?php echo htmlspecialchars($material['mentor_name']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar w-5"></i>
                            <span class="ml-2">Terakhir diperbarui: <?php echo date('d M Y', strtotime($material['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Resources -->
                <?php if (!empty($material['resources'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Sumber Daya</h2>
                    <div class="space-y-3">
                        <?php foreach (json_decode($material['resources'], true) as $resource): ?>
                        <a href="<?php echo $resource['url']; ?>" 
                           class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                           target="_blank">
                            <i class="fas fa-file-download text-primary mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($resource['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo strtoupper($resource['type']); ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notes -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Catatan</h2>
                        <button onclick="showNoteForm()" 
                                class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition">
                            <i class="fas fa-plus mr-2"></i>Tambah Catatan
                        </button>
                    </div>
                    <div class="space-y-4">
                        <!-- Notes will be loaded here -->
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-sticky-note text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Catatan</h3>
                            <p class="text-gray-500">Tambahkan catatan untuk membantu pembelajaran Anda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    <?php if ($material['type'] === 'video'): ?>
    // Video Player
    var player = videojs('material-video');
    player.on('ended', function() {
        // Mark as completed when video ends
        fetch('/api/materials/complete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                material_id: <?php echo $material_id; ?>
            })
        });
    });
    <?php endif; ?>

    function showNoteForm() {
        // Implementasi form catatan
        alert('Fitur catatan akan segera hadir!');
    }
    </script>
</body>
</html> 