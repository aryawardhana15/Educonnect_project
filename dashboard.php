<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$user = $auth->getCurrentUser();
$db = db(); // Inisialisasi koneksi database
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduConnect</title>
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
    <div class="container my-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 class="rounded-circle" width="100" height="100" alt="Profile Picture">
                            <h5 class="mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">Edit Profil</a>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Statistik</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Poin</span>
                            <span class="badge bg-primary"><?php echo $user['points']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Pengalaman</span>
                            <span class="badge bg-success"><?php echo $user['experience']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9">
                <?php if ($user['role'] === 'student'): ?>
                    <!-- Student Dashboard -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Kelas Saya</h5>
                            <div class="row">
                                <?php
                                $stmt = $db->prepare("
                                    SELECT c.*, u.full_name as mentor_name 
                                    FROM courses c 
                                    JOIN user_courses uc ON c.id = uc.course_id 
                                    JOIN users u ON c.mentor_id = u.id 
                                    WHERE uc.user_id = ?
                                ");
                                $stmt->execute([$user['id']]);
                                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($courses)): ?>
                                    <div class="col-12">
                                        <p class="text-muted">Anda belum mengikuti kelas apapun.</p>
                                        <a href="kelas.php" class="btn btn-primary">Jelajahi Kelas</a>
                                    </div>
                                <?php else: 
                                    foreach ($courses as $course): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <img src="<?php echo $course['image'] ?? 'assets/images/default-course.jpg'; ?>" 
                                                     class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                    <p class="card-text small text-muted">
                                                        Mentor: <?php echo htmlspecialchars($course['mentor_name']); ?>
                                                    </p>
                                                    <a href="course.php?id=<?php echo $course['id']; ?>" 
                                                       class="btn btn-sm btn-primary">Lanjutkan Belajar</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Active Missions -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Misi Aktif</h5>
                            <?php
                            $stmt = $db->prepare("
                                SELECT m.*, um.status 
                                FROM missions m 
                                JOIN user_missions um ON m.id = um.mission_id 
                                WHERE um.user_id = ? AND um.status != 'completed'
                            ");
                            $stmt->execute([$user['id']]);
                            $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($missions)): ?>
                                <p class="text-muted">Tidak ada misi aktif.</p>
                            <?php else: 
                                foreach ($missions as $mission): ?>
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($mission['title']); ?></h6>
                                            <p class="card-text small"><?php echo htmlspecialchars($mission['description']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-<?php echo $mission['status'] === 'in_progress' ? 'warning' : 'info'; ?>">
                                                    <?php echo ucfirst($mission['status']); ?>
                                                </span>
                                                <a href="mission.php?id=<?php echo $mission['id']; ?>" 
                                                   class="btn btn-sm btn-primary">Lihat Detail</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                    </div>

                <?php elseif ($user['role'] === 'mentor'): ?>
                    <!-- Mentor Dashboard -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Kelas Saya</h5>
                            <div class="row">
                                <?php
                                $stmt = $db->prepare("SELECT * FROM courses WHERE mentor_id = ?");
                                $stmt->execute([$user['id']]);
                                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($courses)): ?>
                                    <div class="col-12">
                                        <p class="text-muted">Anda belum membuat kelas apapun.</p>
                                        <a href="course/create.php" class="btn btn-primary">Buat Kelas Baru</a>
                                    </div>
                                <?php else: 
                                    foreach ($courses as $course): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <img src="<?php echo $course['image'] ?? 'assets/images/default-course.jpg'; ?>" 
                                                     class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                    <p class="card-text small text-muted">
                                                        Level: <?php echo ucfirst($course['level']); ?>
                                                    </p>
                                                    <div class="d-flex gap-2">
                                                        <a href="course/edit.php?id=<?php echo $course['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <a href="course.php?id=<?php echo $course['id']; ?>" 
                                                           class="btn btn-sm btn-primary">Lihat</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Active Missions -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Misi Aktif</h5>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM missions WHERE mentor_id = ?");
                            $stmt->execute([$user['id']]);
                            $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($missions)): ?>
                                <p class="text-muted">Tidak ada misi aktif.</p>
                                <a href="mission/create.php" class="btn btn-primary">Buat Misi Baru</a>
                            <?php else: 
                                foreach ($missions as $mission): ?>
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($mission['title']); ?></h6>
                                            <p class="card-text small"><?php echo htmlspecialchars($mission['description']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">
                                                    <?php echo $mission['points']; ?> Poin
                                                </span>
                                                <div class="d-flex gap-2">
                                                    <a href="mission/edit.php?id=<?php echo $mission['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <a href="mission.php?id=<?php echo $mission['id']; ?>" 
                                                       class="btn btn-sm btn-primary">Lihat</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                    </div>

                <?php elseif ($user['role'] === 'admin'): ?>
                    <!-- Admin Dashboard -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Users</h5>
                                    <?php
                                    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
                                    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                    <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Courses</h5>
                                    <?php
                                    $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
                                    $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                    <h2 class="mb-0"><?php echo $total_courses; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Missions</h5>
                                    <?php
                                    $stmt = $db->query("SELECT COUNT(*) as total FROM missions");
                                    $total_missions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                    <h2 class="mb-0"><?php echo $total_missions; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Aksi Cepat</h5>
                            <div class="d-flex gap-2">
                                <a href="admin/users.php" class="btn btn-primary">Kelola Users</a>
                                <a href="admin/courses.php" class="btn btn-primary">Kelola Courses</a>
                                <a href="admin/missions.php" class="btn btn-primary">Kelola Missions</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 