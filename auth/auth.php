<?php
require_once __DIR__ . '/../db_connect.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function register($username, $email, $password, $full_name, $role = 'student', $region = '') {
        try {
            // Validasi input
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                throw new Exception("Semua field harus diisi");
            }

            // Cek username dan email sudah ada atau belum
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Username atau email sudah terdaftar");
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name, role, region) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $full_name, $role, $region]);
            
            if ($stmt->rowCount() > 0) {
                return $this->db->lastInsertId();
            } else {
                throw new Exception("Gagal mendaftarkan user");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function login($email, $password, $role) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception("Gagal login: " . $e->getMessage());
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($user_id, $data) {
        try {
            $allowed_fields = ['full_name', 'email'];
            $updates = [];
            $values = [];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowed_fields)) {
                    $updates[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($updates)) {
                throw new Exception("Tidak ada field yang valid untuk diupdate");
            }

            $values[] = $user_id;
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verifikasi password lama
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Password saat ini tidak valid");
            }

            // Update password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashed_password, $user_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['role'] === $role;
    }
}

// Inisialisasi session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 