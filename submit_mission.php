<?php
require_once 'auth/auth.php';
require_once 'db_connect.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$mission_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error = '';
$success = '';

if (!$mission_id) {
    header('Location: mission.php');
    exit;
}

// Ambil data misi
$db = db();
$stmt = $db->prepare("SELECT * FROM missions WHERE id = ?");
$stmt->execute([$mission_id]);
$mission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mission) {
    header('Location: mission.php');
    exit;
}

// Cek status misi pengguna
$stmt = $db->prepare("SELECT * FROM user_missions WHERE user_id = ? AND mission_id = ?");
$stmt->execute([$user['id'], $mission_id]);
$user_mission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_mission || $user_mission['status'] !== 'in_progress') {
    header("Location: start_mission.php?id=$mission_id");
    exit;
}

if ($user_mission['status'] === 'completed') {
    header("Location: mission.php");
    exit;
}

// Proses pengumpulan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission = trim($_POST['submission'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $submission_file = $_FILES['submission_file'] ?? null;

    // Validasi input
    if (empty($submission) && empty($submission_file['name'])) {
        $error = "Harap masukkan link hasil atau unggah file.";
    } else {
        try {
            // Handle file upload
            if (!empty($submission_file['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB
                $upload_dir = 'uploads/missions/';
                $file_ext = strtolower(pathinfo($submission_file['name'], PATHINFO_EXTENSION));
                $file_name = uniqid('mission_') . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;

                if (!in_array($submission_file['type'], $allowed_types)) {
                    $error = "Jenis file tidak diizinkan. Gunakan JPG, PNG, PDF, GIF, atau WEBP.";
                } elseif ($submission_file['size'] > $max_size) {
                    $error = "Ukuran file terlalu besar. Maksimum 5MB.";
                } elseif (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                    $error = "Gagal membuat direktori upload.";
                } elseif (!move_uploaded_file($submission_file['tmp_name'], $file_path)) {
                    $error = "Gagal mengunggah file.";
                } else {
                    $submission = $file_path; // Store file path
                }
            }

            if (!$error) {
                // Update user_missions
                $stmt = $db->prepare("
                    UPDATE user_missions 
                    SET submission = ?, notes = ?, status = 'completed', submitted_at = NOW(), updated_at = NOW()
                    WHERE user_id = ? AND mission_id = ?
                ");
                $stmt->execute([$submission, $notes, $user['id'], $mission_id]);

                // Debug: Log the submission
                error_log("Mission submitted: user_id={$user['id']}, mission_id=$mission_id, submission=$submission");

                // Update poin user
                $points = $mission['points'] ?? 0;
                $experience = $points * 10; // 10x poin untuk experience
                $stmt = $db->prepare("
                    UPDATE users 
                    SET points = points + ?, experience = experience + ?
                    WHERE id = ?
                ");
                $stmt->execute([$points, $experience, $user['id']]);

                $success = "Misi berhasil dikumpulkan! Anda akan diarahkan kembali ke daftar misi.";
                header("Refresh: 2; url=mission.php");
            }
        } catch (PDOException $e) {
            $error = "Error: " . htmlspecialchars($e->getMessage());
            error_log("Submission error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulkan Misi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .floating-emoji {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0.1;
            z-index: 0;
        }
        .emoji-1 { top: 10%; left: 5%; }
        .emoji-2 { top: 30%; right: 5%; }
        .emoji-3 { bottom: 20%; left: 15%; }
        .emoji-4 { bottom: 10%; right: 10%; }
        .file-input-label {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-input-label:hover {
            background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="kelas.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Kelas</span>
                    </a>
                    <a href="leaderboard.php" class="hover:text-gray-200">Leaderboard</a>
                    <a href="community.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-users"></i>
                        <span>Komunitas</span>
                    </a>
                    <a href="dashboardstudent.php" class="font-semibold hover:text-blue-200 flex items-center space-x-1">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-white hover:text-blue-200 focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden lg:inline"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <hr class="my-1">
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-blue-200 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-700 border-t border-blue-600">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="kelas.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelas
                </a>
                <a href="mission.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-tasks mr-2"></i>Misi
                </a>
                <a href="community.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-users mr-2"></i>Komunitas
                </a>
                <a href="dashboardstudent.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <hr class="my-2 border-blue-600">
                <a href="/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <a href="/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-300 hover:bg-blue-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 py-10 relative">
        <!-- Floating Emojis -->
        <div class="floating-emoji emoji-1 animate-float">🎯</div>
        <div class="floating-emoji emoji-2 animate-float-delay">🏆</div>
        <div class="floating-emoji emoji-3 animate-float">📚</div>
        <div class="floating-emoji emoji-4 animate-float-delay">✨</div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Kumpulkan Misi: <?php echo htmlspecialchars($mission['title']); ?></h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
               

                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <p class="text-sm text-gray-600 mt-2">Anda akan diarahkan dalam beberapa detik.</p>
                </div>
                <div class="text-center">
                    <a href="mission.php" class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Misi
                    </a>
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Detail Misi</h3>
                    <p class="text-gray-600"><strong>Judul:</strong> <?php echo htmlspecialchars($mission['title']); ?></p>
                    <p class="text-gray-600"><strong>Deskripsi:</strong> <?php echo htmlspecialchars($mission['description']); ?></p>
                    <p class="text-gray-600"><strong>Poin:</strong> <?php echo $mission['points'] ?? 0; ?></p>
                    <p class="text-gray-600"><strong>Deadline:</strong> <?php echo date('d M Y', strtotime($mission['deadline'])); ?></p>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-6">
                        <label for="submission" class="block text-gray-700 font-medium mb-2">Link Hasil</label>
                        <input type="url" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="submission" name="submission" placeholder="Masukkan link hasil misi (GitHub, Google Drive, dll)">
                        <p class="text-sm text-gray-500 mt-1">Pastikan link dapat diakses oleh mentor. Kosongkan jika mengunggah file.</p>
                    </div>
                    <div class="mb-6">
                        <label for="submission_file" class="block text-gray-700 font-medium mb-2">Unggah File (JPG, PNG, PDF, GIF, WEBP)</label>
                        <label class="file-input-label">
                            <i class="fas fa-upload mr-2"></i> Pilih File
                            <input type="file" id="submission_file" name="submission_file" accept=".jpg,.jpeg,.png,.pdf,.gif,.webp" class="hidden">
                        </label>
                        <p class="text-sm text-gray-500 mt-2">Maksimum 5MB. Kosongkan jika hanya mengirim link.</p>
                    </div>
                    <div class="mb-6">
                        <label for="notes" class="block text-gray-700 font-medium mb-2">Catatan Tambahan</label>
                        <textarea class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="notes" name="notes" rows="4" placeholder="Tambahkan catatan atau penjelasan tentang hasil misi Anda"></textarea>
                    </div>
                    <div class="flex justify-between items-center">
                        <a href="mission.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl transition-all font-bold transform hover:scale-105">
                            <i class="fas fa-paper-plane mr-2"></i> Kumpulkan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        EduConnect
                    </a>
                    <p class="mt-2 text-gray-400">Platform pembelajaran online terbaik untuk generasi muda.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                        <ul class="space-y-2">
                            <li><a href="kelas.php" class="text-gray-400 hover:text-white transition">Kelas</a></li>
                            <li><a href="mission.php" class="text-gray-400 hover:text-white transition">Misi</a></li>
                            <li><a href="community.php" class="text-gray-400 hover:text-white transition">Komunitas</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Bantuan</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition">FAQ</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Kontak</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a></li>
                        </ul>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <h3 class="text-lg font-semibold mb-4">Ikuti Kami</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>© <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
                } else {
                    mobileMenu.style.maxHeight = '0';
                }
            });

            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    mobileMenu.style.maxHeight = '0';
                }
            });

            // File input preview
            const fileInput = document.getElementById('submission_file');
            fileInput.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'Tidak ada file dipilih';
                const label = this.parentElement;
                label.innerHTML = `<i class="fas fa-upload mr-2"></i> ${fileName}`;
            });
        });
    </script>
</body>
</html>