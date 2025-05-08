<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth();

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil transaction ID dari URL
$transaction_id = $_GET['transaction_id'] ?? null;
if (!$transaction_id) {
    header('Location: /index.php');
    exit;
}

// Ambil data transaksi
$query = "SELECT t.*, c.title as course_title, c.price, u.full_name as mentor_name 
          FROM transactions t 
          JOIN courses c ON t.course_id = c.id 
          JOIN users u ON c.mentor_id = u.id 
          WHERE t.transaction_id = ? AND t.user_id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$transaction_id, $_SESSION['user_id']]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header('Location: /index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .text-primary { color: #4F46E5; }
        .bg-primary { background-color: #4F46E5; }
        .hover\:bg-primary-dark:hover { background-color: #4338CA; }
        .border-primary { border-color: #4F46E5; }
    </style>
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
                    <a href="/profile.php" class="text-gray-700 hover:text-primary">
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
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-3xl text-green-500"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Berhasil</h1>
                <p class="text-gray-600">Terima kasih telah mendaftar di kelas kami</p>
            </div>

            <!-- Transaction Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Detail Transaksi</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID Transaksi</span>
                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($transaction['transaction_id']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kelas</span>
                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($transaction['course_title']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Mentor</span>
                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($transaction['mentor_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Metode Pembayaran</span>
                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($transaction['payment_method']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Pembayaran</span>
                        <span class="font-bold text-primary">Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Menunggu Verifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-medium text-blue-800 mb-2">Langkah Selanjutnya</h3>
                <ul class="list-disc list-inside text-blue-700 space-y-1">
                    <li>Tim kami akan memverifikasi pembayaran Anda dalam waktu 1x24 jam</li>
                    <li>Anda akan menerima email konfirmasi setelah pembayaran diverifikasi</li>
                    <li>Setelah diverifikasi, Anda dapat mengakses kelas melalui dashboard</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/course.php?id=<?php echo $transaction['course_id']; ?>" 
                   class="flex-1 bg-gray-100 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-200 transition">
                    Kembali ke Kelas
                </a>
                <a href="/dashboard/student/index.php" 
                   class="flex-1 bg-primary text-white text-center py-3 rounded-lg hover:bg-primary-dark transition">
                    Lihat Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html> 