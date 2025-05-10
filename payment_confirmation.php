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
$db = db();
$query = "SELECT t.*, c.title as course_title, c.price, u.full_name as mentor_name 
          FROM transactions t 
          JOIN courses c ON t.course_id = c.id 
          JOIN users u ON c.mentor_id = u.id 
          WHERE t.transaction_id = ? AND t.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$transaction_id, $_SESSION['user_id']]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header('Location: /index.php');
    exit;
}

// Handle form submission untuk konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Upload bukti pembayaran
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Silakan upload bukti pembayaran");
        }

        $file = $_FILES['payment_proof'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Format file tidak didukung. Gunakan JPG, JPEG, atau PNG");
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            throw new Exception("Ukuran file terlalu besar. Maksimal 2MB");
        }

        // Generate nama file unik
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . $transaction_id . '_' . time() . '.' . $extension;
        $upload_path = 'uploads/payment_proofs/' . $filename;

        // Buat direktori jika belum ada
        if (!file_exists('uploads/payment_proofs')) {
            mkdir('uploads/payment_proofs', 0777, true);
        }

        // Upload file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("Gagal mengupload file");
        }

        // Update status transaksi
        $query = "UPDATE transactions SET 
                  status = 'pending_verification',
                  payment_proof = ?,
                  updated_at = NOW()
                  WHERE transaction_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$upload_path, $transaction_id]);

        // Redirect ke halaman sukses
        header('Location: /payment_success.php?transaction_id=' . $transaction_id);
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
    <title>Konfirmasi Pembayaran - EduConnect</title>
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
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Konfirmasi Pembayaran</h1>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
            <?php endif; ?>

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
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Instruksi Pembayaran</h2>
                <div class="space-y-4">
                    <?php if ($transaction['payment_method'] === 'bank_transfer'): ?>
                    <div>
                        <p class="font-medium text-gray-900 mb-2">Transfer Bank</p>
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-600 mb-1">Bank BCA</p>
                            <p class="font-mono text-lg">1234567890</p>
                            <p class="text-sm text-gray-500">a.n. EduConnect</p>
                        </div>
                    </div>
                    <?php elseif ($transaction['payment_method'] === 'e_wallet'): ?>
                    <div>
                        <p class="font-medium text-gray-900 mb-2">E-Wallet</p>
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-600 mb-1">GoPay</p>
                            <p class="font-mono text-lg">0812-3456-7890</p>
                            <p class="text-sm text-gray-500">a.n. EduConnect</p>
                        </div>
                    </div>
                    <?php elseif ($transaction['payment_method'] === 'credit_card'): ?>
                    <div>
                        <p class="font-medium text-gray-900 mb-2">Kartu Kredit</p>
                        <p class="text-gray-600">Anda akan diarahkan ke halaman pembayaran yang aman.</p>
                    </div>
                    <?php elseif ($transaction['payment_method'] === 'qris'): ?>
                    <div>
                        <p class="font-medium text-gray-900 mb-2">QRIS</p>
                        <div class="bg-white p-4 rounded-lg border text-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($transaction_id); ?>" 
                                 alt="QR Code" class="mx-auto mb-2">
                            <p class="text-sm text-gray-500">Scan QR code di atas menggunakan aplikasi e-wallet Anda</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upload Proof Form -->
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Bukti Pembayaran
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none">
                                    <span>Upload file</span>
                                    <input id="payment_proof" name="payment_proof" type="file" class="sr-only" accept="image/*" required>
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, JPEG sampai 2MB
                            </p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition">
                    Konfirmasi Pembayaran
                </button>
            </form>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('payment_proof').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'mx-auto h-32 object-contain';
                    
                    const container = document.querySelector('.border-dashed');
                    container.innerHTML = '';
                    container.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 