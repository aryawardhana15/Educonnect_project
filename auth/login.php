<?php
// auth/login.php
require_once('../config.php');
require_once('../db_connect.php');
require_once 'auth.php';

// Inisialisasi Auth
$auth = new Auth();

// Jika sudah login, redirect ke dashboard sesuai role
if ($auth->isLoggedIn()) {
    $user_data = $auth->getUserById($_SESSION['user_id']);
    switch ($user_data['role']) {
        case 'admin':
            header('Location: ../dashboardadmin.php');
            break;
        case 'mentor':
            header('Location: ../dashboardmentor.php');
            break;
        case 'student':
            header('Location: ../dashboardstudent.php');
            break;
        default:
            header('Location: ../index.php');
    }
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $remember_me = isset($_POST['remember_me']);

    // Validasi input
    if (empty($email)) {
        $error = "Email tidak boleh kosong";
    } elseif (empty($password)) {
        $error = "Password tidak boleh kosong";
    } else {
        try {
            if ($auth->login($email, $password, $role)) {
                // Set remember me cookie jika dicentang
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 hari
                    
                    // Simpan token ke database
                    $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("isi", $_SESSION['user_id'], $token, $expires);
                    $stmt->execute();
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }

                $user_data = $auth->getUserById($_SESSION['user_id']);
                switch ($user_data['role']) {
                    case 'admin':
                        header('Location: ../dashboardadmin.php');
                        break;
                    case 'mentor':
                        header('Location: ../dashboardmentor.php');
                        break;
                    case 'student':
                        header('Location: ../dashboardstudent.php');
                        break;
                    default:
                        header('Location: ../index.php');
                }
                exit;
            } else {
                $error = "Email atau password salah";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .text-primary { color: #4F46E5; }
        .bg-primary { background-color: #4F46E5; }
        .hover\:bg-primary-dark:hover { background-color: #4338CA; }
        .hover\:text-primary-dark:hover { color: #4338CA; }
        .focus\:ring-primary:focus { --tw-ring-color: #4F46E5; }
        .focus\:border-primary:focus { border-color: #4F46E5; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <a href="/" class="flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-primary text-4xl mr-2"></i>
                    <span class="text-3xl font-bold">EduConnect</span>
                </a>
                <h2 class="mt-6 text-2xl font-bold text-gray-900">Masuk ke Akun Anda</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Atau
                    <a href="register.php" class="font-medium text-primary hover:text-primary-dark">
                        daftar akun baru
                    </a>
                </p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="nama@email.com">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                   placeholder="••••••••">
                            <button type="button" 
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Masuk Sebagai</label>
                        <select id="role" name="role" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Siswa</option>
                            <option value="mentor" <?php echo (isset($_POST['role']) && $_POST['role'] === 'mentor') ? 'selected' : ''; ?>>Mentor</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                               <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>>
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Ingat saya
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="forgot_password.php" class="font-medium text-primary hover:text-primary-dark">
                            Lupa password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt"></i>
                        </span>
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">
                            Atau masuk dengan
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" 
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-google text-red-600 mr-2"></i>
                        Google
                    </button>
                    <button type="button" 
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fab fa-facebook text-blue-600 mr-2"></i>
                        Facebook
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.querySelector('.fa-eye');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>