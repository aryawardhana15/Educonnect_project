<?php
require_once('config.php');
require_once('db_connect.php');
require_once 'auth/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}
$db = db();
$role = $user['role'];

// Handle booking slot mentoring
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_slot') {
    $mentor_id = (int)$_POST['mentor_id'];
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $topic = trim($_POST['topic']);
    
    // Validasi input
    if (empty($session_date) || empty($session_time) || empty($topic)) {
        $error = "Semua field harus diisi";
    } else {
        // Cek apakah slot masih tersedia
        $query = "SELECT COUNT(*) FROM mentoring_sessions 
                 WHERE mentor_id = ? AND session_date = ? AND session_time = ? AND status = 'scheduled'";
        $stmt = $db->prepare($query);
        $stmt->execute([$mentor_id, $session_date, $session_time]);
        $slot_count = $stmt->fetchColumn();
        
        if ($slot_count > 0) {
            $error = "Slot sudah dipesan oleh siswa lain";
        } else {
            // Insert booking baru
            $query = "INSERT INTO mentoring_sessions (mentor_id, student_id, session_date, session_time, topic, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, 'scheduled', NOW())";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$mentor_id, $user['id'], $session_date, $session_time, $topic])) {
                $success = "Booking berhasil! Silakan cek jadwal Anda.";
                // Redirect untuk menghindari resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = "Gagal melakukan booking. Silakan coba lagi.";
            }
        }
    }
}

// Handle pembatalan sesi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_session') {
    $session_id = (int)$_POST['session_id'];
    
    // Cek apakah sesi milik user
    $query = "SELECT * FROM mentoring_sessions WHERE id = ? AND student_id = ? AND status = 'scheduled'";
    $stmt = $db->prepare($query);
    $stmt->execute([$session_id, $user['id']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        // Update status sesi
        $query = "UPDATE mentoring_sessions SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$session_id])) {
            $success = "Sesi berhasil dibatalkan";
            // Redirect untuk menghindari resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Gagal membatalkan sesi. Silakan coba lagi.";
        }
    } else {
        $error = "Sesi tidak ditemukan atau tidak dapat dibatalkan";
    }
}

// Handle pengiriman pertanyaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ask_question') {
    $mentor_id = (int)$_POST['mentor_id'];
    $topic = trim($_POST['topic']);
    $question = trim($_POST['question']);
    $file = '';
    
    // Handle file upload jika ada
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/questions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                $file = $uploadFile;
            }
        }
    }
    
    // Validasi input
    if (empty($topic) || empty($question)) {
        $error = "Topik dan pertanyaan harus diisi";
    } else {
        // Insert pertanyaan baru
        $query = "INSERT INTO mentoring_questions (mentor_id, student_id, topic, question, file, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$mentor_id, $user['id'], $topic, $question, $file])) {
            $success = "Pertanyaan berhasil dikirim!";
            // Redirect untuk menghindari resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Gagal mengirim pertanyaan. Silakan coba lagi.";
        }
    }
}

// Handle penambahan slot oleh mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slot' && $role === 'mentor') {
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $duration = (int)$_POST['duration'];
    
    // Validasi input
    if (empty($session_date) || empty($session_time) || $duration <= 0) {
        $error = "Semua field harus diisi dengan benar";
    } else {
        // Insert slot baru
        $query = "INSERT INTO mentor_schedules (mentor_id, session_date, session_time, duration, status, created_at) 
                 VALUES (?, ?, ?, ?, 'available', NOW())";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$user['id'], $session_date, $session_time, $duration])) {
            $success = "Slot berhasil ditambahkan";
            // Redirect untuk menghindari resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Gagal menambahkan slot. Silakan coba lagi.";
        }
    }
}

// Handle penghapusan slot oleh mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_slot' && $role === 'mentor') {
    $slot_id = (int)$_POST['slot_id'];
    
    // Cek apakah slot milik mentor
    $query = "SELECT * FROM mentor_schedules WHERE id = ? AND mentor_id = ? AND status = 'available'";
    $stmt = $db->prepare($query);
    $stmt->execute([$slot_id, $user['id']]);
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($slot) {
        // Hapus slot
        $query = "DELETE FROM mentor_schedules WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$slot_id])) {
            $success = "Slot berhasil dihapus";
            // Redirect untuk menghindari resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Gagal menghapus slot. Silakan coba lagi.";
        }
    } else {
        $error = "Slot tidak ditemukan atau tidak dapat dihapus";
    }
}

