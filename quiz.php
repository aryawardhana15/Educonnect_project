<?php
require_once('config.php');
require_once('db_connect.php');
require_once('auth/auth.php');

// Inisialisasi Auth
$auth = new Auth($conn);

// Cek login
if (!$auth->isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Ambil ID kuis dari URL
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail kuis
$query = "SELECT q.*, c.title as course_title, c.id as course_id, u.full_name as mentor_name
          FROM quizzes q
          JOIN courses c ON q.course_id = c.id
          JOIN users u ON c.mentor_id = u.id
          WHERE q.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: /');
    exit;
}

// Cek status enrollment
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $quiz['course_id']);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    header('Location: /course.php?id=' . $quiz['course_id']);
    exit;
}

// Cek apakah kuis sudah pernah dikerjakan
$query = "SELECT * FROM user_quizzes WHERE user_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$user_quiz = $stmt->get_result()->fetch_assoc();

// Ambil pertanyaan
$query = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sequence ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Proses submit kuis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$user_quiz) {
    $answers = $_POST['answers'] ?? [];
    $score = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        if (isset($answers[$question['id']]) && $answers[$question['id']] === $question['correct_answer']) {
            $score++;
        }
    }
    
    $percentage = ($score / $total_questions) * 100;
    
    // Simpan hasil kuis
    $query = "INSERT INTO user_quizzes (user_id, quiz_id, score, total_questions, percentage, completed_at)
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiidi", $user_id, $quiz_id, $score, $total_questions, $percentage);
    $stmt->execute();
    
    // Redirect untuk menghindari resubmit
    header('Location: /quiz.php?id=' . $quiz_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - EduConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/dashboard/student/index.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-user-circle text-xl"></i>
                    </a>
                    <a href="/auth/logout.php" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="flex items-center text-sm text-gray-500 mb-6">
            <a href="/course.php?id=<?php echo $quiz['course_id']; ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($quiz['course_title']); ?>
            </a>
            <i class="fas fa-chevron-right mx-2"></i>
            <span class="text-gray-900"><?php echo htmlspecialchars($quiz['title']); ?></span>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <?php if ($user_quiz): ?>
                <div class="flex items-center">
                    <div class="text-right mr-4">
                        <div class="text-sm text-gray-500">Nilai</div>
                        <div class="text-xl font-bold text-primary"><?php echo $user_quiz['percentage']; ?>%</div>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-check text-primary text-xl"></i>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($user_quiz): ?>
            <!-- Hasil Kuis -->
            <div class="mb-8">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-medium text-green-800">Selamat!</h3>
                            <p class="text-green-600">Anda telah menyelesaikan kuis ini dengan nilai <?php echo $user_quiz['percentage']; ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Jawaban -->
            <div class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-sm font-medium text-gray-600"><?php echo $index + 1; ?></span>
                        </div>
                        <div class="flex-grow">
                            <p class="text-gray-900 mb-3"><?php echo htmlspecialchars($question['question']); ?></p>
                            <div class="space-y-2">
                                <?php 
                                $options = json_decode($question['options'], true);
                                foreach ($options as $key => $option): 
                                ?>
                                <div class="flex items-center">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center mr-3
                                        <?php if ($key === $question['correct_answer']): ?>
                                        border-green-500 bg-green-500
                                        <?php elseif ($key === $user_quiz['answers'][$question['id']]): ?>
                                        border-red-500 bg-red-500
                                        <?php else: ?>
                                        border-gray-300
                                        <?php endif; ?>">
                                        <?php if ($key === $question['correct_answer'] || $key === $user_quiz['answers'][$question['id']]): ?>
                                        <i class="fas fa-check text-white text-xs"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-gray-700"><?php echo htmlspecialchars($option); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <!-- Form Kuis -->
            <form method="POST" class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-sm font-medium text-gray-600"><?php echo $index + 1; ?></span>
                        </div>
                        <div class="flex-grow">
                            <p class="text-gray-900 mb-3"><?php echo htmlspecialchars($question['question']); ?></p>
                            <div class="space-y-2">
                                <?php 
                                $options = json_decode($question['options'], true);
                                foreach ($options as $key => $option): 
                                ?>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="answers[<?php echo $question['id']; ?>]" 
                                           value="<?php echo $key; ?>"
                                           class="w-5 h-5 text-primary border-gray-300 focus:ring-primary">
                                    <span class="ml-3 text-gray-700"><?php echo htmlspecialchars($option); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                        Selesai & Kirim Jawaban
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Timer untuk kuis
    <?php if (!$user_quiz && $quiz['time_limit']): ?>
    let timeLeft = <?php echo $quiz['time_limit'] * 60; ?>;
    const timerElement = document.getElementById('quiz-timer');
    
    const timer = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            document.querySelector('form').submit();
        }
    }, 1000);
    <?php endif; ?>
    </script>
</body>
</html> 