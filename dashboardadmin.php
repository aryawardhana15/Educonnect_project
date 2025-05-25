<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';
require_once 'helpers.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$user = $auth->getCurrentUser();
$db = db(); // Inisialisasi koneksi database

// Cek apakah user adalah admin
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - EduConnect</title>
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
                        <?php
                        // Link dashboard sesuai role
                        switch ($user['role']) {
                            case 'admin':
                                echo '<a class="nav-link active" href="dashboardadmin.php">Dashboard</a>';
                                break;
                            case 'mentor':
                                echo '<a class="nav-link" href="dashboardmentor.php">Dashboard</a>';
                                break;
                            case 'student':
                                echo '<a class="nav-link" href="dashboardstudent.php">Dashboard</a>';
                                break;
                            default:
                                echo '<a class="nav-link" href="dashboard.php">Dashboard</a>';
                        }
                        ?>
                    </li>
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo $user['profile_picture'] ?? getRandomDefaultAvatar($user['id']); ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php
                            switch ($user['role']) {
                                case 'admin':
                                    echo '<li><a class="dropdown-item" href="dashboardadmin.php">Dashboard</a></li>';
                                    break;
                                case 'mentor':
                                    echo '<li><a class="dropdown-item" href="dashboardmentor.php">Dashboard</a></li>';
                                    break;
                                case 'student':
                                    echo '<li><a class="dropdown-item" href="dashboardstudent.php">Dashboard</a></li>';
                                    break;
                                default:
                                    echo '<li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>';
                            }
                            ?>
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
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
                            <img src="<?php echo $user['profile_picture'] ?? getRandomDefaultAvatar($user['id']); ?>" 
                                 class="rounded-circle" width="100" height="100" alt="Profile Picture">
                            <h5 class="mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <p class="text-muted">Admin</p>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">Edit Profil</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9">
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
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
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
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
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
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

                <!-- Aksi Cepat -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Aksi Cepat</h5>
                        <div class="d-flex gap-2">
                            <a href="admin/users.php" class="btn btn-primary">Kelola Users</a>
                            <a href="admin/courses.php" class="btn btn-primary">Kelola Courses</a>
                            <a href="admin/missions.php" class="btn btn-primary">Kelola Missions</a>
                        </div>
                    </div>
                </div>

                <!-- User Terbaru -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">User Terbaru</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo ucfirst($row['role']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Course Terbaru -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Course Terbaru</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Mentor</th>
                                        <th>Level</th>
                                        <th>Tanggal Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $db->query("
                                        SELECT c.*, u.full_name as mentor_name 
                                        FROM courses c 
                                        JOIN users u ON c.mentor_id = u.id 
                                        ORDER BY c.created_at DESC 
                                        LIMIT 5
                                    ");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['mentor_name']); ?></td>
                                            <td><?php echo ucfirst($row['level']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 