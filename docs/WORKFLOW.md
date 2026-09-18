# Klasify - Workflow Documentation

Dokumen ini menjelaskan alur kerja (workflow), matriks hak akses, dan transisi status dari aplikasi Klasify. Klasify adalah sistem pengelolaan kas kelas yang menghubungkan tiga peran utama: Guru, Bendahara, dan Siswa.

## 1. Matriks Hak Akses (Role & Permission Matrix)

Berikut adalah tabel otoritas untuk setiap peran dalam sistem:

| Fitur / Modul         | Guru (Wali Kelas)                                 | Bendahara                                       | Siswa                                  |
| --------------------- | ------------------------------------------------- | ----------------------------------------------- | -------------------------------------- |
| **Dashboard**         | Lihat statistik & grafik kelasnya sendiri          | Lihat statistik & grafik global semua kelas     | Lihat statistik tagihan & denda sendiri |
| **Data Kelas**        | Read-Only                                         | Read-Only                                       | -                                      |
| **Data Siswa**        | CRUD (Khusus kelasnya) & Lihat Data Orang Tua     | Read-Only (Semua kelas, tidak bisa lihat ortu)  | -                                      |
| **Data Iuran**        | CRUD (Khusus kelasnya)                            | Read-Only (Semua kelas)                         | Read-Only (Iuran di kelasnya)          |
| **Transaksi**         | Approve/Reject, Input Cash, Read (Kelasnya)      | Approve/Reject, Input Cash, Read (Semua)      | Upload Bukti Bayar, Read (Miliknya)   |
| **Pengeluaran**       | Approve/Reject, Read (Kelasnya)                   | Ajukan Dana, Read (Semua)                      | -                                      |
| **Keterlambatan**     | Cek Manual, Read (Kelasnya)                       | Read (Semua)                                   | Read (Miliknya)                        |
| **Laporan**           | Lihat & Export (Khusus kelasnya)                 | Lihat & Export (Semua kelas)                    | -                                      |
| **Notifikasi**        | Kirim Manual (Siswa/Kelas), Terima, Kelola       | Kirim Manual (Siswa/Kelas), Terima, Kelola     | Terima, Kelola                         |
| **Pengaturan**        | Set Denda & Maks Denda                            | -                                               | -                                      |
| **Profile**           | Edit diri sendiri                                 | Edit diri sendiri                               | Edit diri sendiri                      |

## 2. State Transition (Transisi Status)

Tabel perubahan status pada entitas utama:

### 2.1. Transaksi (Pembayaran Iuran)
| Status Awal | Aksi                  | Status Akhir | Pelaku                |
| ----------- | --------------------- | ------------ | --------------------- |
| -           | Upload Bukti Bayar    | `pending`    | Siswa                 |
| -           | Input Cash (Langsung) | `confirmed`  | Guru / Bendahara      |
| -           | Input Cash (Pending)  | `pending`    | Guru / Bendahara      |
| `pending`   | Approve               | `confirmed`  | Guru / Bendahara      |
| `pending`   | Reject                | `rejected`   | Guru / Bendahara      |

### 2.2. Pengeluaran (Pengajuan Dana)
| Status Awal | Aksi    | Status Akhir | Pelaku      |
| ----------- | ------- | ------------ | ----------- |
| -           | Ajukan  | `pending`    | Bendahara   |
| `pending`   | Approve | `approved`   | Guru        |
| `pending`   | Reject  | `rejected`   | Guru        |

### 2.3. Keterlambatan (Denda)
| Status Awal     | Aksi                                       | Status Akhir          | Pelaku             |
| --------------- | ------------------------------------------ | --------------------- | ------------------ |
| -               | Cek Manual / Cron Job                      | `belum_bayar`        | Sistem / Guru      |
| `belum_bayar`   | Approve Transaksi Iuran terkait            | `sudah_bayar_denda`  | Guru / Bendahara   |

## 3. Alur Master Data

### 3.1. Manajemen Kelas
- Hanya Guru yang dapat membuat, mengubah, dan menghapus kelas (via halaman khusus yang tidak bisa diakses Bendahara/Siswa).
- Bendahara dan Siswa hanya dapat melihat daftar kelas (Read-Only).
- Saat membuat kelas, Guru dapat menetapkan dirinya atau Guru lain sebagai Wali Kelas.
- Data kelas digunakan sebagai *scope* (batasan) untuk data Siswa, Iuran, Transaksi, Pengeluaran, dan Laporan bagi Guru.

