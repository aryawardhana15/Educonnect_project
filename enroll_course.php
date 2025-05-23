<?php
require_once 'auth/auth.php';
$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login atau bukan siswa
if (!$auth->isLoggedIn() || $user['role'] !== 'student') {
    header('Location: auth/login.php');
    exit;
}

$db = db();
$error = '';
$success = '';

// Ambil ID kelas dari URL
$courseId = $_GET['id'] ?? 0;

// Ambil detail kelas
$stmt = $db->prepare("
    SELECT c.*, u.full_name as mentor_name
    FROM courses c
    JOIN users u ON c.mentor_id = u.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $courseId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Jika kelas tidak ditemukan
if (!$course) {
    header('Location: kelas.php');
    exit;
}

// Cek apakah sudah terdaftar
$stmt = $db->prepare("SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user['id'], $courseId);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if ($enrollment) {
    header('Location: course.php?id=' . $courseId);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $db->prepare("
            INSERT INTO user_courses (user_id, course_id, enrolled_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("ii", $user['id'], $courseId);
        
        if ($stmt->execute()) {
            $success = 'Berhasil mendaftar ke kelas!';
            // Redirect ke halaman kelas setelah 2 detik
            header("refresh:2;url=course.php?id=" . $courseId);
        } else {
            $error = 'Gagal mendaftar ke kelas. Silakan coba lagi.';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelas - EduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>EduConnect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="kelas.php">
                            <i class="fas fa-book me-1"></i>Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mission.php">
                            <i class="fas fa-tasks me-1"></i>Misi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="community.php">
                            <i class="fas fa-users me-1"></i>Komunitas
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="portfolio.php">Portofolio</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h1 class="h3 mb-0">Daftar Kelas</h1>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-user me-1"></i>Mentor: <?php echo htmlspecialchars($course['mentor_name']); ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-tag me-1"></i>Kategori: <?php echo htmlspecialchars($course['category']); ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-signal me-1"></i>Level: 
                                <?php
                                $levels = [
                                    'beginner' => 'Pemula',
                                    'intermediate' => 'Menengah',
                                    'advanced' => 'Lanjutan'
                                ];
                                echo $levels[$course['level']] ?? $course['level'];
                                ?>
                            </p>
                            <?php if ($course['price'] > 0): ?>
                            <p class="text-muted mb-2">
                                <i class="fas fa-tag me-1"></i>Harga: Rp <?php echo number_format($course['price'], 0, ',', '.'); ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <h6>Deskripsi Kelas</h6>
                            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        </div>

                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Daftar Kelas
                                </button>
                                <a href="kelas.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>EduConnect</h5>
                    <p>Platform pembelajaran digital untuk pemerataan pendidikan di Indonesia.</p>
                </div>
                <div class="col-md-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">Tentang Kami</a></li>
                        <li><a href="#" class="text-light">Kontak</a></li>
                        <li><a href="#" class="text-light">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Ikuti Kami</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 