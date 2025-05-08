<?php
// profile.php
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
$error = '';
$success = '';

// Ambil data user
$query = "
    SELECT u.*, 
           COALESCE((SELECT COUNT(*) FROM user_courses WHERE user_id = u.id), 0) as enrolled_courses,
           COALESCE((SELECT COUNT(*) FROM user_missions WHERE user_id = u.id AND status = 'completed'), 0) as completed_missions,
           COALESCE((SELECT COUNT(*) FROM community_posts WHERE user_id = u.id), 0) as total_posts
    FROM users u 
    WHERE u.id = ?
";

$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Proses update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $region = trim($_POST['region'] ?? '');

    // Validasi input
    if (empty($full_name)) {
        $error = 'Nama lengkap tidak boleh kosong';
    } elseif (empty($email)) {
        $error = 'Email tidak boleh kosong';
    } else {
        try {
            // Cek apakah email sudah digunakan oleh user lain
            $check_email = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->execute([$email, $user['id']]);
            if ($check_email->rowCount() > 0) {
                $error = 'Email sudah digunakan oleh user lain';
            } else {
                // Update profil
                $query = "UPDATE users SET full_name = ?, email = ?, bio = ?, region = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $result = $stmt->execute([$full_name, $email, $bio, $region, $user['id']]);
                
                if ($result) {
                    $success = 'Profil berhasil diperbarui';
                    
                    // Refresh data user
                    $query = "SELECT u.*, 
                             COALESCE((SELECT COUNT(*) FROM user_courses WHERE user_id = u.id), 0) as enrolled_courses,
                             COALESCE((SELECT COUNT(*) FROM user_missions WHERE user_id = u.id AND status = 'completed'), 0) as completed_missions,
                             COALESCE((SELECT COUNT(*) FROM community_posts WHERE user_id = u.id), 0) as total_posts
                      FROM users u 
                      WHERE u.id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user['id']]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Update session data
                    $_SESSION['user_full_name'] = $full_name;
                } else {
                    $error = 'Gagal memperbarui profil';
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - EduConnect</title>
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
                        <a class="nav-link" href="community.php">Komunitas</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="profile.php">Profil</a></li>
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
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo $user_data['profile_picture'] ?? 'assets/images/default-avatar.jpg'; ?>" 
                             class="rounded-circle mb-3" width="120" height="120" alt="Profile Picture">
                        <h2 class="h4 mb-1"><?php echo htmlspecialchars($user_data['full_name']); ?></h2>
                        <p class="text-muted mb-3"><?php echo ucfirst($user_data['role']); ?></p>
                        
                        <div class="row text-center g-3">
                            <div class="col">
                                <div class="h5 mb-0"><?php echo $user_data['enrolled_courses'] ?? 0; ?></div>
                                <small class="text-muted">Kelas</small>
                            </div>
                            <div class="col">
                                <div class="h5 mb-0"><?php echo $user_data['completed_missions'] ?? 0; ?></div>
                                <small class="text-muted">Misi</small>
                            </div>
                            <div class="col">
                                <div class="h5 mb-0"><?php echo $user_data['total_posts'] ?? 0; ?></div>
                                <small class="text-muted">Post</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h3 mb-4">Edit Profil</h1>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="region" class="form-label">Daerah</label>
                                <input type="text" class="form-control" id="region" name="region" 
                                       value="<?php echo htmlspecialchars($user_data['region'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Statistik</label>
                                <div class="row g-3">
                                    <div class="col">
                                        <div class="p-3 border rounded text-center">
                                            <div class="h4 mb-0"><?php echo $user_data['points'] ?? 0; ?></div>
                                            <small class="text-muted">Poin</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3 border rounded text-center">
                                            <div class="h4 mb-0"><?php echo $user_data['experience'] ?? 0; ?></div>
                                            <small class="text-muted">XP</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

