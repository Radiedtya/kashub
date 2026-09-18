# 📋 ALUR APLIKASI KAS KELAS (KasHub) - PEMBAGIAN AKSES PER ROLE

---

## 1. 👨‍🏫 ROLE GURU (Admin Utama)

Guru adalah **pemegang kendali tertinggi** dalam aplikasi. Guru dapat mengelola semua data master dan melakukan approve.

### ✅ Yang BISA Dilakukan Guru

| No | Fitur | Keterangan |
|----|-------|------------|
| 1 | **Dashboard Guru** | Melihat statistik total siswa, total kas, siswa telat, iuran aktif, grafik pembayaran & pengeluaran, daftar siswa telat. |
| 2 | **Mengelola Kelas** | Tambah, edit, hapus, dan melihat detail kelas. |
| 3 | **Mengelola Siswa** | Tambah, edit, hapus, dan melihat detail siswa (termasuk pindah kelas). |
| 4 | **Mengelola Iuran** | Tambah, edit, hapus, dan melihat status siswa per iuran. |
| 5 | **Melihat Transaksi** | Melihat semua transaksi, filter berdasarkan status, siswa, kelas, dll. |
| 6 | **Konfirmasi Transaksi** | Mengkonfirmasi (setujui) atau menolak transaksi pending. |
| 7 | **Melihat Pengeluaran** | Melihat semua pengeluaran yang diajukan bendahara. |
| 8 | **Setujui/Tolak Pengeluaran** | Menyetujui atau menolak pengajuan pengeluaran dari bendahara. |
| 9 | **Melihat Keterlambatan** | Melihat daftar siswa yang telat bayar iuran, filter kelas & status. |
| 10 | **Kirim Notifikasi** | Mengirim notifikasi manual ke siswa tertentu atau ke seluruh siswa di kelas. |
| 11 | **Laporan** | Melihat laporan kas, per bulan, per siswa, per kelas, dan export PDF/Excel. |
| 12 | **Hapus Transaksi** | Menghapus transaksi yang masih status pending. |

### ❌ Yang TIDAK BISA Dilakukan Guru

| No | Fitur | Alasan |
|----|-------|--------|
| 1 | **Membayar Iuran** | Hanya siswa dan bendahara yang bisa bayar iuran. |
| 2 | **Mengajukan Pengeluaran** | Hanya bendahara yang bisa mengajukan pengeluaran. |
| 3 | **Akses Dashboard Bendahara/Siswa** | Dashboard sudah disesuaikan per role. |
| 4 | **Mengelola Data Bendahara** | Tidak ada fitur CRUD untuk bendahara, hanya bisa membuat user baru dengan role siswa/guru di backend. |
| 5 | **Menghapus Transaksi yang Sudah Confirmed** | Hanya transaksi pending yang bisa dihapus. |

---

## 2. 💰 ROLE BENDAHARA (Pengelola Keuangan)

Bendahara bertugas mencatat pembayaran, mengajukan pengeluaran, dan melakukan konfirmasi.

### ✅ Yang BISA Dilakukan Bendahara

| No | Fitur | Keterangan |
|----|-------|------------|
| 1 | **Dashboard Bendahara** | Melihat total kas, transaksi pending, pengeluaran pending, pemasukan & pengeluaran bulan ini, grafik kas. |
| 2 | **Melihat Data Kelas** | Hanya baca (read only) – melihat daftar kelas dan detail. |
| 3 | **Melihat Data Siswa** | Hanya baca – melihat daftar siswa, detail siswa. |
| 4 | **Melihat Data Iuran** | Hanya baca – melihat daftar iuran per kelas. |
| 5 | **Melakukan Pembayaran Iuran** | Bisa bayar iuran untuk siswa (atas nama siswa) atau dirinya sendiri. |
| 6 | **Melihat Semua Transaksi** | Melihat semua transaksi, filter berdasarkan status, siswa, kelas. |
| 7 | **Konfirmasi Transaksi** | Mengkonfirmasi (setujui) atau menolak transaksi pending. |
| 8 | **Mengajukan Pengeluaran** | Tambah pengeluaran baru, edit sebelum disetujui. |
| 9 | **Melihat Pengeluaran** | Melihat semua pengeluaran yang diajukan (termasuk status). |
| 10 | **Melihat Keterlambatan** | Melihat daftar siswa telat bayar (hanya baca). |
| 11 | **Kirim Notifikasi** | Mengirim notifikasi manual ke siswa tertentu atau ke seluruh siswa di kelas. |
| 12 | **Laporan** | Melihat laporan kas, per bulan, per siswa, per kelas, dan export PDF/Excel. |

### ❌ Yang TIDAK BISA Dilakukan Bendahara

