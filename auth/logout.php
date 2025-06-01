<?php
// Mulai output buffering
ob_start();

// Debugging: Log semua langkah
error_log("Memulai logout.php", 3, __DIR__ . "/logs/debug.log");

// Include file yang diperlukan
require_once('../config.php');
require_once('../db_connect.php');
require_once('auth.php');

// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_log("Session dimulai", 3, __DIR__ . "/logs/debug.log");

// Inisialisasi Auth dan panggil metode logout
try {
    $auth = new Auth();
    $auth->logout();
    error_log("Metode logout Auth berhasil dipanggil", 3, __DIR__ . "/logs/debug.log");
} catch (Exception $e) {
    error_log("Error saat memanggil Auth logout: " . $e->getMessage(), 3, __DIR__ . "/logs/debug.log");
}

// Hapus cookie "remember me" jika ada (lakukan sebelum session destroy)
if (isset($_COOKIE['remember_token'])) {
    try {
        $db = db();
        $query = "DELETE FROM remember_tokens WHERE token = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_COOKIE['remember_token']]);
        error_log("Token remember me dihapus dari database", 3, __DIR__ . "/logs/debug.log");
    } catch (Exception $e) {
        error_log("Error menghapus remember token dari database: " . $e->getMessage(), 3, __DIR__ . "/logs/debug.log");
    }
    
    // Hapus cookie dengan parameter lengkap
    setcookie('remember_token', '', time() - 3600, '/', '', 
              isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
    error_log("Cookie remember me dihapus", 3, __DIR__ . "/logs/debug.log");
}

// Hapus semua data session
$_SESSION = array();
error_log("Data session dihapus", 3, __DIR__ . "/logs/debug.log");

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    error_log("Session cookie dihapus", 3, __DIR__ . "/logs/debug.log");
}

// Hancurkan session
session_destroy();
error_log("Session dihancurkan", 3, __DIR__ . "/logs/debug.log");

// Bersihkan output buffer sebelum redirect
ob_end_clean();

// Set header untuk mencegah caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect ke index.php
$redirect_url = defined('BASE_URL') ? BASE_URL . "index.php" : "../index.php";
error_log("Mengalihkan ke: " . $redirect_url, 3, __DIR__ . "/logs/debug.log");

header("Location: " . $redirect_url);
exit();
?>