// Query untuk mengambil sesi berikutnya
$query = "SELECT ms.*, u.full_name as mentor_name, u.profile_picture as mentor_image
          FROM mentoring_sessions ms
          JOIN users u ON ms.mentor_id = u.id
          WHERE ms.student_id = ? AND ms.status = 'scheduled'
          ORDER BY ms.session_date ASC, ms.session_time ASC
          LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$nextSession = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'date' => null,
    'time' => null,
    'mentor_name' => null,
    'topic' => null,
    'link' => null,
    'status' => null
];

// Query untuk mengambil sesi aktif
$query = "SELECT ms.*, u.full_name as mentor_name, u.profile_picture as mentor_image
          FROM mentoring_sessions ms
          JOIN users u ON ms.mentor_id = u.id
          WHERE ms.student_id = ? AND ms.status = 'scheduled'
          ORDER BY ms.session_date ASC, ms.session_time ASC";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$activeSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil riwayat sesi
$query = "SELECT ms.*, u.full_name as mentor_name, u.profile_picture as mentor_image
          FROM mentoring_sessions ms
          JOIN users u ON ms.mentor_id = u.id
          WHERE ms.student_id = ? AND ms.status = 'completed'
          ORDER BY ms.session_date DESC, ms.session_time DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$historySessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil pertanyaan
$query = "SELECT mq.*, u.full_name as mentor_name, u.profile_picture as mentor_image
          FROM mentoring_questions mq
          JOIN users u ON mq.mentor_id = u.id
          WHERE mq.student_id = ?
          ORDER BY mq.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil daftar mentor dengan filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$field = isset($_GET['field']) ? $_GET['field'] : '';
$rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$availability = isset($_GET['availability']) ? $_GET['availability'] : '';

$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM mentoring_sessions ms WHERE ms.mentor_id = u.id AND ms.status = 'completed') as total_sessions,
          (SELECT AVG(overall_rating) FROM mentoring_ratings mr WHERE mr.mentor_id = u.id) as avg_rating,
          (SELECT COUNT(*) FROM mentor_schedules ms WHERE ms.mentor_id = u.id AND ms.status = 'available' AND ms.session_date >= CURDATE()) as available_slots
          FROM users u
          WHERE u.role = 'mentor'";

$params = array();

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.bio LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($field)) {
    $query .= " AND u.field = ?";
    $params[] = $field;
}

if ($rating > 0) {
    $query .= " AND (SELECT AVG(overall_rating) FROM mentoring_ratings mr WHERE mr.mentor_id = u.id) >= ?";
    $params[] = $rating;
}

if ($availability === 'today') {
    $query .= " AND EXISTS (SELECT 1 FROM mentor_schedules ms WHERE ms.mentor_id = u.id AND ms.status = 'available' AND ms.session_date = CURDATE())";
} elseif ($availability === 'this_week') {
    $query .= " AND EXISTS (SELECT 1 FROM mentor_schedules ms WHERE ms.mentor_id = u.id AND ms.status = 'available' AND ms.session_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY))";
}

$query .= " ORDER BY avg_rating DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$mentors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil progress belajar
$query = "SELECT DISTINCT topic, 
          CASE WHEN EXISTS (
              SELECT 1 FROM mentoring_sessions ms2 
              WHERE ms2.student_id = ? AND ms2.topic = ms1.topic AND ms2.status = 'completed'
          ) THEN 1 ELSE 0 END as done
          FROM mentoring_sessions ms1
          WHERE ms1.student_id = ?
          ORDER BY topic";
