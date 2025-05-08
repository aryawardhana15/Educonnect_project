<?php
// community.php
require_once('config.php');
require_once('db_connect.php');
require_once 'auth/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$db = db();
$role = $user['role'];

// Query untuk mengambil post
$query = "
    SELECT cp.*, u.full_name as author_name, u.profile_picture,
           (SELECT COUNT(*) FROM comments WHERE post_id = cp.id) as comment_count
    FROM community_posts cp
    JOIN users u ON cp.user_id = u.id
    ORDER BY cp.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komunitas - EduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">EduConnect</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="kelas.php">Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mission.php">Misi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="community.php">Komunitas</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <?php if ($auth->hasRole('admin')): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php">Admin Panel</a></li>
                            <?php endif; ?>
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
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h2 mb-0">Komunitas</h1>
                <p class="text-muted">Diskusikan dan berbagi pengalaman dengan sesama pengguna</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="community/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Buat Post
                </a>
            </div>
        </div>

        <!-- Post List -->
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo $post['profile_picture'] ?? 'assets/images/default-avatar.jpg'; ?>" 
                                 class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
                            <div>
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <small class="text-muted">
                                    Oleh <?php echo htmlspecialchars($post['author_name']); ?> • 
                                    <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($post['category']); ?></span>
                                <small class="text-muted ms-2">
                                    <i class="bi bi-chat"></i> <?php echo $post['comment_count']; ?> Komentar
                                </small>
                            </div>
                            <a href="community/post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Lihat Diskusi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 