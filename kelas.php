<?php
// kelas.php
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

// Filter
$type = $_GET['type'] ?? 'all';
$level = $_GET['level'] ?? 'all';
$education_level = $_GET['education_level'] ?? 'all';
$subject = $_GET['subject'] ?? 'all';

// Query untuk courses
$query = "SELECT c.*, u.full_name as mentor_name, 
          CASE WHEN uc.user_id IS NOT NULL THEN uc.status ELSE 'not_started' END as user_status,
          CASE WHEN uc.user_id IS NOT NULL THEN uc.payment_status ELSE 'pending' END as payment_status
          FROM courses c
          JOIN users u ON c.mentor_id = u.id
          LEFT JOIN user_courses uc ON c.id = uc.course_id AND uc.user_id = ?
          WHERE 1=1";

$params = [$user['id']];

if ($type !== 'all') {
    $query .= " AND c.type = ?";
    $params[] = $type;
}

if ($level !== 'all') {
    $query .= " AND c.level = ?";
    $params[] = $level;
}

if ($education_level !== 'all') {
    $query .= " AND c.education_level = ?";
    $params[] = $education_level;
}

if ($subject !== 'all') {
    $query .= " AND c.subject = ?";
    $params[] = $subject;
}

$query .= " ORDER BY c.education_level, c.grade, c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk bootcamp
$query_bootcamp = "SELECT b.*, u.full_name as mentor_name,
                   CASE WHEN ub.user_id IS NOT NULL THEN ub.status ELSE 'not_registered' END as user_status,
                   CASE WHEN ub.user_id IS NOT NULL THEN ub.payment_status ELSE 'pending' END as payment_status
                   FROM bootcamp b
                   JOIN users u ON b.mentor_id = u.id
                   LEFT JOIN user_bootcamp ub ON b.id = ub.bootcamp_id AND ub.user_id = ?
                   WHERE b.status = 'upcoming'
                   ORDER BY b.start_date ASC";

$stmt = $db->prepare($query_bootcamp);
$stmt->execute([$user['id']]);
$bootcamps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique subjects for filter
$stmt = $db->query("SELECT DISTINCT subject FROM courses ORDER BY subject");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas - EduConnect</title>
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
                        <a class="nav-link active" href="kelas.php">Kelas</a>
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
    <div class="container py-5">
        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="btn-group w-100" role="group">
                    <a href="?type=all" class="btn btn-outline-primary <?php echo $type === 'all' ? 'active' : ''; ?>">Semua</a>
                    <a href="?type=free" class="btn btn-outline-primary <?php echo $type === 'free' ? 'active' : ''; ?>">Gratis</a>
                    <a href="?type=premium" class="btn btn-outline-primary <?php echo $type === 'premium' ? 'active' : ''; ?>">Berbayar</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100" role="group">
                    <a href="?education_level=all" class="btn btn-outline-primary <?php echo $education_level === 'all' ? 'active' : ''; ?>">Semua</a>
                    <a href="?education_level=sd" class="btn btn-outline-primary <?php echo $education_level === 'sd' ? 'active' : ''; ?>">SD</a>
                    <a href="?education_level=smp" class="btn btn-outline-primary <?php echo $education_level === 'smp' ? 'active' : ''; ?>">SMP</a>
                    <a href="?education_level=sma" class="btn btn-outline-primary <?php echo $education_level === 'sma' ? 'active' : ''; ?>">SMA</a>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" onchange="window.location.href=this.value">
                    <option value="?subject=all">Semua Mata Pelajaran</option>
                    <?php foreach ($subjects as $sub): ?>
                    <option value="?subject=<?php echo urlencode($sub); ?>" <?php echo $subject === $sub ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100" role="group">
                    <a href="?level=all" class="btn btn-outline-primary <?php echo $level === 'all' ? 'active' : ''; ?>">Semua Level</a>
                    <a href="?level=beginner" class="btn btn-outline-primary <?php echo $level === 'beginner' ? 'active' : ''; ?>">Pemula</a>
                    <a href="?level=intermediate" class="btn btn-outline-primary <?php echo $level === 'intermediate' ? 'active' : ''; ?>">Menengah</a>
                    <a href="?level=advanced" class="btn btn-outline-primary <?php echo $level === 'advanced' ? 'active' : ''; ?>">Lanjutan</a>
                </div>
            </div>
        </div>

        <!-- Courses -->
        <h2 class="mb-4">Kelas</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            <?php foreach ($courses as $course): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-<?php echo $course['type'] === 'free' ? 'success' : 'primary'; ?>">
                                <?php echo $course['type'] === 'free' ? 'Gratis' : 'Rp ' . number_format($course['price'], 0, ',', '.'); ?>
                            </span>
                            <span class="badge bg-info">
                                <?php 
                                if ($course['education_level'] !== 'umum') {
                                    echo strtoupper($course['education_level']) . ' Kelas ' . $course['grade'];
                                } else {
                                    echo ucfirst($course['level']);
                                }
                                ?>
                            </span>
                        </div>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($course['mentor_name']); ?>
                            </small>
                        </p>
                        <?php if ($course['user_status'] === 'not_started'): ?>
                            <?php if ($course['type'] === 'free'): ?>
                            <a href="course/enroll.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">
                                Mulai Belajar
                            </a>
                            <?php else: ?>
                            <a href="course/payment.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">
                                Daftar Kelas
                            </a>
                            <?php endif; ?>
                        <?php elseif ($course['user_status'] === 'in_progress'): ?>
                            <a href="course/learn.php?id=<?php echo $course['id']; ?>" class="btn btn-success w-100">
                                Lanjutkan Belajar
                            </a>
                        <?php else: ?>
                            <a href="course/learn.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary w-100">
                                Lihat Materi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Bootcamps -->
        <h2 class="mb-4">Bootcamp</h2>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($bootcamps as $bootcamp): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo $bootcamp['thumbnail'] ?? 'assets/images/default-bootcamp.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($bootcamp['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($bootcamp['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($bootcamp['description']); ?></p>
                        <div class="mb-3">
                            <p class="mb-1">
                                <i class="bi bi-calendar"></i> 
                                <?php echo date('d M Y', strtotime($bootcamp['start_date'])); ?> - 
                                <?php echo date('d M Y', strtotime($bootcamp['end_date'])); ?>
                            </p>
                            <p class="mb-1">
                                <i class="bi bi-clock"></i> <?php echo $bootcamp['duration']; ?> minggu
                            </p>
                            <p class="mb-1">
                                <i class="bi bi-people"></i> 
                                <?php echo $bootcamp['current_students']; ?>/<?php echo $bootcamp['max_students']; ?> peserta
                            </p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0">Rp <?php echo number_format($bootcamp['price'], 0, ',', '.'); ?></span>
                            <span class="badge bg-primary">Bootcamp</span>
                        </div>
                        <?php if ($bootcamp['user_status'] === 'not_registered'): ?>
                        <a href="bootcamp/register.php?id=<?php echo $bootcamp['id']; ?>" class="btn btn-primary w-100">
                            Daftar Bootcamp
                        </a>
                        <?php elseif ($bootcamp['user_status'] === 'registered'): ?>
                        <a href="bootcamp/payment.php?id=<?php echo $bootcamp['id']; ?>" class="btn btn-warning w-100">
                            Selesaikan Pembayaran
                        </a>
                        <?php else: ?>
                        <a href="bootcamp/dashboard.php?id=<?php echo $bootcamp['id']; ?>" class="btn btn-success w-100">
                            Masuk Bootcamp
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>