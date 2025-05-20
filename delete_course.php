<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth();

// Cek login dan role mentor
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'mentor') {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID mentor dari sesi
$mentor_id = $_SESSION['user_id'];

// Ambil ID kelas dari parameter GET
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id === 0) {
    header('Location: /course.php?status=error');
    exit;
}

// Initialize database connection
$db = db();

try {
    // Mulai transaksi untuk memastikan semua data terkait dihapus
    $db->beginTransaction();

    // Hapus materi terkait
    $stmt = $db->prepare("DELETE FROM course_materials WHERE course_id = ?");
    $stmt->execute([$course_id]);

    // Hapus kuis terkait (jika tabel quizzes ada)
    try {
        $stmt = $db->prepare("DELETE FROM quizzes WHERE course_id = ?");
        $stmt->execute([$course_id]);
    } catch (PDOException $e) {
        // Abaikan jika tabel quizzes belum ada
        error_log("Error deleting quizzes: " . $e->getMessage());
    }

    // Hapus diskusi terkait (jika tabel discussions ada)
    try {
        $stmt = $db->prepare("DELETE FROM discussions WHERE course_id = ?");
        $stmt->execute([$course_id]);
    } catch (PDOException $e) {
        // Abaikan jika tabel discussions belum ada
        error_log("Error deleting discussions: " . $e->getMessage());
    }

    // Hapus enrollment siswa (user_courses)
    $stmt = $db->prepare("DELETE FROM user_courses WHERE course_id = ?");
    $stmt->execute([$course_id]);

    // Hapus jadwal terkait (jika tabel schedules ada)
    try {
        $stmt = $db->prepare("DELETE FROM schedules WHERE course_id = ?");
        $stmt->execute([$course_id]);
    } catch (PDOException $e) {
        // Abaikan jika tabel schedules belum ada
        error_log("Error deleting schedules: " . $e->getMessage());
    }

    // Hapus transaksi terkait (jika tabel transactions ada)
    try {
        $stmt = $db->prepare("DELETE FROM transactions WHERE course_id = ?");
        $stmt->execute([$course_id]);
    } catch (PDOException $e) {
        // Abaikan jika tabel transactions belum ada
        error_log("Error deleting transactions: " . $e->getMessage());
    }

    // Hapus kelas itu sendiri (hanya jika dimiliki oleh mentor)
    $stmt = $db->prepare("DELETE FROM courses WHERE id = ? AND mentor_id = ?");
    $stmt->execute([$course_id, $mentor_id]);

    // Commit transaksi
    $db->commit();
    header('Location: /course.php?status=deleted');
    exit;
} catch (PDOException $e) {
    // Rollback transaksi jika ada error
    $db->rollBack();
    error_log("Error deleting course: " . $e->getMessage());
    header('Location: /course.php?status=error');
    exit;
}
?>