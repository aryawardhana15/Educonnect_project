-- Buat database
CREATE DATABASE IF NOT EXISTS educonnect;
USE educonnect;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'mentor', 'student') NOT NULL DEFAULT 'student',
    profile_picture VARCHAR(255),
    bio TEXT,
    region VARCHAR(100),
    points INT DEFAULT 0,
    experience INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drop existing tables
DROP TABLE IF EXISTS user_courses;
DROP TABLE IF EXISTS course_materials;
DROP TABLE IF EXISTS courses;

-- Tabel courses
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    type ENUM('free', 'premium', 'bootcamp') NOT NULL DEFAULT 'free',
    level ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    education_level ENUM('sd', 'smp', 'sma', 'umum') NOT NULL DEFAULT 'umum',
    subject VARCHAR(50) NOT NULL,
    grade INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id)
);

-- Tabel bootcamp
CREATE TABLE IF NOT EXISTS bootcamp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in weeks',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    max_students INT DEFAULT 20,
    current_students INT DEFAULT 0,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id)
);

-- Tabel user_courses
CREATE TABLE IF NOT EXISTS user_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    course_id INT,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress INT DEFAULT 0,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Tabel user_bootcamp
CREATE TABLE IF NOT EXISTS user_bootcamp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    bootcamp_id INT,
    status ENUM('registered', 'ongoing', 'completed') DEFAULT 'registered',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (bootcamp_id) REFERENCES bootcamp(id)
);

-- Tabel course_materials
CREATE TABLE IF NOT EXISTS course_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    type ENUM('video', 'document', 'quiz') NOT NULL,
    file_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Tabel bootcamp_materials
CREATE TABLE IF NOT EXISTS bootcamp_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bootcamp_id INT,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    type ENUM('video', 'document', 'quiz', 'live_session') NOT NULL,
    file_url VARCHAR(255),
    session_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bootcamp_id) REFERENCES bootcamp(id)
);

-- Tabel missions
CREATE TABLE IF NOT EXISTS missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    points INT DEFAULT 0,
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id)
);

-- Tabel user_missions
CREATE TABLE IF NOT EXISTS user_missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    mission_id INT,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    submission TEXT,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (mission_id) REFERENCES missions(id)
);

-- Tabel community_posts
CREATE TABLE IF NOT EXISTS community_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    category ENUM('discussion', 'question', 'share') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel comments
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT,
    user_id INT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel payments
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('course', 'bootcamp') NOT NULL,
    reference_id INT NOT NULL COMMENT 'ID dari course atau bootcamp',
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel untuk jadwal mentor
CREATE TABLE mentor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    duration INT NOT NULL COMMENT 'Durasi dalam menit',
    status ENUM('available', 'booked', 'cancelled') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk sesi mentoring
CREATE TABLE mentoring_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    student_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    topic VARCHAR(255) NOT NULL,
    link VARCHAR(255) COMMENT 'Link meeting (Google Meet/Zoom)',
    note TEXT COMMENT 'Catatan dari mentor',
    feedback TEXT COMMENT 'Feedback dari siswa',
    rating DECIMAL(2,1) COMMENT 'Rating dari siswa (1-5)',
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk pertanyaan mentoring
CREATE TABLE mentoring_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    student_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    answer TEXT,
    file VARCHAR(255) COMMENT 'Path file lampiran',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk rating detail
CREATE TABLE mentoring_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    mentor_id INT NOT NULL,
    overall_rating DECIMAL(2,1) NOT NULL,
    communication_rating DECIMAL(2,1) NOT NULL,
    knowledge_rating DECIMAL(2,1) NOT NULL,
    teaching_rating DECIMAL(2,1) NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES mentoring_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk tag rating
CREATE TABLE mentoring_rating_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rating_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rating_id) REFERENCES mentoring_ratings(id) ON DELETE CASCADE
);

