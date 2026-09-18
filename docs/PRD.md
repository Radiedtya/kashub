# Klasify - Product Requirements Document (PRD)

Dokumen ini menjelaskan visi, target pengguna, kebutuhan fungsional, aturan bisnis, dan metrik kesuksesan dari aplikasi Klasify.

## 1. Product Overview

**Visi Produk:**
Klasify adalah sistem pengelolaan keuangan kelas (Kas Kelas) yang dirancang untuk meningkatkan transparansi, akuntabilitas, dan efisiensi pengelolaan dana kelas di lingkungan sekolah. Aplikasi ini menggantikan metode manual (buku catatan atau Excel seadanya) dengan sistem digital terpusat yang menghubungkan Guru, Bendahara, dan Siswa.

**Masalah yang Dipecahkan:**
- Sulitnya melacak siapa siswa yang belum membayar iuran.
- Kurangnya transparansi dalam pengeluaran dana kelas.
- Proses pencatatan transaksi tunai yang rentan hilang atau salah catat.
- Kesulitan membuat laporan kas bulanan atau tahunan secara cepat dan akurat.

## 2. Target Pengguna (User Personas)

| Persona | Deskripsi | Kebutuhan Utama | Tingkat Teknologi |
| --- | --- | --- | --- |
| **Guru (Wali Kelas)** | Penanggung jawab utama keuangan kelas. | Dashboard ringkasan, approve pengeluaran, konfirmasi pembayaran, laporan kelas. | Menengah - Mahir |
| **Bendahara** | Eksekutor harian pengelolaan kas. | Input pengeluaran, terima tunai, konfirmasi transfer, laporan global. | Menengah - Mahir |
| **Siswa** | Pembayar iuran. | Lihat tagihan, upload bukti bayar, lacak status, lihat denda. | Dasar - Menengah |

## 3. Modul dan Prioritas Fitur

| Prioritas | Modul | Fitur Utama | Status Pengembangan |
| --- | --- | --- | --- |
| **P0 (Wajib)** | Autentikasi | Login, Logout, Token Management | Selesai |
| **P0 (Wajib)** | Dashboard | Ringkasan statistik & grafik per role | Selesai |
| **P0 (Wajib)** | Master Data | CRUD Kelas, Siswa, Iuran (Scoped per Guru) | Selesai |
| **P0 (Wajib)** | Transaksi | Upload bukti bayar, Approve/Reject, Input Cash | Selesai |
| **P0 (Wajib)** | Pengeluaran | Ajuan dana, Approve/Reject | Selesai |
| **P0 (Wajib)** | Keterlambatan | Cek manual, hitung denda, auto-lunas | Selesai |
| **P0 (Wajib)** | Laporan | Filter bulan/tahun, Export PDF/Excel | Selesai |
| **P0 (Wajib)** | Notifikasi | Notif otomatis & manual, kelola notif | Selesai |
| **P1 (Penting)** | Pengaturan | Set denda per hari & maks denda | Selesai |
| **P1 (Penting)** | Profile | Edit profile & ganti password | Selesai |
| **P2 (Opsional)** | Lainnya | Import Excel, Cetak Kwitansi, Dark Mode, WA Blast | Roadmap |

## 4. Functional Requirements (Detail per Modul)

### 4.1. Autentikasi & Otorisasi
- Sistem login menggunakan Email dan Password.
- Manajemen sesi menggunakan Token (Laravel Sanctum).
- Pembagian hak akses (Role-Based Access Control) berdasarkan Role (Guru, Bendahara, Siswa).
- Sistem auto-logout jika token expired (401 Unauthorized).

### 4.2. Manajemen Profile
- Setiap user dapat melihat dan mengedit data diri (Nama, Email, No HP, Foto Profil via URL).
- Setiap user dapat mengubah password (dengan validasi password lama).
- Update profil tidak boleh menghilangkan role atau kelas_id user.

