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
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-post:hover { box-shadow: 0 8px 32px 0 rgba(0,0,0,0.12); transform: translateY(-2px) scale(1.01); }
        .status-badge { font-size: 0.85rem; }
        .point-indicator {
            background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
            box-shadow: 0 4px 24px 0 rgba(14,165,233,0.12);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
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

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Komunitas</h1>
                <p class="text-gray-500">Diskusikan dan berbagi pengalaman dengan sesama pengguna</p>
            </div>
            <div>
                <a href="community/create.php" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Buat Post
                </a>
            </div>
        </div>
        <!-- Post List -->
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($posts as $post): ?>
            <div class="card-post bg-white rounded-2xl shadow p-6 flex flex-col">
                <div class="flex items-center mb-3">
                    <img src="<?php echo $post['profile_picture'] ?? 'assets/images/default-avatar.jpg'; ?>" class="w-10 h-10 rounded-full border-2 border-primary-400 mr-3" alt="Profile Picture">
                    <div>
                        <h5 class="font-bold text-lg mb-0 text-gray-800"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <small class="text-gray-500">
                            Oleh <?php echo htmlspecialchars($post['author_name']); ?> •
                            <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <div class="flex justify-between items-center mt-auto">
                    <div>
                        <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-xs font-semibold mr-2"><?php echo htmlspecialchars($post['category']); ?></span>
                        <span class="text-gray-400 text-xs"><i class="fas fa-comment mr-1"></i><?php echo $post['comment_count']; ?> Komentar</span>
                    </div>
                    <a href="community/post.php?id=<?php echo $post['id']; ?>" class="inline-flex items-center px-3 py-1 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 transition text-sm font-semibold">
                        <i class="fas fa-eye mr-1"></i> Lihat Diskusi
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            // Animasi slide
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
            } else {
                mobileMenu.style.maxHeight = '0';
            }
        });
        // Tutup mobile menu saat klik di luar
        document.addEventListener('click', function(event) {
            if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenu.style.maxHeight = '0';
            }
        });
        // Highlight menu aktif
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('nav a');
        menuLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath.split('/').pop()) {
                link.classList.add('text-primary-200');
            }
        });
    });
    </script>

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
                    },
                }
            }
        }
    }
    </script>
</body>
</html> 