<?php
require_once('../config.php');
require_once('../includes/header.php');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../includes/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$recipientId = $_GET['to'] ?? 0;

// Validasi recipient
if ($recipientId > 0) {
    $stmt = $conn->prepare("SELECT id, name, role, photo FROM users WHERE id = ? AND id != ?");
    $stmt->bind_param("ii", $recipientId, $userId);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();
    
    if (!$recipient) {
        $recipientId = 0;
    }
}

// Ambil daftar kontak (mentor atau siswa)
if ($_SESSION['role'] === 'siswa') {
    // Untuk siswa: tampilkan semua mentor dari kursus yang diikuti
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.name, u.photo 
        FROM users u
        JOIN courses c ON c.mentor_id = u.id
        JOIN progress p ON p.course_id = c.id
        WHERE p.user_id = ? AND u.role = 'mentor'
        ORDER BY u.name
    ");
    $stmt->bind_param("i", $userId);
} else {
    // Untuk mentor: tampilkan siswa yang mengikuti kursusnya
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.name, u.photo 
        FROM users u
        JOIN progress p ON p.user_id = u.id
        JOIN courses c ON c.id = p.course_id
        WHERE c.mentor_id = ? AND u.role = 'siswa'
        ORDER BY u.name
    ");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Jika ada recipient yang dipilih, ambil history chat
$messages = [];
if ($recipientId > 0) {
    // Tandai pesan sebagai terbaca
    $stmt = $conn->prepare("UPDATE chat SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
    $stmt->bind_param("ii", $userId, $recipientId);
    $stmt->execute();
    
    // Ambil history chat
    $stmt = $conn->prepare("
        SELECT c.*, u.name as sender_name, u.photo as sender_photo
        FROM chat c
        JOIN users u ON u.id = c.sender_id
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY timestamp ASC
    ");
    $stmt->bind_param("iiii", $userId, $recipientId, $recipientId, $userId);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = "Pesan";
?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex flex-col md:flex-row h-[70vh]">
            <!-- Daftar Kontak -->
            <div class="w-full md:w-1/3 border-r border-gray-200 bg-gray-50">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Pesan</h2>
                </div>
                
                <div class="overflow-y-auto h-[calc(70vh-57px)]">
                    <?php if (count($contacts) > 0): ?>
                        <?php foreach ($contacts as $contact): ?>
                            <a href="?to=<?php echo $contact['id']; ?>" class="flex items-center p-4 border-b border-gray-200 hover:bg-gray-100 <?php echo $recipientId == $contact['id'] ? 'bg-indigo-50' : ''; ?>">
                                <img src="<?php echo $contact['photo'] ?? 'https://randomuser.me/api/portraits/men/32.jpg'; ?>" alt="<?php echo htmlspecialchars($contact['name']); ?>" class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($contact['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo $contact['role'] === 'mentor' ? 'Mentor' : 'Siswa'; ?></p>
                                </div>
                                
                                <!-- Indicator pesan belum dibaca -->
                                <?php if (false): // Anda perlu query untuk mengecek pesan belum dibaca ?>
                                    <span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">3</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-comments text-4xl mb-4"></i>
                            <p>Tidak ada kontak tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Area Chat -->
            <div class="w-full md:w-2/3 flex flex-col">
                <?php if ($recipientId > 0): ?>
                    <!-- Header Chat -->
                    <div class="p-4 border-b border-gray-200 flex items-center">
                        <img src="<?php echo $recipient['photo'] ?? 'https://randomuser.me/api/portraits/men/32.jpg'; ?>" alt="<?php echo htmlspecialchars($recipient['name']); ?>" class="w-10 h-10 rounded-full mr-3">
                        <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($recipient['name']); ?></h3>
                    </div>
                    
                    <!-- History Chat -->
                    <div id="chatMessages" class="flex-grow p-4 overflow-y-auto bg-gray-100">
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="mb-4 flex <?php echo $message['sender_id'] == $userId ? 'justify-end' : 'justify-start'; ?>">
                                    <div class="max-w-xs md:max-w-md lg:max-w-lg rounded-lg px-4 py-2 <?php echo $message['sender_id'] == $userId ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800'; ?>">
                                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                                        <p class="text-xs mt-1 <?php echo $message['sender_id'] == $userId ? 'text-indigo-200' : 'text-gray-500'; ?>">
                                            <?php echo date('H:i', strtotime($message['timestamp'])); ?>
                                            <?php if ($message['sender_id'] == $userId && $message['is_read']): ?>
                                                <i class="fas fa-check ml-1"></i>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="h-full flex items-center justify-center text-gray-500">
                                <p>Mulailah percakapan dengan <?php echo htmlspecialchars($recipient['name']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Input Pesan -->
                    <div class="p-4 border-t border-gray-200">
                        <form id="chatForm" class="flex">
                            <input type="hidden" name="receiver_id" value="<?php echo $recipientId; ?>">
                            <input 
                                type="text" 
                                name="message" 
                                placeholder="Ketik pesan..." 
                                class="flex-grow px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required
                            >
                            <button 
                                type="submit" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="h-full flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-comment-dots text-4xl mb-4"></i>
                            <p>Pilih kontak untuk memulai percakapan</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// AJAX untuk mengirim dan menerima pesan
document.getElementById('chatForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('sender_id', <?php echo $userId; ?>);
    
    fetch('chat-send.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tambahkan pesan ke chat
            const chatMessages = document.getElementById('chatMessages');
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            const messageHtml = `
                <div class="mb-4 flex justify-end">
                    <div class="max-w-xs md:max-w-md lg:max-w-lg rounded-lg px-4 py-2 bg-indigo-600 text-white">
                        <p>${formData.get('message')}</p>
                        <p class="text-xs mt-1 text-indigo-200">
                            ${timeString}
                            <i class="fas fa-check ml-1"></i>
                        </p>
                    </div>
                </div>
            `;
            
            chatMessages.innerHTML += messageHtml;
            chatMessages.scrollTop = chatMessages.scrollHeight;
            this.reset();
        } else {
            alert('Gagal mengirim pesan: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim pesan');
    });
});

// Polling untuk pesan baru (setiap 5 detik)
<?php if ($recipientId > 0): ?>
setInterval(() => {
    fetch(`chat-get-new.php?recipient_id=<?php echo $recipientId; ?>&last_id=<?php echo count($messages) > 0 ? end($messages)['id'] : 0; ?>`)
    .then(response => response.json())
    .then(data => {
        if (data.messages && data.messages.length > 0) {
            const chatMessages = document.getElementById('chatMessages');
            
            data.messages.forEach(message => {
                const timeString = new Date(message.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                const messageHtml = `
                    <div class="mb-4 flex justify-start">
                        <div class="max-w-xs md:max-w-md lg:max-w-lg rounded-lg px-4 py-2 bg-white text-gray-800">
                            <p>${message.message}</p>
                            <p class="text-xs mt-1 text-gray-500">
                                ${timeString}
                            </p>
                        </div>
                    </div>
                `;
                
                chatMessages.innerHTML += messageHtml;
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
}, 5000);
<?php endif; ?>
</script>

<?php require_once('../includes/footer.php'); ?>