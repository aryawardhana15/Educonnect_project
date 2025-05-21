
## 📘 EduConnect

**EduConnect** adalah platform pembelajaran daring yang mendukung interaksi antara mentor dan peserta melalui kursus, forum komunitas, dan sistem chat internal. Proyek ini dikembangkan untuk mendukung kolaborasi pembelajaran digital, cocok untuk kompetisi inovasi pendidikan maupun pengembangan platform e-learning mandiri.

---

## 📂 Table of Contents

* [Features](#features)
* [Tech Stack](#tech-stack)
* [Installation](#installation)
* [Usage](#usage)
* [Configuration](#configuration)
* [Database Schema](#database-schema)
* [Troubleshooting](#troubleshooting)
* [Contributors](#contributors)
* [License](#license)

---

## 🚀 Features

* 👤 **Manajemen Pengguna** (Admin, Mentor, Peserta)
* 📚 **Manajemen Kursus dan Materi**

  * Buat kursus
  * Tambahkan materi pembelajaran
* 👥 **Dashboard Berbasis Role**

  * Tampilan khusus untuk admin, mentor, dan peserta
* 💬 **Sistem Chat Internal**

  * Komunikasi antara pengguna secara langsung
* 🌐 **Forum Komunitas**

  * Diskusi dan posting dalam komunitas pembelajaran
* 📈 **Statistik Kursus dan Aktivitas**

---

## 🛠 Tech Stack

| Layer        | Teknologi                         |
| ------------ | --------------------------------- |
| **Frontend** | HTML5, CSS3, JavaScript (vanilla) |
| **Backend**  | PHP (Procedural)                  |
| **Database** | MySQL                             |
| **Server**   | Apache / XAMPP / LAMP stack       |

Tidak menggunakan framework seperti Laravel atau frontend framework (React/Vue). Semua kode menggunakan PHP murni.

---

## 📥 Installation

1. **Clone Repository**

   ```bash
   git clone https://github.com/your-username/educonnect.git
   ```

2. **Setup Environment**

   * Pastikan XAMPP/LAMP sudah terinstal.
   * Letakkan folder proyek ke direktori `htdocs/`.

3. **Buat Database**

   * Import file `create_users_table.sql` ke dalam MySQL.
   * Sesuaikan konfigurasi database di `config.php`:

     ```php
     $host = 'localhost';
     $username = 'root';
     $password = '';
     $dbname = 'educonnect';
     ```

4. **Jalankan Proyek**
   Akses di browser melalui:

   ```
   http://localhost/educonnect/
   ```

---

## 💡 Usage

* **Admin** dapat mengelola pengguna dan konten secara menyeluruh.
* **Mentor** bisa membuat kursus dan materi, serta berinteraksi dengan peserta.
* **Peserta** dapat mengikuti kelas, berdiskusi di komunitas, dan menggunakan chat.

---

## ⚙️ Configuration

Konfigurasi utama ada di file `config.php`:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'educonnect';
```

---

## 🧾 Database Schema

Struktur database dapat dilihat pada file SQL berikut:

* `create_users_table.sql`

Berisi pembuatan tabel `users` dan lainnya untuk autentikasi serta manajemen pengguna.

---

## 🛠 Troubleshooting

| Masalah                         | Solusi                                     |
| ------------------------------- | ------------------------------------------ |
| Halaman kosong / error PHP      | Periksa konfigurasi PHP & error log        |
| Gagal koneksi ke database       | Pastikan kredensial di `config.php` sesuai |
| Gagal mengakses melalui browser | Periksa apakah folder berada di `htdocs/`  |

---

## 👤 Contributors

* **Dikembangkan oleh:** *Arya Wardhana*

---

## 📄 MIT License



