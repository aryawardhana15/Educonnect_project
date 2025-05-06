<?php
// auth/login.php
require_once('../config.php');
require_once('../db_connect.php');
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../kelas.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password, profile_picture FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            header('Location: ../kelas.php');
            exit;
        } else {
            $error = 'Email atau kata sandi salah.';
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
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .login-box {
            background: linear-gradient(135deg, #4F46E5, #10B981);
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="login-box bg-white p-8 rounded-xl shadow-lg max-w-md w-full" data-aos="fade-up">
        <div class="flex justify-center mb-6">
            <i class="fas fa-graduation-cap text-primary text-4xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 text-center mb-4">Masuk ke EduConnect</h1>
        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Kata Sandi</label>
                <input type="password" name="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <button type="submit" class="w-full bg-primary text-white p-3 rounded-lg hover:bg-primary-dark transition">Masuk</button>
        </form>
        <p class="text-center mt-4 text-gray-600">
            Belum punya akun? <a href="register.php" class="text-primary hover:underline">Daftar</a>
        </p>
    </div>

    <script>
        AOS.init();
    </script>
</body>
</html>