### 3.2. Manajemen Siswa
- Hanya Guru yang dapat menambah, mengubah, dan menghapus data siswa.
- Bendahara dapat melihat semua siswa di semua kelas (tanpa data orang tua).
- Guru hanya dapat melihat siswa yang berada di kelas yang dia waliki (Wali Kelas). Data orang tua siswa hanya visible untuk Guru.
- Saat menambah siswa, sistem otomatis membuat akun User dengan role "Siswa" beserta data detail siswa (NIS, NISN, Orang Tua, dll).
- Saat klik nama siswa di tabel, akan diarahkan ke Halaman Detail Siswa yang menampilkan profil lengkap, Progress Bar tagihan iuran, riwayat pembayaran, dan riwayat keterlambatan.

## 4. Alur Iuran (Tagihan)

- Guru membuat iuran bulanan untuk kelas tertentu (misal: Iuran September 2026 untuk XII RPL 1).
- Iuran memiliki tanggal jatuh tempo, nominal, dan status aktif/nonaktif.
- Siswa hanya dapat membayar iuran yang berstatus aktif.
- Jika siswa mencoba membayar iuran yang sudah lunas atau sedang pending, sistem akan menolak (mencegah pembayaran ganda).

## 5. Alur Transaksi (Pembayaran Iuran)

### 5.1. Pembayaran oleh Siswa (Transfer/QRIS)
1. Siswa masuk ke halaman Iuran dan melihat tagihan berstatus "Belum Bayar".
2. Siswa mengklik "Bayar Sekarang" pada iuran terkait.
3. Siswa mengisi tanggal bayar, metode (Transfer/QRIS), dan mengunggah link foto bukti pembayaran.
4. Status transaksi berubah menjadi "Pending".
5. Sistem otomatis mengirim notifikasi ke Guru dan Bendahara bahwa ada pembayaran baru yang butuh dikonfirmasi.
6. Guru atau Bendahara mengecek bukti pembayaran dan melakukan "Approve" atau "Reject".
   - Jika Approve: Status berubah menjadi "Confirmed" (Lunas). Sistem mengirim notifikasi sukses ke siswa. Status keterlambatan (jika ada) berubah menjadi "Sudah Bayar Denda".
   - Jika Reject: Status berubah menjadi "Rejected". Sistem mengirim notifikasi penolakan ke siswa.

### 5.2. Input Pembayaran oleh Bendahara/Guru (Cash)
1. Bendahara atau Guru mengklik "Input Pembayaran" di halaman Transaksi.
2. Bendahara/Guru memilih nama siswa dari dropdown.
3. Sistem otomatis memfilter iuran yang belum dibayar oleh siswa tersebut.
4. Bendahara/Guru memilih iuran, metode (Cash), dan status transaksi.
   - Jika status dipilih "Langsung Lunas (Confirm)", transaksi langsung berstatus "Confirmed".
   - Jika status dipilih "Pending", transaksi akan menunggu konfirmasi pihak lain.

## 6. Alur Pengeluaran Dana

1. Bendahara mengajukan pengeluaran dana melalui halaman Pengeluaran.
2. Bendahara mengisi judul, kategori, nominal, tanggal, deskripsi, dan opsional link foto bukti/nota.
3. Status pengeluaran berubah menjadi "Pending".
4. Sistem otomatis mengirim notifikasi ke Guru bahwa ada pengajuan dana baru.
5. Guru mengecek pengajuan tersebut di halaman Pengeluaran dan melakukan "Approve" atau "Reject".
   - Jika Approve: Status berubah menjadi "Approved". Nominal pengeluaran masuk ke perhitungan kas. Sistem mengirim notifikasi ke Bendahara.
   - Jika Reject: Status berubah menjadi "Rejected". Sistem mengirim notifikasi ke Bendahara.

## 7. Alur Keterlambatan dan Denda

