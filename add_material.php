<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'mentor') {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $type = $_POST['type'];

    $db = db();
    $query = "INSERT INTO course_materials (course_id, title, content, type) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$course_id, $title, $content, $type]);

    header('Location: /course.php?status=material_added');
    exit;
}
?>