### 4.3. Manajemen Master Data
- **Data Kelas**: CRUD oleh Guru. Data meliputi nama kelas, tahun ajaran, dan wali kelas.
- **Data Siswa**: CRUD oleh Guru. Data meliputi NIS, NISN, tempat/tanggal lahir, alamat, data orang tua. Saat tambah siswa, otomatis generate akun User. Hapus siswa akan menghapus akun User terkait (Cascade).
- **Data Iuran**: CRUD oleh Guru. Data meliputi periode (bulan/tahun), nominal, jatuh tempo, dan status aktif. Sistem mencegah iuran ganda (kelas + bulan + tahun yang sama).
- **Scoping Wali Kelas**: Guru hanya melihat/mengelola data master di kelas yang dia waliki. Bendahara melihat semua kelas.

### 4.4. Modul Transaksi (Pembayaran)
- **Siswa**: Melihat list iuran aktif, upload bukti bayar (status pending). Tidak bisa membayar iuran yang sudah pending/confirmed.
- **Guru/Bendahara**: Melihat list transaksi, filter berdasarkan status. Approve/Reject transaksi pending. Input pembayaran tunai (cash) secara langsung (bisa set langsung confirmed atau pending).
- Sistem mengirim notifikasi otomatis ke Guru/Bendahara saat siswa upload bukti, dan ke siswa saat transaksi di-approve/reject.

### 4.5. Modul Pengeluaran
- **Bendahara**: Mengajukan pengeluaran (judul, nominal, tanggal, kategori, bukti foto).
- **Guru**: Melihat list pengajuan. Approve/Reject pengajuan.
- Sistem mencatat siapa pengaju (created_by) dan siapa penyetuju (approved_by).
- Sistem mengirim notifikasi otomatis ke Guru saat diajukan, dan ke Bendahara saat di-approve/reject.

### 4.6. Modul Keterlambatan & Denda
- Sistem/Cron Job/Guru dapat memicu pengecekan keterlambatan.
- Sistem menghitung hari telat dari jatuh tempo.
- Denda dihitung otomatis berdasarkan pengaturan global (Denda per hari & Maksimal denda).
- Status keterlambatan otomatis berubah menjadi "Sudah Bayar Denda" jika transaksi iuran terkait di-approve oleh Guru/Bendahara.
- Siswa mendapat notifikasi peringatan saat terdeteksi telat.

### 4.7. Modul Laporan
- Filter laporan berdasarkan Bulan dan Tahun.
- Guru melihat laporan kelasnya sendiri (dropdown kelas di-hide dan auto-filled). Bendahara melihat semua kelas atau bisa filter.
- Menampilkan Total Pemasukan, Total Pengeluaran, dan Saldo.
- Menampilkan rasio keuangan (Chart Donat).
- Menampilkan detail list transaksi masuk (beserta avatar siswa) dan pengeluaran keluar (beserta avatar bendahara).
- Export laporan ke format PDF dan Excel.

### 4.8. Modul Notifikasi
- **Otomatis**: Mengirim notifikasi saat ada transaksi baru, konfirmasi transaksi, pengajuan dana, konfirmasi dana, dan peringatan keterlambatan.
- **Manual**: Guru/Bendahara dapat mengirim broadcast notifikasi ke 1 siswa atau 1 kelas penuh.
- **Kelola**: User dapat menandai dibaca, hapus notifikasi (satu per satu, massal, atau hapus semua).
- Notifikasi menampilkan avatar, nama, dan role pengirim.

### 4.9. Modul Pengaturan (Settings)
- Khusus Guru.
- Mengatur nominal Denda per Hari dan Batas Maksimal Denda.
- Perubahan ini akan berlaku untuk perhitungan keterlambatan berikutnya.

## 5. Business Rules (Aturan Bisnis)