1. Sistem menjalankan pengecekan keterlambatan secara manual oleh Guru melalui tombol "Cek Keterlambatan Manual" atau dapat dijadwalkan via Cron Job backend.
2. Sistem akan mencari semua iuran aktif yang sudah lewat tanggal jatuh tempo, namun siswa belum membayarnya (status belum bayar).
3. Sistem menghitung jumlah hari telat dari tanggal jatuh tempo.
4. Denda dihitung berdasarkan nominal per hari dan batas maksimal denda yang diatur oleh Guru di halaman Pengaturan.
5. Sistem membuat record di tabel keterlambatan dan mengirim notifikasi peringatan ke siswa.
6. Jika siswa kemudian melakukan pembayaran dan Guru/Bendahara mengkonfirmasi (Approve) transaksinya, sistem otomatis mengubah status keterlambatan siswa tersebut menjadi "Sudah Bayar Denda" (Lunas).

## 8. Alur Laporan

- Guru dan Bendahara dapat mengakses halaman Laporan.
- Guru hanya dapat melihat laporan kas khusus untuk kelas yang dia waliki (dropdown kelas di-hide dan auto-filled).
- Bendahara dapat melihat laporan kas gabungan semua kelas atau memfilter kelas tertentu.
- Laporan dapat difilter berdasarkan Bulan dan Tahun.
- Laporan menampilkan:
  - Total Pemasukan (dari transaksi berstatus Confirmed).
  - Total Pengeluaran (dari pengeluaran berstatus Approved).
  - Saldo Akhir.
  - Rasio Keuangan (Chart Donat).
  - Detail list transaksi masuk (beserta avatar siswa).
  - Detail list pengeluaran keluar (beserta avatar bendahara pengaju).
- Pengguna dapat mengunduh laporan dalam format PDF atau Excel. Sistem akan menghasilkan file dan mengunduhnya secara otomatis.

## 9. Alur Notifikasi

Notifikasi dibagi menjadi dua jenis berdasarkan pemicunya:

### 9.1. Notifikasi Otomatis (Sistem)
Sistem otomatis mengirim notifikasi saat event tertentu terjadi:
- **Pembayaran Baru**: Saat siswa mengunggah bukti bayar (status pending), Guru dan Bendahara mendapat notifikasi.
- **Konfirmasi Pembayaran**: Saat Guru/Bendahara Approve/Reject, siswa mendapat notifikasi.
- **Pengajuan Dana Baru**: Saat Bendahara mengajukan dana, Guru mendapat notifikasi.
- **Konfirmasi Dana**: Saat Guru Approve/Reject pengajuan dana, Bendahara mendapat notifikasi.
- **Keterlambatan**: Saat sistem mendeteksi siswa telat bayar, siswa mendapat notifikasi peringatan.

### 9.2. Notifikasi Manual
Guru dan Bendahara dapat mengirim pesan manual secara langsung:
- **Kirim ke Satu Siswa**: Memilih satu siswa spesifik untuk dikirimi pesan.
- **Kirim ke Satu Kelas (Broadcast)**: Memilih satu kelas untuk mengirim broadcast pesan ke semua siswa di kelas tersebut.

### 9.3. Manajemen Notifikasi
- Setiap user dapat melihat daftar notifikasi di dropdown header atau di halaman khusus Notifikasi.
- User dapat menandai notifikasi sebagai "Sudah Dibaca" satu per satu atau semua sekaligus.
- User dapat menghapus notifikasi (satu per satu, massal via checkbox, atau hapus semua).
- Notifikasi menampilkan avatar dan nama pengirim (beserta role pengirim).

## 10. Alur Manajemen Profile & Pengaturan

### 10.1. Profile User
- Setiap user (Guru, Bendahara, Siswa) dapat mengakses halaman Profile.
- User dapat mengubah Nama, Email, No HP, dan URL Foto Profil.
- User dapat mengubah Password (dengan konfirmasi password lama).

### 10.2. Pengaturan Sistem (Khusus Guru)
- Guru dapat mengakses halaman Pengaturan.
- Guru dapat mengatur nominal Denda per Hari dan Maksimal Denda.
- Perubahan ini akan berlaku untuk perhitungan keterlambatan berikutnya saat tombol "Cek Keterlambatan" dijalankan.