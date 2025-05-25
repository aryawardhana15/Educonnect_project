<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth/auth.php';
require_once 'helpers.php';

// Get all missions with creator info
$stmt = $conn->prepare("SELECT m.*, u.id as creator_id, u.full_name as creator_name, u.profile_picture as creator_profile_picture,
                              (SELECT COUNT(*) FROM submissions s WHERE s.mission_id = m.id) as submission_count
                       FROM missions m 
                       LEFT JOIN users u ON m.created_by = u.id 
                       ORDER BY m.created_at DESC");
$stmt->execute();
$missions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Misi - EduAI</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Daftar Misi</h1>
            <?php if ($user['role'] === 'mentor' || $user['role'] === 'admin'): ?>
            <a href="create_mission.php" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Buat Misi Baru
            </a>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($missions as $mission): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="<?php echo $mission['creator_profile_picture'] ?? getRandomDefaultAvatar($mission['creator_id']); ?>" 
                             alt="Creator Profile" 
                             class="w-10 h-10 rounded-full border-2 border-primary-50">
                        <div>
                            <h3 class="font-medium text-gray-800">
                                <?php echo htmlspecialchars($mission['creator_name']); ?>
                            </h3>
                            <p class="text-sm text-gray-500">Pembuat Misi</p>
                        </div>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-800 mb-3">
                        <?php echo htmlspecialchars($mission['title']); ?>
                    </h2>

                    <p class="text-gray-600 mb-4 line-clamp-3">
                        <?php echo strip_tags($mission['description']); ?>
                    </p>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            <span>Deadline: <?php echo date('d M Y', strtotime($mission['deadline'])); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-star mr-2"></i>
                            <span>Poin: <?php echo $mission['points']; ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users mr-2"></i>
                            <span><?php echo $mission['submission_count']; ?> Pengumpulan</span>
                        </div>
                    </div>

                    <a href="mission.php?id=<?php echo $mission['id']; ?>" 
                       class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium">
                        Lihat Detail
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($missions)): ?>
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-tasks fa-3x"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Misi</h3>
            <p class="text-gray-600">Belum ada misi yang dibuat saat ini.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 