-- Tabel untuk grup mentoring
CREATE TABLE mentoring_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    max_students INT DEFAULT 5,
    price DECIMAL(10,2) DEFAULT 0.00,
    schedule VARCHAR(100) COMMENT 'Contoh: Setiap Senin, 19:00-20:00',
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk anggota grup mentoring
CREATE TABLE mentoring_group_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('registered', 'active', 'completed') DEFAULT 'registered',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES mentoring_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk sesi grup mentoring
CREATE TABLE mentoring_group_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    topic VARCHAR(255) NOT NULL,
    link VARCHAR(255) COMMENT 'Link meeting (Google Meet/Zoom)',
    recording_url VARCHAR(255) COMMENT 'Link rekaman sesi',
    materials TEXT COMMENT 'Materi yang dibagikan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES mentoring_groups(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Admin User', 'admin'),
('mentor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor1@example.com', 'Mentor Satu', 'mentor'),
('mentor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor2@example.com', 'Mentor Dua', 'mentor'),
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student1@example.com', 'Student Satu', 'student'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student2@example.com', 'Student Dua', 'student');

-- Insert sample courses (gratis dan berbayar)
INSERT INTO courses (mentor_id, title, description, price, type, level, education_level, subject, grade) VALUES
-- SD
(2, 'Matematika SD Kelas 1', 'Belajar dasar-dasar matematika untuk kelas 1 SD', 0.00, 'free', 'beginner', 'sd', 'Matematika', 1),
(2, 'Bahasa Indonesia SD Kelas 2', 'Belajar membaca dan menulis untuk kelas 2 SD', 0.00, 'free', 'beginner', 'sd', 'Bahasa Indonesia', 2),
(2, 'IPA SD Kelas 3', 'Pengenalan sains dasar untuk kelas 3 SD', 0.00, 'free', 'beginner', 'sd', 'IPA', 3),
(3, 'Matematika SD Kelas 4', 'Belajar operasi hitung dan geometri dasar', 99000.00, 'premium', 'beginner', 'sd', 'Matematika', 4),
(3, 'Bahasa Inggris SD Kelas 5', 'Pengenalan bahasa Inggris dasar untuk anak SD', 149000.00, 'premium', 'beginner', 'sd', 'Bahasa Inggris', 5),
(2, 'IPS SD Kelas 6', 'Persiapan UASBN untuk kelas 6 SD', 199000.00, 'premium', 'intermediate', 'sd', 'IPS', 6),

-- SMP
(2, 'Matematika SMP Kelas 7', 'Aljabar dan geometri dasar untuk kelas 7 SMP', 0.00, 'free', 'beginner', 'smp', 'Matematika', 7),
(2, 'IPA SMP Kelas 8', 'Fisika dan Biologi untuk kelas 8 SMP', 0.00, 'free', 'beginner', 'smp', 'IPA', 8),
(3, 'Bahasa Inggris SMP Kelas 9', 'Persiapan UN Bahasa Inggris SMP', 199000.00, 'premium', 'intermediate', 'smp', 'Bahasa Inggris', 9),
(3, 'Matematika SMP Kelas 7-9', 'Paket lengkap matematika SMP', 299000.00, 'premium', 'intermediate', 'smp', 'Matematika', 7),
(2, 'IPS SMP Kelas 8', 'Sejarah dan Geografi Indonesia', 149000.00, 'premium', 'intermediate', 'smp', 'IPS', 8),

-- SMA
(2, 'Matematika SMA Kelas 10', 'Aljabar dan Trigonometri dasar', 0.00, 'free', 'beginner', 'sma', 'Matematika', 10),
(2, 'Fisika SMA Kelas 11', 'Mekanika dan Termodinamika', 0.00, 'free', 'beginner', 'sma', 'Fisika', 11),
(3, 'Kimia SMA Kelas 12', 'Persiapan UTBK Kimia', 249000.00, 'premium', 'advanced', 'sma', 'Kimia', 12),
(3, 'Biologi SMA Kelas 10-12', 'Paket lengkap Biologi SMA', 399000.00, 'premium', 'advanced', 'sma', 'Biologi', 10),
(2, 'Ekonomi SMA Kelas 11', 'Mikroekonomi dan Makroekonomi', 199000.00, 'premium', 'intermediate', 'sma', 'Ekonomi', 11),

