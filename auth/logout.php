<?php
// auth/logout.php
require_once('../config.php');
require_once('../db_connect.php');
require_once 'auth.php';

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Hancurkan session
session_destroy();

// Redirect ke halaman index dengan path absolut
header('Location: /test_eduai/index.php');
exit;
?>