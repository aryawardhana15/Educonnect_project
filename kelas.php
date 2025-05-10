<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas - EduConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Tailwind Config -->
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
    <style>
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .course-card {
            transition: all 0.3s ease;
        }
        .filter-btn.active {
            background-color: #0ea5e9;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold">EduConnect</a>
                </div>
                
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
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-primary-400 flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($user['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Profil</a>
                            <?php if ($auth->hasRole('admin')): ?>
                            <a href="admin/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Admin Panel</a>
                            <?php endif; ?>
                            <div class="border-t border-gray-200"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Keluar</a>
                        </div>
                    </div>
                    <button class="md:hidden focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl p-8 mb-8 text-white">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Temukan Kelas Terbaik Untukmu</h1>
            <p class="text-lg mb-6 max-w-2xl">Tingkatkan keterampilanmu dengan kelas dan bootcamp berkualitas dari mentor berpengalaman.</p>
            <div class="flex space-x-4">
                <a href="#courses" class="bg-white text-primary-600 px-6 py-2 rounded-lg font-medium hover:bg-gray-100 transition duration-300">
                    Lihat Kelas
                </a>
                <a href="#bootcamps" class="border-2 border-white text-white px-6 py-2 rounded-lg font-medium hover:bg-white hover:text-primary-600 transition duration-300">
                    Jelajahi Bootcamp
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Filter Kelas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kelas</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?type=all" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $type === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Semua
                        </a>
                        <a href="?type=free" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $type === 'free' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Gratis
                        </a>
                        <a href="?type=premium" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $type === 'premium' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Berbayar
                        </a>
                    </div>
                </div>
                
                <!-- Education Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang Pendidikan</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?education_level=all" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $education_level === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Semua
                        </a>
                        <a href="?education_level=sd" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $education_level === 'sd' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            SD
                        </a>
                        <a href="?education_level=smp" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $education_level === 'smp' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            SMP
                        </a>
                        <a href="?education_level=sma" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $education_level === 'sma' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            SMA
                        </a>
                    </div>
                </div>
                
                <!-- Subject Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran</label>
                    <select onchange="window.location.href=this.value" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        <option value="?subject=all">Semua Mata Pelajaran</option>
                        <?php foreach ($subjects as $sub): ?>
                        <option value="?subject=<?php echo urlencode($sub); ?>" <?php echo $subject === $sub ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sub); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level Kesulitan</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?level=all" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $level === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Semua
                        </a>
                        <a href="?level=beginner" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $level === 'beginner' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Pemula
                        </a>
                        <a href="?level=intermediate" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $level === 'intermediate' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Menengah
                        </a>
                        <a href="?level=advanced" class="filter-btn px-3 py-1 rounded-full border border-primary-500 text-sm <?php echo $level === 'advanced' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Lanjutan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div id="courses" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Kelas Tersedia</h2>
                <div class="text-primary-600 font-medium"><?php echo count($courses); ?> Kelas Ditemukan</div>
            </div>
            
            <?php if (empty($courses)): ?>
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-600 mb-2">Tidak ada kelas yang ditemukan</h3>
                    <p class="text-gray-500 mb-4">Coba ubah filter pencarian Anda untuk menemukan kelas yang sesuai.</p>
                    <a href="?type=all&level=all&education_level=all&subject=all" class="text-primary-600 font-medium hover:underline">
                        Reset semua filter
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="relative">
                            <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>"
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-3 right-3">
                                <span class="<?php echo $course['type'] === 'free' ? 'bg-green-500' : 'bg-primary-500'; ?> text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    <?php echo $course['type'] === 'free' ? 'Gratis' : 'Rp ' . number_format($course['price'], 0, ',', '.'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-lg text-gray-800 line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    <?php 
                                    if ($course['education_level'] !== 'umum') {
                                        echo strtoupper($course['education_level']) . ' Kelas ' . $course['grade'];
                                    } else {
                                        echo ucfirst($course['level']);
                                    }
                                    ?>
                                </span>
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    <?php echo htmlspecialchars($course['mentor_name']); ?>
                                </div>
                            </div>
                            
                            <?php if ($course['user_status'] === 'not_started'): ?>
                                <?php if ($course['type'] === 'free'): ?>
                                <a href="course/enroll.php?id=<?php echo $course['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                    Mulai Belajar
                                </a>
                                <?php else: ?>
                                <a href="course/payment.php?id=<?php echo $course['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                    Daftar Kelas
                                </a>
                                <?php endif; ?>
                            <?php elseif ($course['user_status'] === 'in_progress'): ?>
                                <a href="course/learn.php?id=<?php echo $course['id']; ?>" class="w-full block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                    Lanjutkan Belajar
                                </a>
                            <?php else: ?>
                                <a href="course/learn.php?id=<?php echo $course['id']; ?>" class="w-full block bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                    Lihat Materi
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bootcamps Section -->
        <div id="bootcamps" class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Bootcamp Mendatang</h2>
                <div class="text-primary-600 font-medium"><?php echo count($bootcamps); ?> Bootcamp Tersedia</div>
            </div>
            
            <?php if (empty($bootcamps)): ?>
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <i class="fas fa-laptop-code text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-600 mb-2">Tidak ada bootcamp yang tersedia saat ini</h3>
                    <p class="text-gray-500">Cek kembali nanti untuk melihat bootcamp terbaru.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($bootcamps as $bootcamp): ?>
                    <div class="course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="relative">
                            <img src="<?php echo $bootcamp['thumbnail'] ?? 'assets/images/default-bootcamp.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($bootcamp['title']); ?>"
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-3 right-3">
                                <span class="bg-purple-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    Bootcamp
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-800 mb-2"><?php echo htmlspecialchars($bootcamp['title']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($bootcamp['description']); ?></p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar-alt mr-2 text-primary-500"></i>
                                    <?php echo date('d M Y', strtotime($bootcamp['start_date'])); ?> - <?php echo date('d M Y', strtotime($bootcamp['end_date'])); ?>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-clock mr-2 text-primary-500"></i>
                                    <?php echo $bootcamp['duration']; ?> minggu
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-users mr-2 text-primary-500"></i>
                                    <?php echo $bootcamp['current_students']; ?>/<?php echo $bootcamp['max_students']; ?> peserta terdaftar
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-bold text-gray-800">
                                    Rp <?php echo number_format($bootcamp['price'], 0, ',', '.'); ?>
                                </span>
                            </div>
                            
                            <?php if ($bootcamp['user_status'] === 'not_registered'): ?>
                            <a href="bootcamp/register.php?id=<?php echo $bootcamp['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                Daftar Bootcamp
                            </a>
                            <?php elseif ($bootcamp['user_status'] === 'registered'): ?>
                            <a href="bootcamp/payment.php?id=<?php echo $bootcamp['id']; ?>" class="w-full block bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                Selesaikan Pembayaran
                            </a>
                            <?php else: ?>
                            <a href="bootcamp/dashboard.php?id=<?php echo $bootcamp['id']; ?>" class="w-full block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                Masuk Bootcamp
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">EduConnect</h3>
                    <p class="text-gray-400">Platform pembelajaran online terbaik untuk siswa SD, SMP, dan SMA.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Menu</h4>
                    <ul class="space-y-2">
                        <li><a href="kelas.php" class="text-gray-400 hover:text-white">Kelas</a></li>
                        <li><a href="mission.php" class="text-gray-400 hover:text-white">Misi</a></li>
                        <li><a href="community.php" class="text-gray-400 hover:text-white">Komunitas</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Bantuan</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Kontak Kami</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle functionality would go here
        document.addEventListener('DOMContentLoaded', function() {
            // You can add any JavaScript interactions here
        });
    </script>
</body>
</html>