| No | Fitur | Alasan |
|----|-------|--------|
| 1 | **Mengelola Kelas** | Tidak bisa tambah, edit, atau hapus kelas. |
| 2 | **Mengelola Siswa** | Tidak bisa tambah, edit, atau hapus siswa. |
| 3 | **Mengelola Iuran** | Tidak bisa tambah, edit, atau hapus iuran. |
| 4 | **Menyetujui Pengeluaran** | Hanya guru yang bisa menyetujui atau menolak pengeluaran. |
| 5 | **Menghapus Transaksi** | Hanya guru yang bisa menghapus transaksi. |
| 6 | **Akses Dashboard Guru/Siswa** | Dashboard sudah disesuaikan per role. |

---

## 3. 🧑‍🎓 ROLE SISWA (Anggota Kelas)

Siswa hanya bisa mengelola data pribadi dan melakukan pembayaran.

### ✅ Yang BISA Dilakukan Siswa

| No | Fitur | Keterangan |
|----|-------|------------|
| 1 | **Dashboard Siswa** | Melihat profil sendiri, statistik total transaksi, total bayar, transaksi pending, total keterlambatan, denda, status bayar bulan ini, dan riwayat transaksi. |
| 2 | **Melihat Data Iuran** | Hanya baca – melihat daftar iuran di kelasnya. |
| 3 | **Melihat Transaksi Sendiri** | Melihat riwayat transaksi sendiri (filter, detail). |
| 4 | **Melakukan Pembayaran Iuran** | Bayar iuran yang tersedia (pilih iuran, upload bukti jika diperlukan). |
| 5 | **Melihat Pengeluaran** | Hanya baca – melihat pengeluaran di kelasnya. |
| 6 | **Melihat Keterlambatan Sendiri** | Melihat data keterlambatan sendiri (status, denda, dll). |
| 7 | **Melihat Notifikasi** | Melihat notifikasi yang dikirim ke dirinya. |

### ❌ Yang TIDAK BISA Dilakukan Siswa

| No | Fitur | Alasan |
|----|-------|--------|
| 1 | **Mengelola Kelas** | Tidak bisa tambah, edit, hapus kelas. |
| 2 | **Mengelola Siswa** | Tidak bisa tambah, edit, hapus siswa. |
| 3 | **Mengelola Iuran** | Tidak bisa tambah, edit, hapus iuran. |
| 4 | **Mengkonfirmasi Transaksi** | Hanya guru dan bendahara yang bisa konfirmasi. |
| 5 | **Mengajukan Pengeluaran** | Hanya bendahara yang bisa mengajukan. |
| 6 | **Menyetujui Pengeluaran** | Hanya guru yang bisa menyetujui. |
| 7 | **Menghapus Transaksi** | Hanya guru yang bisa menghapus transaksi. |
| 8 | **Melihat Laporan** | Laporan hanya untuk guru dan bendahara. |
| 9 | **Melihat Data Siswa Lain** | Tidak bisa melihat data pribadi siswa lain (hanya data sendiri). |
| 10 | **Akses Dashboard Guru/Bendahara** | Dashboard sudah disesuaikan per role. |

---

## 4. 📊 TABEL REKAP AKSES PER ROLE

| No | Fitur | Guru | Bendahara | Siswa |
|----|-------|:----:|:---------:|:-----:|
| 1 | Dashboard | ✅ | ✅ | ✅ |
| 2 | Lihat Kelas | ✅ | ✅ | ❌ |
| 3 | Kelola Kelas (CRUD) | ✅ | ❌ | ❌ |
| 4 | Lihat Siswa | ✅ | ✅ | ❌ (hanya diri sendiri) |
| 5 | Kelola Siswa (CRUD) | ✅ | ❌ | ❌ |
| 6 | Lihat Iuran | ✅ | ✅ | ✅ |
| 7 | Kelola Iuran (CRUD) | ✅ | ❌ | ❌ |
| 8 | Lihat Transaksi | ✅ | ✅ | ✅ (hanya sendiri) |
| 9 | Bayar Iuran | ❌ | ✅ | ✅ |
| 10 | Konfirmasi Transaksi | ✅ | ✅ | ❌ |
| 11 | Lihat Pengeluaran | ✅ | ✅ | ✅ |
| 12 | Ajukan Pengeluaran | ❌ | ✅ | ❌ |
| 13 | Setujui Pengeluaran | ✅ | ❌ | ❌ |
| 14 | Lihat Keterlambatan | ✅ | ✅ | ✅ (hanya sendiri) |
| 15 | Kirim Notifikasi | ✅ | ✅ | ❌ |
| 16 | Lihat Laporan | ✅ | ✅ | ❌ |
| 17 | Export Laporan | ✅ | ✅ | ❌ |
| 18 | Hapus Transaksi | ✅ | ❌ | ❌ |