-- Umum (existing courses)
(2, 'HTML & CSS Dasar', 'Belajar dasar-dasar HTML dan CSS untuk pemula', 0.00, 'free', 'beginner', 'umum', 'Programming', NULL),
(2, 'JavaScript Fundamentals', 'Pengenalan JavaScript untuk pemula', 0.00, 'free', 'beginner', 'umum', 'Programming', NULL),
(2, 'PHP & MySQL Lanjutan', 'Belajar PHP dan MySQL tingkat lanjut', 199000.00, 'premium', 'intermediate', 'umum', 'Programming', NULL),
(3, 'React.js Masterclass', 'Kursus lengkap React.js dari dasar hingga mahir', 299000.00, 'premium', 'advanced', 'umum', 'Programming', NULL),
(3, 'Python untuk Data Science', 'Belajar Python untuk analisis data', 249000.00, 'premium', 'intermediate', 'umum', 'Programming', NULL);

-- Insert sample bootcamp
INSERT INTO bootcamp (mentor_id, title, description, price, duration, start_date, end_date, max_students) VALUES
(2, 'Full Stack Web Development Bootcamp', 'Bootcamp intensif 12 minggu untuk menjadi Full Stack Developer', 4999000.00, 12, '2024-04-01', '2024-06-24', 20),
(3, 'Data Science Bootcamp', 'Bootcamp 16 minggu untuk menjadi Data Scientist', 5999000.00, 16, '2024-05-01', '2024-08-24', 15);

-- Insert sample user_courses
INSERT INTO user_courses (user_id, course_id, status, payment_status) VALUES
(4, 1, 'completed', 'paid'),
(4, 2, 'in_progress', 'paid'),
(5, 3, 'not_started', 'pending');

-- Insert sample user_bootcamp
INSERT INTO user_bootcamp (user_id, bootcamp_id, status, payment_status) VALUES
(4, 1, 'registered', 'pending'),
(5, 2, 'registered', 'pending');

-- Insert sample missions
INSERT INTO missions (mentor_id, title, description, points, deadline) VALUES
(2, 'Buat Website Portfolio', 'Buat website portfolio sederhana menggunakan HTML dan CSS', 100, DATE_ADD(NOW(), INTERVAL 7 DAY)),
(2, 'Implementasi CRUD dengan PHP', 'Buat sistem CRUD sederhana menggunakan PHP dan MySQL', 150, DATE_ADD(NOW(), INTERVAL 14 DAY)),
(3, 'Desain Logo dengan Canva', 'Buat logo untuk bisnis fiktif menggunakan Canva', 50, DATE_ADD(NOW(), INTERVAL 5 DAY)),
(3, 'Presentasi Bahasa Inggris', 'Buat presentasi 5 menit dalam bahasa Inggris tentang teknologi', 75, DATE_ADD(NOW(), INTERVAL 10 DAY));

-- Insert sample user_missions
INSERT INTO user_missions (user_id, mission_id, status) VALUES
(4, 1, 'in_progress'),
(4, 2, 'not_started'),
(5, 3, 'completed'),
(5, 4, 'not_started');

-- Insert sample community posts
INSERT INTO community_posts (user_id, title, content, category) VALUES
(4, 'Tips Belajar Programming', 'Berikut beberapa tips untuk belajar programming...', 'share'),
(5, 'Pertanyaan tentang React', 'Saya mengalami kesulitan dalam...', 'question'),
(2, 'Diskusi tentang AI', 'Bagaimana pendapat kalian tentang perkembangan AI?', 'discussion'),
(3, 'Sharing Project', 'Saya baru saja menyelesaikan project...', 'share');

-- Insert sample comments
INSERT INTO comments (post_id, user_id, content) VALUES
(1, 5, 'Terima kasih atas tipsnya!'),
(2, 3, 'Coba periksa dokumentasi React...'),
(3, 4, 'Menurut saya AI akan sangat membantu...'),
(4, 2, 'Project yang keren!');

-- Insert sample data untuk mentoring
INSERT INTO mentor_schedules (mentor_id, session_date, session_time, duration, status) VALUES
(2, '2024-03-25', '10:00:00', 60, 'available'),
(2, '2024-03-25', '14:00:00', 60, 'available'),
(2, '2024-03-26', '09:00:00', 60, 'available'),
(3, '2024-03-25', '13:00:00', 60, 'available'),
(3, '2024-03-26', '15:00:00', 60, 'available');

