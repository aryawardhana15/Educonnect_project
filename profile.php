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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(247,250,252,0.9) 100%);
            backdrop-filter: blur(5px);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .profile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(14, 165, 233, 0.2);
        }
        .stat-card {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(247,250,252,0.95) 100%);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #f0f9ff, #e0f2fe, #bae6fd, #7dd3fc);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
    <script>
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen">
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    
    <!-- Navbar (same as previous page) -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-xl font-bold">EduConnect</a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="mission.php" class="hover:text-gray-200">Misi</a>
                    <a href="leaderboard.php" class="hover:text-gray-200">Leaderboard</a>
                    <a href="community.php" class="hover:text-gray-200">Komunitas</a>
                    <a href="dashboard.php" class="hover:text-gray-200">Dashboard</a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <img src="<?php echo $user_data['profile_picture'] ?? 'assets/images/default-avatar.jpg'; ?>" 
                                 class="w-8 h-8 rounded-full border-2 border-white">
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50">Profil</a>
                            <?php if ($auth->hasRole('admin')): ?>
                            <a href="admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50">Admin Panel</a>
                            <?php endif; ?>
                            <div class="border-t border-gray-100"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50">Keluar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Profile Sidebar -->
            <div class="w-full md:w-1/3">
                <div class="profile-card p-6 shadow-lg border border-white/30">
                    <div class="flex flex-col items-center">
                        <img src="<?php echo $user_data['profile_picture'] ?? 'assets/images/default-avatar.jpg'; ?>" 
                             class="w-32 h-32 rounded-full border-4 border-white shadow-lg mb-4 object-cover">
                        <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user_data['full_name']); ?></h2>
                        <span class="text-sm text-primary-600 font-medium mb-4"><?php echo ucfirst($user_data['role']); ?></span>
                        
                        <div class="w-full grid grid-cols-3 gap-2 mb-6">
                            <div class="stat-card p-3 rounded-lg text-center">
                                <div class="text-2xl font-bold text-primary-600"><?php echo $user_data['enrolled_courses'] ?? 0; ?></div>
                                <div class="text-xs text-gray-500">Kelas</div>
                            </div>
                            <div class="stat-card p-3 rounded-lg text-center">
                                <div class="text-2xl font-bold text-primary-600"><?php echo $user_data['completed_missions'] ?? 0; ?></div>
                                <div class="text-xs text-gray-500">Misi</div>
                            </div>
                            <div class="stat-card p-3 rounded-lg text-center">
                                <div class="text-2xl font-bold text-primary-600"><?php echo $user_data['total_posts'] ?? 0; ?></div>
                                <div class="text-xs text-gray-500">Post</div>
                            </div>
                        </div>
                        
                        <div class="w-full space-y-3">
                            <div class="flex items-center">
                                <div class="w-8 text-primary-600">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-700">Level</div>
                                    <div class="text-xs text-gray-500">Pembelajar Aktif</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 text-primary-600">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-700">Lokasi</div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user_data['region'] ?? 'Belum diatur'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 text-primary-600">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-700">Bergabung</div>
                                    <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($user_data['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Achievement Badges -->
                <div class="profile-card mt-6 p-6 shadow-lg border border-white/30">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-trophy text-yellow-500 mr-2"></i> Prestasi
                    </h3>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-yellow-50 rounded-full w-12 h-12 flex items-center justify-center text-yellow-500">
                            <i class="fas fa-star text-xl"></i>
                        </div>
                        <div class="bg-blue-50 rounded-full w-12 h-12 flex items-center justify-center text-blue-500">
                            <i class="fas fa-book-reader text-xl"></i>
                        </div>
                        <div class="bg-green-50 rounded-full w-12 h-12 flex items-center justify-center text-green-500">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="bg-purple-50 rounded-full w-12 h-12 flex items-center justify-center text-purple-500">
                            <i class="fas fa-comments text-xl"></i>
                        </div>
                        <div class="bg-pink-50 rounded-full w-12 h-12 flex items-center justify-center text-pink-500">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <div class="bg-indigo-50 rounded-full w-12 h-12 flex items-center justify-center text-indigo-500">
                            <i class="fas fa-bolt text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="w-full md:w-2/3">
                <div class="profile-card p-6 shadow-lg border border-white/30">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">Edit Profil</h1>
                        <div class="flex space-x-2">
                            <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                                <i class="fas fa-eye mr-2"></i> Lihat Profil
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" id="username" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                       value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                            </div>
                            
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" id="full_name" name="full_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                       value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                   value="<?php echo htmlspecialchars($user_data['email']); ?>">
                        </div>
                        
                        <div class="mb-6">
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                            <textarea id="bio" name="bio" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-6">
                            <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Daerah</label>
                            <input type="text" id="region" name="region"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                   value="<?php echo htmlspecialchars($user_data['region'] ?? ''); ?>">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="stat-card p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Total Poin</div>
                                        <div class="text-2xl font-bold text-primary-600"><?php echo $user_data['points'] ?? 0; ?></div>
                                    </div>
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-600">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Total XP</div>
                                        <div class="text-2xl font-bold text-primary-600"><?php echo $user_data['experience'] ?? 0; ?></div>
                                    </div>
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-600">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition flex items-center">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Progress Section -->
                <div class="profile-card mt-6 p-6 shadow-lg border border-white/30">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-primary-600 mr-2"></i> Progress Pembelajaran
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Kelas Selesai</span>
                                <span>2 dari 5</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-primary-600 h-2.5 rounded-full" style="width: 40%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Misi Terselesaikan</span>
                                <span><?php echo $user_data['completed_missions'] ?? 0; ?> dari 10</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" 

                style="width: <?php echo min(100, (($user_data['completed_missions'] ?? 0) * 10)); ?>%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Level Pembelajaran</span>
                                <span>3 dari 10</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-purple-500 h-2.5 rounded-full" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date('Y'); ?> EduConnect. Semua Hak Dilindungi.</p>
        </div>
    </footer>
</body>
</html>