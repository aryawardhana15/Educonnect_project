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

// Ambil ID kelas dari parameter URL
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header('Location: /index.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_data = $auth->getUserById($user_id);

// Ambil detail kelas
$query = "SELECT c.*, u.full_name as mentor_name 
          FROM courses c 
          JOIN users u ON c.mentor_id = u.id 
          WHERE c.id = ? AND c.type = 'premium'";
$stmt = $auth->db->prepare($query);
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: /index.php');
    exit;
}

// Cek apakah user sudah terdaftar di kelas ini
$query = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
$stmt = $auth->db->prepare($query);
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($enrollment) {
    header('Location: /course.php?id=' . $course_id);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payment_method = $_POST['payment_method'] ?? '';
        $amount = $course['price'];
        
        if (empty($payment_method)) {
            throw new Exception("Silakan pilih metode pembayaran");
        }
        
        // Generate unique transaction ID
        $transaction_id = 'TRX-' . time() . '-' . rand(1000, 9999);
        
        // Simpan transaksi ke database
        $query = "INSERT INTO transactions (user_id, course_id, amount, payment_method, transaction_id, status) 
                  VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $auth->db->prepare($query);
        $stmt->execute([$user_id, $course_id, $amount, $payment_method, $transaction_id]);
        
        // Redirect ke halaman konfirmasi pembayaran
        header('Location: /payment_confirmation.php?transaction_id=' . $transaction_id);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - EduConnect</title>
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
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Pembayaran</h1>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Course Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="font-semibold text-gray-900 mb-2">Detail Kelas</h2>
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="text-gray-600">Mentor: <?php echo htmlspecialchars($course['mentor_name']); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-primary">
                            Rp <?php echo number_format($course['price'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" class="space-y-6">
                <!-- Payment Method -->
                <div>
                    <h2 class="font-semibold text-gray-900 mb-4">Metode Pembayaran</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Bank Transfer -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="bank_transfer" class="sr-only" required>
                            <div class="flex items-center">
                                <i class="fas fa-university text-2xl text-gray-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Transfer Bank</p>
                                    <p class="text-sm text-gray-500">BCA, Mandiri, BNI, BRI</p>
                                </div>
                            </div>
                        </label>

                        <!-- E-Wallet -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="e_wallet" class="sr-only">
                            <div class="flex items-center">
                                <i class="fas fa-wallet text-2xl text-gray-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">E-Wallet</p>
                                    <p class="text-sm text-gray-500">GoPay, OVO, DANA, LinkAja</p>
                                </div>
                            </div>
                        </label>

                        <!-- Credit Card -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="credit_card" class="sr-only">
                            <div class="flex items-center">
                                <i class="fas fa-credit-card text-2xl text-gray-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Kartu Kredit</p>
                                    <p class="text-sm text-gray-500">Visa, Mastercard, JCB</p>
                                </div>
                            </div>
                        </label>

                        <!-- QRIS -->
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="qris" class="sr-only">
                            <div class="flex items-center">
                                <i class="fas fa-qrcode text-2xl text-gray-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">QRIS</p>
                                    <p class="text-sm text-gray-500">Scan QR Code</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h2 class="font-semibold text-gray-900 mb-4">Ringkasan Pembayaran</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Kelas</span>
                            <span class="text-gray-900">Rp <?php echo number_format($course['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Diskon</span>
                            <span class="text-green-600">- Rp 0</span>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-900">Total</span>
                                <span class="font-bold text-primary">Rp <?php echo number_format($course['price'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition">
                    Bayar Sekarang
                </button>
            </form>
        </div>
    </div>

    <script>
        // Handle payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(input => {
            input.addEventListener('change', function() {
                // Remove selected class from all labels
                document.querySelectorAll('label').forEach(label => {
                    label.classList.remove('border-primary');
                });
                
                // Add selected class to current label
                if (this.checked) {
                    this.closest('label').classList.add('border-primary');
                }
            });
        });
    </script>
</body>
</html> 