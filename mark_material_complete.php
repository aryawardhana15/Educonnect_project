<?php
require_once 'auth/auth.php';
$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login
if (!$auth->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Hanya siswa yang bisa menandai materi
if ($user['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validasi input
$materialId = $_POST['material_id'] ?? 0;
$isCompleted = $_POST['is_completed'] ?? 0;

if (!$materialId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit;
}

$db = db();

try {
    // Cek apakah materi ada dan siswa terdaftar di kelas
    $stmt = $db->prepare("
        SELECT m.id 
        FROM materials m
        JOIN courses c ON m.course_id = c.id
        JOIN user_courses uc ON c.id = uc.course_id
        WHERE m.id = ? AND uc.user_id = ?
    ");
    $stmt->bind_param("ii", $materialId, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Material not found or unauthorized']);
        exit;
    }

    if ($isCompleted) {
        // Tambah atau update status materi
        $stmt = $db->prepare("
            INSERT INTO user_materials (user_id, material_id, completed_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE completed_at = NOW()
        ");
        $stmt->bind_param("ii", $user['id'], $materialId);
    } else {
        // Hapus status materi
        $stmt = $db->prepare("
            DELETE FROM user_materials 
            WHERE user_id = ? AND material_id = ?
        ");
        $stmt->bind_param("ii", $user['id'], $materialId);
    }

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update material status');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error']);
} 