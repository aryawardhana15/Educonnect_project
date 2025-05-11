-- Data dummy misi Mission-Based Learning
INSERT INTO missions (mentor_id, title, description, points, deadline)
VALUES
(1, 'Misi EduConnect: Atasi Sampah di Desa',
 'Identifikasi masalah sampah di lingkunganmu, lakukan aksi nyata (misal: kampanye, daur ulang, atau membuat poster edukasi), dokumentasikan proses dan hasilnya.',
 100, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(1, 'Misi EduConnect: Kelola Air Bersih',
 'Pelajari sumber air di sekitarmu, buat poster atau video edukasi tentang pentingnya menjaga air bersih, dan ajak keluarga atau teman untuk ikut aksi hemat air.',
 120, DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
(2, 'Misi EduConnect: Kembangkan Produk Lokal',
 'Cari produk lokal di desamu (makanan, kerajinan, dsb), buat dokumentasi (foto/video) proses pembuatan atau promosi produk tersebut, lalu ceritakan manfaatnya untuk masyarakat.',
 150, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
(2, 'Misi EduConnect: Taman Ramah Anak',
 'Buat desain atau maket taman bermain sederhana yang ramah anak dan ramah lingkungan, lalu presentasikan idemu ke teman atau keluarga.',
 110, DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
(3, 'Misi EduConnect: Energi Terbarukan di Sekitar Kita',
 'Pelajari sumber energi terbarukan di sekitarmu (matahari, angin, air), buat poster atau eksperimen sederhana, lalu upload hasilnya.',
 130, DATE_ADD(CURDATE(), INTERVAL 75 DAY)); 