<?php
// Contoh sederhana untuk edit_course.php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'mentor') {
    header('Location: /auth/login.php');
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = db();
$query = "SELECT * FROM courses WHERE id = ? AND mentor_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: /course.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $level = $_POST['level'];

    $query = "UPDATE courses SET title = ?, description = ?, type = ?, level = ? WHERE id = ? AND mentor_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$title, $description, $type, $level, $course_id, $_SESSION['user_id']]);

    header('Location: /course.php?status=updated');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Kelas</h1>
        <form method="POST" class="bg-white p-6 rounded-xl shadow-md space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Judul Kelas</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" class="w-full p-2 border rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" class="w-full p-2 border rounded-lg" rows="3"><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipe</label>
                <select name="type" class="w-full p-2 border rounded-lg">
                    <option value="free" <?php echo $course['type'] === 'free' ? 'selected' : ''; ?>>Gratis</option>
                    <option value="premium" <?php echo $course['type'] === 'premium' ? 'selected' : ''; ?>>Premium</option>
                    <option value="bootcamp" <?php echo $course['type'] === 'bootcamp' ? 'selected' : ''; ?>>Bootcamp</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Level</label>
                <select name="level" class="w-full p-2 border rounded-lg">
                    <option value="beginner" <?php echo $course['level'] === 'beginner' ? 'selected' : ''; ?>>Pemula</option>
                    <option value="intermediate" <?php echo $course['level'] === 'intermediate' ? 'selected' : ''; ?>>Menengah</option>
                    <option value="advanced" <?php echo $course['level'] === 'advanced' ? 'selected' : ''; ?>>Lanjutan</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>