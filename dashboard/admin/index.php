<?php
require_once('../../config.php');
require_once('../../db_connect.php');
require_once('../../auth/auth.php');

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login dan role admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_data = $auth->getUserById($user_id);

// Ambil statistik
$stats = [
    'total_users' => 0,
    'total_students' => 0,
    'total_mentors' => 0,
    'total_courses' => 0,
    'total_free_courses' => 0,
    'total_premium_courses' => 0,
    'total_transactions' => 0,
    'total_revenue' => 0,
    'monthly_revenue' => 0
];

// Total Users
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
            SUM(CASE WHEN role = 'mentor' THEN 1 ELSE 0 END) as mentors
          FROM users";
$result = $conn->query($query);
$user_stats = $result->fetch_assoc();
$stats['total_users'] = $user_stats['total'];
$stats['total_students'] = $user_stats['students'];
$stats['total_mentors'] = $user_stats['mentors'];

// Total Courses
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN price = 0 THEN 1 ELSE 0 END) as free,
            SUM(CASE WHEN price > 0 THEN 1 ELSE 0 END) as premium
          FROM courses";
$result = $conn->query($query);
$course_stats = $result->fetch_assoc();
$stats['total_courses'] = $course_stats['total'];
$stats['total_free_courses'] = $course_stats['free'];
$stats['total_premium_courses'] = $course_stats['premium'];

// Total Transactions & Revenue
$query = "SELECT 
            COUNT(*) as total,
            SUM(amount) as revenue,
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) 
                     AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                THEN amount ELSE 0 END) as monthly_revenue
          FROM transactions 
          WHERE status = 'completed'";
$result = $conn->query($query);
$transaction_stats = $result->fetch_assoc();
$stats['total_transactions'] = $transaction_stats['total'];
$stats['total_revenue'] = $transaction_stats['revenue'];
$stats['monthly_revenue'] = $transaction_stats['monthly_revenue'];

// Ambil data revenue 6 bulan terakhir untuk chart
$query = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as revenue
          FROM transactions 
          WHERE status = 'completed'
          AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
          ORDER BY month ASC";
$result = $conn->query($query);
$revenue_data = [];
while ($row = $result->fetch_assoc()) {
    $revenue_data[] = [
        'month' => date('M Y', strtotime($row['month'] . '-01')),
        'revenue' => $row['revenue']
    ];
}

// Ambil user terbaru
$query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($query);
$recent_users = $result->fetch_all(MYSQLI_ASSOC);

// Ambil kelas terbaru
$query = "SELECT c.*, u.full_name as mentor_name 
          FROM courses c 
          JOIN users u ON c.mentor_id = u.id 
          ORDER BY c.created_at DESC LIMIT 5";
$result = $conn->query($query);
$recent_courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">
                        <i class="fas fa-user-shield mr-2"></i>
                        <?php echo htmlspecialchars($user_data['full_name']); ?>
                    </span>
                    <a href="/auth/logout.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Selamat datang, <?php echo htmlspecialchars($user_data['full_name']); ?>!</h1>
            <p class="text-gray-600">Kelola sistem EduConnect Anda</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Pengguna</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_users']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Courses -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-book text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Kelas</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_courses']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-exchange-alt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Transaksi</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_transactions']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">Rp <?php echo number_format($stats['monthly_revenue']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Grafik Pendapatan</h2>
                <canvas id="revenueChart" height="300"></canvas>
            </div>

            <!-- Recent Users -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pengguna Terbaru</h2>
                    <a href="/admin/users.php" class="text-primary hover:text-primary-dark text-sm">
                        Lihat Semua
                    </a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($recent_users as $user): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo ucfirst($user['role']); ?></p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Courses -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Kelas Terbaru</h2>
                    <a href="/admin/courses.php" class="text-primary hover:text-primary-dark text-sm">
                        Lihat Semua
                    </a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($recent_courses as $course): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-book text-gray-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['title']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['mentor_name']); ?></p>
                            </div>
                        </div>
                        <div class="text-sm font-medium text-gray-900">
                            Rp <?php echo number_format($course['price']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($revenue_data, 'month')); ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?php echo json_encode(array_column($revenue_data, 'revenue')); ?>,
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 
</html> 