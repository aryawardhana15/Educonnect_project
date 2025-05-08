<?php
require_once 'auth/auth.php';
$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$post_id = $_GET['id'] ?? 0;

// Ambil data post
$db = db();
$stmt = $db->prepare("
    SELECT p.*, u.username, u.full_name
    FROM community_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: community.php');
    exit;
}

// Ambil komentar
$stmt = $db->prepare("
    SELECT c.*, u.username, u.full_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Proses komentar baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    try {
        $stmt = $db->prepare("
            INSERT INTO comments (post_id, user_id, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $post_id, $user['id'], $content);
        
        if ($stmt->execute()) {
            header("Location: post.php?id=$post_id");
            exit;
        } else {
            throw new Exception("Gagal menambahkan komentar");
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
    <title><?php echo htmlspecialchars($post['title']); ?> - EduConnect</title>
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
                        <a class="nav-link" href="mission.php">
                            <i class="fas fa-tasks me-1"></i>Misi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="community.php">
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
        <!-- Post -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="assets/images/avatar.png" alt="Avatar" class="rounded-circle me-3" width="40" height="40">
                    <div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <h2 class="card-title h4"><?php echo htmlspecialchars($post['title']); ?></h2>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <div>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($post['category']); ?></span>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <h3 class="h5 mb-4">Komentar (<?php echo count($comments); ?>)</h3>

        <!-- Comment Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Kirim Komentar
                    </button>
                </form>
            </div>
        </div>

        <!-- Comment List -->
        <?php foreach ($comments as $comment): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="assets/images/avatar.png" alt="Avatar" class="rounded-circle me-3" width="40" height="40">
                    <div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($comment['full_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
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