INSERT INTO mentoring_sessions (mentor_id, student_id, session_date, session_time, topic, link, note, feedback, rating, status) VALUES
(2, 4, '2024-03-20', '10:00:00', 'Dasar HTML', 'https://meet.google.com/abc-defg-hij', 'Materi HTML dasar dan struktur dokumen', 'Sesi sangat membantu untuk memahami dasar HTML', 4.5, 'completed'),
(3, 4, '2024-03-21', '14:00:00', 'CSS Lanjutan', 'https://meet.google.com/xyz-uvw-123', 'Flexbox dan Grid Layout', 'Penjelasan yang sangat detail dan mudah dipahami', 5.0, 'completed'),
(2, 5, '2024-03-24', '09:00:00', 'JavaScript Dasar', 'https://meet.google.com/def-ghi-jkl', NULL, NULL, NULL, 'scheduled');

INSERT INTO mentoring_questions (mentor_id, student_id, topic, question, answer, created_at) VALUES
(2, 4, 'JavaScript Dasar', 'Bagaimana cara kerja event loop?', 'Event loop adalah mekanisme yang memungkinkan JavaScript melakukan operasi asynchronous...', '2024-03-19 15:30:00'),
(3, 5, 'CSS Layout', 'Apa perbedaan antara Flexbox dan Grid?', 'Flexbox dirancang untuk layout satu dimensi (row atau column), sementara Grid untuk layout dua dimensi...', '2024-03-20 10:15:00'),
(2, 4, 'HTML Semantics', 'Kapan sebaiknya menggunakan tag article vs section?', 'Tag article digunakan untuk konten yang berdiri sendiri dan dapat didistribusikan secara independen...', '2024-03-21 14:45:00');

-- Insert sample data untuk rating
INSERT INTO mentoring_ratings (session_id, student_id, mentor_id, overall_rating, communication_rating, knowledge_rating, teaching_rating, review_text) VALUES
(1, 4, 2, 4.5, 4.0, 5.0, 4.5, 'Sesi mentoring yang sangat membantu. Mentor sangat menguasai materi dan bisa menjelaskan dengan baik.'),
(2, 4, 3, 5.0, 5.0, 5.0, 5.0, 'Penjelasan yang sangat detail dan mudah dipahami. Mentor sangat ramah dan sabar.');

INSERT INTO mentoring_rating_tags (rating_id, tag) VALUES
(1, 'Penjelasan Jelas'),
(1, 'Materi Lengkap'),
(2, 'Ramah'),
(2, 'Sabar'),
(2, 'Detail');

-- Insert sample data untuk grup mentoring
INSERT INTO mentoring_groups (mentor_id, title, description, max_students, price, schedule, start_date, end_date) VALUES
(2, 'Grup Belajar JavaScript Dasar', 'Belajar JavaScript dari dasar hingga mahir dalam grup kecil', 5, 299000.00, 'Setiap Senin & Kamis, 19:00-20:00', '2024-04-01', '2024-05-30'),
(3, 'Grup Belajar UI/UX Design', 'Workshop design thinking dan UI/UX untuk pemula', 5, 399000.00, 'Setiap Selasa & Jumat, 20:00-21:00', '2024-04-02', '2024-05-31');

INSERT INTO mentoring_group_members (group_id, student_id, status, payment_status) VALUES
(1, 4, 'active', 'paid'),
(1, 5, 'active', 'paid'),
(2, 4, 'registered', 'pending');

INSERT INTO mentoring_group_sessions (group_id, session_date, session_time, topic, link) VALUES
(1, '2024-04-01', '19:00:00', 'Pengenalan JavaScript', 'https://meet.google.com/abc-defg-hij'),
(1, '2024-04-04', '19:00:00', 'Variabel dan Tipe Data', 'https://meet.google.com/xyz-uvw-123'),
(2, '2024-04-02', '20:00:00', 'Design Thinking Basics', 'https://meet.google.com/def-ghi-jkl');

ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'hafiz1180';
FLUSH PRIVILEGES; 