$stmt = $db->prepare($query);
$stmt->execute([$user['id'], $user['id']]);
$progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil testimoni
$query = "SELECT ms.*, u.full_name as mentor_name, u.profile_picture as mentor_image
          FROM mentoring_sessions ms
          JOIN users u ON ms.mentor_id = u.id
          WHERE ms.student_id = ? AND ms.status = 'completed' AND ms.feedback IS NOT NULL
          ORDER BY ms.session_date DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([$user['id']]);
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil slot yang tersedia (untuk mentor)
if ($role === 'mentor') {
    $query = "SELECT * FROM mentor_schedules WHERE mentor_id = ? AND status = 'available' ORDER BY session_date ASC, session_time ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user['id']]);
    $available_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query untuk mengambil sesi yang dijadwalkan
    $query = "SELECT ms.*, u.full_name as student_name, u.profile_picture as student_image
              FROM mentoring_sessions ms
              JOIN users u ON ms.student_id = u.id
              WHERE ms.mentor_id = ? AND ms.status = 'scheduled'
              ORDER BY ms.session_date ASC, ms.session_time ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user['id']]);
    $scheduled_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentoring - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a',
                        },
                    }
                }
            }
        }

            tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                    },
                    animation: {
                        'bounce-slow': 'bounce 3s infinite',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                    },
                    keyframes: {
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar sederhana dengan tombol logout -->
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo dan Brand -->
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="kelas.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="mission.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-tasks"></i>
                        <span>Misi</span>
                    </a>
                    <a href="community.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php
                            if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                            elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                            else echo 'dashboardstudent.php';
                        ?>" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                        
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-white hover:text-primary-200 focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden lg:inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/dashboardadmin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="/auth/login.php" class="font-semibold hover:text-primary-200 flex items-center space-x-1">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-primary-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-primary-700 border-t border-primary-600">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="kelas.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelas
                </a>
                <a href="mission.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-tasks mr-2"></i>Misi
                </a>
                <a href="community.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-users mr-2"></i>Komunitas
                </a>
                <a href="mentoring.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>Mentoring
                </a>
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600"><i class="fas fa-home mr-2"></i> beranda</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php
                    if ($_SESSION['role'] === 'admin') echo 'dashboardadmin.php';
                    elseif ($_SESSION['role'] === 'mentor') echo 'dashboardmentor.php';
                    else echo 'dashboardstudent.php';
                ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>

                <hr class="my-2 border-primary-600">
                <a href="/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/dashboardadmin.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-cog mr-2"></i>Admin Panel
                </a>
                <?php endif; ?>
                <a href="/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-300 hover:bg-primary-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
                <?php else: ?>
                <a href="/auth/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-primary-600">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

<script>
    // Dropdown logic
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    document.addEventListener('click', function (e) {
        if (avatarBtn && avatarBtn.contains(e.target)) {
            dropdownMenu.classList.toggle('hidden');
        } else if (dropdownMenu && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });

    // Mobile menu logic
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuButton.addEventListener('click', function () {
        mobileMenu.classList.toggle('hidden');
    });
</script>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- 1. Header/Overview Mentoring -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl p-6 mb-8 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-6 shadow-lg">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2 flex items-center"><i class="fas fa-chalkboard-teacher mr-3"></i> Mentoring</h1>
                <div class="flex flex-wrap gap-4 text-base">
                    <div><i class="fas fa-calendar-alt mr-1"></i> Sesi Berikutnya: <b><?= $nextSession['date'] ?? '-' ?> <?= $nextSession['time'] ?? '-' ?></b></div>
                    <div><i class="fas fa-user-friends mr-1"></i> Mentor Tersedia: <b><?= count($mentors) ?></b></div>
                    <div><i class="fas fa-envelope mr-1"></i> Pertanyaan Terakhir: <b><?= $questions[0]['topic'] ?? '-' ?></b></div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <a href="#book" class="bg-white text-primary-600 px-6 py-2 rounded-lg font-medium hover:bg-gray-100 transition text-center"><i class="fas fa-calendar-plus mr-2"></i>Booking Sesi</a>
                <a href="#ask" class="border-2 border-white text-white px-6 py-2 rounded-lg font-medium hover:bg-white hover:text-primary-600 transition text-center"><i class="fas fa-question-circle mr-2"></i>Kirim Pertanyaan</a>
            </div>
        </div>
        <!-- Tambahkan setelah header/overview mentoring -->
        <?php if ($role === 'student'): ?>
        <!-- Section Booking Slot Mentoring (Siswa) -->
        <div class="mb-8" id="booking">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-calendar-plus mr-2 text-primary-500"></i> Booking Jadwal Mentoring</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $error ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $success ?></span>
                </div>
            <?php endif; ?>
            <form method="POST" class="bg-white rounded-xl shadow p-6 flex flex-col gap-4 mb-4">
                <input type="hidden" name="action" value="book_slot">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Mentor</label>
                    <select name="mentor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                        <?php foreach ($mentors as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= $m['full_name'] ?? '-' ?> (<?= $m['bio'] ?? '-' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="session_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam</label>
                    <input type="time" name="session_time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Topik</label>
                    <input type="text" name="topic" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" placeholder="Contoh: Algoritma Dasar" required>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-lg transition w-full md:w-auto"><i class="fas fa-calendar-check mr-2"></i>Booking Sekarang</button>
            </form>
        </div>
        <?php elseif ($role === 'mentor'): ?>
        <!-- Section Kelola Slot Mentoring (Mentor) -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-calendar-plus mr-2 text-primary-500"></i> Kelola Slot Mentoring</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $error ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $success ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Form Tambah Slot -->
            <form method="POST" class="bg-white rounded-xl shadow p-6 flex flex-col gap-4 mb-6">
                <input type="hidden" name="action" value="add_slot">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="session_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                        <input type="time" name="session_time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                        <input type="number" name="duration" min="30" step="30" value="60" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-lg transition w-full md:w-auto"><i class="fas fa-plus-circle mr-2"></i>Tambah Slot</button>
            </form>
            
            <!-- Daftar Slot Tersedia -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Slot Tersedia</h3>
                <?php if (empty($available_slots)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">Belum ada slot yang tersedia.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($available_slots as $slot): ?>
                        <div class="bg-white rounded-xl shadow p-4 flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-bold text-primary-700"><?= date('d M Y', strtotime($slot['session_date'])) ?></div>
                                    <div class="text-gray-600 text-sm"><?= date('H:i', strtotime($slot['session_time'])) ?> - <?= date('H:i', strtotime($slot['session_time'] . ' + ' . $slot['duration'] . ' minutes')) ?></div>
                                </div>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="delete_slot">
                                    <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus slot ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="text-sm text-gray-500">Durasi: <?= $slot['duration'] ?> menit</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Daftar Sesi Terjadwal -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Sesi Terjadwal</h3>
                <?php if (empty($scheduled_sessions)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">Belum ada sesi yang terjadwal.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($scheduled_sessions as $session): ?>
                        <div class="bg-white rounded-xl shadow p-4 flex flex-col">
                            <div class="flex items-center mb-3">
                                <img src="<?= $session['student_image'] ?: 'assets/images/default-avatar.png' ?>" alt="<?= $session['student_name'] ?>" class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <div class="font-bold text-primary-700"><?= $session['student_name'] ?></div>
                                    <div class="text-gray-600 text-sm"><?= date('d M Y', strtotime($session['session_date'])) ?> <?= date('H:i', strtotime($session['session_time'])) ?></div>
                                </div>
                            </div>
                            <div class="text-gray-700 mb-2">Topik: <?= $session['topic'] ?></div>
                            <div class="flex gap-2">
                                <a href="<?= $session['link'] ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition text-sm"><i class="fas fa-video mr-1"></i>Join</a>
                                <button class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded text-sm"><i class="fas fa-bell mr-1"></i>Reminder</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- END Section Booking/Kelola Slot -->
        <!-- 2. Sesi Aktif / Terjadwal -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-clock mr-2 text-primary-500"></i> Sesi Aktif / Terjadwal</h2>
            <?php if (empty($activeSessions)): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">Belum ada sesi terjadwal.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($activeSessions as $s): ?>
                    <div class="bg-white rounded-xl shadow p-5 flex flex-col gap-2">
                        <div class="flex justify-between items-center mb-2">
                            <div class="font-bold text-lg text-primary-700"><?= $s['topic'] ?></div>
                            <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-xs font-semibold">Terjadwal</span>
                        </div>
                        <div class="text-gray-600 text-sm mb-1"><i class="fas fa-user mr-1"></i> Mentor: <?= $s['mentor_name'] ?></div>
                        <div class="text-gray-600 text-sm mb-1"><i class="fas fa-calendar-alt mr-1"></i> <?= $s['session_date'] ?> <?= $s['session_time'] ?></div>
                        <div class="flex gap-2 mt-2">
                            <a href="<?= $s['link'] ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition text-sm"><i class="fas fa-video mr-1"></i>Join</a>
                            <button class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded text-sm"><i class="fas fa-bell mr-1"></i>Reminder</button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="cancel_session">
                                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm hover:bg-red-200 transition" onclick="return confirm('Apakah Anda yakin ingin membatalkan sesi ini?')"><i class="fas fa-times mr-1"></i>Batalkan</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <!-- 3. Riwayat Mentoring -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-history mr-2 text-primary-500"></i> Riwayat Mentoring</h2>
            <?php if (empty($historySessions)): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">Belum ada riwayat sesi.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($historySessions as $h): ?>
                    <div class="bg-white rounded-xl shadow p-5 flex flex-col gap-2">
                        <div class="flex justify-between items-center mb-2">
                            <div class="font-bold text-lg text-primary-700"><?= $h['topic'] ?></div>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Selesai</span>
                        </div>
                        <div class="text-gray-600 text-sm mb-1"><i class="fas fa-user mr-1"></i> Mentor: <?= $h['mentor_name'] ?></div>
                        <div class="text-gray-600 text-sm mb-1"><i class="fas fa-calendar-alt mr-1"></i> <?= $h['session_date'] ?> <?= $h['session_time'] ?></div>
                        <div class="text-gray-600 text-sm mb-1"><i class="fas fa-sticky-note mr-1"></i> Catatan: <?= $h['note'] ?></div>
                        <div class="flex gap-2 mt-2">
                            <a href="<?= $h['replay'] ?>" class="bg-purple-100 text-purple-700 px-3 py-1 rounded text-sm"><i class="fas fa-play mr-1"></i>Replay</a>
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm"><i class="fas fa-star mr-1"></i>Feedback: <?= $h['feedback'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <!-- 4. Kirim Pertanyaan ke Mentor -->
        <div class="mb-8" id="ask">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-question-circle mr-2 text-primary-500"></i> Kirim Pertanyaan ke Mentor</h2>
            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-6 flex flex-col gap-4 mb-4">
                <input type="hidden" name="action" value="ask_question">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Mentor</label>
                    <select name="mentor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" required>
                        <?php foreach ($mentors as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= $m['full_name'] ?? '-' ?> (<?= $m['bio'] ?? '-' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Topik</label>
                    <input type="text" name="topic" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" placeholder="Contoh: JavaScript Dasar" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                    <textarea name="question" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" placeholder="Tulis pertanyaan Anda di sini..." required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (Opsional)</label>
                    <input type="file" name="file" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Format yang didukung: JPG, PNG, PDF, DOC, DOCX</p>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-lg transition w-full md:w-auto"><i class="fas fa-paper-plane mr-2"></i>Kirim Pertanyaan</button>
            </form>
            <div class="mt-4">
                <h3 class="font-semibold mb-2 text-gray-700">Jawaban Mentor</h3>
                <?php foreach ($questions as $q): ?>
                <div class="bg-gray-50 rounded-lg p-4 mb-2">
                    <div class="font-bold text-primary-700 mb-1"><?= $q['topic'] ?></div>
                    <div class="text-gray-700 mb-1"><?= $q['question'] ?></div>
                    <div class="text-green-700 text-sm"><i class="fas fa-reply mr-1"></i><?= $q['answer'] ?></div>
                    <div class="text-xs text-gray-400 mt-1">Dijawab: <?= $q['created_at'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- 5. Rekomendasi Mentor -->
        <div class="mb-8" id="book">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-user-tie mr-2 text-primary-500"></i> Rekomendasi Mentor</h2>
            <div class="mb-8">
                <form method="GET" class="bg-white rounded-xl shadow p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Mentor</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500" placeholder="Nama atau keahlian...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bidang</label>
                            <select name="field" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Semua Bidang</option>
                                <option value="Programming" <?= $field === 'Programming' ? 'selected' : '' ?>>Programming</option>
                                <option value="Design" <?= $field === 'Design' ? 'selected' : '' ?>>Design</option>
                                <option value="Business" <?= $field === 'Business' ? 'selected' : '' ?>>Business</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating Minimal</label>
                            <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                <option value="0">Semua Rating</option>
                                <option value="4" <?= $rating == 4 ? 'selected' : '' ?>>4.0+</option>
                                <option value="4.5" <?= $rating == 4.5 ? 'selected' : '' ?>>4.5+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ketersediaan</label>
                            <select name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Semua Waktu</option>
                                <option value="today" <?= $availability === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                <option value="this_week" <?= $availability === 'this_week' ? 'selected' : '' ?>>Minggu Ini</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-lg transition">
                            <i class="fas fa-search mr-2"></i>Cari Mentor
                        </button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php foreach ($mentors as $m): ?>
                <div class="bg-white rounded-xl shadow p-5 flex flex-col items-center">
                    <img src="<?= $m['profile_picture'] ?? 'assets/images/default-avatar.png' ?>" class="w-20 h-20 rounded-full border-4 border-primary-200 mb-3">
                    <div class="font-bold text-lg text-primary-700 mb-1"><?= $m['full_name'] ?? '-' ?></div>
                    <div class="text-gray-500 text-sm mb-1"><?= $m['bio'] ?? '-' ?></div>
                    
                    <!-- Rating Detail -->
                    <div class="w-full mt-2 space-y-1">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Komunikasi:</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="font-semibold"><?= number_format($m['communication_rating'] ?? 0, 1) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Pengetahuan:</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="font-semibold"><?= number_format($m['knowledge_rating'] ?? 0, 1) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Mengajar:</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="font-semibold"><?= number_format($m['teaching_rating'] ?? 0, 1) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-400 mt-2"><?= $m['total_sessions'] ?? 0 ?> sesi • <?= $m['available_slots'] ?? 0 ?> slot tersedia</div>
                    
                    <div class="flex gap-2 mt-4">
                        <a href="#book" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-1 rounded transition text-sm">
                            <i class="fas fa-calendar-plus mr-1"></i>Ajukan Sesi
                        </a>
                        <a href="#ask" class="bg-gray-100 hover:bg-gray-200 text-primary-700 px-4 py-1 rounded transition text-sm">
                            <i class="fas fa-comment-dots mr-1"></i>Kirim Pertanyaan
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- 6. Progress Belajar dengan Mentor -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-chart-line mr-2 text-primary-500"></i> Progress Belajar</h2>
            <div class="bg-white rounded-xl shadow p-6">
                <ul class="space-y-2">
                    <?php foreach ($progress as $p): ?>
                    <li class="flex items-center gap-3">
                        <span class="inline-block w-6 h-6 rounded-full text-center text-white font-bold <?php echo $p['done'] ? 'bg-green-500' : 'bg-gray-300'; ?>">
                            <i class="fas <?php echo $p['done'] ? 'fa-check' : 'fa-minus'; ?>"></i>
                        </span>
                        <span class="font-medium text-gray-700"><?= $p['topic'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <!-- 7. Testimoni atau Pesan dari Mentor -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-quote-left mr-2 text-primary-500"></i> Pesan dari Mentor</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($testimonials as $t): ?>
                <div class="bg-white rounded-xl shadow p-5 flex flex-col">
                    <div class="font-bold text-primary-700 mb-2"><i class="fas fa-user-tie mr-2"></i><?= $t['mentor_name'] ?></div>
                    <div class="text-gray-700 italic">"<?= $t['feedback'] ?>"</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <footer class="bg-gray-800 text-white py-8 mt-10">
        <div class="container mx-auto px-4 text-center text-gray-400">
            &copy; <?= date('Y') ?> EduConnect. All rights reserved.
        </div>
    </footer>
    <script>
    // Dropdown logic
    const userDropdownBtn = document.getElementById('userDropdownBtn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    document.addEventListener('click', function(e) {
        if (userDropdownBtn.contains(e.target)) {
            userDropdownMenu.classList.toggle('hidden');
        } else if (!userDropdownMenu.contains(e.target)) {
            userDropdownMenu.classList.add('hidden');
        }
    });
    </script>
</body>
</html> 