| ID | Aturan Bisnis | Modul Terkait |
| --- | --- | --- |
| BR-01 | Hanya Guru yang dapat menambah/edit/hapus data master (Kelas, Siswa, Iuran). | Master Data |
| BR-02 | Guru hanya dapat mengelola data master di kelas yang dia waliki. | Master Data, Scoping |
| BR-03 | Data Orang Tua siswa hanya dapat dilihat oleh Guru, tidak untuk Bendahara. | Master Siswa |
| BR-04 | Siswa tidak dapat membayar iuran yang sama dua kali (mencegah pembayaran ganda). | Transaksi |
| BR-05 | Hanya Bendahara yang dapat mengajukan pengeluaran dana. | Pengeluaran |
| BR-06 | Hanya Guru yang dapat menyetujui (approve) pengajuan pengeluaran. | Pengeluaran |
| BR-07 | Denda keterlambatan dihitung otomatis berdasarkan jatuh tempo iuran. | Keterlambatan |
| BR-08 | Status keterlambatan otomatis lunas jika transaksi iuran terkait dikonfirmasi. | Keterlambatan, Transaksi |
| BR-09 | Pengajuan dana yang sudah diproses (approved/rejected) tidak dapat diubah atau dihapus. | Pengeluaran |
| BR-10 | Transaksi yang sudah dikonfirmasi (confirmed) tidak dapat diubah atau dihapus. | Transaksi |
| BR-11 | Laporan kas hanya menampilkan transaksi berstatus Confirmed dan pengeluaran berstatus Approved. | Laporan |

## 6. Non-Functional Requirements

### 6.1. Performance
- Waktu muat halaman (Load Time) kurang dari 3 detik pada koneksi 4G.
- API response time kurang dari 500ms untuk operasi CRUD standar.
- Frontend menggunakan Lazy Load untuk gambar dan Code Splitting untuk mempercepat initial load.

### 6.2. Security
- Password di-hash menggunakan Bcrypt.
- API dilindungi oleh Sanctum Token Middleware.
- Proteksi terhadap Cross-Site Scripting (XSS) dan SQL Injection (ditangani by default oleh Laravel dan Vue).
- Validasi input dilakukan di sisi Frontend dan Backend.
- RESTRICT/SET NULL Foreign Key diatur di level database migration untuk menjaga integritas data.

### 6.3. Usability & UI/UX
- Desain antarmuka mengadopsi tema "Clean & Corporate" (warna dasar putih/zinc, aksen biru gelap).
- Fully Responsive (Mobile, Tablet, Desktop). Sidebar berubah menjadi Drawer di mode Mobile.
- Penggunaan animasi transisi halus (Anime.js) dan Toast Notification (Vue3 Toastify) untuk feedback user.
- Penggunaan Chart.js untuk visualisasi data dashboard dan laporan.
- Penggunaan Count-Up animation untuk angka statistik.

### 6.4. Tech Stack
- **Frontend**: Vue 3 (Composition API), Vite, Tailwind CSS, Pinia (State Management), Vue Router, Anime.js, Chart.js, Headless UI, Heroicons.
- **Backend**: Laravel 11, MySQL/SQLite, Laravel Sanctum, Eloquent ORM.
- **Tools**: Git, Postman/Bruno (API Testing), Vercel/Netlify (Frontend Deployment), VPS/Shared Hosting (Backend Deployment).

## 7. Success Metrics (Metrik Kesuksesan)

| Metrik | Target | Cara Pengukuran |
| --- | --- | --- |
| **Adopsi User** | 100% siswa aktif login dalam 1 bulan pertama. | Hitung user yang login via API. |
| **Transaksi Digital** | 80% pembayaran iuran via upload bukti (Transfer/QRIS). | Bandingkan jumlah transaksi status pending vs confirmed cash. |
| **Transparansi Pengeluaran** | 100% pengeluaran diajukan via sistem (bukan cash di luar). | Cek semua record pengeluaran memiliki status approved. |
| **Akurasi Laporan** | 0 selisah antara kas fisik dan saldo di sistem. | Audit manual bulanan. |
| **Response Time API** | < 500ms pada beban normal. | Monitoring via Laravel Telescope / Postman. |
| **Uptime Server** | 99.9% (kurang dari 8 jam downtime per tahun). | Monitoring server (UptimeRobot). |

## 8. Future Roadmap (Out of Scope for V1)

- Integrasi WhatsApp Gateway untuk blast notifikasi tagihan ke Orang Tua/Wali Siswa.
- Fitur Import Data Siswa via Excel/CSV.
- Fitur Cetak Kwitansi Pembayaran langsung dari sistem (PDF).
- Dark Mode untuk antarmuka.
- Fitur Naik Kelas / Akhir Tahun Ajaran (Archiving).
- PWA (Progressive Web App) agar bisa diinstall di HP siswa.