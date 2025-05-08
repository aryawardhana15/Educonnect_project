<?php
require_once 'auth/auth.php';
$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$mission_id = $_GET['id'] ?? 0;

// Ambil data misi
$db = db();
$stmt = $db->prepare("SELECT * FROM missions WHERE id = ?");
$stmt->bind_param("i", $mission_id);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();

if (!$mission) {
    header('Location: mission.php');
    exit;
}

// Cek apakah user sudah memulai misi ini
$stmt = $db->prepare("SELECT * FROM user_missions WHERE user_id = ? AND mission_id = ?");
$stmt->bind_param("ii", $user['id'], $mission_id);
$stmt->execute();
$user_mission = $stmt->get_result()->fetch_assoc();

if ($user_mission) {
    // Jika sudah memulai, redirect ke halaman submit
    header("Location: submit_mission.php?id=$mission_id");
    exit;
}

// Mulai misi baru
$stmt = $db->prepare("
    INSERT INTO user_missions (user_id, mission_id, status, started_at)
    VALUES (?, ?, 'in_progress', NOW())
");
$stmt->bind_param("ii", $user['id'], $mission_id);

if ($stmt->execute()) {
    header("Location: submit_mission.php?id=$mission_id");
    exit;
} else {
    $error = "Gagal memulai misi";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mulai Misi - EduConnect</title>
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
                        <a class="nav-link" href="kelas.php">
                            <i class="fas fa-book me-1"></i>Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mission.php">
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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="spinner-border text-primary mb-4" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h2>Memulai Misi...</h2>
                <p class="text-muted">Anda akan diarahkan ke halaman misi dalam beberapa detik.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "submit_mission.php?id=<?php echo $mission_id; ?>";
                }, 2000);
            </script>
        <?php endif; ?>
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