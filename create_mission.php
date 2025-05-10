<?php
require_once 'auth/auth.php';
require_once 'db_connect.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

// Redirect jika belum login atau bukan mentor
if (!$auth->isLoggedIn() || $user['role'] !== 'mentor') {
    header('Location: auth/login.php');
    exit;
}

$db = db();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $level = trim($_POST['level'] ?? 'beginner');
    $deadline = trim($_POST['deadline'] ?? '');
    $points = (int)($_POST['points'] ?? 0);
    $image = '';

    // Validasi input
    if (empty($title)) {
        $error = 'Judul misi harus diisi';
    } elseif (empty($description)) {
        $error = 'Deskripsi misi harus diisi';
    } elseif (empty($category)) {
        $error = 'Kategori harus dipilih';
    } elseif (empty($deadline)) {
        $error = 'Deadline harus diisi';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/missions/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = $uploadFile;
                } else {
                    $error = 'Gagal mengupload gambar. Silakan coba lagi.';
                }
            } else {
                $error = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.';
            }
        }

        if (empty($error)) {
            try {
                // Mulai transaksi
                $db->beginTransaction();

                // Insert ke tabel missions
                $stmt = $db->prepare("
                    INSERT INTO missions (
                        mentor_id, 
                        title, 
                        description, 
                        image,
                        category,
                        level,
                        deadline,
                        points,
                        status,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                
                $stmt->execute([
                    $user['id'],
                    $title,
                    $description,
                    $image,
                    $category,
                    $level,
                    $deadline,
                    $points
                ]);

                $missionId = $db->lastInsertId();

                // Commit transaksi
                $db->commit();

                if ($missionId) {
                    $success = 'Misi berhasil dibuat!';
                    // Redirect ke halaman misi setelah 2 detik
                    header("refresh:2;url=mentor_missions.php");
                } else {
                    $error = 'Gagal membuat misi. Silakan coba lagi.';
                }
            } catch (PDOException $e) {
                // Rollback jika terjadi error
                $db->rollBack();
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
                error_log('Error creating mission: ' . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Misi - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-input:focus {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }
        .preview-image {
            transition: all 0.3s ease;
        }
        .preview-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    <span class="font-bold text-xl text-gray-800">EduConnect</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="mentor_classes.php" class="text-gray-700 hover:text-blue-600">Kelas</a>
                    <a href="mentor_missions.php" class="text-blue-600 font-semibold hover:underline">Misi</a>
                    <a href="dashboardmentor.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <div class="relative group">
                        <button type="button" class="focus:outline-none flex items-center" id="avatarBtn">
                            <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" class="rounded-full w-9 h-9 border-2 border-blue-600" alt="Avatar">
                        </button>
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                            <a href="dashboardmentor.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <div class="border-t my-1"></div>
                            <a href="auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keluar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Buat Misi Baru</h1>
                    <a href="mentor_missions.php" class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-blue-600">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                    <p><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
                    <p><?php echo $success; ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Misi</label>
                        <input type="text" id="title" name="title" required
                               class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select id="category" name="category" required
                                    class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                                <option value="">Pilih Kategori</option>
                                <option value="Tugas">Tugas</option>
                                <option value="Proyek">Proyek</option>
                                <option value="Kuis">Kuis</option>
                                <option value="Latihan">Latihan</option>
                                <option value="Challenge">Challenge</option>
                            </select>
                        </div>
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select id="level" name="level" required
                                    class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                                <option value="beginner">Pemula</option>
                                <option value="intermediate">Menengah</option>
                                <option value="advanced">Lanjutan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                            <input type="datetime-local" id="deadline" name="deadline" required
                                   class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                        <div>
                            <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Poin</label>
                            <input type="number" id="points" name="points" min="0" value="0" required
                                   class="form-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Misi</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition">
                            <div class="space-y-1 text-center">
                                <div class="flex text-sm text-gray-600">
                                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload gambar</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF sampai 10MB</p>
                            </div>
                        </div>
                        <div id="imagePreview" class="mt-4 hidden">
                            <img src="" alt="Preview" class="preview-image max-h-48 rounded-lg shadow-md">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center items-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow-lg border-2 border-blue-700 hover:bg-blue-700 hover:shadow-xl transition font-bold text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                            <i class="fas fa-plus mr-2"></i> Buat Misi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Dropdown logic
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    document.addEventListener('click', function(e) {
        if (avatarBtn && avatarBtn.contains(e.target)) {
            dropdownMenu.classList.toggle('hidden');
        } else if (dropdownMenu && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });

    // Image preview
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Drag and drop functionality
    const dropZone = document.querySelector('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-blue-500');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-blue-500');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const input = document.getElementById('image');
        input.files = files;
        previewImage(input);
    }
    </script>
</body>
</html> 