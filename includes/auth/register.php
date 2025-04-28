<?php
require_once('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'siswa';
    
    // Validasi input
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Nama lengkap harus diisi';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email tidak valid';
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'Password minimal 8 karakter';
    }
    
    if (empty($errors)) {
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'Email sudah terdaftar';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verificationToken = bin2hex(random_bytes(32));
            
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashedPassword, $role, $verificationToken);
            
            if ($stmt->execute()) {
                // Kirim email verifikasi
                $verificationLink = "https://" . $_SERVER['HTTP_HOST'] . "/includes/auth/verify.php?token=" . $verificationToken;
                $subject = "Verifikasi Akun EduConnect Anda";
                $message = "Halo $name,\n\nSilakan klik link berikut untuk verifikasi akun Anda:\n$verificationLink";
                $headers = "From: no-reply@educonnect.id";
                
                mail($email, $subject, $message, $headers);
                
                $_SESSION['registration_success'] = true;
                header("Location: register-success.php");
                exit;
            } else {
                $errors['database'] = 'Terjadi kesalahan saat menyimpan data';
            }
        }
    }
}

$pageTitle = "Daftar Akun";
include('../../includes/header.php');
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Buat Akun Baru
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Sudah punya akun? <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Masuk di sini</a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if (isset($errors['database'])): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo $errors['database']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="" method="POST">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    </div>
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Daftar sebagai</label>
                    <div class="mt-1">
                        <select id="role" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="siswa" <?php echo ($role ?? 'siswa') == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                            <option value="mentor" <?php echo ($role ?? 'siswa') == 'mentor' ? 'selected' : ''; ?>>Mentor</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Daftar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>