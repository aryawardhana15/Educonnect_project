<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$user = $auth->getCurrentUser();

// Redirect berdasarkan role
switch ($user['role']) {
    case 'student':
        header('Location: dashboardstudent.php');
        break;
    case 'mentor':
        header('Location: dashboardmentor.php');
        break;
    case 'admin':
        header('Location: dashboardadmin.php');
        break;
    default:
        // Jika role tidak valid, logout user
        header('Location: auth/logout.php');
}
exit; 