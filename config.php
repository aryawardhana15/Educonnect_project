<?php
// Konfigurasi Dasar
define('BASE_URL', 'http://localhost/test_eduai');
define('SITE_NAME', 'EduConnect');

// Path untuk upload
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('COURSE_IMAGE_PATH', UPLOAD_PATH . '/courses');
define('PROFILE_IMAGE_PATH', UPLOAD_PATH . '/profiles');

// Buat direktori jika belum ada
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!file_exists(COURSE_IMAGE_PATH)) {
    mkdir(COURSE_IMAGE_PATH, 0777, true);
}
if (!file_exists(PROFILE_IMAGE_PATH)) {
    mkdir(PROFILE_IMAGE_PATH, 0777, true);
}

// Fungsi helper untuk URL
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Fungsi helper untuk asset
function asset($path = '') {
    return url('assets/' . ltrim($path, '/'));
}

// Fungsi helper untuk upload
function upload_path($path = '') {
    return UPLOAD_PATH . '/' . ltrim($path, '/');
}

// Fungsi helper untuk redirect
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// Fungsi helper untuk mengecek halaman aktif
function is_active($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path) !== false ? 'active' : '';
}

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'hafiz1180');
define('DB_NAME', 'educonnect');

// Konfigurasi API
define('GROQ_API_KEY', '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');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('MODEL_NAME', 'mixtral-8x7b-32768'); // atau 'llama3-70b-8192' untuk model terbaru

// Konfigurasi Aplikasi
define('APP_NAME', 'EduConnect');
define('APP_URL', 'http://localhost/test_eduai');
define('APP_VERSION', '1.0.0');

// Konfigurasi Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Konfigurasi Session
define('SESSION_LIFETIME', 86400); // 24 jam

// Validasi Konfigurasi
if (!defined('GROQ_API_KEY') || empty(GROQ_API_KEY)) {
    die('API key not configured');
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>