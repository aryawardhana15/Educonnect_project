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

ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'hafiz1180';
FLUSH PRIVILEGES; 