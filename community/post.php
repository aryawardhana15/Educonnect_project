<?php
// community/post.php
require_once('../config.php');
require_once('../db_connect.php');
require_once '../auth/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$db = db();
$error = '';
$success = '';

// Ambil ID post dari URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header('Location: ../community.php');
    exit;
}

// Ambil data post
$query = "
    SELECT cp.*, u.full_name as author_name, u.profile_picture,
           (SELECT COUNT(*) FROM comments WHERE post_id = cp.id) as comment_count
    FROM community_posts cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.id = ?
";

$stmt = $db->prepare($query);
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: ../community.php');
    exit;
}

// Proses komentar baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    
    if (empty($comment)) {
        $error = 'Komentar tidak boleh kosong';
    } else {
        try {
            $query = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$post_id, $user['id'], $comment]);
            
            $success = 'Komentar berhasil ditambahkan';
            
            // Redirect untuk menghindari resubmission
            header("Location: post.php?id=" . $post_id . "#comments");
            exit;
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan saat menyimpan komentar';
        }
    }
}

// Ambil komentar
$query = "
    SELECT c.*, u.full_name as author_name, u.profile_picture
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
";

$stmt = $db->prepare($query);
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - EduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">EduConnect</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../kelas.php">Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../mission.php">Misi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../community.php">Komunitas</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile.php">Profil</a></li>
                            <?php if ($auth->hasRole('admin')): ?>
                            <li><a class="dropdown-item" href="../admin/dashboard.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php">Keluar</a></li>
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
                <!-- Post -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo $post['profile_picture'] ?? '../assets/images/default-avatar.jpg'; ?>" 
                                 class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
                            <div>
                                <h1 class="h3 mb-0"><?php echo htmlspecialchars($post['title']); ?></h1>
                                <small class="text-muted">
                                    Oleh <?php echo htmlspecialchars($post['author_name']); ?> • 
                                    <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($post['category']); ?></span>
                        </div>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div id="comments" class="card">
                    <div class="card-body">
                        <h2 class="h4 mb-4">Komentar (<?php echo $post['comment_count']; ?>)</h2>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <!-- Comment Form -->
                        <form method="POST" action="" class="mb-4">
                            <div class="mb-3">
                                <label for="comment" class="form-label">Tambah Komentar</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Kirim Komentar
                            </button>
                        </form>

                        <!-- Comments List -->
                        <?php if (empty($comments)): ?>
                        <p class="text-muted">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <img src="<?php echo $comment['profile_picture'] ?? '../assets/images/default-avatar.jpg'; ?>" 
                                     class="rounded-circle me-2" width="32" height="32" alt="Profile Picture">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($comment['author_name']); ?></h6>
                                        <small class="text-muted ms-2">
                                            <?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="../community.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Komunitas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 