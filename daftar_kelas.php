<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$user = $isLoggedIn ? $auth->getCurrentUser() : null;

$db = db();

// Filter (optional, if filters are applied)
$type = $_GET['type'] ?? 'all';
$education_level = $_GET['education_level'] ?? 'all';
$subject = $_GET['subject'] ?? 'all';
$level = $_GET['level'] ?? 'all';

// Query all courses
$where = [];
$params = [];
if ($type !== 'all') {
    $where[] = 'type = ?';
    $params[] = $type;
}
if ($education_level !== 'all') {
    $where[] = 'education_level = ?';
    $params[] = $education_level;
}
if ($subject !== 'all') {
    $where[] = 'subject = ?';
    $params[] = $subject;
}
if ($level !== 'all') {
    $where[] = 'level = ?';
    $params[] = $level;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $db->prepare("SELECT c.*, u.full_name as mentor_name FROM courses c JOIN users u ON c.mentor_id = u.id $where_sql ORDER BY c.created_at DESC");
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For subject filter
$subjects_stmt = $db->query("SELECT DISTINCT subject FROM courses WHERE subject IS NOT NULL AND subject != ''");
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_COLUMN);

// Dummy bootcamp (or query bootcamp if available)
$bootcamps = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - EduConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
                        accent: {
                            500: '#ec4899',
                            600: '#db2777',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .course-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            perspective: 1000px;
        }
        
        .course-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        .course-card-inner {
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }
        
        .course-card:hover .course-card-inner {
            transform: rotateY(5deg);
        }
        
        .filter-btn {
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background-color: #0ea5e9;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.3);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover:after {
            width: 100%;
        }
        
        .progress-ring {
            transition: stroke-dashoffset 0.5s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold flex items-center">
                        <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        EduConnect
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="kelas.php" class="nav-link font-medium flex items-center space-x-2">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Courses</span>
                    </a>
                    <a href="mission.php" class="nav-link font-medium flex items-center space-x-2">
                        <i class="fas fa-tasks"></i>
                        <span>Missions</span>
                    </a>
                    <a href="community.php" class="nav-link font-medium flex items-center space-x-2">
                        <i class="fas fa-users"></i>
                        <span>Community</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn && $user): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-primary-400 flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Profile</a>
                            <?php if ($auth->hasRole('admin')): ?>
                            <a href="admin/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Admin Panel</a>
                            <?php endif; ?>
                            <div class="border-t border-gray-200"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100">Log Out</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="auth/login.php" class="px-5 py-2 bg-white text-primary-600 font-bold rounded-lg shadow hover:bg-primary-100 transition-all duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Log In
                    </a>
                    <?php endif; ?>
                    <button class="md:hidden focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-primary-600/10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="animate__animated animate__fadeInLeft">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                        Discover the <span class="text-primary-600">Best Courses</span> for Your Future
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-lg">
                        Enhance your skills with high-quality courses and bootcamps from experienced mentors across the globe.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#courses" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fas fa-book-open mr-2"></i> Explore Courses
                        </a>
                        <a href="#bootcamps" class="px-8 py-3 bg-white text-primary-600 border-2 border-primary-600 font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fas fa-laptop-code mr-2"></i> View Bootcamps
                        </a>
                    </div>
                </div>
                <div class="animate__animated animate__fadeInRight hidden md:block">
                    <div class="relative">
                        <div class="absolute -top-10 -left-10 w-64 h-64 bg-accent-500/10 rounded-full filter blur-3xl"></div>
                        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-primary-500/10 rounded-full filter blur-3xl"></div>
                        <img src="https://illustrations.popsy.co/amber/digital-nomad.svg" class="relative z-10 floating w-full max-w-md mx-auto" alt="Learning Illustration">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Filter Section -->
        <div class="glass-card rounded-2xl shadow-xl p-6 mb-12 animate__animated animate__fadeIn">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-sliders-h mr-3 text-primary-600"></i>
                Filter Courses
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Course Type</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?type=all" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $type === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            All
                        </a>
                        <a href="?type=free" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $type === 'free' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Free
                        </a>
                        <a href="?type=premium" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $type === 'premium' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Paid
                        </a>
                    </div>
                </div>
                
                <!-- Education Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Education Level</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?education_level=all" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $education_level === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            All
                        </a>
                        <a href="?education_level=sd" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $education_level === 'sd' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Elementary
                        </a>
                        <a href="?education_level=smp" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $education_level === 'smp' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Junior High
                        </a>
                        <a href="?education_level=sma" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $education_level === 'sma' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Senior High
                        </a>
                    </div>
                </div>
                
                <!-- Subject Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Subject</label>
                    <div class="relative">
                        <select onchange="window.location.href=this.value" class="appearance-none w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 bg-white shadow-sm">
                            <option value="?subject=all">All Subjects</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="?subject=<?php echo urlencode($sub); ?>" <?php echo $subject === $sub ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sub); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Difficulty Level</label>
                    <div class="flex flex-wrap gap-2">
                        <a href="?level=all" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $level === 'all' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            All
                        </a>
                        <a href="?level=beginner" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $level === 'beginner' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Beginner
                        </a>
                        <a href="?level=intermediate" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $level === 'intermediate' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Intermediate
                        </a>
                        <a href="?level=advanced" class="filter-btn px-4 py-2 rounded-xl border border-primary-500 text-sm font-medium <?php echo $level === 'advanced' ? 'bg-primary-500 text-white' : 'text-primary-500 hover:bg-primary-50'; ?>">
                            Advanced
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div id="courses" class="mb-16 animate__animated animate__fadeIn">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Available Courses</h2>
                    <p class="text-gray-500">Choose courses that match your learning needs</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo count($courses); ?> Courses Found
                    </span>
                </div>
            </div>
            
            <?php if (empty($courses)): ?>
                <div class="glass-card rounded-2xl p-12 text-center shadow-lg">
                    <div class="mx-auto w-48 h-48 bg-primary-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-book-open text-5xl text-primary-600"></i>
                    </div>
                    <h3 class="text-2xl font-medium text-gray-900 mb-3">No Courses Found</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">Try adjusting your search filters to find courses that suit your needs.</p>
                    <a href="?type=all&level=all&education_level=all&subject=all" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-sync-alt mr-2"></i> Reset All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="relative course-card-inner">
                            <div class="relative h-48 overflow-hidden">
                                <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>"
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute top-4 right-4">
                                    <span class="<?php echo $course['type'] === 'free' ? 'bg-green-500' : 'bg-primary-500'; ?> text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-md">
                                        <?php echo $course['type'] === 'free' ? 'Free' : '$' . number_format($course['price'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-xl text-gray-800 line-clamp-2 leading-tight"><?php echo htmlspecialchars($course['title']); ?></h3>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($course['description']); ?></p>
                                
                                <div class="flex items-center justify-between mb-5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php 
                                        if ($course['education_level'] !== 'umum') {
                                            echo strtoupper($course['education_level']) . ' Grade ' . $course['grade'];
                                        } else {
                                            echo ucfirst($course['level']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-user mr-1.5 text-primary-500"></i>
                                        <?php echo htmlspecialchars($course['mentor_name']); ?>
                                    </div>
                                </div>
                                
                                <?php if (!$isLoggedIn): ?>
                                    <a href="auth/login.php" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                        Log In to Enroll
                                    </a>
                                <?php else: ?>
                                    <?php if ($user['role'] === 'student'): ?>
                                        <?php if ($course['type'] === 'free'): ?>
                                            <a href="course/enroll.php?id=<?php echo $course['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                                Start Learning
                                            </a>
                                        <?php else: ?>
                                            <a href="course/payment.php?id=<?php echo $course['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                                Enroll in Course
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="course/detail.php?id=<?php echo $course['id']; ?>" class="w-full block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-300">
                                            View Details
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bootcamps Section -->
        <div id="bootcamps" class="animate__animated animate__fadeIn">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Upcoming Bootcamps</h2>
                    <p class="text-gray-500">Boost your skills intensively with our bootcamps</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-laptop-code mr-2"></i> <?php echo count($bootcamps); ?> Bootcamps Available
                    </span>
                </div>
            </div>
            
            <?php if (empty($bootcamps)): ?>
                <div class="glass-card rounded-2xl p-12 text-center shadow-lg">
                    <div class="mx-auto w-48 h-48 bg-purple-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-laptop-code text-5xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-medium text-gray-900 mb-3">No Bootcamps Available</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">We are preparing new bootcamps. Keep checking this page for the latest updates!</p>
                    <button class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 cursor-not-allowed opacity-75">
                        <i class="fas fa-bell mr-2"></i> Notify Me
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <?php foreach ($bootcamps as $bootcamp): ?>
                    <div class="course-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="relative course-card-inner">
                            <div class="relative h-56 overflow-hidden">
                                <img src="<?php echo $bootcamp['thumbnail'] ?? 'assets/images/default-bootcamp.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($bootcamp['title']); ?>"
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-purple-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-md">
                                        Bootcamp
                                    </span>
                                </div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <h3 class="text-xl font-bold text-white leading-tight"><?php echo htmlspecialchars($bootcamp['title']); ?></h3>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <p class="text-gray-600 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars($bootcamp['description']); ?></p>
                                
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar-alt mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo date('d M Y', strtotime($bootcamp['start_date'])); ?> - <?php echo date('d M Y', strtotime($bootcamp['end_date'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-clock mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo $bootcamp['duration']; ?> weeks (<?php echo ($bootcamp['duration'] * 10); ?> hours of learning)</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-users mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo $bootcamp['current_students']; ?>/<?php echo $bootcamp['max_students']; ?> participants enrolled</span>
                                        <div class="ml-auto w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500" style="width: <?php echo ($bootcamp['current_students'] / $bootcamp['max_students']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center mb-6">
                                    <span class="text-2xl font-bold text-gray-800">
                                        $<?php echo number_format($bootcamp['price'], 0, ',', '.'); ?>
                                    </span>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="font-medium">4.9</span>
                                        <span class="text-gray-500 text-sm ml-1">(128)</span>
                                    </div>
                                </div>
                                
                                <?php if ($bootcamp['user_status'] === 'not_registered'): ?>
                                <a href="bootcamp/register.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-pen-fancy mr-2"></i> Enroll in Bootcamp
                                </a>
                                <?php elseif ($bootcamp['user_status'] === 'registered'): ?>
                                <a href="bootcamp/payment.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-credit-card mr-2"></i> Complete Payment
                                </a>
                                <?php else: ?>
                                <a href="bootcamp/dashboard.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-door-open mr-2"></i> Access Bootcamp
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($bootcamps as $bootcamp): ?>
                    <div class="course-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="relative course-card-inner">
                            <div class="relative h-56 overflow-hidden">
                                <img src="<?php echo $bootcamp['thumbnail'] ?? 'assets/images/default-bootcamp.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($bootcamp['title']); ?>"
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-purple-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-md">
                                        Bootcamp
                                    </span>
                                </div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <h3 class="text-xl font-bold text-white leading-tight"><?php echo htmlspecialchars($bootcamp['title']); ?></h3>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <p class="text-gray-600 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars($bootcamp['description']); ?></p>
                                
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar-alt mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo date('d M Y', strtotime($bootcamp['start_date'])); ?> - <?php echo date('d M Y', strtotime($bootcamp['end_date'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-clock mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo $bootcamp['duration']; ?> weeks (<?php echo ($bootcamp['duration'] * 10); ?> hours of learning)</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-users mr-3 text-lg text-primary-500"></i>
                                        <span><?php echo $bootcamp['current_students']; ?>/<?php echo $bootcamp['max_students']; ?> participants enrolled</span>
                                        <div class="ml-auto w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500" style="width: <?php echo ($bootcamp['current_students'] / $bootcamp['max_students']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center mb-6">
                                    <span class="text-2xl font-bold text-gray-800">
                                        $<?php echo number_format($bootcamp['price'], 0, ',', '.'); ?>
                                    </span>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="font-medium">4.9</span>
                                        <span class="text-gray-500 text-sm ml-1">(128)</span>
                                    </div>
                                </div>
                                
                                <?php if ($bootcamp['user_status'] === 'not_registered'): ?>
                                <a href="bootcamp/register.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-pen-fancy mr-2"></i> Enroll in Bootcamp
                                </a>
                                <?php elseif ($bootcamp['user_status'] === 'registered'): ?>
                                <a href="bootcamp/payment.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-credit-card mr-2"></i> Complete Payment
                                </a>
                                <?php else: ?>
                                <a href="bootcamp/dashboard.php?id=<?php echo $bootcamp['id']; ?>" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-door-open mr-2"></i> Access Bootcamp
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white py-16 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Your Learning Journey?</h2>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto mb-8">Join thousands of students who have enhanced their skills with EduConnect.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="kelas.php" class="px-8 py-3.5 bg-white text-primary-600 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-book-open mr-2"></i> Explore Courses
                </a>
                <a href="mission.php" class="px-8 py-3.5 bg-transparent border-2 border-white text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-tasks mr-2"></i> View Missions
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <div class="flex items-center mb-6">
                        <svg class="w-8 h-8 mr-2 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-xl font-bold text-white">EduConnect</span>
                    </div>
                    <p class="text-gray-400 mb-6">The best online learning platform for elementary, junior high, and senior high students worldwide.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-6">Menu</h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="kelas.php" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Courses
                            </a>
                        </li>
                        <li>
                            <a href="mission.php" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Missions
                            </a>
                        </li>
                        <li>
                            <a href="community.php" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Community
                            </a>
                        </li>
                        <li>
                            <a href="about.php" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                About Us
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-6">Support</h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                FAQ
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Contact Us
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Privacy Policy
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i>
                                Terms & Conditions
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-6">Newsletter</h4>
                    <p class="text-gray-400 mb-4">Get the latest updates on courses and special promotions.</p>
                    <form class="flex">
                        <input type="email" placeholder="Your Email" class="px-4 py-2 w-full rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-800">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-r-lg transition-colors">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
                <p>© <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    mobileMenu.classList.toggle('animate__fadeInDown');
                });
            }
            
            // Add animation to elements when they come into view
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.animate__animated');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementPosition < windowHeight - 100) {
                        const animation = element.getAttribute('data-animate');
                        element.classList.add(animation);
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on page load
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>