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

// Cek status misi user
$stmt = $db->prepare("SELECT * FROM user_missions WHERE user_id = ? AND mission_id = ?");
$stmt->bind_param("ii", $user['id'], $mission_id);
$stmt->execute();
$user_mission = $stmt->get_result()->fetch_assoc();

if (!$user_mission) {
    header("Location: start_mission.php?id=$mission_id");
    exit;
}

if ($user_mission['status'] === 'completed') {
    header("Location: mission.php");
    exit;
}

// Proses pengumpulan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission = $_POST['submission'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    try {
        $stmt = $db->prepare("
            UPDATE user_missions 
            SET submission = ?, notes = ?, status = 'completed', completed_at = NOW()
            WHERE user_id = ? AND mission_id = ?
        ");
        $stmt->bind_param("ssii", $submission, $notes, $user['id'], $mission_id);
        
        if ($stmt->execute()) {
            // Update poin user
            $stmt = $db->prepare("
                UPDATE users 
                SET points = points + ?, 
                    experience = experience + ?
                WHERE id = ?
            ");
            $points = $mission['points'];
            $experience = $mission['points'] * 10; // 10x poin untuk experience
            $stmt->bind_param("iii", $points, $experience, $user['id']);
            $stmt->execute();
            
            header("Location: mission.php");
            exit;
        } else {
            throw new Exception("Gagal mengumpulkan misi");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulkan Misi - EduConnect</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title h3 mb-4">Kumpulkan Misi</h1>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h2 class="h5">Detail Misi</h2>
                            <p class="mb-1"><strong>Judul:</strong> <?php echo htmlspecialchars($mission['title']); ?></p>
                            <p class="mb-1"><strong>Deskripsi:</strong> <?php echo htmlspecialchars($mission['description']); ?></p>
                            <p class="mb-1"><strong>Poin:</strong> <?php echo $mission['points']; ?></p>
                            <p class="mb-1"><strong>Deadline:</strong> <?php echo date('d M Y', strtotime($mission['deadline'])); ?></p>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="submission" class="form-label">Link Hasil</label>
                                <input type="url" class="form-control" id="submission" name="submission" 
                                       placeholder="Masukkan link hasil misi (GitHub, Google Drive, dll)" required>
                                <div class="form-text">Pastikan link dapat diakses oleh mentor</div>
                            </div>

                            <div class="mb-4">
                                <label for="notes" class="form-label">Catatan Tambahan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" 
                                          placeholder="Tambahkan catatan atau penjelasan tentang hasil misi Anda"></textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="mission.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Kumpulkan
                                </button>
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