USE edux6882_educonnect;


CREATE TABLE bootcamp (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  description text DEFAULT NULL,
  thumbnail varchar(255) DEFAULT NULL,
  price decimal(10,2) NOT NULL,
  duration int(11) NOT NULL COMMENT 'Duration in weeks',
  start_date date NOT NULL,
  end_date date NOT NULL,
  max_students int(11) DEFAULT 20,
  current_students int(11) DEFAULT 0,
  status enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  CONSTRAINT bootcamp_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE bootcamp_materials (
  id int(11) NOT NULL AUTO_INCREMENT,
  bootcamp_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  content text DEFAULT NULL,
  type enum('video','document','quiz','live_session') NOT NULL,
  file_url varchar(255) DEFAULT NULL,
  session_date timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY bootcamp_id (bootcamp_id),
  CONSTRAINT bootcamp_materials_ibfk_1 FOREIGN KEY (bootcamp_id) REFERENCES bootcamp (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE comments (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  content text DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY post_id (post_id),
  KEY user_id (user_id),
  CONSTRAINT comments_ibfk_1 FOREIGN KEY (post_id) REFERENCES community_posts (id),
  CONSTRAINT comments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE community_posts (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  content text DEFAULT NULL,
  category enum('discussion','question','share') NOT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT community_posts_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE course_enrollments (
  id int(11) NOT NULL AUTO_INCREMENT,
  student_id int(11) NOT NULL,
  course_id int(11) NOT NULL,
  enrollment_date timestamp NULL DEFAULT current_timestamp(),
  status enum('active','completed','dropped') DEFAULT 'active',
  PRIMARY KEY (id),
  KEY student_id (student_id),
  KEY course_id (course_id),
  CONSTRAINT course_enrollments_ibfk_1 FOREIGN KEY (student_id) REFERENCES users (id),
  CONSTRAINT course_enrollments_ibfk_2 FOREIGN KEY (course_id) REFERENCES courses (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE course_materials (
  id int(11) NOT NULL AUTO_INCREMENT,
  course_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  content text DEFAULT NULL,
  type enum('video','document','quiz') NOT NULL,
  file_url varchar(255) DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  sequence int(11) DEFAULT 0,
  is_completed tinyint(1) DEFAULT 0,
  PRIMARY KEY (id),
  KEY course_id (course_id),
  CONSTRAINT course_materials_ibfk_1 FOREIGN KEY (course_id) REFERENCES courses (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE courses (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  description text DEFAULT NULL,
  thumbnail varchar(255) DEFAULT NULL,
  price decimal(10,2) DEFAULT 0.00,
  type enum('free','premium','bootcamp') NOT NULL DEFAULT 'free',
  level enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  education_level enum('sd','smp','sma','umum') NOT NULL DEFAULT 'umum',
  subject varchar(50) NOT NULL,
  grade int(11) DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  duration int(11) DEFAULT 0,
  student_count int(11) DEFAULT 0,
  rating decimal(3,1) DEFAULT 0.0,
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  CONSTRAINT courses_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentor_schedules (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) NOT NULL,
  session_date date NOT NULL,
  session_time time NOT NULL,
  duration int(11) NOT NULL COMMENT 'Durasi dalam menit',
  status enum('available','booked','cancelled') NOT NULL DEFAULT 'available',
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  CONSTRAINT mentor_schedules_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_group_members (
  id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  status enum('registered','active','completed') DEFAULT 'registered',
  payment_status enum('pending','paid','failed') DEFAULT 'pending',
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY group_id (group_id),
  KEY student_id (student_id),
  CONSTRAINT mentoring_group_members_ibfk_1 FOREIGN KEY (group_id) REFERENCES mentoring_groups (id) ON DELETE CASCADE,
  CONSTRAINT mentoring_group_members_ibfk_2 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_group_sessions (
  id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) NOT NULL,
  session_date date NOT NULL,
  session_time time NOT NULL,
  topic varchar(255) NOT NULL,
  link varchar(255) DEFAULT NULL COMMENT 'Link meeting (Google Meet/Zoom)',
  recording_url varchar(255) DEFAULT NULL COMMENT 'Link rekaman sesi',
  materials text DEFAULT NULL COMMENT 'Materi yang dibagikan',
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY group_id (group_id),
  CONSTRAINT mentoring_group_sessions_ibfk_1 FOREIGN KEY (group_id) REFERENCES mentoring_groups (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_groups (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) NOT NULL,
  title varchar(100) NOT NULL,
  description text DEFAULT NULL,
  max_students int(11) DEFAULT 5,
  price decimal(10,2) DEFAULT 0.00,
  schedule varchar(100) DEFAULT NULL COMMENT 'Contoh: Setiap Senin, 19:00-20:00',
  start_date date DEFAULT NULL,
  end_date date DEFAULT NULL,
  status enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  CONSTRAINT mentoring_groups_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_questions (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  topic varchar(255) NOT NULL,
  question text NOT NULL,
  answer text DEFAULT NULL,
  file varchar(255) DEFAULT NULL COMMENT 'Path file lampiran',
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  KEY student_id (student_id),
  CONSTRAINT mentoring_questions_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT mentoring_questions_ibfk_2 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_rating_tags (
  id int(11) NOT NULL AUTO_INCREMENT,
  rating_id int(11) NOT NULL,
  tag varchar(50) NOT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY rating_id (rating_id),
  CONSTRAINT mentoring_rating_tags_ibfk_1 FOREIGN KEY (rating_id) REFERENCES mentoring_ratings (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_ratings (
  id int(11) NOT NULL AUTO_INCREMENT,
  session_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  mentor_id int(11) NOT NULL,
  overall_rating decimal(2,1) NOT NULL,
  communication_rating decimal(2,1) NOT NULL,
  knowledge_rating decimal(2,1) NOT NULL,
  teaching_rating decimal(2,1) NOT NULL,
  review_text text DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY session_id (session_id),
  KEY student_id (student_id),
  KEY mentor_id (mentor_id),
  CONSTRAINT mentoring_ratings_ibfk_1 FOREIGN KEY (session_id) REFERENCES mentoring_sessions (id) ON DELETE CASCADE,
  CONSTRAINT mentoring_ratings_ibfk_2 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT mentoring_ratings_ibfk_3 FOREIGN KEY (mentor_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE mentoring_sessions (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) NOT NULL,
  student_id int(11) NOT NULL,
  session_date date NOT NULL,
  session_time time NOT NULL,
  topic varchar(255) NOT NULL,
  link varchar(255) DEFAULT NULL COMMENT 'Link meeting (Google Meet/Zoom)',
  note text DEFAULT NULL COMMENT 'Catatan dari mentor',
  feedback text DEFAULT NULL COMMENT 'Feedback dari siswa',
  rating decimal(2,1) DEFAULT NULL COMMENT 'Rating dari siswa (1-5)',
  status enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  KEY student_id (student_id),
  CONSTRAINT mentoring_sessions_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT mentoring_sessions_ibfk_2 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE missions (
  id int(11) NOT NULL AUTO_INCREMENT,
  mentor_id int(11) DEFAULT NULL,
  title varchar(100) NOT NULL,
  description text DEFAULT NULL,
  points int(11) DEFAULT 0,
  deadline date DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  category varchar(50) DEFAULT NULL,
  level enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  status enum('active','inactive') DEFAULT 'active',
  image varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY mentor_id (mentor_id),
  CONSTRAINT missions_ibfk_1 FOREIGN KEY (mentor_id) REFERENCES users (id)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE payments (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  amount decimal(10,2) NOT NULL,
  payment_type enum('course','bootcamp') NOT NULL,
  reference_id int(11) NOT NULL COMMENT 'ID dari course atau bootcamp',
  status enum('pending','success','failed') DEFAULT 'pending',
  payment_method varchar(50) DEFAULT NULL,
  transaction_id varchar(100) DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT payments_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE schedules (
  id int(11) NOT NULL AUTO_INCREMENT,
  course_id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  description text DEFAULT NULL,
  start_time datetime NOT NULL,
  end_time datetime NOT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY course_id (course_id),
  CONSTRAINT schedules_ibfk_1 FOREIGN KEY (course_id) REFERENCES courses (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE transactions (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  course_id int(11) NOT NULL,
  amount int(11) NOT NULL,
  payment_method varchar(50) DEFAULT NULL,
  transaction_id varchar(100) DEFAULT NULL,
  status varchar(50) DEFAULT 'pending',
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY course_id (course_id),
  CONSTRAINT transactions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT transactions_ibfk_2 FOREIGN KEY (course_id) REFERENCES courses (id)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE user_bootcamp (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  bootcamp_id int(11) DEFAULT NULL,
  status enum('registered','ongoing','completed') DEFAULT 'registered',
  payment_status enum('pending','paid','failed') DEFAULT 'pending',
  payment_date timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY bootcamp_id (bootcamp_id),
  CONSTRAINT user_bootcamp_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT user_bootcamp_ibfk_2 FOREIGN KEY (bootcamp_id) REFERENCES bootcamp (id)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE user_courses (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  course_id int(11) DEFAULT NULL,
  status enum('not_started','in_progress','completed') DEFAULT 'not_started',
  progress int(11) DEFAULT 0,
  payment_status enum('pending','paid','failed') DEFAULT 'pending',
  payment_date timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  enrolled_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY course_id (course_id),
  CONSTRAINT user_courses_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT user_courses_ibfk_2 FOREIGN KEY (course_id) REFERENCES courses (id)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE user_missions (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  mission_id int(11) DEFAULT NULL,
  status enum('not_started','in_progress','completed') DEFAULT 'not_started',
  submission text DEFAULT NULL,
  caption varchar(255) DEFAULT NULL,
  file_type varchar(50) DEFAULT NULL,
  submitted_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT current_timestamp(),
  started_at datetime DEFAULT NULL,
  notes text DEFAULT NULL,
  updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_user_missions_user_id (user_id),
  KEY idx_user_missions_mission_id (mission_id),
  CONSTRAINT user_missions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT user_missions_ibfk_2 FOREIGN KEY (mission_id) REFERENCES missions (id)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  full_name varchar(100) NOT NULL,
  role enum('admin','mentor','student') NOT NULL DEFAULT 'student',
  profile_picture varchar(255) DEFAULT NULL,
  bio text DEFAULT NULL,
  region varchar(100) DEFAULT NULL,
  points int(11) DEFAULT 0,
  experience int(11) DEFAULT 0,
  created_at timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- Insert sample users (admin, mentors, and students)
INSERT INTO users (username, password, email, full_name, role, profile_picture, bio, region, points, experience) VALUES
-- Admin
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@educonnect.id', 'Admin EduConnect', 'admin', 'admin-profile.jpg', 'System Administrator', 'Jakarta', 0, 0),

-- Mentors
('mentor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor1@educonnect.id', 'Dr. Andi Wijaya', 'mentor', 'mentor1-profile.jpg', 'Pengajar Matematika dengan pengalaman 10 tahun', 'Bandung', 500, 2500),
('mentor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor2@educonnect.id', 'Budi Santoso, M.Pd', 'mentor', 'mentor2-profile.jpg', 'Guru Bahasa Inggris berpengalaman', 'Surabaya', 450, 2000),
('mentor3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor3@educonnect.id', 'Citra Dewi, S.Si', 'mentor', 'mentor3-profile.jpg', 'Ahli Fisika dan tutor OSN', 'Yogyakarta', 600, 3000),

-- Students
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student1@educonnect.id', 'Dewi Lestari', 'student', 'student1-profile.jpg', 'Siswa SMA kelas 11', 'Jakarta', 150, 800),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student2@educonnect.id', 'Eko Prasetyo', 'student', 'student2-profile.jpg', 'Siswa SMP kelas 8', 'Bandung', 200, 1200),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student3@educonnect.id', 'Fitriani Sari', 'student', 'student3-profile.jpg', 'Siswa SMA kelas 10', 'Surabaya', 300, 1500),
('student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student4@educonnect.id', 'Gunawan Setiawan', 'student', 'student4-profile.jpg', 'Siswa SMP kelas 9', 'Medan', 100, 500),
('student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student5@educonnect.id', 'Hana Putri', 'student', 'student5-profile.jpg', 'Siswa SD kelas 6', 'Bali', 50, 200);

-- Insert sample courses
INSERT INTO courses (mentor_id, title, description, thumbnail, price, type, level, education_level, subject, grade, duration, student_count, rating) VALUES
-- Mathematics courses
(2, 'Matematika Dasar SMP', 'Belajar konsep dasar matematika untuk siswa SMP', 'math-smp.jpg', 0.00, 'free', 'beginner', 'smp', 'Matematika', 7, 30, 150, 4.5),
(2, 'Aljabar Lanjutan SMA', 'Penguasaan konsep aljabar untuk persiapan UTBK', 'aljabar-sma.jpg', 150000.00, 'premium', 'intermediate', 'sma', 'Matematika', 11, 45, 80, 4.8),
(2, 'Geometri Dasar', 'Memahami konsep geometri untuk pemula', 'geometri.jpg', 0.00, 'free', 'beginner', 'smp', 'Matematika', 8, 20, 200, 4.2),

-- English courses
(3, 'Bahasa Inggris Dasar', 'Belajar bahasa Inggris dari dasar', 'english-basic.jpg', 0.00, 'free', 'beginner', 'umum', 'Bahasa Inggris', NULL, 25, 300, 4.3),
(3, 'Persiapan TOEFL', 'Strategi dan latihan untuk tes TOEFL', 'toefl.jpg', 250000.00, 'premium', 'advanced', 'sma', 'Bahasa Inggris', 12, 60, 50, 4.9),
(3, 'Conversation English', 'Belajar percakapan bahasa Inggris sehari-hari', 'conversation.jpg', 100000.00, 'premium', 'intermediate', 'umum', 'Bahasa Inggris', NULL, 30, 120, 4.6),

-- Science courses
(4, 'Fisika Dasar SMA', 'Konsep dasar fisika untuk siswa SMA', 'fisika.jpg', 0.00, 'free', 'beginner', 'sma', 'Fisika', 10, 35, 180, 4.4),
(4, 'Persiapan OSN Fisika', 'Materi dan latihan untuk Olimpiade Fisika', 'osn-fisika.jpg', 300000.00, 'premium', 'advanced', 'sma', 'Fisika', 11, 90, 30, 5.0),
(4, 'Kimia Dasar', 'Pengenalan konsep kimia untuk pemula', 'kimia.jpg', 0.00, 'free', 'beginner', 'smp', 'Kimia', 8, 25, 150, 4.1);

-- Insert sample bootcamps
INSERT INTO bootcamp (mentor_id, title, description, thumbnail, price, duration, start_date, end_date, max_students, current_students, status) VALUES
(2, 'Bootcamp Matematika UTBK', 'Persiapan intensif matematika UTBK selama 8 minggu', 'bootcamp-math.jpg', 1000000.00, 8, '2023-11-01', '2023-12-20', 30, 15, 'upcoming'),
(3, 'Bootcamp IELTS', 'Pelatihan intensif IELTS dengan mentor berpengalaman', 'bootcamp-ielts.jpg', 1500000.00, 6, '2023-11-15', '2023-12-27', 25, 10, 'upcoming'),
(4, 'Bootcamp OSN Fisika', 'Persiapan Olimpiade Sains Nasional bidang Fisika', 'bootcamp-osn.jpg', 2000000.00, 12, '2023-10-20', '2024-01-05', 20, 8, 'ongoing');

-- Insert sample mentoring groups
INSERT INTO mentoring_groups (mentor_id, title, description, max_students, price, schedule, start_date, end_date, status) VALUES
(2, 'Grup Belajar Matematika SMA', 'Grup belajar matematika untuk siswa SMA', 10, 500000.00, 'Setiap Senin dan Kamis, 19:00-20:30', '2023-11-01', '2023-12-21', 'upcoming'),
(3, 'Grup Speaking English', 'Praktik speaking English dengan mentor', 8, 750000.00, 'Setiap Rabu, 18:00-19:30', '2023-11-05', '2023-12-27', 'upcoming'),
(4, 'Grup Diskusi Fisika', 'Diskusi masalah fisika dan pemecahannya', 6, 600000.00, 'Setiap Jumat, 17:00-18:30', '2023-10-27', '2023-12-15', 'ongoing');

-- Insert sample missions
INSERT INTO missions (mentor_id, title, description, points, deadline, category, level, status, image) VALUES
-- Math missions
(2, 'Selesaikan Latihan Aljabar', 'Selesaikan 10 soal aljabar dasar', 50, '2023-11-15', 'Matematika', 'beginner', 'active', 'mission-math.jpg'),
(2, 'Buat Ringkasan Geometri', 'Buat ringkasan konsep geometri dengan contoh', 75, '2023-11-20', 'Matematika', 'intermediate', 'active', 'mission-geometry.jpg'),
(2, 'Selesaikan Soal Cerita', 'Selesaikan 5 soal cerita matematika', 100, '2023-11-30', 'Matematika', 'advanced', 'active', 'mission-problem.jpg'),

-- English missions
(3, 'Tulis Esai Pendek', 'Tulis esai 200 kata tentang hobi Anda', 50, '2023-11-10', 'Bahasa Inggris', 'beginner', 'active', 'mission-essay.jpg'),
(3, 'Rekam Percakapan', 'Rekam percakapan bahasa Inggris dengan teman', 75, '2023-11-25', 'Bahasa Inggris', 'intermediate', 'active', 'mission-speaking.jpg'),
(3, 'Baca Artikel Bahasa Inggris', 'Baca artikel dan buat rangkuman', 60, '2023-11-18', 'Bahasa Inggris', 'intermediate', 'active', 'mission-reading.jpg'),

-- Science missions
(4, 'Eksperimen Sederhana', 'Lakukan eksperimen fisika sederhana dan laporkan', 80, '2023-11-22', 'Sains', 'beginner', 'active', 'mission-experiment.jpg'),
(4, 'Analisis Kasus Fisika', 'Analisis kasus fisika dalam kehidupan sehari-hari', 100, '2023-12-05', 'Sains', 'advanced', 'active', 'mission-analysis.jpg'),
(4, 'Presentasi Konsep Kimia', 'Buat presentasi tentang konsep kimia dasar', 90, '2023-11-28', 'Sains', 'intermediate', 'active', 'mission-presentation.jpg');

-- Insert course enrollments
INSERT INTO user_courses (user_id, course_id, status, progress, payment_status, payment_date) VALUES
-- Student 1 enrollments
(5, 1, 'in_progress', 30, 'paid', '2023-10-01 10:00:00'),
(5, 4, 'in_progress', 15, 'paid', '2023-10-05 11:30:00'),
(5, 7, 'not_started', 0, 'pending', NULL),

-- Student 2 enrollments
(6, 2, 'in_progress', 45, 'paid', '2023-09-15 09:15:00'),
(6, 5, 'completed', 100, 'paid', '2023-08-20 14:00:00'),

-- Student 3 enrollments
(7, 3, 'completed', 100, 'paid', '2023-07-10 08:45:00'),
(7, 6, 'in_progress', 60, 'paid', '2023-09-01 16:30:00'),

-- Student 4 enrollments
(8, 1, 'in_progress', 20, 'paid', '2023-10-10 13:20:00'),
(8, 8, 'not_started', 0, 'pending', NULL),

-- Student 5 enrollments
(9, 4, 'in_progress', 10, 'paid', '2023-09-25 10:45:00'),
(9, 9, 'in_progress', 5, 'paid', '2023-10-05 11:10:00');

-- Insert user missions
INSERT INTO user_missions (user_id, mission_id, status, submission, caption, file_type, submitted_at, started_at, notes) VALUES
-- Student 1 missions
(5, 1, 'completed', 'https://drive.google.com/file/soal-aljabar', 'Saya telah menyelesaikan 10 soal aljabar', 'pdf', '2023-10-20 15:30:00', '2023-10-15 10:00:00', 'Soal nomor 5 cukup menantang'),
(5, 4, 'in_progress', NULL, NULL, NULL, NULL, '2023-10-25 14:00:00', 'Masih dalam proses menulis'),

-- Student 2 missions
(6, 2, 'completed', 'https://drive.google.com/file/ringkasan-geometri', 'Ringkasan konsep geometri', 'pdf', '2023-10-18 16:45:00', '2023-10-10 09:00:00', 'Sudah mencakup semua materi'),
(6, 5, 'in_progress', NULL, NULL, NULL, NULL, '2023-10-22 13:30:00', 'Mencari partner untuk rekaman'),

-- Student 3 missions
(7, 3, 'completed', 'https://drive.google.com/file/soal-cerita', 'Penyelesaian 5 soal cerita', 'pdf', '2023-10-25 11:20:00', '2023-10-15 14:00:00', 'Soal nomor 3 membutuhkan waktu lama'),
(7, 6, 'completed', 'https://drive.google.com/file/rangkuman-artikel', 'Rangkuman artikel teknologi', 'pdf', '2023-10-17 10:15:00', '2023-10-10 08:30:00', 'Artikel tentang AI sangat menarik'),

-- Student 4 missions
(8, 7, 'in_progress', NULL, NULL, NULL, NULL, '2023-10-23 15:00:00', 'Mempersiapkan bahan eksperimen'),
(8, 1, 'completed', 'https://drive.google.com/file/soal-aljabar-2', 'Saya sudah menyelesaikan misi ini', 'pdf', '2023-10-19 14:30:00', '2023-10-12 10:00:00', 'Lumayan sulit tapi menyenangkan'),

-- Student 5 missions
(9, 8, 'not_started', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 4, 'completed', 'https://drive.google.com/file/esai-hobi', 'Esai tentang hobi saya', 'pdf', '2023-10-21 09:45:00', '2023-10-15 13:00:00', 'Menulis tentang hobi bermain musik');

-- Insert community posts
INSERT INTO community_posts (user_id, title, content, category) VALUES
(5, 'Tips Belajar Matematika', 'Bagaimana cara efektif belajar matematika? Saya sering kesulitan memahami konsep aljabar. Ada tips?', 'question'),
(6, 'Pengalaman Ikut Bootcamp IELTS', 'Saya baru saja menyelesaikan bootcamp IELTS di EduConnect. Hasilnya sangat memuaskan! Nilai saya meningkat dari 5.5 ke 7.0 dalam 2 bulan!', 'share'),
(7, 'Diskusi Soal Fisika', 'Ada yang bisa bantu saya memahami soal fisika tentang gerak parabola? Saya bingung dengan konsep waktu tempuhnya.', 'discussion'),
(3, 'Kuis Bahasa Inggris', 'Saya buat kuis kecil untuk melatih vocabulary. Siapa yang mau mencoba?', 'share'),
(4, 'Materi Tambahan OSN Fisika', 'Untuk yang ikut persiapan OSN, saya upload materi tambahan di link berikut...', 'share');

-- Insert comments
INSERT INTO comments (post_id, user_id, content) VALUES
(1, 2, 'Coba fokus pada pemahaman konsep dasar dulu. Jangan langsung ke soal yang sulit.'),
(1, 6, 'Saya biasanya buat mind mapping untuk setiap bab, sangat membantu!'),
(3, 4, 'Untuk gerak parabola, ingat bahwa gerak horizontal dan vertikal independen. Waktu tempuh ditentukan oleh komponen vertikal.'),
(2, 5, 'Wah keren! Boleh share tips belajarnya?'),
(2, 3, 'Selamat! Memang dengan latihan rutin dan strategi yang tepat, skor bisa meningkat signifikan.');

-- Insert course materials
INSERT INTO course_materials (course_id, title, content, type, file_url, sequence) VALUES
-- Math course materials
(1, 'Pengenalan Aljabar', 'Konsep dasar aljabar dan contoh soal', 'video', 'https://youtube.com/video/aljabar-dasar', 1),
(1, 'Latihan Soal Aljabar', '10 soal latihan aljabar dasar', 'quiz', 'https://educonnect.id/quiz/123', 2),
(1, 'Materi Tambahan Aljabar', 'PDF materi tambahan untuk aljabar', 'document', 'https://drive.google.com/file/aljabar-pdf', 3),

-- English course materials
(4, 'Basic Grammar', 'Pengenalan tata bahasa Inggris dasar', 'video', 'https://youtube.com/video/basic-grammar', 1),
(4, 'Vocabulary Builder', 'Daftar kosakata penting untuk pemula', 'document', 'https://drive.google.com/file/vocabulary-pdf', 2),
(4, 'Listening Practice', 'Latihan listening bahasa Inggris', 'video', 'https://youtube.com/video/listening-practice', 3),

-- Physics course materials
(7, 'Konsep Gerak', 'Pengenalan konsep gerak dalam fisika', 'video', 'https://youtube.com/video/konsep-gerak', 1),
(7, 'Latihan Soal Gerak', '5 soal latihan tentang gerak', 'quiz', 'https://educonnect.id/quiz/456', 2),
(7, 'Eksperimen Sederhana', 'Panduan eksperimen gerak parabola', 'document', 'https://drive.google.com/file/eksperimen-pdf', 3);

-- Insert mentoring sessions
INSERT INTO mentoring_sessions (mentor_id, student_id, session_date, session_time, topic, link, status) VALUES
(2, 5, '2023-11-05', '15:00:00', 'Pemecahan Masalah Aljabar', 'https://meet.google.com/abc123', 'scheduled'),
(3, 6, '2023-11-08', '16:30:00', 'Strategi Menjawab TOEFL', 'https://meet.google.com/def456', 'scheduled'),
(4, 7, '2023-10-30', '14:00:00', 'Pembahasan Soal OSN Fisika', 'https://meet.google.com/ghi789', 'completed');

-- Insert mentoring group members
INSERT INTO mentoring_group_members (group_id, student_id, status, payment_status) VALUES
(1, 5, 'active', 'paid'),
(1, 6, 'active', 'paid'),
(2, 7, 'active', 'paid'),
(3, 8, 'registered', 'pending');

-- Insert mentoring group sessions
INSERT INTO mentoring_group_sessions (group_id, session_date, session_time, topic, link) VALUES
(1, '2023-11-06', '19:00:00', 'Pembahasan Soal UTBK Matematika', 'https://meet.google.com/math-group'),
(2, '2023-11-08', '18:00:00', 'Speaking Practice: Daily Activities', 'https://meet.google.com/english-group'),
(3, '2023-10-27', '17:00:00', 'Diskusi Soal Mekanika', 'https://meet.google.com/physics-group');

-- Insert transactions
INSERT INTO transactions (user_id, course_id, amount, payment_method, transaction_id, status) VALUES
(5, 2, 150000, 'bank_transfer', 'TRX001', 'success'),
(6, 5, 250000, 'credit_card', 'TRX002', 'success'),
(7, 6, 100000, 'ewallet', 'TRX003', 'success'),
(8, 8, 300000, 'bank_transfer', 'TRX004', 'pending'),
(5, 6, 100000, 'credit_card', 'TRX005', 'success');

-- Insert payments
INSERT INTO payments (user_id, amount, payment_type, reference_id, status, payment_method, transaction_id) VALUES
(5, 1000000.00, 'bootcamp', 1, 'success', 'bank_transfer', 'PAY001'),
(6, 1500000.00, 'bootcamp', 2, 'pending', 'credit_card', 'PAY002'),
(7, 500000.00, 'course', 6, 'success', 'ewallet', 'PAY003');

-- Insert mentor schedules
INSERT INTO mentor_schedules (mentor_id, session_date, session_time, duration, status) VALUES
(2, '2023-11-12', '10:00:00', 60, 'available'),
(2, '2023-11-12', '14:00:00', 60, 'available'),
(3, '2023-11-15', '13:00:00', 90, 'available'),
(4, '2023-11-10', '09:00:00', 60, 'available'),
(4, '2023-11-17', '16:00:00', 90, 'available');

-- Insert mentoring questions
INSERT INTO mentoring_questions (mentor_id, student_id, topic, question, answer, file) VALUES
(2, 5, 'Aljabar', 'Saya tidak mengerti tentang persamaan kuadrat, bisakah dijelaskan?', 'Persamaan kuadrat adalah persamaan yang memiliki bentuk ax² + bx + c = 0...', NULL),
(3, 6, 'TOEFL', 'Bagaimana strategi untuk meningkatkan skor reading TOEFL?', 'Untuk reading TOEFL, pertama-tama baca pertanyaan dulu sebelum membaca teks...', 'strategi-reading.pdf'),
(4, 7, 'Fisika', 'Saya kesulitan dengan konsep energi kinetik', NULL, NULL);

-- Insert mentoring ratings
INSERT INTO mentoring_ratings (session_id, student_id, mentor_id, overall_rating, communication_rating, knowledge_rating, teaching_rating, review_text) VALUES
(3, 7, 4, 5.0, 5.0, 5.0, 5.0, 'Penjelasan sangat jelas dan membantu saya memahami konsep yang sulit');

-- Insert mentoring rating tags
INSERT INTO mentoring_rating_tags (rating_id, tag) VALUES
(1, 'penjelasan_jelas'),
(1, 'sangat_membantu'),
(1, 'ramah');


