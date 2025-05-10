<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_data = $auth->getUserById($user_id);

// Ambil riwayat transaksi user
$query = "SELECT t.*, c.title as course_title, c.price, u.full_name as mentor_name
          FROM transactions t
          JOIN courses c ON t.course_id = c.id
          JOIN users u ON c.mentor_id = u.id
          WHERE t.user_id = ?
          ORDER BY t.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <?php
                    $user = $auth->getCurrentUser();
                    switch ($user['role']) {
                        case 'admin':
                            echo '<a href="dashboardadmin.php" class="text-gray-700 hover:text-primary">';
                            break;
                        case 'mentor':
                            echo '<a href="dashboardmentor.php" class="text-gray-700 hover:text-primary">';
                            break;
                        case 'student':
                            echo '<a href="dashboardstudent.php" class="text-gray-700 hover:text-primary">';
                            break;
                        default:
                            echo '<a href="dashboard.php" class="text-gray-700 hover:text-primary">';
                    }
                    ?>
                        <i class="fas fa-user-circle text-xl"></i>
                    </a>
                    <a href="/auth/logout.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Riwayat Transaksi</h1>
                <a href="/" class="text-primary hover:text-primary-dark">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Beranda
                </a>
            </div>

            <?php if (empty($transactions)): ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Transaksi</h2>
                <p class="text-gray-600 mb-6">Anda belum memiliki riwayat transaksi</p>
                <a href="/courses.php" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">
                    Jelajahi Kelas
                </a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo $transaction['transaction_id']; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($transaction['course_title']); ?></div>
                                <div class="text-sm text-gray-500">Mentor: <?php echo htmlspecialchars($transaction['mentor_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    <?php
                                    switch ($transaction['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'pending_confirmation':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php
                                    switch ($transaction['status']) {
                                        case 'pending':
                                            echo 'Menunggu Pembayaran';
                                            break;
                                        case 'pending_confirmation':
                                            echo 'Menunggu Konfirmasi';
                                            break;
                                        case 'completed':
                                            echo 'Selesai';
                                            break;
                                        default:
                                            echo ucfirst($transaction['status']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($transaction['status'] === 'pending'): ?>
                                <a href="/payment_confirmation.php?transaction_id=<?php echo $transaction['transaction_id']; ?>" 
                                   class="text-primary hover:text-primary-dark">
                                    Lanjutkan Pembayaran
                                </a>
                                <?php elseif ($transaction['status'] === 'completed'): ?>
                                <a href="/course.php?id=<?php echo $transaction['course_id']; ?>" 
                                   class="text-primary hover:text-primary-dark">
                                    Akses Kelas
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 